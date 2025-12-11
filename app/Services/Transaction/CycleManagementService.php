<?php

namespace App\Services\Transaction;

use App\Models\Transaction;
use App\Models\TransactionReviewer;
use App\Models\TransactionWorkflow;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CycleManagementService
{
    /**
     * Check if a review can create a new iteration cycle
     */
    public function canCreateNewIteration(TransactionReviewer $review): bool
    {
        $workflow = $review->workflowStep ?? $review->transaction->currentWorkflow;

        if (!$workflow || !$workflow->allow_cycles) {
            return false;
        }

        $currentIterationCount = $this->getIterationCount($review->transaction_id, $workflow->id);

        return $currentIterationCount < $workflow->max_cycle_count;
    }

    /**
     * Get the current iteration count for a specific workflow step
     */
    public function getIterationCount(int $transactionId, int $workflowId): int
    {
        return TransactionReviewer::where('transaction_id', $transactionId)
            ->where('workflow_step_id', $workflowId)
            ->count();
    }

    /**
     * Get the current iteration number for a workflow step
     */
    public function getCurrentIterationNumber(int $transactionId, int $workflowId): int
    {
        return TransactionReviewer::where('transaction_id', $transactionId)
            ->where('workflow_step_id', $workflowId)
            ->max('iteration_number') ?? 0;
    }

    /**
     * Create a new iteration cycle for a rejected review
     */
    public function createIterationCycle(
        TransactionReviewer $previousReview,
        ?int $reassignToReviewerId = null
    ): TransactionReviewer {
        try {
            DB::beginTransaction();

            $transaction = $previousReview->transaction;
            $workflow = $previousReview->workflowStep ?? $transaction->currentWorkflow;

            if (!$workflow) {
                throw new \Exception('Workflow step not found');
            }

            // Calculate due date
            $dueDate = $this->calculateDueDate(
                $workflow->process_time_value,
                $workflow->process_time_unit
            );

            // Create new review record
            $newReview = TransactionReviewer::create([
                'transaction_id' => $transaction->id,
                'reviewer_id' => $reassignToReviewerId ?? $previousReview->reviewer_id,
                'department_id' => $workflow->department_id,
                'workflow_step_id' => $workflow->id,
                'status' => 'pending',
                'reviewer_notes' => null,
                'process_time_value' => $workflow->process_time_value,
                'process_time_unit' => $workflow->process_time_unit,
                'due_date' => $dueDate,
                'is_overdue' => false,
                'iteration_number' => $previousReview->iteration_number + 1,
                'previous_reviewer_id' => $previousReview->reviewer_id,
                'rejection_reason' => null,
                'resubmission_deadline' => null,
            ]);

            // Reset transaction status to in_progress for resubmission
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
     * Get all iterations for a specific workflow step and transaction
     */
    public function getIterationHistory(int $transactionId, int $workflowId)
    {
        return TransactionReviewer::where('transaction_id', $transactionId)
            ->where('workflow_step_id', $workflowId)
            ->orderBy('iteration_number', 'asc')
            ->with(['reviewer', 'department'])
            ->get();
    }

    /**
     * Get cycle statistics for a transaction workflow step
     */
    public function getCycleStatistics(int $transactionId, int $workflowId): array
    {
        $iterations = $this->getIterationHistory($transactionId, $workflowId);
        $workflow = TransactionWorkflow::find($workflowId);

        return [
            'workflow_id' => $workflowId,
            'transaction_id' => $transactionId,
            'allow_cycles' => $workflow?->allow_cycles ?? false,
            'max_cycles' => $workflow?->max_cycle_count ?? 0,
            'current_iteration' => $iterations->count(),
            'total_iterations' => $iterations->count(),
            'approved_count' => $iterations->where('status', 'approved')->count(),
            'rejected_count' => $iterations->where('status', 're_submit')->count(),
            'pending_count' => $iterations->where('status', 'pending')->count(),
            'overdue_count' => $iterations->where('is_overdue', true)->count(),
            'iterations' => $iterations->map(fn($iter) => [
                'iteration_number' => $iter->iteration_number,
                'reviewer_id' => $iter->reviewer_id,
                'reviewer_name' => $iter->reviewer?->name,
                'status' => $iter->status,
                'due_date' => $iter->due_date,
                'reviewed_at' => $iter->reviewed_at,
                'rejection_reason' => $iter->rejection_reason,
                'is_overdue' => $iter->is_overdue,
            ])->toArray()
        ];
    }

    /**
     * Check if a cycle limit has been reached
     */
    public function isCycleLimitReached(TransactionReviewer $review): bool
    {
        $workflow = $review->workflowStep ?? $review->transaction->currentWorkflow;

        if (!$workflow || !$workflow->allow_cycles) {
            return false;
        }

        $iterationCount = $this->getIterationCount($review->transaction_id, $workflow->id);

        return $iterationCount >= $workflow->max_cycle_count;
    }

    /**
     * Get remaining cycles allowed
     */
    public function getRemainingCycles(int $transactionId, int $workflowId): int
    {
        $workflow = TransactionWorkflow::find($workflowId);

        if (!$workflow || !$workflow->allow_cycles) {
            return 0;
        }

        $currentCount = $this->getIterationCount($transactionId, $workflowId);

        return max(0, $workflow->max_cycle_count - $currentCount);
    }

    /**
     * Get the latest iteration for a workflow step
     */
    public function getLatestIteration(int $transactionId, int $workflowId): ?TransactionReviewer
    {
        return TransactionReviewer::where('transaction_id', $transactionId)
            ->where('workflow_step_id', $workflowId)
            ->orderBy('iteration_number', 'desc')
            ->first();
    }

    /**
     * Check if current iteration is overdue
     */
    public function isCurrentIterationOverdue(int $transactionId, int $workflowId): bool
    {
        $latest = $this->getLatestIteration($transactionId, $workflowId);

        if (!$latest) {
            return false;
        }

        return $latest->is_overdue || $latest->due_date < now();
    }

    /**
     * Get cycle timeline/history for display
     */
    public function getCycleTimeline(int $transactionId, int $workflowId): array
    {
        $iterations = $this->getIterationHistory($transactionId, $workflowId);

        return $iterations->map(fn($iter) => [
            'iteration' => $iter->iteration_number,
            'reviewer' => $iter->reviewer?->name,
            'department' => $iter->department?->name,
            'status' => $iter->status,
            'created_at' => $iter->created_at,
            'due_date' => $iter->due_date,
            'reviewed_at' => $iter->reviewed_at,
            'rejection_reason' => $iter->rejection_reason,
            'resubmission_deadline' => $iter->resubmission_deadline,
            'time_taken' => $iter->reviewed_at ? $iter->created_at->diffInDays($iter->reviewed_at) : null,
        ])->toArray();
    }

    /**
     * Check if transaction has excessive cycles (for monitoring)
     */
    public function isExcessiveCycling(int $transactionId, int $workflowId): bool
    {
        $workflow = TransactionWorkflow::find($workflowId);
        $iterations = $this->getIterationHistory($transactionId, $workflowId);

        if (!$workflow || !$workflow->allow_cycles) {
            return false;
        }

        // Flag as excessive if 80% of max cycles reached
        $threshold = $workflow->max_cycle_count * 0.8;

        return $iterations->count() >= $threshold;
    }

    /**
     * Reassign review to different reviewer and create new iteration
     */
    public function reassignAndCreateCycle(
        TransactionReviewer $previousReview,
        int $newReviewerId
    ): TransactionReviewer {
        if (!$this->canCreateNewIteration($previousReview)) {
            throw new \Exception('Cannot create new iteration: cycle limit reached');
        }

        return $this->createIterationCycle($previousReview, $newReviewerId);
    }

    /**
     * Calculate due date based on time value and unit
     */
    private function calculateDueDate(int $value, string $unit): Carbon
    {
        return match ($unit) {
            'days' => Carbon::now()->addDays($value),
            'weeks' => Carbon::now()->addWeeks($value),
            'minutes' => Carbon::now()->addMinutes($value),
            default => Carbon::now(),
        };
    }

    /**
     * Mark cycles as complete and return remaining cycles info
     */
    public function finalizeCycleProcess(int $transactionId, int $workflowId): array
    {
        $cycleStats = $this->getCycleStatistics($transactionId, $workflowId);

        return [
            'total_cycles_used' => $cycleStats['total_iterations'],
            'max_cycles_allowed' => $cycleStats['max_cycles'],
            'cycles_remaining' => max(0, $cycleStats['max_cycles'] - $cycleStats['total_iterations']),
            'final_status' => $this->getLatestIteration($transactionId, $workflowId)?->status,
        ];
    }
}