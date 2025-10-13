<?php

namespace App\Http\Controllers\Document;

use App\Http\Controllers\Controller;
use App\Http\Requests\Document_Type\MayorClearanceRequest;
use App\Models\DocumentReview;
use App\Models\Department;
use App\Models\User;
use App\Services\Document\DocumentIdGenerator;
use App\Services\Document\DocumentWorkflowService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class DocumentController extends Controller
{
    

    public function __construct(
        private DocumentIdGenerator $idGenerator,
        private DocumentWorkflowService $workflowService
    ) {}

    public function index()
    {
        $departments = Department::where('status', 1)->get();
             
        $reviewers = User::whereIn('type', ['Staff', 'Head'])->get();

        return view('documents.index', compact('departments', 'reviewers'));
    }

    public function create()
    {
        $departments = Department::where('status', 1)->get();
        $reviewers = User::whereIn('type', ['Staff', 'Head'])->get();
        
        return view('documents.create', compact('departments', 'reviewers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'document_type' => 'required|string|max:255',
            'client_name' => 'required|string|max:255',
            'reviewer_id' => 'required|exists:users,id',
            'process_time' => 'required|integer|min:1|max:10',
        ]);

        $documentId = $this->idGenerator->generate();
            $data = [
            'name' => $validated['client_name'],
            'reviewer_id' => $validated['reviewer_id'],
            'process_time' => $validated['process_time'],
            'title' => $validated['title'],
            'created_via' => 'custom_form'
        ];

        $docInfo = [
            'title' => $validated['document_type']
        ];

        // Use the workflow service to create the document review
        $review = $this->workflowService->sendForReview($data, $docInfo, $documentId);

        return redirect()->route('documents.index')
            ->with('success', "Document '{$validated['title']}' has been created and sent for review. Document ID: {$documentId}");
    }

    public function form($file)
    {
        // Check if template exists
        $templatePath = storage_path("app/public/templates/{$file}");
        
        if (!file_exists($templatePath)) {
            abort(404, 'Document template not found.');
        }

        $documentType = match ($file) {
            default => 'Unknown Document'
        };

        // Get reviewers for this document type
        $reviewers = User::whereIn('type', ['Staff', 'Head'])->get();

        // Route to appropriate form view
        return match ($file) {
            default => abort(404, 'Form not found for this document type.')
        };
    }

    public function download($file, Request $request)
    {
        $validated = $request->validated();
        $documentId = $this->idGenerator->generate();
        
        $docInfo = [
            'title' => $this->getDocumentType($file)
        ];

        // Use the workflow service to create the document review
        $review = $this->workflowService->sendForReview($validated, $docInfo, $documentId);

        return redirect()->route('documents.reviews.index')
            ->with('success', "Document has been submitted for review. Document ID: {$documentId}");
    }

    private function getDocumentType($file): string
    {
        return match ($file) {
            default => 'Unknown Document'
        };
    }
}