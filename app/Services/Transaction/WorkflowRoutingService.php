<?php
namespace App\Services\Transaction;

use App\Models\Transaction;
use App\Models\TransactionType;
use App\Models\TransactionWorkflow;
use App\Models\TransactionReviewer;
use App\Models\User;
use App\Models\Department;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
class WorkflowRoutingService
{
    /**
     * Get all workflows for a specific transaction type
     * Dynamic and transaction-type specific retrieval
     */
    public function getWorkflowsByTransactionType($transactionTypeId): Collection
    {
        return TransactionWorkflow::byTransactionType($transactionTypeId)
            ->orderedBySequence()
            ->with(['transactionType', 'department', 'department.head'])
            ->get();
    }

    /**
     * Get active workflows ordered by sequence
     */
    public function getActiveWorkflowsByType($transactionTypeId): Collection
    {
        return TransactionWorkflow::byTransactionType($transactionTypeId)
            ->orderedBySequence()
            ->get();
    }

    /**
     * Create a new workflow route
     */
    public function createWorkflowRoute(array $data, User $user): TransactionWorkflow
    {
        // Verify user is head of the department
        if ($user->department_id !== $data['department_id'] && !$user->isAdmin()) {
            throw new \Exception('You can only create workflows for your department');
        }

        return TransactionWorkflow::create($data);
    }

    /**
     * Get the next workflow step based on current action
     */
    public function getNextWorkflowStep(Transaction $transaction, string $action): int
    {
        $workflows = $this->getWorkflowsByTransactionType($transaction->transaction_type_id);
        $currentStep = $transaction->current_workflow_step ?? 0;

        if ($action === 'return_to_originating') {
            return $workflows->where('is_originating', true)->first()?->sequence_order ?? 1;
        }

        // Get next sequential step in the cycle
        $nextWorkflow = $workflows
            ->where('sequence_order', '>', $currentStep)
            ->first();

        // If no next step, cycle back to beginning
        if (!$nextWorkflow) {
            return $workflows->first()?->sequence_order ?? 1;
        }

        return $nextWorkflow->sequence_order;
    }

    /**
     * Create an iterative reviewer record
     * Supports cyclical approval routing for heads
     */
    public function createIterativeReviewer(
        Transaction $transaction,
        User $currentUser,
        ?User $nextReviewer,
        array $data
    ): TransactionReviewer {
        $processTimeValue = $data['process_time_value'] ?? 5;
        $processTimeUnit = $data['process_time_unit'] ?? 'days';

        // Calculate due date
        $dueDate = $this->calculateDueDate($processTimeValue, $processTimeUnit);

        $reviewerData = [
            'transaction_id' => $transaction->id,
            'reviewer_id' => $nextReviewer?->id ?? $currentUser->id,
            'department_id' => $nextReviewer?->department_id ?? $currentUser->department_id,
            'status' => $data['action'] === 'approve' ? 'approved' : 'pending',
            'reviewer_notes' => $data['reviewer_notes'] ?? null,
            'process_time_value' => $processTimeValue,
            'process_time_unit' => $processTimeUnit,
            'due_date' => $dueDate,
            'is_overdue' => false,
            'reviewed_at' => $data['action'] === 'approve' ? now() : null,
        ];

        return TransactionReviewer::create($reviewerData);
    }

    /**
     * Get the complete workflow chain for a transaction
     * Shows the cyclical routing path taken
     */
    public function getTransactionWorkflowChain($transactionId): array
    {
        $reviewers = TransactionReviewer::where('transaction_id', $transactionId)
            ->with(['reviewer', 'transaction'])
            ->orderBy('created_at', 'asc')
            ->get();

        return $reviewers->map(function ($reviewer) {
            return [
                'step' => $reviewer->id,
                'reviewer_name' => $reviewer->reviewer->name,
                'reviewer_type' => $reviewer->reviewer->type,
                'department' => $reviewer->reviewer->department?->name,
                'status' => $reviewer->status,
                'notes' => $reviewer->reviewer_notes,
                'due_date' => $reviewer->due_date,
                'reviewed_at' => $reviewer->reviewed_at,
                'is_overdue' => $reviewer->is_overdue,
                'process_time' => "{$reviewer->process_time_value} {$reviewer->process_time_unit}",
            ];
        })->toArray();
    }

