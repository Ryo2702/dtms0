<?php

namespace App\Http\Controllers\Document;

use App\Http\Controllers\Controller;
use App\Http\Requests\MayorClearanceRequest;
use App\Http\Requests\MpocRequest;
use App\Http\Requests\ReviewUpdateRequest;
use App\Models\DocumentReview;
use App\Models\DocumentVerification;
use App\Models\User;
use App\Services\DocumentReviewService;
use App\Services\DocumentWorkflowService;
use App\Services\DocumentDownloadService;
use App\Services\DocumentRequestService;
use App\ViewModels\DocumentReviewViewModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use PhpOffice\PhpWord\TemplateProcessor;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class DocumentController extends Controller
{
    private $documents = [
        [
            'title' => "Mayor's Clearance",
            'file' => 'Mayors_Clearance.docx',
        ],
        [
            'title' => 'Municipal Peace and Order Council',
            'file' => 'MPOC_Sample.docx',
        ],
    ];

    public function index()
    {
        $user = Auth::user();

        // Get pending reviews count for the current user (only those that need action)
        $pendingCount = DocumentReview::where('assigned_to', $user->id)
            ->where('status', 'pending')
            ->whereNull('downloaded_at') // Exclude downloaded documents
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

    public function download(Request $request, $file)
    {
        $docInfo = collect($this->documents)->firstWhere('file', $file);

        if (!$docInfo) {
            abort(404, "Document not found.");
        }

        $rules = [];

        if ($file === 'Mayors_Clearance.docx') {
            $rules = [
                'name' => 'required|string|max:255',
                'address' => 'required|string|max:1000',
                'fee' => 'nullable|string|max:100',
                'or_number' => 'nullable|string|max:100',
                'date' => 'nullable|string|max:50',
                'purpose' => 'required|string|max:255',
                'action' => 'required|in:send_for_review',
                'reviewer_id' => 'required|exists:users,id',
                'process_time' => 'required|integer|min:1|max:10',
                'initial_notes' => 'required|string|max:1000'
            ];
        } elseif ($file === 'MPOC_Sample.docx') {
            $rules = [
                'barangay_chairman' => 'required|string|max:255',
                'barangay_name' => 'required|string|max:255',
                'barangay_clearance_date' => 'required|date',
                'resident_name' => 'required|string|max:255',
                'resident_barangay' => 'required|string|max:255',
                'certification_date' => 'nullable|date',
                'requesting_party' => 'required',
                'action' => 'required|in:send_for_review',
                'reviewer_id' => 'required|exists:users,id',
                'process_time' => 'required|integer|min:1|max:10',
                'initial_notes' => 'required|string|max:1000'
            ];
        }

        $data = $request->validate($rules);

        // Generate document ID
        $documentId = "DT-" . now()->format('Ymd') . "-" . strtoupper(Str::random(6));

        // Always send for review (no direct download)
        return $this->sendForReview($data, $file, $docInfo, $documentId);
    }

    public function reviewIndex(Request $request)
    {
        $user = Auth::user();
        $status = $request->get('status');

        // Get reviews based on user role and status
        $query = DocumentReview::with(['creator', 'reviewer', 'currentDepartment', 'originalDepartment']);

        if ($user->type === 'Head') {
            // Heads can see reviews assigned to them and from their department
            $query->where(function ($q) use ($user) {
                $q->where('assigned_to', $user->id)
                    ->orWhere('created_by', $user->id)
                    ->orWhere('current_department_id', $user->department_id);
            });
        } else {
            // Staff can see their own created reviews
            $query->where('created_by', $user->id);
        }

        // Handle status filtering with proper logic
        if ($status) {
            switch ($status) {
                case 'pending':
                    // Only pending documents that haven't been downloaded
                    $query->where('status', 'pending')
                        ->whereNull('downloaded_at');
                    break;

                case 'rejected':
                    // Only rejected documents
                    $query->where('status', 'rejected');
                    break;

                case 'approved':
                case 'completed':
                case 'closed':
                    // Show ALL completed documents that the user can see
                    if ($user->type === 'Head') {
                        // Heads can see all completed documents from their department
                        $query->where(function ($q) use ($user) {
                            $q->where('assigned_to', $user->id)
                                ->orWhere('created_by', $user->id)
                                ->orWhere('current_department_id', $user->department_id);
                        })->where('status', 'approved')
                            ->whereNotNull('downloaded_at');
                    } else {
                        // Staff can see all completed documents they were involved with
                        $query->where(function ($q) use ($user) {
                            $q->where('created_by', $user->id)
                                ->orWhere('assigned_to', $user->id);
                        })->where('status', 'approved')
                            ->whereNotNull('downloaded_at');
                    }
                    break;
            }
        } else {
            // Default view: exclude completed documents from main reviews list
            // Only show documents that need action or are in progress
            $query->where(function ($q) {
                $q->where('status', 'pending')
                    ->orWhere('status', 'rejected')
                    ->orWhere(function ($subQ) {
                        // Show approved documents only if not downloaded yet
                        $subQ->where('status', 'approved')
                            ->whereNull('downloaded_at');
                    });
            });
        }

        $reviews = $query->orderBy('created_at', 'desc')->paginate(10);

        // Add due status to each review
        $reviews->getCollection()->transform(function ($review) {
            $review->is_overdue = $review->due_at && now()->greaterThan($review->due_at) && !$review->downloaded_at;
            $review->due_status = $this->getDueStatus($review);
            return $review;
        });

        return view('documents.reviews.index', compact('reviews', 'user', 'status'));
    }

    public function reviewShow($id)
    {
        $review = DocumentReview::with(['creator', 'reviewer', 'currentDepartment', 'originalDepartment'])
            ->findOrFail($id);

        $user = Auth::user();

        // Check if user has permission to view this review
        $canView = ($review->created_by === $user->id) ||
            ($review->assigned_to === $user->id) ||
            ($user->type === 'Head' && $review->current_department_id === $user->department_id) ||
            ($user->type === 'Admin'); // Admin users can view all documents for tracking

        if (!$canView) {
            abort(403, 'You do not have permission to view this review.');
        }

        // Add due status
        $review->is_overdue = $review->due_at && now()->greaterThan($review->due_at) && !$review->downloaded_at;
        $review->due_status = $this->getDueStatus($review);

        return view('documents.reviews.show', compact('review'));
    }

    private function getDueStatus($review)
    {
        if (!$review->due_at) {
            return 'no_deadline';
        }

        $now = now();
        $dueAt = $review->due_at;

        if ($now->lessThan($dueAt)) {
            $minutesLeft = $now->diffInMinutes($dueAt);
            if ($minutesLeft <= 30) {
                return 'due_soon'; // Within 30 minutes
            }
            return 'on_time';
        } else {
            return 'overdue';
        }
    }

    // Add method to get completed documents separately
    public function completedIndex(Request $request)
    {
        $user = Auth::user();

        $query = DocumentReview::with(['creator', 'reviewer', 'currentDepartment', 'originalDepartment'])
            ->where('status', 'approved')
            ->whereNotNull('downloaded_at'); // Only downloaded documents

        if ($user->type === 'Head') {
            // Heads can see all completed documents from their department or that they were involved with
            $query->where(function ($q) use ($user) {
                $q->where('assigned_to', $user->id)
                    ->orWhere('created_by', $user->id)
                    ->orWhere('current_department_id', $user->department_id);
            });
        } else {
            // Staff can see completed documents they created or were assigned to review
            $query->where(function ($q) use ($user) {
                $q->where('created_by', $user->id)
                    ->orWhere('assigned_to', $user->id);
            });
        }

        $completedReviews = $query->orderBy('downloaded_at', 'desc')->paginate(10);

        // Calculate overdue statistics
        $totalCompleted = $completedReviews->total();
        $overdueCompleted = DocumentReview::with(['creator', 'reviewer', 'currentDepartment', 'originalDepartment'])
            ->where('status', 'approved')
            ->whereNotNull('downloaded_at')
            ->whereNotNull('due_at')
            ->whereColumn('downloaded_at', '>', 'due_at');

        if ($user->type === 'Head') {
            $overdueCompleted->where(function ($q) use ($user) {
                $q->where('assigned_to', $user->id)
                    ->orWhere('created_by', $user->id)
                    ->orWhere('current_department_id', $user->department_id);
            });
        } else {
            $overdueCompleted->where(function ($q) use ($user) {
                $q->where('created_by', $user->id)
                    ->orWhere('assigned_to', $user->id);
            });
        }

        $overdueCompletedCount = $overdueCompleted->count();

        return view('documents.reviews.completed', compact('completedReviews', 'user', 'totalCompleted', 'overdueCompletedCount'));
    }

    // New method to get received documents (documents from other departments)
    public function receivedIndex(Request $request)
    {
        $user = Auth::user();

        $query = DocumentReview::with(['creator', 'reviewer', 'currentDepartment', 'originalDepartment'])
            ->where('current_department_id', $user->department_id)
            ->where('original_department_id', '!=', $user->department_id); // From other departments

        // Filter based on user type
        if ($user->type === 'Head') {
            // Heads can see all received documents in their department
            $query->where(function ($q) use ($user) {
                $q->where('assigned_to', $user->id)
                    ->orWhere('current_department_id', $user->department_id);
            });
        } else {
            // Staff can see received documents assigned to them
            $query->where('assigned_to', $user->id);
        }

        $receivedReviews = $query->orderBy('created_at', 'desc')->paginate(10);

        // Add due status to each review
        $receivedReviews->getCollection()->transform(function ($review) {
            $review->is_overdue = $review->due_at && now()->greaterThan($review->due_at) && !$review->downloaded_at;
            $review->due_status = $this->getDueStatus($review);
            return $review;
        });

        return view('documents.reviews.received', compact('receivedReviews', 'user'));
    }

    // New method to get sent documents (documents sent to other departments)
    public function sentIndex(Request $request)
    {
        $user = Auth::user();

        $query = DocumentReview::with(['creator', 'reviewer', 'currentDepartment', 'originalDepartment'])
            ->where('original_department_id', $user->department_id)
            ->where('current_department_id', '!=', $user->department_id); // Sent to other departments

        // Filter based on user type and involvement
        if ($user->type === 'Head') {
            // Heads can see all sent documents from their department
            $query->where(function ($q) use ($user) {
                $q->where('created_by', $user->id)
                    ->orWhere('original_department_id', $user->department_id);
            });
        } else {
            // Staff can see documents they created that were sent to other departments
            $query->where('created_by', $user->id);
        }

        $sentReviews = $query->orderBy('created_at', 'desc')->paginate(10);

        // Add due status to each review
        $sentReviews->getCollection()->transform(function ($review) {
            $review->is_overdue = $review->due_at && now()->greaterThan($review->due_at) && !$review->downloaded_at;
            $review->due_status = $this->getDueStatus($review);
            return $review;
        });

        return view('documents.reviews.sent', compact('sentReviews', 'user'));
    }


    public function adminTrackIndex(Request $request)
    {
        $user = Auth::user();

        // Only allow Admin users
        if ($user->type !== 'Admin') {
            abort(403, 'Only administrators can access document tracking.');
        }

        $query = DocumentReview::with(['creator', 'reviewer', 'currentDepartment', 'originalDepartment']);

        // Search filters
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('document_id', 'LIKE', "%{$search}%")
                    ->orWhere('client_name', 'LIKE', "%{$search}%")
                    ->orWhere('document_type', 'LIKE', "%{$search}%")
                    ->orWhereHas('creator', function ($userQuery) use ($search) {
                        $userQuery->where('name', 'LIKE', "%{$search}%")
                            ->orWhere('employee_id', 'LIKE', "%{$search}%");
                    })
                    ->orWhereHas('reviewer', function ($userQuery) use ($search) {
                        $userQuery->where('name', 'LIKE', "%{$search}%")
                            ->orWhere('employee_id', 'LIKE', "%{$search}%");
                    });
            });
        }

        // Filter by department
        if ($request->filled('department')) {
            $departmentId = $request->get('department');
            $query->where(function ($q) use ($departmentId) {
                $q->where('original_department_id', $departmentId)
                    ->orWhere('current_department_id', $departmentId);
            });
        }

        // Filter by user type (Head or Staff)
        if ($request->filled('user_type')) {
            $userType = $request->get('user_type');
            $query->whereHas('creator', function ($userQuery) use ($userType) {
                $userQuery->where('type', $userType);
            });
        }

        // Filter by document type
        if ($request->filled('document_type')) {
            $query->where('document_type', $request->get('document_type'));
        }

        // Filter by status
        if ($request->filled('status')) {
            $status = $request->get('status');
            if ($status === 'completed') {
                $query->where('status', 'approved')->whereNotNull('downloaded_at');
            } else {
                $query->where('status', $status);
            }
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->get('date_from'));
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->get('date_to'));
        }

        $documents = $query->orderBy('created_at', 'desc')->paginate(15);

        // Add journey analysis to each document
        $documents->getCollection()->transform(function ($review) {
            $review->journey_steps = count($review->forwarding_chain ?? []);
            $review->processing_time = $review->submitted_at && $review->downloaded_at
                ? $review->submitted_at->diffInMinutes($review->downloaded_at)
                : null;
            $review->is_overdue = $review->due_at && now()->greaterThan($review->due_at) && !$review->downloaded_at;
            return $review;
        });

        // Get filter options
        $departments = \App\Models\Department::all();
        $documentTypes = DocumentReview::select('document_type')->distinct()->pluck('document_type');
        $userTypes = ['Head', 'Staff'];

        // Get statistics
        $stats = [
            'total_documents' => DocumentReview::count(),
            'pending_documents' => DocumentReview::where('status', 'pending')->count(),
            'completed_documents' => DocumentReview::where('status', 'approved')->whereNotNull('downloaded_at')->count(),
            'overdue_documents' => DocumentReview::where('status', 'pending')
                ->where('due_at', '<', now())->count(),
            'avg_processing_time' => DocumentReview::where('status', 'approved')
                ->whereNotNull('downloaded_at')
                ->whereNotNull('submitted_at')
                ->get()
                ->avg(function ($doc) {
                    return $doc->submitted_at->diffInMinutes($doc->downloaded_at);
                }),
        ];

        return view('documents.admin.track', compact(
            'documents',
            'user',
            'departments',
            'documentTypes',
            'userTypes',
            'stats'
        ));
    }

    private function sendForReview($data, $file, $docInfo, $documentId)
    {
        $user = Auth::user();

        if (!in_array($user->type, ['Staff', 'Head'])) {
            return back()->with('error', 'You do not have permission to send documents for review.');
        }

        $reviewer = User::findOrFail($data['reviewer_id']);

        if ($reviewer->type !== 'Head') {
            return back()->with('error', 'Documents can only be sent to Department Heads for review.');
        }

        $clientName = $data['name'] ?? $data['resident_name'] ?? 'Unknown';

        $processTime = (int) $data['process_time'];

        $review = DocumentReview::create([
            'document_id' => $documentId,
            'document_type' => $docInfo['title'],
            'client_name' => $clientName,
            'document_data' => $data,
            'official_receipt_number' => $data['or_number'] ?? null,
            'created_by' => $user->id,
            'assigned_to' => $data['reviewer_id'],
            'current_department_id' => $reviewer->department_id,
            'original_department_id' => $user->department_id,
            'status' => 'pending',
            'review_notes' => $data['initial_notes'] ?? null,
            'process_time_minutes' => $processTime,
            'submitted_at' => now(),
            'due_at' => now()->addMinutes($processTime),
            'forwarding_chain' => [
                [
                    'step' => 1,
                    'action' => 'created',
                    'from_user_id' => $user->id,
                    'from_user_name' => $user->name,
                    'from_user_type' => $user->type,
                    'from_department' => $user->department?->name,
                    'to_user_id' => null,
                    'to_user_name' => null,
                    'to_department' => null,
                    'notes' => 'Document created and prepared for review',
                    'process_time' => null,
                    'timestamp' => now()->toISOString(),
                    'status' => 'completed'
                ],
                [
                    'step' => 2,
                    'action' => 'submitted_for_review',
                    'from_user_id' => $user->id,
                    'from_user_name' => $user->name,
                    'from_user_type' => $user->type,
                    'from_department' => $user->department?->name,
                    'to_user_id' => $reviewer->id,
                    'to_user_name' => $reviewer->name,
                    'to_user_type' => $reviewer->type,
                    'to_department' => $reviewer->department?->name,
                    'notes' => $data['initial_notes'] ?? 'Initial document review request',
                    'process_time' => $processTime,
                    'timestamp' => now()->toISOString(),
                    'status' => 'pending',
                    'due_at' => now()->addMinutes($processTime)->toISOString()
                ]
            ]
        ]);

        return redirect()->route('documents.index')
            ->with('success', "Document sent for review to {$reviewer->name} ({$reviewer->department?->name}). Review ID: {$documentId}");
    }

    public function reviewUpdate(Request $request, $id)
    {
        $review = DocumentReview::findOrFail($id);

        // Only Heads can review documents
        $user = Auth::user();
        if ($user->type !== 'Head' || $review->assigned_to !== $user->id) {
            abort(403, 'Only department heads can review documents.');
        }

        // Base validation rules
        $rules = [
            'action' => 'required|in:complete,reject,forward',
            'review_notes' => 'nullable|string|max:1000',
            'or_number_update' => 'nullable|string|max:100',
            'completion_summary' => 'nullable|string|max:1000',
        ];

        // Add conditional validation rules based on action
        if ($request->action === 'forward') {
            $rules['forward_to'] = 'required|exists:users,id';
            $rules['forward_notes'] = 'required|string|max:1000';
            $rules['forward_process_time'] = 'required|integer|min:1|max:10';
        }

        if ($request->action === 'reject') {
            $rules['rejection_reason'] = 'required|string|max:1000';
        }

        $request->validate($rules);

        // Update OR number if provided
        if ($request->or_number_update) {
            $review->official_receipt_number = $request->or_number_update;
            $review->save();
        }

        if ($request->action === 'forward') {
            return $this->forwardReview($review, $request);
        }

        // For complete action - return to original creator with completion notes
        if ($request->action === 'complete') {
            $completionNotes = $request->review_notes ?? '';
            if ($request->completion_summary) {
                $completionNotes .= ($completionNotes ? "\n\n" : '') . "Completion Summary: " . $request->completion_summary;
            }

            return $this->completeReview($review, $completionNotes);
        }

        // For reject action
        if ($request->action === 'reject') {
            $rejectionNotes = $request->review_notes ?? '';
            if ($request->rejection_reason) {
                $rejectionNotes .= ($rejectionNotes ? "\n\n" : '') . "Rejection Reason: " . $request->rejection_reason;
            }

            return $this->rejectReview($review, $rejectionNotes);
        }

        return redirect()->route('documents.reviews.index')
            ->with('success', "Document processed successfully.");
    }

    private function forwardReview($review, $request)
    {
        $currentUser = Auth::user();
        $forwardTo = User::findOrFail($request->forward_to);

        // Ensure forward target is a Head
        if ($forwardTo->type !== 'Head') {
            return back()->with('error', 'Documents can only be forwarded to Department Heads.');
        }

        // Fix: Ensure process_time is an integer
        $forwardProcessTime = (int) $request->forward_process_time;

        // Add to forwarding chain
        $review->addToForwardingChain(
            'forwarded',
            $currentUser,
            $forwardTo,
            $request->forward_notes,
            $forwardProcessTime
        );

        $review->update([
            'assigned_to' => $forwardTo->id,
            'current_department_id' => $forwardTo->department_id,
            'process_time_minutes' => $forwardProcessTime,
            'due_at' => now()->addMinutes($forwardProcessTime),
            'review_notes' => $request->review_notes
        ]);

        return redirect()->route('documents.reviews.index')
            ->with('success', "Document forwarded to {$forwardTo->name} ({$forwardTo->department?->name}).");
    }

    private function completeReview($review, $notes)
    {
        $currentUser = Auth::user();
        $originalCreator = User::find($review->created_by);

        if (!$originalCreator) {
            return back()->with('error', 'Original document creator not found.');
        }

        // Check if document was completed on time or overdue
        $wasOnTime = !$review->due_at || now()->lessThanOrEqualTo($review->due_at);
        $timeStatus = $wasOnTime ? 'completed on time' : 'completed overdue';

        // Create completion message
        $completionMessage = $notes ?? 'Document review completed successfully.';
        $completionMessage .= "\n\nStatus: " . ucfirst($timeStatus);

        if ($review->official_receipt_number) {
            $completionMessage .= "\n\nOR Number: " . $review->official_receipt_number . " (Payment Processed)";
        }
        $completionMessage .= "\n\nDocument is ready for download and client signature.";

        // Add completion step to chain
        $review->addToForwardingChain(
            'completed',
            $currentUser,
            $originalCreator,
            $completionMessage,
            2
        );

        $review->update([
            'assigned_to' => $originalCreator->id,
            'current_department_id' => $originalCreator->department_id,
            'process_time_minutes' => 2,
            'due_at' => now()->addMinutes(2),
            'status' => 'approved',
            'is_final_review' => true,
            'review_notes' => $notes,
            'reviewed_at' => now(),
            'completed_on_time' => $wasOnTime // Track if it was completed on time
        ]);

        $statusMessage = $wasOnTime
            ? "Document review completed on time and returned to {$originalCreator->name}."
            : "Document review completed (overdue) and returned to {$originalCreator->name}.";

        return redirect()->route('documents.reviews.index')
            ->with('success', $statusMessage . " Document is ready for download.");
    }

    private function rejectReview($review, $notes)
    {
        $currentUser = Auth::user();
        $originalCreator = User::find($review->created_by);

        if (!$originalCreator) {
            return back()->with('error', 'Original document creator not found.');
        }

        // Add rejection step to chain
        $review->addToForwardingChain(
            'rejected',
            $currentUser,
            $originalCreator,
            $notes,
            null
        );

        $review->update([
            'status' => 'rejected',
            'review_notes' => $notes,
            'reviewed_at' => now(),
            'assigned_to' => $originalCreator->id,
            'current_department_id' => $originalCreator->department_id
        ]);

        return redirect()->route('documents.reviews.index')
            ->with('success', "Document rejected and returned to {$originalCreator->name} for corrections.");
    }

    public function reviewDownload($id)
    {
        $review = DocumentReview::findOrFail($id);

        // Only original creator can download approved documents
        if ($review->created_by !== Auth::id() || $review->status !== 'approved') {
            abort(403, 'You can only download documents you created that have been approved.');
        }

        // Add download step to chain
        $user = Auth::user();
        $review->addToForwardingChain(
            'downloaded',
            $user,
            null,
            'Document downloaded by original creator for client signature',
            null
        );

        $verification = DocumentVerification::create([
            'verification_code' => DocumentVerification::generateVerificationCode(),
            'document_id' => $review->document_id,
            'document_type' => $review->document_type,
            'client_name' => $review->client_name,
            'issued_by' => $user->name,
            'issued_by_id' => $user->employee_id ?? $user->id,
            'issued_at' => now(),
            'document_data' => $review->document_data,
            'official_receipt_number' => $review->official_receipt_number
        ]);

        // Mark as downloaded (status remains 'approved' but add downloaded_at timestamp)
        $review->update(['downloaded_at' => now()]);

        $file = '';
        if ($review->document_type === "Mayor's Clearance") {
            $file = 'Mayors_Clearance.docx';
        } elseif ($review->document_type === 'MPOC Sample') {
            $file = 'MPOC_Sample.docx';
        }

        if (!$file) {
            abort(404, 'Document template not found.');
        }

        $templatePath = storage_path("app/public/templates/{$file}");

        if (!file_exists($templatePath)) {
            abort(404, 'Document template file not found.');
        }

        $templateProcessor = new TemplateProcessor($templatePath);
        $data = $review->document_data;

        $templateProcessor->setValue('employee_id', $user->employee_id ?? 'N/A');
        $templateProcessor->setValue('document_id', $review->document_id ?? 'N/A');
        $templateProcessor->setValue('issued_at', now()->format('M d, Y')); // Sep 04, 2025

        $templateProcessor->setValue('verification_code', $verification->verification_code);
        // Generate QR code and save as temporary file
        $qrCodePath = $this->generateQrCodeFile($verification);

        if ($qrCodePath && file_exists($qrCodePath)) {
            // Use setImageValue instead of setValue to embed actual image
            $templateProcessor->setImageValue('qr_verification_url', [
                'path' => $qrCodePath,
                'width' => 55,
                'height' => 55,
                'ratio' => false
            ]);
        } else {
            // Fallback - remove the placeholder if QR generation fails
            $templateProcessor->setValue('qr_verification_url', '');
        }
        // Process template based on document type
        if ($review->document_type === "Mayor's Clearance") {
            $templateProcessor->setValue('name', $data['name'] ?? '');
            $templateProcessor->setValue('address', $data['address'] ?? '');
            $templateProcessor->setValue('purpose', $data['purpose'] ?? '');
            $templateProcessor->setValue('fee', $data['fee'] ?? '');
            $templateProcessor->setValue('or_number', $review->official_receipt_number ?? 'N/A');
            $templateProcessor->setValue('date', $data['date'] ?? now()->format('Y-m-d'));
        } elseif ($review->document_type === 'MPOC Sample') {
            $templateProcessor->setValue('barangay_chairman', $data['barangay_chairman'] ?? '');
            $templateProcessor->setValue('barangay_name', $data['barangay_name'] ?? '');
            $templateProcessor->setValue('barangay_clearance_date', $data['barangay_clearance_date'] ?? '');
            $templateProcessor->setValue('resident_name', $data['resident_name'] ?? '');
            $templateProcessor->setValue('resident_barangay', $data['resident_barangay'] ?? '');
            $templateProcessor->setValue('certification_date', $data['certification_date'] ?? now()->format('Y-m-d'));
            $templateProcessor->setValue('requesting_party', $data['requesting_party'] ?? '');
        }

        $fileName = $review->document_id . '_' . str_replace(' ', '_', $review->document_type) . '.docx';
        $outputPath = storage_path("app/temp/{$fileName}");

        // Ensure temp directory exists
        if (!file_exists(dirname($outputPath))) {
            mkdir(dirname($outputPath), 0755, true);
        }

        $templateProcessor->saveAs($outputPath);

        return response()->download($outputPath, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ])->deleteFileAfterSend(true);
    }

    private function generateQrCodeFile($verification)
    {
        try {
            // Create QR code data with document information
            $qrData = "Document ID: {$verification->document_id}\n";
            $qrData .= "Title: {$verification->document_type}\n";
            $qrData .= "Name: {$verification->client_name}\n";
            $qrData .= "Employee ID: {$verification->employee_id}\n";
            $qrData .= "Department: {$verification->department}\n";
            $qrData .= "Verification URL: " . route('documents.verify', $verification->verification_code);

            // Generate temporary file path
            $tempPath = storage_path('app/temp/qr_' . $verification->verification_code . '.png');

            // Ensure directory exists
            $tempDir = dirname($tempPath);
            if (!is_dir($tempDir)) {
                mkdir($tempDir, 0755, true);
            }

            // Generate QR code and save to file
            QrCode::format('png')
                ->size(200)
                ->margin(1)
                ->generate($qrData, $tempPath);

            return $tempPath;
        } catch (\Exception $e) {
            Log::error('Failed to generate QR code file: ' . $e->getMessage());
            return null;
        }
    }
}
