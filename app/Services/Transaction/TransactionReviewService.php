<?php

namespace App\Services\Transaction;

use App\Models\Transaction;
use App\Models\TransactionReviewer;
use App\Models\TransactionWorkflow;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class TransactionReviewService
{
    protected WorkflowService $workflowService;

    public function __construct(WorkflowService $workflowService)
    {
        $this->workflowService = $workflowService;
    }

    /**
     * Create a review record for a transaction reviewer
     */
    public function createReview(
        Transaction $transaction,
        User $reviewer,
        ?TransactionWorkflow $workflow = null
    ): TransactionReviewer {
        try {
            DB::beginTransaction();

            $workflow = $workflow ?? $transaction->currentWorkflow;

            if (!$workflow) {
                throw new \Exception('No workflow step defined for this transaction');
            }

            $dueDate = $this->calculateDueDate(
                $workflow->process_time_value,
                $workflow->process_time_unit
            );

            $review = TransactionReviewer::create([
                'transaction_id' => $transaction->id,
                'reviewer_id' => $reviewer->id,
                'department_id' => $workflow->department_id,
                'status' => 'pending',
                'process_time_value' => $workflow->process_time_value,
                'process_time_unit' => $workflow->process_time_unit,
                'due_date' => $dueDate,
                'is_overdue' => false,
                'iteration_number' => 1
            ]);

            DB::commit();
            return $review;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Create multiple reviewers for a transaction
     */
    public function createReviewsForTransaction(Transaction $transaction, array $reviewerIds): Collection
    {
        try {
            DB::beginTransaction();

            $workflow = $transaction->currentWorkflow;
            if (!$workflow) {
                throw new \Exception('No workflow step defined for this transaction');
            }

            $reviews = collect();

            foreach ($reviewerIds as $reviewerId) {
                $user = User::findOrFail($reviewerId);
                $review = $this->createReview($transaction, $user, $workflow);
                $reviews->push($review);
            }

            DB::commit();
            return $reviews;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Approve a review and move transaction to next workflow step
     */
    public function approve(
        TransactionReviewer $review,
        ?string $notes = null
    ): bool {
        try {
            DB::beginTransaction();

            $review->update([
                'status' => 'approved',
                'reviewer_notes' => $notes,
                'reviewed_at' => now()
            ]);

            // Move to next step
            $this->workflowService->moveToNexStep($review->transaction, 'approved');

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Reject a review with reason and allow resubmission
     */
    public function reject(
        TransactionReviewer $review,
        string $rejectionReason,
        int $resubmissionDays = 3
    ): bool {
        try {
            DB::beginTransaction();

            $resubmissionDeadline = Carbon::now()->addDays($resubmissionDays);

            $review->update([
                'status' => 're_submit',
                'rejection_reason' => $rejectionReason,
                'reviewer_notes' => $rejectionReason,
                'resubmission_deadline' => $resubmissionDeadline,
                'reviewed_at' => now()
            ]);

            // Check if can create new iteration (cycle)
            if ($this->canCreateNewIteration($review)) {
                $this->createIterationCycle($review);
            } else {
                // Mark transaction as completed with rejection
                $review->transaction->update([
                    'transaction_status' => 'overdue'
                ]);
            }

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Return transaction to originating department
     */
    public function returnToOriginating(
        TransactionReviewer $review,
        string $reason
    ): bool {
        try {
            DB::beginTransaction();

            $review->update([
                'status' => 'return_to_originating',
                'rejection_reason' => $reason,
                'reviewer_notes' => $reason,
                'reviewed_at' => now()
            ]);

            // Get the originating workflow step
            $originatingWorkflow = TransactionWorkflow::byTransactionType(
                $review->transaction->transaction_type_id
            )
                ->originating()
                ->first();

            if ($originatingWorkflow) {
                $review->transaction->update([
                    'current_workflow_step' => $originatingWorkflow->id
                ]);

                $this->workflowService->recordWorkflowTransition(
                    $review->transaction,
                    $review->transaction->currentWorkflow,
                    $originatingWorkflow,
                    'return_to_originating'
                );
            }

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Check if a new iteration/cycle can be created for this review
     */
    public function canCreateNewIteration(TransactionReviewer $review): bool
    {
        $workflow = $review->transaction->currentWorkflow;

        if (!$workflow || !$workflow->allow_cycles) {
            return false;
        }

        $currentIterationCount = TransactionReviewer::where('transaction_id', $review->transaction_id)
            ->where('status', '!=', 'cancelled')
            ->count();

        return $currentIterationCount < $workflow->max_cycle_count;
    }

    /**
     * Create a new iteration cycle for resubmission
     */
    public function createIterationCycle(TransactionReviewer $previousReview): TransactionReviewer
    {
        try {
            DB::beginTransaction();

            $transaction = $previousReview->transaction;
            $workflow = $transaction->currentWorkflow;

            $dueDate = $this->calculateDueDate(
                $workflow->process_time_value,
                $workflow->process_time_unit
            );

            $newReview = TransactionReviewer::create([
                'transaction_id' => $transaction->id,
                'reviewer_id' => $previousReview->reviewer_id,
                'department_id' => $workflow->department_id,
                'status' => 'pending',
                'process_time_value' => $workflow->process_time_value,
                'process_time_unit' => $workflow->process_time_unit,
                'due_date' => $dueDate,
                'is_overdue' => false,
                'iteration_number' => $previousReview->iteration_number + 1,
                'previous_reviewer_id' => $previousReview->reviewer_id
            ]);

            // Update transaction status back to in_progress for resubmission
            $transaction->update([
                'transaction_status' => 'in_progress'
            ]);

            DB::commit();
            return $newReview;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Get all reviews for a transaction with their status
     */
    public function getTransactionReviews(Transaction $transaction): Collection
    {
        return $transaction->reviews()
            ->with(['reviewer', 'department'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get pending reviews for a reviewer
     */
    public function getPendingReviewsForReviewer(User $reviewer): Collection
    {
        return TransactionReviewer::where('reviewer_id', $reviewer->id)
            ->where('status', 'pending')
            ->with(['transaction', 'department'])
            ->orderBy('due_date', 'asc')
            ->get();
    }

    /**
     * Get overdue reviews for a reviewer
     */
    public function getOverdueReviewsForReviewer(User $reviewer): Collection
    {
        return TransactionReviewer::where('reviewer_id', $reviewer->id)
            ->where('is_overdue', true)
            ->where('status', 'pending')
            ->with(['transaction', 'department'])
            ->orderBy('due_date', 'asc')
            ->get();
    }

    /**
     * Get review iterations for a transaction
     */
    public function getReviewIterations(TransactionReviewer $review): Collection
    {
        return TransactionReviewer::where('transaction_id', $review->transaction_id)
            ->orderBy('iteration_number', 'asc')
            ->get();
    }

    /**
     * Get review history/audit trail
     */
    public function getReviewHistory(TransactionReviewer $review): Collection
    {
        return TransactionReviewer::where('transaction_id', $review->transaction_id)
            ->where(function ($query) use ($review) {
                $query->where('reviewer_id', $review->reviewer_id)
                    ->orWhere('previous_reviewer_id', $review->reviewer_id);
            })
            ->with(['reviewer', 'department'])
            ->orderBy('created_at', 'asc')
            ->get();
    }

    /**
     * Get statistics for reviews
     */
    public function getReviewStatistics(User $reviewer): array
    {
        $reviews = TransactionReviewer::where('reviewer_id', $reviewer->id)->get();

        return [
            'total_reviews' => $reviews->count(),
            'pending_reviews' => $reviews->where('status', 'pending')->count(),
            'approved_reviews' => $reviews->where('status', 'approved')->count(),
            'rejected_reviews' => $reviews->where('status', 're_submit')->count(),
            'overdue_reviews' => $reviews->where('is_overdue', true)->count(),
            'returned_to_originating' => $reviews->where('status', 'return_to_originating')->count(),
            'cancelled_reviews' => $reviews->where('status', 'cancelled')->count(),
            'average_review_time' => $this->calculateAverageReviewTime($reviews)
        ];
    }

    /**
     * Calculate average time taken to complete reviews
     */
    private function calculateAverageReviewTime(Collection $reviews): ?int
    {
        $completedReviews = $reviews->whereIn('status', ['approved', 're_submit', 'return_to_originating'])
            ->filter(fn($review) => $review->reviewed_at !== null);

        if ($completedReviews->isEmpty()) {
            return null;
        }

        $totalMinutes = $completedReviews->sum(function ($review) {
            return $review->created_at->diffInMinutes($review->reviewed_at);
        });

        return (int) ($totalMinutes / $completedReviews->count());
    }

    /**
     * Cancel a review
     */
    public function cancel(TransactionReviewer $review, string $reason): bool
    {
        try {
            DB::beginTransaction();

            $review->update([
                'status' => 'cancelled',
                'reviewer_notes' => $reason,
                'reviewed_at' => now()
            ]);

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Check if review is overdue
     */
    public function isOverdue(TransactionReviewer $review): bool
    {
        return $review->due_date < now() && $review->status === 'pending';
    }

    /**
     * Get days remaining for review
     */
    public function getDaysRemaining(TransactionReviewer $review): int
    {
        if ($review->status !== 'pending') {
            return 0;
        }

        return (int) $review->due_date->diffInDays(now());
    }

    /**
     * Calculate due date based on time unit
     */
    private function calculateDueDate(int $value, string $unit): Carbon
    {
        return match ($unit) {
            'days' => Carbon::now()->addDays($value),
            'hours' => Carbon::now()->addHours($value),
            'weeks' => Carbon::now()->addWeeks($value),
            'minutes' => Carbon::now()->addMinutes($value),
            default => Carbon::now()
        };
    }

    /**
     * Bulk update overdue status for all reviews
     */
    public function updateOverdueReviews(): int
    {
        return TransactionReviewer::where('status', 'pending')
            ->where('due_date', '<', now())
            ->update(['is_overdue' => true]);
    }

    /**
     * Bulk cancel reviews for a transaction
     */
    public function cancelTransactionReviews(Transaction $transaction, string $reason): int
    {
        return TransactionReviewer::where('transaction_id', $transaction->id)
            ->where('status', 'pending')
            ->update([
                'status' => 'cancelled',
                'reviewer_notes' => $reason,
                'reviewed_at' => now()
            ]);
    }

    /**
     * Validate review can be approved
     */
    public function canApprove(TransactionReviewer $review): array
    {
        $errors = [];

        if ($review->status !== 'pending') {
            $errors[] = "Review status is {$review->status}, cannot approve";
        }

        if ($review->transaction->transaction_status !== 'in_progress') {
            $errors[] = "Transaction is not in progress";
        }

        return $errors;
    }

    /**
     * Validate review can be rejected
     */
    public function canReject(TransactionReviewer $review): array
    {
        $errors = [];

        if ($review->status !== 'pending') {
            $errors[] = "Review status is {$review->status}, cannot reject";
        }

        if ($review->transaction->transaction_status !== 'in_progress') {
            $errors[] = "Transaction is not in progress";
        }

        return $errors;
    }
}