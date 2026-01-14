<?php

namespace App\Services\Transaction;

use App\Models\Transaction;
use App\Models\TransactionLog;
use App\Models\Workflow;
use App\Models\User;
use App\Models\WorkflowLog;
use Illuminate\Support\Facades\DB;

class WorkflowEngineService
{
    /**
     * Initialize a new transaction with workflow
     */
    public function initializeTransaction(Transaction $transaction): void
    {
        $workflow = $transaction->workflow;
        
        if (!$workflow->hasWorkConfigured()) {
            throw new \Exception('Workflow has no configuration.');
        }

        $initialState = $workflow->getInitialState();
        
        $transaction->update([
            'current_state' => $initialState,
            'transaction_status' => 'in_progress',
            'revision_number' => 1,
        ]);
    }

    /**
     * Execute a workflow action
     */
    public function executeAction(
        Transaction $transaction,
        string $action,
        User $actor,
        ?string $remarks = null,
        ?int $returnToDepartmentId = null
    ): bool {
        try {
            DB::beginTransaction();

            $currentState = $transaction->current_state;
            $transitions = $transaction->workflow->getTransition();

            // Handle resubmit action on returned states
            // When a transaction is returned (rejected), resubmitting should move it back to the rejecting department
            if (str_starts_with($currentState, 'returned_to_') && $action === 'resubmit') {
                // Find the department that rejected it
                $lastRejectedReviewer = $transaction->reviewers()
                    ->where('status', 'rejected')
                    ->latest('reviewed_at')
                    ->first();
                
                if ($lastRejectedReviewer && $lastRejectedReviewer->department) {
                    $departmentName = strtolower(str_replace(' ', '_', $lastRejectedReviewer->department->name));
                    $nextState = "pending_{$departmentName}_review";
                } else {
                    // Fallback to first step if we can't determine the rejecting department
                    $nextState = $transaction->workflow->getInitialState();
                }
            }
            // Allow forward approval on returned states by aliasing to resubmit
            // This supports UI semantics where reviewers "approve" after corrections.
            elseif (str_starts_with($currentState, 'returned_to_') && $action === 'approve') {
                $action = 'resubmit';
                // Determine next state same as resubmit
                $lastRejectedReviewer = $transaction->reviewers()
                    ->where('status', 'rejected')
                    ->latest('reviewed_at')
                    ->first();
                
                if ($lastRejectedReviewer && $lastRejectedReviewer->department) {
                    $departmentName = strtolower(str_replace(' ', '_', $lastRejectedReviewer->department->name));
                    $nextState = "pending_{$departmentName}_review";
                } else {
                    $nextState = $transaction->workflow->getInitialState();
                }
            }
            else {
                // Check if action is valid for current state
                if (!isset($transitions[$currentState][$action])) {
                    throw new \Exception("Action '{$action}' is not valid for current state '{$currentState}'.");
                }

                $nextState = $transitions[$currentState][$action];
            }

            // For reject action, we might need to determine the specific return destination
            if ($action === 'reject' && $returnToDepartmentId) {
                $nextState = $this->getReturnState($transaction, $returnToDepartmentId);
            }

            // Log the transition
            TransactionLog::create([
                'transaction_id' => $transaction->id,
                'from_state' => $currentState,
                'to_state' => $nextState,
                'action' => $action,
                'action_by' => $actor->id,
                'remarks' => $remarks,
            ]);

            // Update transaction state
            $updateData = ['current_state' => $nextState];

            if ($nextState === 'completed') {
                $updateData['transaction_status'] = 'completed';
                $updateData['completed_at'] = now();
            } elseif ($nextState === 'cancelled') {
                $updateData['transaction_status'] = 'cancelled';
            } elseif (str_starts_with($nextState, 'returned_to_')) {
                // Increment revision on return
                $transaction->incrementRevision();
            }

            $transaction->update($updateData);

            DB::commit();
            return true;

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Get return state for a specific department
     */
    protected function getReturnState(Transaction $transaction, int $departmentId): string
    {
        $steps = $transaction->getWorkflowSteps();
        
        foreach ($steps as $step) {
            if ($step['department_id'] == $departmentId) {
                $deptName = $this->sanitizeDepartmentName($step['department_name']);
                return "returned_to_{$deptName}";
            }
        }

        throw new \Exception('Invalid return department.');
    }

    /**
     * Sanitize department name for state string
     */
    protected function sanitizeDepartmentName(string $name): string
    {
        return strtolower(str_replace(' ', '_', preg_replace('/[^a-zA-Z0-9\s]/', '', $name)));
    }

    /**
     * Get available actions for a transaction and user
     */
    public function getAvailableActions(Transaction $transaction, User $user): array
    {
        $currentState = $transaction->current_state;
        $transitions = $transaction->workflow->getTransition();

        if (!isset($transitions[$currentState])) {
            return [];
        }

        $actions = [];
        $rawActions = $transitions[$currentState];

        // Check if user can perform actions on this state
        // (You may want to add department-based permission checks here)
        
        foreach ($rawActions as $action => $nextState) {
            $actions[] = [
                'action' => $action,
                'label' => $this->getActionLabel($action),
                'next_state' => $nextState,
                'requires_remarks' => in_array($action, ['reject', 'cancel']),
            ];
        }

        return $actions;
    }

    /**
     * Get human-readable action label
     */
    protected function getActionLabel(string $action): string
    {
        return match($action) {
            'approve' => 'Approve & Forward',
            'reject' => 'Return for Revision',
            'resubmit' => 'Resubmit',
            'cancel' => 'Cancel Transaction',
            default => ucfirst($action),
        };
    }

    /**
     * Get return options for reject action
     */
    public function getReturnOptions(Transaction $transaction): array
    {
        $steps = $transaction->getWorkflowSteps();
        $currentState = $transaction->current_state;

        // Find current step index
        $currentDept = $transaction->getCurrentDepartmentFromState();
        $currentIndex = -1;
        
        foreach ($steps as $index => $step) {
            if ($step['department_name'] === $currentDept) {
                $currentIndex = $index;
                break;
            }
        }

        if ($currentIndex <= 0) {
            return [];
        }

        // Return option is always the previous step
        $previousStep = $steps[$currentIndex - 1] ?? null;
        
        if (!$previousStep) {
            return [];
        }

        return [
            [
                'department_id' => $previousStep['department_id'],
                'department_name' => $previousStep['department_name'],
            ]
        ];
    }

    /**
     * Get workflow progress visualization data
     */
    public function getWorkflowProgress(Transaction $transaction): array
    {
        $steps = $transaction->getWorkflowSteps();
        
        // If no steps from snapshot, try from workflow
        if (empty($steps)) {
            $steps = $transaction->workflow?->getWorkflowSteps() ?? [];
        }
        
        $currentStep = $transaction->current_workflow_step;
        $currentState = $transaction->current_state;

        $progress = [];

        foreach ($steps as $index => $step) {
            $stepNumber = $index + 1;
            $status = 'pending';
            
            if ($stepNumber < $currentStep) {
                $status = 'completed';
            } elseif ($stepNumber === $currentStep) {
                $status = str_starts_with($currentState, 'returned_to_') ? 'returned' : 'current';
            }

            $progress[] = [
                'order' => $step['order'] ?? $stepNumber,
                'department_id' => $step['department_id'],
                'department_name' => $step['department_name'],
                'process_time_value' => $step['process_time_value'] ?? 3,
                'process_time_unit' => $step['process_time_unit'] ?? 'days',
                'status' => $status,
            ];
        }

        // Mark all as completed if transaction is done
        if ($transaction->isCompleted()) {
            foreach ($progress as &$step) {
                $step['status'] = 'completed';
            }
        }

        return ['steps' => $progress];
    }
}