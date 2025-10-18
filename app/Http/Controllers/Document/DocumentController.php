<?php

namespace App\Http\Controllers\Document;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\User;
use App\Models\DocumentType;
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
    ) {}

    public function index()
    {
        $departments = Department::where('status', 1)->get();

        $reviewers = User::whereIn('type', ['Head'])->get();

        $assignedStaff =
            [
                ['id' => 1, 'name' => 'John Doe', 'position' => 'Document Processor'],
                ['id' => 2, 'name' => 'Jane Smith', 'position' => 'Senior Clerk'],
                ['id' => 3, 'name' => 'Mike Johnson', 'position' => 'Administrative Assistant'],
                ['id' => 4, 'name' => 'Sarah Wilson', 'position' => 'Records Officer'],
                ['id' => 5, 'name' => 'David Brown', 'position' => 'Document Specialist'],
            ];
        $documentTypes = DocumentType::active()
            ->orderBy('title')
            ->paginate(10);

        return view('documents.index', compact('departments', 'reviewers', 'assignedStaff', 'documentTypes'));
    }

    public function create()
    {
        $departments = Department::where('status', 1)->get();
        $reviewers = User::whereIn('type', ['Head'])->get();
        $assignedStaff =
            [
                ['id' => 1, 'name' => 'John Doe', 'position' => 'Document Processor'],
                ['id' => 2, 'name' => 'Jane Smith', 'position' => 'Senior Clerk'],
                ['id' => 3, 'name' => 'Mike Johnson', 'position' => 'Administrative Assistant'],
                ['id' => 4, 'name' => 'Sarah Wilson', 'position' => 'Records Officer'],
                ['id' => 5, 'name' => 'David Brown', 'position' => 'Document Specialist'],
            ];

        return view('documents.create', compact('departments', 'reviewers', 'assignedStaff'));
    }

    public function store(Request $request)
    {
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

        // Handle file upload
        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $attachmentPath = $request->file('attachment')->store('document_attachments', 'public');
        }

        // Convert time to minutes for internal processing
        $processTimeInMinutes = $this->convertTimeToMinutes($validated['process_time'], $validated['time_unit']);

        $data = [
            'name' => $validated['client_name'],
            'reviewer_id' => $validated['reviewer_id'],
            'process_time' => $processTimeInMinutes,
            'time_unit' => $validated['time_unit'],
            'time_value' => $validated['process_time'],
            'title' => $validated['title'],
            'difficulty' => $validated['difficulty'],
            'assigned_staff' => $validated['assigned_staff'],
            'attachment_path' => $attachmentPath,
            'created_via' => 'request_form',
        ];

        $docInfo = [
            'title' => $validated['document_type'],
        ];

        // Use the workflow service to create the document review
        $review = $this->workflowService->sendForReview($data, $docInfo, $documentId);
        try {
            $this->printService->printReceipt($review);
            $printMessage = ' Receipt printed successfully.';
        } catch (\Exception $e) {
            $printMessage = ' Note: Receipt printing failed - '.$e->getMessage();
        }

        return redirect()->route('documents.index')
            ->with('success', "Document '{$validated['title']}' has been created and sent for review. Document ID: {$documentId}");
    }

    public function form($file)
    {
        // Check if template exists
        $templatePath = storage_path("app/public/templates/{$file}");

        if (! file_exists($templatePath)) {
            abort(404, 'Document template not found.');
        }

        // Get reviewers for this document type
        $reviewers = User::whereIn('type', ['Heads'])->get();

        // Route to appropriate form view
        return match ($file) {
            default => abort(404, 'Form not found for this document type.')
        };
    }

    public function download($file, Request $request)
    {
        $validated = $request->validated();
        $documentId = $this->idGenerator->generate();

        // Use the workflow service to create the document review
        $review = $this->workflowService->sendForReview($validated ,$documentId);

        return redirect()->route('documents.reviews.index')
            ->with('success', "Document has been submitted for review. Document ID: {$documentId}");
    }

    public function getDocumentTypes($departmentId)
    {
        // Get document types from database
        $documentTypes = \App\Models\DocumentType::active()
            ->byDepartment($departmentId)
            ->pluck('name')
            ->toArray();

        // Fallback to hardcoded types if no database types exist
        if (empty($documentTypes)) {
            // Local Government Unit Document Types - 5 tracking processes only
            $hardcodedTypes = [
                1 => [ // Mayor's Office
                    'Business Permit',
                    'Barangay Clearance',
                    'Certificate of Indigency',
                    'Certificate of Residency',
                    'Mayor\'s Clearance',
                ],
                2 => [ // City Engineer's Office
                    'Building Permit',
                    'Electrical Permit',
                    'Plumbing Permit',
                    'Excavation Permit',
                    'Demolition Permit',
                ],
                3 => [ // City Treasurer's Office
                    'Real Property Tax Clearance',
                    'Business Tax Clearance',
                    'Certificate of No Pending Case',
                    'Tax Declaration',
                    'Payment Certification',
                ],
                4 => [ // City Health Office
                    'Health Certificate',
                    'Sanitary Permit',
                    'Medical Certificate',
                    'Food Handler\'s Permit',
                    'Water Testing Certificate',
                ],
                5 => [ // City Planning Office
                    'Zoning Clearance',
                    'Site Development Permit',
                    'Subdivision Clearance',
                    'Location Clearance',
                    'Development Plan Approval',
                ],
            ];

            $documentTypes = $hardcodedTypes[$departmentId] ?? [];
        }

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

    //     public function getDocumentTypes($departmentId)
    // {
    //     // Dynamic document types based on department
    //     $documentTypes = [
    //         1 => [ // Mayor's Office
    //             'Mayor\'s Clearance',
    //             'Business Permit',
    //             'Barangay Clearance',
    //             // ... more types
    //         ],
    //         // ... other departments
    //     ];

    //     return response()->json($documentTypes[$departmentId] ?? []);
    // }

}
