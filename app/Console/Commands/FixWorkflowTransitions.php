<?php

namespace App\Console\Commands;

use App\Models\Workflow;
use Illuminate\Console\Command;

class FixWorkflowTransitions extends Command
{
    protected $signature = 'workflow:fix-transitions';
    protected $description = 'Fix workflow transitions to use lowercase state keys';

    public function handle()
    {
        $workflows = Workflow::all();
        
        foreach ($workflows as $workflow) {
            $config = $workflow->workflow_config;
            
            if (!empty($config['steps'])) {
                // Rebuild transitions with lowercase keys
                $config['transitions'] = $this->buildTransitions($config['steps']);
                $workflow->update(['workflow_config' => $config]);
                $this->info("Updated workflow: {$workflow->transaction_name}");
            }
        }
        
        $this->info('All workflow transitions have been fixed!');
        
        return Command::SUCCESS;
    }

    /**
     * Build transition map from steps with lowercase state names
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
                $nextDept = $this->sanitizeDepartmentName($steps[$index + 1]['department_name']);
                $transitions[$currentState]['approve'] = "pending_{$nextDept}_review";
            } else {
                $transitions[$currentState]['approve'] = 'completed';
            }

            // Backward transition (reject)
            if ($index > 0) {
                $prevDept = $this->sanitizeDepartmentName($steps[$index - 1]['department_name']);
                $transitions[$currentState]['reject'] = "returned_to_{$prevDept}";
            }

            // Add resubmit transition for returned states
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
     * Sanitize department name for state string (lowercase)
     */
    protected function sanitizeDepartmentName(string $name): string
    {
        return strtolower(str_replace(' ', '_', preg_replace('/[^a-zA-Z0-9\s]/', '', $name)));
    }
}
