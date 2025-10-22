<?php

namespace App\Http\Controllers\Document;

use App\Http\Controllers\Controller;
use App\Models\AssignStaff;
use App\Models\Department;
use App\Models\DocumentReview;
use App\Models\User;
use App\Models\DocumentType;
use App\Models\Document; // Add this import
use App\Services\Document\DocumentIdGenerator;
use App\Services\Document\DocumentPrintService;
use App\Services\Document\DocumentWorkflowService;
use Illuminate\Http\Request;

class DocumentController extends Controller
{
    public function __construct(
        private DocumentIdGenerator $idGenerator,
        private DocumentWorkflowService $workflowService,
        private DocumentPrintService $printService
    ) {
    }

    public function index()
    {
        $user = auth()->user();

        $departments = Department::where('status', 1)->get();

        $reviewers = User::where('type', 'Head')->get();

        $assignedStaff = AssignStaff::where('is_active', true)
            ->where('department_id', $user->department_id)
            ->select('full_name', 'position')
            ->get()
            ->map(fn($staff) => [
                'full_name' => $staff->full_name,
                'position' => $staff->position ?? 'No Position',
            ]);

        $documentTypes = DocumentType::where('department_id', $user->department_id)
            ->orderBy('title')
            ->paginate(10);

        return view('documents.index', compact('departments', 'reviewers', 'assignedStaff', 'documentTypes'));
    }

    public function store(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'document_type' => 'required|string|max:255',
            'client_name' => 'required|string|max:255',
            'reviewer_id' => 'required|exists:users,id',
            'process_time' => 'required|integer|min:1',
            'time_unit' => 'required|in:minutes,days,weeks',
            'difficulty' => 'required|in:normal,important,urgent,immediate',
            'assigned_staff' => 'required|string|max:255',
            'attachment' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240', // 10MB max
        ]);

        $documentId = $this->idGenerator->generate();

        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $attachmentPath = $request->file('attachment')->store('document_attachments', 'public');
        }

        $processTimeInMinutes = $this->convertTimeToMinutes($validated['process_time'], $validated['time_unit']);

        $documentData = [
            'title' => $validated['title'],
            'document_type' => $validated['document_type'],
            'client_name' => $validated['client_name'],
            'reviewer_id' => $validated['reviewer_id'],
            'process_time' => $processTimeInMinutes,
            'time_value' => $validated['process_time'],
            'time_unit' => $validated['time_unit'],
            'difficulty' => $validated['difficulty'],
            'assigned_staff' => $validated['assigned_staff'],
            'attachment_path' => $attachmentPath,
            'initial_notes' => 'Document request form submitted for review',

        ];

        $docInfo = [
            'title' => $validated['document_type'],
        ];

        try {
            $review = $this->workflowService->sendForReview($documentData, $docInfo, $documentId);

            $this->printService->printReceipt($review);
            $printMessage = ' Receipt printed successfully.';
        } catch (\Exception $e) {
            $printMessage = ' Note: Receipt printing failed - ' . $e->getMessage();
        }

        return redirect()->route('documents.index')
            ->with('success', "Document '{$validated['title']}' has been created and sent for review. Document ID: {$documentId}." . $printMessage);
    }

    public function getDocumentTypes($departmentId)
    {
        $documentTypes = DocumentType::active()
            ->byDepartment($departmentId)
            ->pluck('name')
            ->toArray();

        return response()->json($documentTypes);
    }

    private function convertTimeToMinutes($value, $unit)
    {
        return match ($unit) {
            'minutes' => $value,
            'days' => $value * 24 * 60,
            'weeks' => $value * 7 * 24 * 60,
            default => $value
        };
    }
}