    /**
     * Verify transaction access for current user
     */
    public function verifyTransactionAccess(Transaction $transaction, User $user): void
    {
        // Check if user is in the workflow chain or is originating department head
        $isInChain = TransactionReviewer::where('transaction_id', $transaction->id)
            ->where('reviewer_id', $user->id)
            ->exists();

        $isOriginating = $transaction->assignStaff?->department_id === $user->department_id;

        if (!$isInChain && !$isOriginating && !$user->isAdmin()) {
            throw new \Exception('You do not have access to this transaction');
        }
    }

    /**
     * Configure the iterative workflow cycle for a transaction type
     */
    public function configureWorkflowCycle($transactionTypeId, array $departments): Collection
    {
        $workflows = collect();

        foreach ($departments as $dept) {
            $workflow = TransactionWorkflow::create([
                'transaction_type_id' => $transactionTypeId,
                'department_id' => $dept['department_id'],
                'sequence_order' => $dept['sequence_order'],
                'is_originating' => $dept['is_originating'],
            ]);

            $workflows->push($workflow);
        }

        return $workflows;
    }

    /**
     * Get pending transactions for a head
     */
    public function getPendingTransactionsForHead(User $head, ?int $transactionTypeId = null): Collection
    {
        $query = TransactionReviewer::where('reviewer_id', $head->id)
            ->where('status', 'pending');

        if ($transactionTypeId) {
            $query->whereHas('transaction', function ($q) use ($transactionTypeId) {
                $q->where('transaction_type_id', $transactionTypeId);
            });
        }

        return $query->with(['transaction', 'reviewer', 'department'])
            ->orderBy('due_date', 'asc')
            ->get();
    }

    /**
     * Get workflow statistics for a head
     */
    public function getHeadWorkflowStats(User $head): array
    {
        $reviewData = TransactionReviewer::where('reviewer_id', $head->id);

        return [
            'pending_count' => (clone $reviewData)->where('status', 'pending')->count(),
            'approved_count' => (clone $reviewData)->where('status', 'approved')->count(),
            'rejected_count' => (clone $reviewData)->where('status', 're_submit')->count(),
            'total_reviewed' => (clone $reviewData)->whereNotNull('reviewed_at')->count(),
            'average_time_to_approve' => $this->calculateAverageApprovalTime($head),
            'overdue_count' => (clone $reviewData)
                ->where('status', 'pending')
                ->where('due_date', '<', now())
                ->count(),
        ];
    }

    /**
     * Get the next reviewers in the workflow cycle
     */
    public function getNextReviewersInCycle($transactionId): array
    {
        $transaction = Transaction::findOrFail($transactionId);
        $workflows = $this->getWorkflowsByTransactionType($transaction->transaction_type_id);
        $currentStep = $transaction->current_workflow_step ?? 0;

        $nextWorkflows = $workflows
            ->where('sequence_order', '>', $currentStep)
            ->take(3); // Get next 3 steps in cycle

        return $nextWorkflows->map(function ($workflow) {
            return [
                'workflow_id' => $workflow->id,
                'department' => $workflow->department->name,
                'sequence_order' => $workflow->sequence_order,
                'head' => $workflow->department->head?->name,
                'head_id' => $workflow->department->head?->id,
                'is_originating' => $workflow->is_originating,
            ];
        })->toArray();
    }

    /**
     * Calculate due date based on process time
     */
    private function calculateDueDate(int $value, string $unit): Carbon
    {
        $now = now();

        return match ($unit) {
            'minutes' => $now->addMinutes($value),
            'hours' => $now->addHours($value),
            'days' => $now->addDays($value),
            default => $now->addDays($value),
        };
    }

    /**
     * Calculate average approval time for a head
     */
    private function calculateAverageApprovalTime(User $head): float
    {
        $approvals = TransactionReviewer::where('reviewer_id', $head->id)
            ->where('status', 'approved')
            ->whereNotNull('reviewed_at')
            ->get();

        if ($approvals->isEmpty()) {
            return 0;
        }

        $totalMinutes = $approvals->sum(function ($approval) {
            return $approval->created_at->diffInMinutes($approval->reviewed_at);
        });

        return round($totalMinutes / $approvals->count(), 2);
    }
}