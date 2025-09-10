<?php

namespace App\Http\Controllers\Document;

use App\Http\Controllers\Controller;
use App\Http\Requests\Document_Type\MayorClearanceRequest;
use App\Http\Requests\Document_Type\MpocRequest;
use App\Models\DocumentReview;
use App\Models\User;
use App\Services\Document\DocumentIdGenerator;
use App\Services\Document\DocumentWorkflowService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DocumentController extends Controller
{
    private array $documents = [
        [
            'title' => "Mayor's Clearance",
            'file' => 'Mayors_Clearance.docx',
        ],
        [
            'title' => 'Municipal Peace and Order Council',
            'file' => 'MPOC_Sample.docx',
        ],
    ];

    public function __construct(
        private DocumentWorkflowService $workflowService
    ) {}

    public function index()
    {
        $user = Auth::user();

        $pendingCount = DocumentReview::where('assigned_to', $user->id)
            ->where('status', 'pending')
            ->whereNull('downloaded_at')
            ->count();

        return view('documents.index', compact('user', 'pendingCount'))
            ->with('documents', $this->documents);
    }

    public function form($file)
    {
        $docInfo = collect($this->documents)->firstWhere('file', $file);

        if (!$docInfo) {
            abort(404, "Document not found.");
        }

        $viewName = 'documents.' . strtolower(str_replace([' ', "'", '_', '.docx'], ['-', '', '-', ''], $docInfo['title']));

        return view($viewName, compact('docInfo'));
    }

    public function download(Request $request, $file, DocumentIdGenerator $idGenerator)
    {
        $docInfo = collect($this->documents)->firstWhere('file', $file);

        if (!$docInfo) {
            abort(404, "Document not found.");
        }

        $data = $this->validateDocumentData($request, $file);
        $documentId = $idGenerator->generate();

        return $this->sendForReview($data, $docInfo, $documentId);
    }

    private function validateDocumentData(Request $request, string $file): array
    {
        $validatedData = match ($file) {
            'Mayors_Clearance.docx' => $request->validate((new MayorClearanceRequest())->rules()),
            'MPOC_Sample.docx' => $request->validate((new MpocRequest())->rules()),
            default => throw new \InvalidArgumentException("Unsupported document type.")
        };

        return $validatedData;
    }

    private function sendForReview(array $data, array $docInfo, string $documentId)
    {
        try {
            $review = $this->workflowService->sendForReview($data, $docInfo, $documentId);
            $reviewer = User::find($data['reviewer_id']);

            return redirect()->route('documents.index')
                ->with('success', "Document sent for review to {$reviewer->name} ({$reviewer->department?->name}). Review ID: {$documentId}");
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
