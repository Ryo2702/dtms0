<?php

namespace App\Services\Transaction;

use App\Models\Transaction;
use App\Models\TransactionReviewer;
use App\Models\TransactionWorkflow;
use App\Models\User;
use App\Models\WorkflowHistory;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

use function Symfony\Component\Clock\now;

class WorkflowService
{
    public function getNextWorkflowStep(TransactionWorkflow $currentWorkflow, string $reviewStatus)
    {
        return match ($reviewStatus) {
            'approved' => $currentWorkflow->nextStepOnApproval(),
            're_submit' => $currentWorkflow->nextStepOnRejection(),
            'return_to_originating' => TransactionWorkflow::byTransactionType($currentWorkflow->transaction_type_id)->originating()->first()
        };
    }


    public function moveToNexStep(Transaction $transaction, string $reviewStatus)
    {
        try {
            DB::beginTransaction();

            $currentWorkflow = $transaction->currentWorkflow;
            if (!$currentWorkflow) {
                throw new \Exception('Current workflow step not found');
            }

            $nextWorkflow = $this->getNextWorkflowStep($currentWorkflow, $reviewStatus);


            if ($nextWorkflow) {
                $transaction->update([
                    'transaction_status' => 'completed',
                    'completed_at' => now()
                ]);
            } else {
                $transaction->update([
                    'current_workflow_step' => $nextWorkflow->id
                ]);

                $this->recordWorkflowTransition($transaction, $currentWorkflow, $nextWorkflow, $reviewStatus);
            }

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function recordWorkflowTransition(
        Transaction $transaction,
        TransactionWorkflow $fromWorkflow,
        TransactionWorkflow $toWorkflow,
        string $status
    ) {
        return WorkflowHistory::create([
            'transaction_id' => $transaction->id,
            'from_workflow_id' => $fromWorkflow->id,
            'to_workflow_id' => $toWorkflow->id,
            'transition_status' => $status,
            'transitioned_at' => now()
        ]);
    }

    public function createReviewForCurrentStep(Transaction $transaction, User $reviewer)
    {
        $workflow = $transaction->currentWorkflow;

        if (!$workflow) {
            throw new \Exception('No current workflow step found');
        }
        $dueDate = $this->calculateDueDate(
            $workflow->process_time_value,
            $workflow->process_time_unit,
        );

        return TransactionReviewer::create([
            'transaction_id' => $transaction->id,
            'reviewer_id' => $reviewer->id,
            'department_id' => $workflow->department_id,
            'workflow_step_id' => $workflow->id,
            'status' => 'pending',
            'proccess_time_value' => $workflow->process_time_value,
            'process_time_unit' => $workflow->process_time_unit,
            'due_date' => $dueDate,
            'iteration_number'
        ]);
    }

    public function approveReview(TransactionReviewer $review, $notes = null)
    {
        try {
            DB::beginTransaction();

            $review->update([
                'status' => 'approved',
                'reviewer_notes' => $notes,
                'review_at' => now()
            ]);


            $this->moveToNexStep($review->transaction, 'approved');

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function rejectReview(
        TransactionReviewer $review,
        string $rejectionReason,
        int $resubmissionDays = 3
    ) {
        try {
            DB::beginTransaction();

            $resubmissionDays = Carbon::now()->addDays($resubmissionDays);

            $review->update([
                'status' => 're_submit',
                'rejection_reason' => $rejectionReason,
                'reviewer_notes' => $rejectionReason,
                'resubmission_deadline' => $resubmissionDays,
                'review_at' => now(),
            ]);

            //Check if can Cycle
            if ($this->canCreateNewIteration($review)) {
                //create new iteration record
                $this->canCreateIterationCycle($review);
            } else {
                //mark as unable to continue
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

    public function canCreateNewIteration(TransactionReviewer $review)
    {
        $workflow = $review->workflow ?? $review->transaction->currentWorkflow;


        if (!$workflow || !$workflow->allow_cycles) {
            return false;
        }

        $iterationCount = TransactionReviewer::where('transaction_id', $review->transaction_id)->where('workflow_step_id', $review->workflow_step_id ?? $workflow->id)->count();

        return $iterationCount < $workflow->max_cycle_count;
    }

    public function canCreateIterationCycle(TransactionReviewer $previousReview)
    {
        $transaction = $previousReview->transaction;
        $workflow = $previousReview->workflow ?? $transaction->currentWorkflow;

        $newReview = TransactionReviewer::create([
            'transaction_id' => $transaction->id,
            'reviewer_id' => $previousReview->reviewer_id,
            'department_id' => $workflow->department_id,
            'workflow_step_id' => $workflow->id,
            'status' => 'pending',
            'reviewer_notes' => null,
            'process_time_value' => $workflow->process_time_value,
            'process_time_unit' => $workflow->process_time_unit,
            'due_date' => $this->calculateDueDate(
                $workflow->process_time_value,
                $workflow->process_time_unit
            ),
            'is_overdue' => false,
            'iteration_number' => $previousReview->iteration_number + 1,
            'previous_reviewer_id' => $previousReview->reviewer_id
        ]);

        // Update transaction status back to in_progress for re-submission
        $transaction->update([
            'transaction_status' => 'in_progress'
        ]);

        return $newReview;
    }

    public function returnToOriginating(TransactionReviewer $review, string $reason): bool
    {
        try {
            DB::beginTransaction();

            $review->update([
                'status' => 'return_to_originating',
                'rejection_reason' => $reason,
                'reviewer_notes' => $reason,
                'reviewed_at' => now()
            ]);

            // Get the originating workflow step
            $originatingWorkflow = TransactionWorkflow::byTransactionType($review->transaction->transaction_type_id)
                ->originating()
                ->first();

            if ($originatingWorkflow) {
                $review->transaction->update([
                    'current_workflow_step' => $originatingWorkflow->id
                ]);

                $this->recordWorkflowTransition(
                    $review->transaction,
                    $review->workflow ?? $review->transaction->currentWorkflow,
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

    private function calculateDueDate(int $value, string $unit): Carbon
    {
        return match ($unit) {
            'days' => Carbon::now()->addDays($value),
            'hours' => Carbon::now()->addHours($value),
            'minutes' => Carbon::now()->addMinutes($value),
            default => Carbon::now()
        };
    }


    public function updateOverdueReviews(): int
    {
        $updatedCount = TransactionReviewer::where('status', 'pending')
            ->where('due_date', '<', now())
            ->update(['is_overdue' => true]);

        // Also mark transaction as overdue if any review is overdue
        $overdueReviews = TransactionReviewer::where('is_overdue', true)
            ->where('status', 'pending')
            ->pluck('transaction_id')
            ->unique();

        Transaction::whereIn('id', $overdueReviews)
            ->where('transaction_status', 'in_progress')
            ->update(['transaction_status' => 'overdue']);

        return $updatedCount;
    }

    public function getWorkflowChain(int $transactionTypeId): array
    {
        return TransactionWorkflow::byTransactionType($transactionTypeId)
            ->orderedBySequence()
            ->with(['department', 'nextStepOnApproval', 'nextStepOnRejection'])
            ->get()
            ->toArray();
    }


    public function getReviewProgress(Transaction $transaction): array
    {
        $allReviews = $transaction->reviews()->get();
        $workflow = $transaction->currentWorkflow;

        return [
            'transaction_id' => $transaction->id,
            'current_step' => $workflow ? $workflow->id : null,
            'current_step_name' => $workflow ? $workflow->department->name : null,
            'total_reviews' => $allReviews->count(),
            'completed_reviews' => $allReviews->where('status', 'approved')->count(),
            'pending_reviews' => $allReviews->where('status', 'pending')->count(),
            'rejected_reviews' => $allReviews->where('status', 're_submit')->count(),
            'overdue_reviews' => $allReviews->where('is_overdue', true)->count(),
            'history' => $transaction->workflowHistory()->with('workflow.department')->get()
        ];
    }

    public function validateWorkflowConfiguration(TransactionWorkflow $workflow): array
    {
        $errors = [];

        if ($workflow->allow_cycles && $workflow->max_cycle_count < 1) {
            $errors[] = 'Max cycle count must be at least 1 when cycles are allowed';
        }

        if ($workflow->nextStepOnApproval && $workflow->nextStepOnApproval->transaction_type_id !== $workflow->transaction_type_id) {
            $errors[] = 'Next step on approval must be in the same transaction type';
        }

        if ($workflow->nextStepOnRejection && $workflow->nextStepOnRejection->transaction_type_id !== $workflow->transaction_type_id) {
            $errors[] = 'Next step on rejection must be in the same transaction type';
        }

        return $errors;
    }
}
