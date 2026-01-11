<?php

namespace App\Services\Transaction;

use App\Models\Transaction;
use App\Models\TransactionReviewer;
use App\Models\User;
use App\Models\Workflow;
use Illuminate\Support\Facades\DB;

use function Symfony\Component\Clock\now;

class TrasactionService
{

    public function __construct(
        protected WorkflowEngineService $workflowEngine
    ) {}

    //paginated transaction with filters
    public function getTransactions(array $filters = [], int $perPage = 10)
    {
        $query = Transaction::with(['workflow', 'creator', 'department', 'assignStaff', 'currentReviewer']);


        if (!empty($filters['status'])) {
            $query->where('transaction_status', $filters['status']);
        }

        if (!empty($filters['urgency'])) {
            $query->where('level_of_urgency', $filters['urgent']);
        }

        if (!empty($filters['department_id'])) {
            $query->where('department_id', $filters['department_id']);
        }

        if (!empty($filters['workflow_id'])) {
            $query->where('workflow_id', $filters['workflow_id']);
        }

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('transaction_code', 'like', "%{$search}%")->orWhereHas('workflow', fn($wq) => $wq->where('transaction_name', 'like', "%{$search}%"));
            });
        }

        if (!empty($filters['date_from'])) {
            $query->whereDate('submitted_at', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->whereDate('submitted_at', '<=', $filters['date_to']);
        }

        return $query->orderBy('submitted_at', 'desc')->paginate($perPage);
    }


    //get transaction for a specific user based on their department
    public function getTransactionForUser(User $user, array $filters = [], int $perPage = 10)
    {
        $query = Transaction::with(['workflow', 'creator', 'department', 'assignStaff', 'currentReviewer']);

        $query->where(function ($q) use ($user) {
            $q->where('department_id', $user->department_id)
                ->orWhere('created_by', $user->id)
                ->orWhereHas('reviewers', fn($rq) => $rq->where('reviewer_id', $user->id));
        });

        // Apply additional filters
        if (!empty($filters['status'])) {
            $query->where('transaction_status', $filters['status']);
        }

        if (!empty($filters['urgency'])) {
            $query->where('level_of_urgency', $filters['urgency']);
        }

        return $query->orderBy('submitted_at', 'desc')->paginate($perPage);
    }

    //create a new transaction
    public function createTransaction(array $data, User $creator)
    {
        return DB::transaction(function () use ($data, $creator) {
            $workflow = Workflow::findOrFail($data['workflow_id']);

            // Use custom workflow_snapshot if provided (Head user modified it)
            // Otherwise use the default from workflow
            $workflowSnapshot = $data['workflow_snapshot'] ?? $workflow->workflow_config;
            
            // Normalize steps - ensure order is set based on array index
            if (isset($workflowSnapshot['steps'])) {
                $workflowSnapshot['steps'] = $this->normalizeWorkflowSteps($workflowSnapshot['steps']);
            }
            
            // Calculate total steps from the snapshot
            $workflowSteps = $workflowSnapshot['steps'] ?? $workflow->getWorkflowSteps();
            $totalSteps = count($workflowSteps);

            // Prepare custom document tags data if provided
            $customDocumentTags = null;
            if (isset($data['document_tag_ids']) && is_array($data['document_tag_ids']) && count($data['document_tag_ids']) > 0) {
                $documentTags = \App\Models\DocumentTag::whereIn('id', $data['document_tag_ids'])->get();
                $customDocumentTags = $documentTags->map(function($tag) {
                    return [
                        'id' => $tag->id,
                        'name' => $tag->name,
                        'description' => $tag->description ?? null
                    ];
                })->toArray();
            }

            $transaction = Transaction::create([
                'transaction_code' => Transaction::generateTransactionCode(),
                'workflow_id' => $data['workflow_id'],
                'workflow_snapshot' => $workflowSnapshot,
                'total_workflow_steps' => $totalSteps,
                'assign_staff_id' => $data['assign_staff_id'],
                'department_id' => $data['department_id'] ?? $creator->department_id,
                'origin_department_id' => $data['department_id'] ?? $creator->department_id,
                'level_of_urgency' => $data['level_of_urgency'] ?? 'normal',
                'created_by' => $creator->id,
                'current_workflow_step' => 1,
                'submitted_at' => now(),
                'custom_document_tags' => $customDocumentTags
            ]);

            $this->workflowEngine->initializeTransaction($transaction);

            // Create initial reviewer based on the workflow_snapshot (could be customized by Head)
            $this->createInitialReviewer($transaction, $workflowSteps);

            return $transaction->fresh(['workflow', 'creator', 'department', 'reviewers']);
        });
    }

    public function updateTransaction(Transaction $transaction, array $data)
    {
        return DB::transaction(function () use ($transaction, $data) {
            if ($transaction->isCompleted() || $transaction->isCancelled()) {
                throw new \Exception('Cannot updated completed or cancelled transaction');
            }

            $updateData = [];

            if (isset($data['level_of_urgency'])) {
                $updateData['level_of_urgency'] = $data['level_of_urgency'];
            }

            if (isset($data['assign_staff_id'])) {
                $updateData['assign_staff_id'] = $data['assign_staff_id'];
            }

            if (isset($data['department_id'])) {
                $updateData['department_id'] = $data['department_id'];
            }

            // Allow workflow_snapshot update only if transaction is still at initial state (step 1)
            if (isset($data['workflow_snapshot']) && (int) $transaction->current_workflow_step === 1) {
                $newSnapshot = $data['workflow_snapshot'];
                
                // Normalize steps - ensure order is set based on array index
                if (isset($newSnapshot['steps'])) {
                    $newSnapshot['steps'] = $this->normalizeWorkflowSteps($newSnapshot['steps']);
                }
                
                $newSteps = $newSnapshot['steps'] ?? [];
                $updateData['workflow_snapshot'] = $newSnapshot;
                $updateData['total_workflow_steps'] = count($newSteps);
                
                // Check if first step's department changed - need to update reviewer
                $oldSteps = $transaction->workflow_snapshot['steps'] ?? [];
                if ($this->hasFirstStepChanged($oldSteps, $newSteps)) {
                    // Delete current pending reviewer and create new one based on new first step
                    $this->recreateInitialReviewer($transaction, $newSteps);
                }
            }

            if (!empty($updateData)) {
                $transaction->update($updateData);
            }

            return $transaction->fresh(['workflow', 'creator', 'department', 'reviewers']);
        });
    }

    /**
     * Normalize workflow steps - ensure order field matches array index
     */
    protected function normalizeWorkflowSteps(array $steps): array
    {
        return array_values(array_map(function ($step, $index) {
            $step['order'] = $index + 1;
            $step['process_time_value'] = (int) ($step['process_time_value'] ?? 1);
            $step['process_time_unit'] = $step['process_time_unit'] ?? 'days';
            return $step;
        }, $steps, array_keys($steps)));
    }

    /**
     * Check if the first step department has changed
     */
    protected function hasFirstStepChanged(array $oldSteps, array $newSteps): bool
    {
        $oldFirstDept = $oldSteps[0]['department_id'] ?? null;
        $newFirstDept = $newSteps[0]['department_id'] ?? null;
        
        return $oldFirstDept != $newFirstDept;
    }

    /**
     * Recreate the initial reviewer when workflow steps are modified
     */
    protected function recreateInitialReviewer(Transaction $transaction, array $workflowSteps): void
    {
        // Delete existing pending reviewers (only if at step 1)
        $transaction->reviewers()
            ->where('status', 'pending')
            ->delete();
        
        // Create new reviewer based on updated first step
        $this->createInitialReviewer($transaction, $workflowSteps);
    }

    public function getTransactionDetails(Transaction $transaction)
    {
        return $transaction->load([
            'workflow',
            'creator',
            'department',
            'assignStaff',
            'reviewers.reviewer',
            'reviewers.department',
            'currentReviewer.reviewer',
            'currentReviewer.department',
            'transactionLogs.actionBy'
        ]);
    }
    public function executeAction(
        Transaction $transaction,
        string $action,
        User $actor,
        ?string $remarks = null,
        ?int $returnToDepartmentId = null
    ) {
        $this->workflowEngine->executeAction(
            $transaction,
            $action,
            $actor,
            $remarks,
            $returnToDepartmentId
        );

        //Update reviewer status based on action
        $this->updateReviewerStatus($transaction, $action, $actor, $remarks);

        if ($action === 'approve' && !$transaction->fresh()->isCompleted()) {
            $this->createNextReviewer($transaction->fresh());
        }

        return $transaction->fresh(['workflow', 'reviewers', 'transactionLogs']);
    }

    public function getWorkflowProgress(Transaction $transaction)
    {
        return $this->workflowEngine->getWorkflowProgress($transaction);
    }

    public function getAvailableActions(Transaction $transaction, User $user)
    {
        $currentReviewer = $transaction->currentReviewer;
        if (!$currentReviewer || $currentReviewer->reviewer_id !== $user->id) {
            return [];
        }

        return $this->workflowEngine->getAvailableActions($transaction, $user);
    }
    public function createInitialReviewer(Transaction $transaction, array $workflowSteps)
    {
        if (empty($workflowSteps)) {
            return;
        }

        $firstStep = $workflowSteps[0];
        $dueDate = $this->calculateDueDate($firstStep);

        TransactionReviewer::create([
            'transaction_id' => $transaction->id,
            'reviewer_id' => $this->getReviewerForDepartment($firstStep['department_id']),
            'department_id' => $firstStep['department_id'],
            'status' => 'pending',
            'due_date' => $dueDate,
            'iteration_number' => 1,
        ]);
    }

    protected function createNextReviewer(Transaction $transaction)
    {
        $workflowSteps = $transaction->getWorkflowSteps();
        $currentStep = $transaction->current_workflow_step;
        $nextStepIndex = $currentStep; // 0-indexed, so current step = next index

        if ($nextStepIndex >= count($workflowSteps)) {
            return; // No more steps
        }

        $nextStep = $workflowSteps[$nextStepIndex];
        $dueDate = $this->calculateDueDate($nextStep);

        TransactionReviewer::create([
            'transaction_id' => $transaction->id,
            'reviewer_id' => $this->getReviewerForDepartment($nextStep['department_id']),
            'department_id' => $nextStep['department_id'],
            'status' => 'pending',
            'due_date' => $dueDate,
            'iteration_number' => $transaction->revision_number,
        ]);

        $transaction->increment('current_workflow_step');
    }

    protected function updateReviewerStatus(
        Transaction $transaction,
        string $action,
        User $actor,
        ?string $remarks
    ) {

        $currentReviewer = $transaction->currentReviewer;

        if (!$currentReviewer) {
            return;
        }

        $statusMap = [
            'approve' => 'approved',
            'reject' => 'return_to_orginating',
            'resubmit' => 're_submit',
            'cancel' => 'cancelled',
        ];

        $currentReviewer->update([
            'status' => $statusMap[$action] ?? 'pending',
            'reviewed_at' => now(),
            'rejection_reason' => $action === 'reject' ? $remarks : null,
        ]);
    }

    protected function calculateDueDate(array $step)
    {
        $value = $step['process_time_value'] ?? 3;
        $unit = $step['process_time_unit'] ?? 'minutes';

        $dueDate = now();

        return match ($unit) {
            'days' => $dueDate->modify("+{$value} days"),
            'hours' => $dueDate->modify("+{$value} hours"),
            'weeks' => $dueDate->modify("+{$value} weeks"),
            default => $dueDate->modify("+{$value} minutes")
        };
    }

    protected function getReviewerForDepartment(int $departmentId)
    {
        // Find department head or first active user in department
        $reviewer = User::where('department_id', $departmentId)
            ->where('status', true)
            ->whereHas('roles', fn($q) => $q->whereIn('name', ['department_head', 'head']))
            ->first();

        if (!$reviewer) {
            // Fallback to any active user in department
            $reviewer = User::where('department_id', $departmentId)
                ->where('status', true)
                ->first();
        }

        if (!$reviewer) {
            throw new \Exception("No reviewer found for department ID: {$departmentId}");
        }

        return $reviewer->id;
    }


    public function getStatistics(?int $departmentId = null): array
    {
        $query = Transaction::query();

        if ($departmentId) {
            $query->where('department_id', $departmentId);
        }

        return [
            'total' => (clone $query)->count(),
            'in_progress' => (clone $query)->inProgress()->count(),
            'completed' => (clone $query)->completed()->count(),
            'overdue' => (clone $query)->overdue()->count(),
            'urgent' => (clone $query)->where('level_of_urgency', '!=', 'normal')->count(),
        ];
    }
}
