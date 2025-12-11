<?php

namespace App\Services\Transaction;

use App\Models\Department;
use App\Models\TransactionType;

class WorkflowConfigService
{
    /**
     * Build workflow configuration from admin input
     * 
     * @param array $steps Array of step configurations
     *   [
     *     ['department_id' => 1, 'can_return_to' => []],
     *     ['department_id' => 2, 'can_return_to' => [1]],
     *     ['department_id' => 3, 'can_return_to' => [1, 2]],
     *   ]
     */
    public function buildWorkflowConfig(array $steps): array
    {
        $config = [
            'steps' => [],
            'transitions' => [],
        ];

        // Build steps with department info
        $departments = Department::whereIn('id', array_column($steps, 'department_id'))
            ->pluck('name', 'id')
            ->toArray();

        foreach ($steps as $index => $step) {
            $deptId = $step['department_id'];
            $deptName = $departments[$deptId] ?? "Department_{$deptId}";
            
            $config['steps'][] = [
                'order' => $index + 1,
                'department_id' => $deptId,
                'department_name' => $deptName,
                'can_return_to' => $step['can_return_to'] ?? [],
            ];
        }

        // Build transitions automatically
        $config['transitions'] = $this->buildTransitions($config['steps']);

        return $config;
    }

    /**
     * Build transition map from steps
     */
    protected function buildTransitions(array $steps): array
    {
        $transitions = [];
        $stepCount = count($steps);

        foreach ($steps as $index => $step) {
            $deptName = $this->sanitizeDepartmentName($step['department_name']);
            $currentState = "pending_{$deptName}_review";
            $returnedState = "returned_to_{$deptName}";

            $transitions[$currentState] = [];

            // Forward transition (approve)
            if ($index < $stepCount - 1) {
                // Not the final step - approve goes to next department
                $nextDept = $this->sanitizeDepartmentName($steps[$index + 1]['department_name']);
                $transitions[$currentState]['approve'] = "pending_{$nextDept}_review";
            } else {
                // Final step - approve completes the workflow
                $transitions[$currentState]['approve'] = 'completed';
            }

            // Backward transitions (reject) based on can_return_to
            foreach ($step['can_return_to'] as $returnToDeptId) {
                // Find the step with this department_id
                $returnToStep = collect($steps)->firstWhere('department_id', $returnToDeptId);
                if ($returnToStep) {
                    $returnToDept = $this->sanitizeDepartmentName($returnToStep['department_name']);
                    $transitions[$currentState]['reject'] = "returned_to_{$returnToDept}";
                    // Only one reject destination per step (take the first one for simplicity)
                    // Or we can allow selection during runtime
                    break;
                }
            }

            // If this step can be returned to, add resubmit transition
            $canBeReturnedTo = collect($steps)->contains(function ($s) use ($step) {
                return in_array($step['department_id'], $s['can_return_to'] ?? []);
            });

            if ($canBeReturnedTo || $index === 0) {
                // Find which step returns to this one
                $returningStep = collect($steps)->first(function ($s) use ($step) {
                    return in_array($step['department_id'], $s['can_return_to'] ?? []);
                });

                if ($returningStep) {
                    $returningDept = $this->sanitizeDepartmentName($returningStep['department_name']);
                    $transitions[$returnedState] = [
                        'resubmit' => "pending_{$returningDept}_review",
                    ];
                } else {
                    // Default: resubmit goes to next step
                    if ($index < $stepCount - 1) {
                        $nextDept = $this->sanitizeDepartmentName($steps[$index + 1]['department_name']);
                        $transitions[$returnedState] = [
                            'resubmit' => "pending_{$nextDept}_review",
                        ];
                    }
                }
            }
        }

        // Add cancel transition from any pending state
        foreach ($transitions as $state => $actions) {
            if (str_starts_with($state, 'pending_')) {
                $transitions[$state]['cancel'] = 'cancelled';
            }
        }

        return $transitions;
    }

    /**
     * Sanitize department name for state string
     */
    protected function sanitizeDepartmentName(string $name): string
    {
        return str_replace(' ', '_', preg_replace('/[^a-zA-Z0-9\s]/', '', $name));
    }

    /**
     * Validate workflow configuration
     */
    public function validateConfig(array $config): array
    {
        $errors = [];

        if (empty($config['steps'])) {
            $errors[] = 'At least one workflow step is required.';
            return $errors;
        }

        // Check for duplicate departments
        $deptIds = array_column($config['steps'], 'department_id');
        if (count($deptIds) !== count(array_unique($deptIds))) {
            $errors[] = 'Each department can only appear once in the workflow.';
        }

        // Check that can_return_to references valid earlier steps
        foreach ($config['steps'] as $index => $step) {
            foreach ($step['can_return_to'] ?? [] as $returnToDeptId) {
                $found = false;
                for ($i = 0; $i < $index; $i++) {
                    if ($config['steps'][$i]['department_id'] == $returnToDeptId) {
                        $found = true;
                        break;
                    }
                }
                if (!$found) {
                    $errors[] = "Step {$step['department_name']} can only return to earlier steps in the workflow.";
                }
            }
        }

        // Check final step leads to completed
        $lastStep = end($config['steps']);
        $lastDept = $this->sanitizeDepartmentName($lastStep['department_name']);
        $lastState = "pending_{$lastDept}_review";
        
        if (($config['transitions'][$lastState]['approve'] ?? '') !== 'completed') {
            $errors[] = 'Final step must lead to completed state.';
        }

        return $errors;
    }

    /**
     * Get available return destinations for a step
     */
    public function getReturnableSteps(TransactionType $transactionType, int $currentStepIndex): array
    {
        $steps = $transactionType->getWorkflowSteps();
        $returnable = [];

        for ($i = 0; $i < $currentStepIndex; $i++) {
            $returnable[] = $steps[$i];
        }

        return $returnable;
    }
}