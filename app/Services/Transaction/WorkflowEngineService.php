<?php

namespace App\Services\Transaction;

use App\Models\Transaction;
use App\Models\TransactionLog;
use App\Models\TransactionType;
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
        $transactionType = $transaction->transactionType;
        
        if (!$transactionType->hasWorkflowConfigured()) {
            throw new \Exception('Transaction type has no workflow configured.');
        }

        $initialState = $transactionType->getInitialState();
        
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
            $transitions = $transaction->transactionType->getTransitions();

            // Check if action is valid for current state
            if (!isset($transitions[$currentState][$action])) {
                throw new \Exception("Action '{$action}' is not valid for current state '{$currentState}'.");
            }

            $nextState = $transitions[$currentState][$action];

            // For reject action, we might need to determine the specific return destination
            if ($action === 'reject' && $returnToDepartmentId) {
                $nextState = $this->getReturnState($transaction->transactionType, $returnToDepartmentId);
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
    protected function getReturnState(TransactionType $transactionType, int $departmentId): string
    {
        $steps = $transactionType->getWorkflowSteps();
        
        foreach ($steps as $step) {
            if ($step['department_id'] == $departmentId) {
                $deptName = $transactionType->sanitizeDepartmentName($step['department_name']);
                return "returned_to_{$deptName}";
            }
        }

        throw new \Exception('Invalid return department.');
    }

    /**
     * Get available actions for a transaction and user
     */
    public function getAvailableActions(Transaction $transaction, User $user): array
    {
        $currentState = $transaction->current_state;
        $transitions = $transaction->transactionType->getTransitions();

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
        $steps = $transaction->transactionType->getWorkflowSteps();
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

        // Get the can_return_to from current step
        $currentStep = $steps[$currentIndex] ?? null;
        $canReturnTo = $currentStep['can_return_to'] ?? [];

        $options = [];
        foreach ($steps as $step) {
            if (in_array($step['department_id'], $canReturnTo)) {
                $options[] = [
                    'department_id' => $step['department_id'],
                    'department_name' => $step['department_name'],
                ];
            }
        }

        return $options;
    }

    /**
     * Get workflow progress visualization data
     */
    public function getWorkflowProgress(Transaction $transaction): array
    {
        $steps = $transaction->transactionType->getWorkflowSteps();
        $currentState = $transaction->current_state;
        $currentDept = $transaction->getCurrentDepartmentFromState();

        $progress = [];
        $foundCurrent = false;

        foreach ($steps as $step) {
            $status = 'pending';
            
            if ($step['department_name'] === $currentDept) {
                $status = str_starts_with($currentState, 'returned_to_') ? 'returned' : 'current';
                $foundCurrent = true;
            } elseif (!$foundCurrent) {
                $status = 'completed';
            }

            $progress[] = [
                'order' => $step['order'],
                'department_id' => $step['department_id'],
                'department_name' => $step['department_name'],
                'status' => $status,
                'can_return_to' => $step['can_return_to'],
            ];
        }

        // Mark as completed if transaction is done
        if ($transaction->isCompleted()) {
            foreach ($progress as &$step) {
                $step['status'] = 'completed';
            }
        }

        return $progress;
    }
}