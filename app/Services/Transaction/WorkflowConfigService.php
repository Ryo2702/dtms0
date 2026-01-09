<?php

namespace App\Services\Transaction;

use App\Models\Department;
use App\Models\Workflow;

class WorkflowConfigService
{
    /**
     * Build workflow configuration from admin input
     * 
     * @param array $steps Array of step configurations
     *   [
     *     ['department_id' => 1],
     *     ['department_id' => 2],
     *     ['department_id' => 3],
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
                'process_time_value' => (int) ($step['process_time_value'] ?? 3),
                'process_time_unit' => $step['process_time_unit'] ?? 'days',
                'notes' => $step['notes'] ?? '',
                'difficulty' => $step['difficulty'] ?? 'simple',
            ];
        }

        // Build transitions automatically
        $config['transitions'] = $this->buildTransitions($config['steps']);

        return $config;
    }

    /**
     * Build transition map from steps
     * Each step can reject back to the previous department
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

            // Backward transition (reject) - returns to previous department
            if ($index > 0) {
                $prevDept = $this->sanitizeDepartmentName($steps[$index - 1]['department_name']);
                $transitions[$currentState]['reject'] = "returned_to_{$prevDept}";
            }

            // Add resubmit transition for returned states (goes back to the department that rejected)
            if ($index < $stepCount - 1) {
                $nextDept = $this->sanitizeDepartmentName($steps[$index + 1]['department_name']);
                $transitions[$returnedState] = [
                    'resubmit' => "pending_{$nextDept}_review",
                ];
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
        return strtolower(str_replace(' ', '_', preg_replace('/[^a-zA-Z0-9\s]/', '', $name)));
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
    public function getReturnableSteps(Workflow $workflow, int $currentStepIndex): array
    {
        $steps = $workflow->getWorkflowSteps();
        $returnable = [];

        for ($i = 0; $i < $currentStepIndex; $i++) {
            $returnable[] = $steps[$i];
        }

        return $returnable;
    }
}