<?php

namespace App\Http\Controllers\Document;

use App\Http\Controllers\Controller;
use App\Models\AssignStaff;
use App\Models\DocumentReview;
use App\Models\User;
use App\Services\Document\DocumentPrintService;
use App\Services\Document\DocumentWorkflowService;
use App\Services\Document\DocumentTimingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DocumentReviewController extends Controller
{
    public function __construct(
        private DocumentWorkflowService $workflowService,
        private DocumentPrintService $printService,
        private DocumentTimingService $timingService
    ) {
    }

    public function pending(Request $request)
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
            $query->where(function ($q) {
                $q->where('status', 'pending')
                    ->orWhere(function ($subQ) {
                        $subQ->where('status', 'approved')
                            ->whereNull('downloaded_at');
                    });
            });
        }

        $reviews = $query->orderBy('created_at', 'desc')->paginate(10);

        $this->timingService->calculateMultipleDocumentsTiming($reviews->getCollection());

        $reviews->getCollection()->transform(function ($review) {
            $review->is_overdue = $review->calculated_is_overdue ?? $review->is_overdue;
            $review->due_status = $this->getDueStatus($review);
            
            if (!$review->submitted_at) {
                $review->submitted_at = $review->created_at;
            }

            return $review;
        });

        return view('documents.status.pending', compact('reviews', 'user', 'status'));
    }

    public function show($id)
    {
        $review = DocumentReview::with(['creator', 'reviewer', 'currentDepartment', 'originalDepartment'])
            ->findOrFail($id);

        $user = Auth::user();


        $assignedStaff = AssignStaff::where('is_active', true)
            ->where('department_id', $user->department_id)
            ->select('full_name', 'position')
            ->get()
            ->map(fn($staff) => [
                'full_name' => $staff->full_name,
                'position' => $staff->position ?? 'No Position'
            ]);

        if (!$this->canViewReview($review, $user)) {
            abort(403, 'You do not have permission to view this review.');
        }

        $review->is_overdue = $review->due_at && now()->greaterThan($review->due_at) && !$review->downloaded_at;
        $review->due_status = $this->getDueStatus($review);

        return view('documents.reviews.show', compact('review', 'assignedStaff'));
    }

    public function showByDocumentId($documentId)
    {
        $review = DocumentReview::with(['creator', 'reviewer', 'currentDepartment', 'originalDepartment'])
            ->where('document_id', $documentId)
            ->first();

        if (!$review) {
            return view('documents.reviews.not-found', compact('documentId'));
        }

        $user = Auth::user();

        if (!$this->canViewReview($review, $user)) {
            abort(403, 'You do not have permission to view this review.');
        }

        $review->is_overdue = $review->due_at && now()->greaterThan($review->due_at) && !$review->downloaded_at;
        $review->due_status = $this->getDueStatus($review);

        return view('documents.reviews.show', compact('review'));
    }

    public function update(Request $request, $id)
    {
        $review = DocumentReview::findOrFail($id);
        $user = Auth::user();


        $this->validateReviewUpdate($request);

        if ($request->or_number_update) {
            $review->update(['official_receipt_number' => $request->or_number_update]);
        }

        try {
            switch ($request->action) {
                case 'forward':
                    $forwardTo = User::findOrFail($request->forward_to);
                    
                    // Convert time to minutes
                    $processTimeMinutes = $this->convertToMinutes(
                        $request->forward_time_value, 
                        $request->forward_time_unit
                    );
                    
                    $this->workflowService->forwardReview(
                        $review,
                        $user,
                        $forwardTo,
                        $request->forward_notes,
                        $processTimeMinutes
                    );
                    
                    if ($request->forward_assigned_staff) {
                        $review->update(['assigned_staff' => $request->forward_assigned_staff]);
                    }
                    
                    $message = "Document forwarded to {$forwardTo->name} ({$forwardTo->department?->name}).";
                    break;

                case 'complete':
                    $notes = $this->buildCompletionNotes($request);
                    $this->workflowService->completeReview($review, $user, $notes);
                    $originalCreator = User::find($review->created_by);
                    $message = "Document review completed and returned to {$originalCreator->name}. Document is completed.";
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
            $review->is_overdue = $review->due_at && now()->greaterThan($review->due_at) && !$review->downloaded_at;
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
            $review->is_overdue = $review->due_at && now()->greaterThan($review->due_at) && !$review->downloaded_at;
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
            $review->is_overdue = $review->due_at && now()->greaterThan($review->due_at) && !$review->downloaded_at;
            $review->due_status = $this->getDueStatus($review);

            return $review;
        });

        return view('documents.status.canceled', compact('canceledReviews', 'user'));
    }

    private function validateReviewUpdate(Request $request): void
    {
        $rules = [
            'action' => 'required|in:complete,approved,reject,forward,cancel',
            'review_notes' => 'required|string|max:1000',
            'assigned_staff' => 'nullable|string|max:255',
            'completion_summary' => 'nullable|string|max:1000',
        ];

        if ($request->action === 'forward') {
            $rules['forward_to'] = 'required|exists:users,id';
            $rules['forward_notes'] = 'required|string|max:1000';
            $rules['forward_time_value'] = 'required|integer|min:1';
            $rules['forward_time_unit'] = 'required|in:minutes,days,weeks';
            $rules['forward_assigned_staff'] = 'nullable|string|max:255';
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
            $notes .= ($notes ? "\n\n" : '') . 'Completion Summary: ' . $request->completion_summary;
        }

        return $notes;
    }

    private function buildRejectionNotes(Request $request): string
    {
        $notes = $request->review_notes ?? '';
        if ($request->rejection_reason) {
            $notes .= ($notes ? "\n\n" : '') . 'Rejection Reason: ' . $request->rejection_reason;
        }

        return $notes;
    }

    private function buildCancellationNotes(Request $request): string
    {
        $notes = $request->review_notes ?? '';
        if ($request->cancellation_reason) {
            $notes .= ($notes ? "\n\n" : '') . 'Cancellation Reason: ' . $request->cancellation_reason;
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
        if (!$review->due_at) {
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

    private function calculateTimeProperties($review)
    {
        $timing = $this->timingService->calculateDocumentTiming($review);
        
        $review->remaining_time_minutes = $timing['remaining_minutes'];
        $review->is_overdue = $timing['is_overdue'];
        $review->due_status = $this->getDueStatus($review);
        
        if (isset($timing['updated_chain'])) {
            $review->forwarding_chain = $timing['updated_chain'];
        }
    }

public function markDone($id)
    {
        $review = DocumentReview::findOrFail($id);
        $user = auth()->user();

        if ($review->created_by !== $user->id) {
            abort(403, 'You can only mark your own documents as done.');
        }

        if ($review->status !== 'approved' || $review->downloaded_at) {
            return back()->with('error', 'This document cannot be marked as done.');
        }

        try {
            $review->update([
                'downloaded_at' => now(),
                'notes' => ($review->notes ?? '') . "\n\nDocument marked as done by " . $user->name . " on " . now()->format('Y-m-d H:i:s')
            ]);

            return redirect()->route('documents.status.closed')
                ->with('success', 'Document has been marked as done and moved to closed status.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to mark document as done: ' . $e->getMessage());
        }
    }
    

    public function getRemainingTime(DocumentReview $review)
    {
        try {
            $timing = $this->timingService->calculateDocumentTiming($review);
            
            return response()->json([
                'success' => true,
                'remaining_minutes' => $timing['remaining_minutes'],
                'is_overdue' => $timing['is_overdue'],
                'status' => $review->status,
                'current_step_index' => $timing['step_index'] ?? null,
                'forwarding_chain' => $timing['updated_chain'] ?? null
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching remaining time'
            ], 500);
        }
    }
    private function convertToMinutes($value, $unit)
    {
        return match($unit) {
            'minutes' => $value,
            'days' => $value * 24 * 60,
            'weeks' => $value * 7 * 24 * 60,
            default => $value
        };
    }
}
