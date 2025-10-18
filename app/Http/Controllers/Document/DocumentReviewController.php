<?php

namespace App\Http\Controllers\Document;

use App\Http\Controllers\Controller;
use App\Models\DocumentReview;
use App\Models\User;
use App\Services\Document\DocumentPrintService;
use App\Services\Document\DocumentWorkflowService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DocumentReviewController extends Controller
{
    public function __construct(
        private DocumentWorkflowService $workflowService,
        private DocumentPrintService $printService
    ) {}

    public function index(Request $request)
    {
        $user = Auth::user();
        $status = $request->get('status');

        $query = DocumentReview::with(['creator', 'reviewer', 'currentDepartment', 'originalDepartment']);

        if ($user->type === 'Head') {
            $query->where(function ($q) use ($user) {
                $q->where('assigned_to', $user->id)
                    ->orWhere('created_by', $user->id)
                    ->orWhere('current_department_id', $user->department_id);
            });
        } else {
            // For regular users, show documents assigned to them OR documents they created that are approved and ready for download
            $query->where(function ($q) use ($user) {
                $q->where('assigned_to', $user->id)
                    ->orWhere(function ($subQ) use ($user) {
                        $subQ->where('created_by', $user->id)
                            ->where('status', 'approved')
                            ->whereNull('downloaded_at');
                    });
            });
        }

        if ($status) {
            $this->applyStatusFilter($query, $status, $user);
        } else {
            // Show pending documents OR approved documents waiting for download
            $query->where(function ($q) {
                $q->where('status', 'pending')
                    ->orWhere(function ($subQ) {
                        $subQ->where('status', 'approved')
                            ->whereNull('downloaded_at');
                    });
            });
        }

        $reviews = $query->orderBy('created_at', 'desc')->paginate(10);

        $reviews->getCollection()->transform(function ($review) {
            $review->is_overdue = $review->due_at && now()->greaterThan($review->due_at) && ! $review->downloaded_at;
            $review->due_status = $this->getDueStatus($review);

            return $review;
        });

        return view('documents.status.pending', compact('reviews', 'user', 'status'));
    }

    public function show($id)
    {
        $review = DocumentReview::with(['creator', 'reviewer', 'currentDepartment', 'originalDepartment'])
            ->findOrFail($id);

        $user = Auth::user();

        if (! $this->canViewReview($review, $user)) {
            abort(403, 'You do not have permission to view this review.');
        }

        $review->is_overdue = $review->due_at && now()->greaterThan($review->due_at) && ! $review->downloaded_at;
        $review->due_status = $this->getDueStatus($review);

        return view('documents.reviews.show', compact('review'));
    }

    public function showByDocumentId($documentId)
    {
        $review = DocumentReview::with(['creator', 'reviewer', 'currentDepartment', 'originalDepartment'])
            ->where('document_id', $documentId)
            ->first();

        if (! $review) {
            return view('documents.reviews.not-found', compact('documentId'));
        }

        $user = Auth::user();

        if (! $this->canViewReview($review, $user)) {
            abort(403, 'You do not have permission to view this review.');
        }

        $review->is_overdue = $review->due_at && now()->greaterThan($review->due_at) && ! $review->downloaded_at;
        $review->due_status = $this->getDueStatus($review);

        return view('documents.reviews.show', compact('review'));
    }

    public function update(Request $request, $id)
    {
        $review = DocumentReview::findOrFail($id);
        $user = Auth::user();

        if ($user->type !== 'Head' || $review->assigned_to !== $user->id) {
            abort(403, 'Only department heads and staff can review their documents.');
        }

        $this->validateReviewUpdate($request);

        if ($request->or_number_update) {
            $review->update(['official_receipt_number' => $request->or_number_update]);
        }

        try {
            switch ($request->action) {
                case 'forward':
                    $forwardTo = User::findOrFail($request->forward_to);
                    $this->workflowService->forwardReview(
                        $review,
                        $user,
                        $forwardTo,
                        $request->forward_notes,
                        (int) $request->forward_process_time
                    );
                    $message = "Document forwarded to {$forwardTo->name} ({$forwardTo->department?->name}).";
                    break;

                case 'complete':
                    $notes = $this->buildCompletionNotes($request);
                    $this->workflowService->completeReview($review, $user, $notes);
                    $originalCreator = User::find($review->created_by);
                    $message = "Document review completed and returned to {$originalCreator->name}. Document is ready for download.";
                    break;

                case 'reject':
                    $notes = $this->buildRejectionNotes($request);
                    $this->workflowService->rejectReview($review, $user, $notes);
                    $originalCreator = User::find($review->created_by);
                    $message = "Document rejected and returned to {$originalCreator->name} for corrections.";
                    break;

                case 'cancel':
                    $notes = $this->buildCancellationNotes($request);
                    $this->workflowService->cancelReview($review, $user, $notes);
                    $originalCreator = User::find($review->created_by);
                    $message = "Document canceled and returned to {$originalCreator->name}.";
                    break;
            }

            return redirect()->route('documents.status.pending')->with('success', $message);
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function closed(Request $request)
    {
        $user = Auth::user();

        $query = DocumentReview::with(['creator', 'reviewer', 'currentDepartment', 'originalDepartment'])
            ->where('status', 'approved')
            ->whereNotNull('downloaded_at');

        if ($user->type === 'Head') {
            $query->where(function ($q) use ($user) {
                $q->where('assigned_to', $user->id)
                    ->orWhere('created_by', $user->id)
                    ->orWhere('current_department_id', $user->department_id);
            });
        } else {
            $query->where(function ($q) use ($user) {
                $q->where('created_by', $user->id)
                    ->orWhere('assigned_to', $user->id);
            });
        }

        $completedReviews = $query->orderBy('downloaded_at', 'desc')->paginate(10);

        // Calculate statistics
        $totalCompleted = $completedReviews->total();
        $overdueCompletedCount = $this->getOverdueCompletedCount($user);

        return view('documents.status.closed', compact('completedReviews', 'user', 'totalCompleted', 'overdueCompletedCount'));
    }

    public function received(Request $request)
    {
        $user = Auth::user();

        $query = DocumentReview::with(['creator', 'reviewer', 'currentDepartment', 'originalDepartment'])
            ->where('current_department_id', $user->department_id)
            ->where('original_department_id', '!=', $user->department_id);

        if ($user->type === 'Head') {
            $query->where(function ($q) use ($user) {
                $q->where('assigned_to', $user->id)
                    ->orWhere('current_department_id', $user->department_id);
            });
        } else {
            $query->where('assigned_to', $user->id);
        }

        $receivedReviews = $query->orderBy('created_at', 'desc')->paginate(10);

        $receivedReviews->getCollection()->transform(function ($review) {
            $review->is_overdue = $review->due_at && now()->greaterThan($review->due_at) && ! $review->downloaded_at;
            $review->due_status = $this->getDueStatus($review);

            return $review;
        });

        return view('documents.reviews.request', compact('receivedReviews', 'user'));
    }

    public function rejected(Request $request)
    {
        $user = Auth::user();

        $query = DocumentReview::with(['creator', 'reviewer', 'currentDepartment', 'originalDepartment'])
            ->where('status', 'rejected');

        if ($user->type === 'Head') {
            $query->where(function ($q) use ($user) {
                $q->where('created_by', $user->id)
                    ->orWhere('assigned_to', $user->id)
                    ->orWhere('current_department_id', $user->department_id)
                    ->orWhere('original_department_id', $user->department_id);
            });
        } else {
            $query->where(function ($q) use ($user) {
                $q->where('created_by', $user->id)
                    ->orWhere('assigned_to', $user->id);
            });
        }

        $rejectedReviews = $query->orderBy('updated_at', 'desc')->paginate(10);

        $rejectedReviews->getCollection()->transform(function ($review) {
            $review->is_overdue = $review->due_at && now()->greaterThan($review->due_at) && ! $review->downloaded_at;
            $review->due_status = $this->getDueStatus($review);

            return $review;
        });

        return view('documents.status.rejected', compact('rejectedReviews', 'user'));
    }

    public function canceled(Request $request)
    {
        $user = Auth::user();

        $query = DocumentReview::with(['creator', 'reviewer', 'currentDepartment', 'originalDepartment'])
            ->where('status', 'canceled');

        if ($user->type === 'Head') {
            $query->where(function ($q) use ($user) {
                $q->where('created_by', $user->id)
                    ->orWhere('assigned_to', $user->id)
                    ->orWhere('current_department_id', $user->department_id)
                    ->orWhere('original_department_id', $user->department_id);
            });
        } else {
            $query->where(function ($q) use ($user) {
                $q->where('created_by', $user->id)
                    ->orWhere('assigned_to', $user->id);
            });
        }

        $canceledReviews = $query->orderBy('updated_at', 'desc')->paginate(10);

        $canceledReviews->getCollection()->transform(function ($review) {
            $review->is_overdue = $review->due_at && now()->greaterThan($review->due_at) && ! $review->downloaded_at;
            $review->due_status = $this->getDueStatus($review);

            return $review;
        });

        return view('documents.status.canceled', compact('canceledReviews', 'user'));
    }

    private function validateReviewUpdate(Request $request): void
    {
        $rules = [
            'action' => 'required|in:complete,reject,forward,cancel',
            'review_notes' => 'nullable|string|max:1000',
            'or_number_update' => 'nullable|string|max:100',
            'completion_summary' => 'nullable|string|max:1000',
        ];

        if ($request->action === 'forward') {
            $rules['forward_to'] = 'required|exists:users,id';
            $rules['forward_notes'] = 'required|string|max:1000';
            $rules['forward_process_time'] = 'required|integer|min:1|max:10';
        }

        if ($request->action === 'reject') {
            $rules['rejection_reason'] = 'required|string|max:1000';
        }

        if ($request->action === 'cancel') {
            $rules['cancellation_reason'] = 'required|string|max:1000';
        }

        $request->validate($rules);
    }

    private function buildCompletionNotes(Request $request): string
    {
        $notes = $request->review_notes ?? '';
        if ($request->completion_summary) {
            $notes .= ($notes ? "\n\n" : '').'Completion Summary: '.$request->completion_summary;
        }

        return $notes;
    }

    private function buildRejectionNotes(Request $request): string
    {
        $notes = $request->review_notes ?? '';
        if ($request->rejection_reason) {
            $notes .= ($notes ? "\n\n" : '').'Rejection Reason: '.$request->rejection_reason;
        }

        return $notes;
    }

    private function buildCancellationNotes(Request $request): string
    {
        $notes = $request->review_notes ?? '';
        if ($request->cancellation_reason) {
            $notes .= ($notes ? "\n\n" : '').'Cancellation Reason: '.$request->cancellation_reason;
        }

        return $notes;
    }

    private function applyStatusFilter($query, string $status, $user): void
    {
        switch ($status) {
            case 'pending':
                $query->where('status', 'pending')->whereNull('downloaded_at');
                break;
            case 'rejected':
                $query->where('status', 'rejected');
                break;
            case 'canceled':
                $query->where('status', 'canceled');
                break;
            case 'approved':
            case 'completed':
            case 'closed':
                if ($user->type === 'Head') {
                    $query->where(function ($q) use ($user) {
                        $q->where('assigned_to', $user->id)
                            ->orWhere('created_by', $user->id)
                            ->orWhere('current_department_id', $user->department_id);
                    })->where('status', 'approved')->whereNotNull('downloaded_at');
                } else {
                    $query->where(function ($q) use ($user) {
                        $q->where('created_by', $user->id)
                            ->orWhere('assigned_to', $user->id);
                    })->where('status', 'approved')->whereNotNull('downloaded_at');
                }
                break;
        }
    }

    private function canViewReview(DocumentReview $review, $user): bool
    {
        return ($review->created_by === $user->id) ||
            ($review->assigned_to === $user->id) ||
            ($user->type === 'Head' && $review->current_department_id === $user->department_id) ||
            ($user->type === 'Admin');
    }

    private function getDueStatus($review): string
    {
        if (! $review->due_at) {
            return 'no_deadline';
        }

        $now = now();
        $dueAt = $review->due_at;

        if ($now->lessThan($dueAt)) {
            $minutesLeft = $now->diffInMinutes($dueAt);
            if ($minutesLeft <= 30) {
                return 'due_soon';
            }

            return 'on_time';
        }

        return 'overdue';
    }

    private function getOverdueCompletedCount($user): int
    {
        $query = DocumentReview::where('status', 'approved')
            ->whereNotNull('downloaded_at')
            ->whereNotNull('due_at')
            ->whereColumn('downloaded_at', '>', 'due_at');

        if ($user->type === 'Head') {
            $query->where(function ($q) use ($user) {
                $q->where('assigned_to', $user->id)
                    ->orWhere('created_by', $user->id)
                    ->orWhere('current_department_id', $user->department_id);
            });
        } else {
            $query->where(function ($q) use ($user) {
                $q->where('created_by', $user->id)
                    ->orWhere('assigned_to', $user->id);
            });
        }

        return $query->count();
    }

    public function print($id)
    {
        $review = DocumentReview::findOrFail($id);

        return $this->printService->generateDocument($review);
    }
}
