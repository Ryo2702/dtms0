<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Transaction;
use App\Services\Transaction\TrasactionService;
use App\Http\Requests\Transaction\TransactionRequest;

class TestWorkflowSnapshot extends Command
{
    protected $signature = 'test:workflow-snapshot';
    protected $description = 'Test workflow snapshot functionality';

    public function handle()
    {
        $this->info('Testing Workflow Snapshot Functionality');
        $this->newLine();

        // Test 1: JSON Parsing
        $this->info('Test 1: JSON Parsing');
        $workflowConfig = [
            'steps' => [
                [
                    'department_id' => 1,
                    'department_name' => 'Department A',
                    'process_time_value' => 2,
                    'process_time_unit' => 'days',
                ],
                [
                    'department_id' => 2,
                    'department_name' => 'Department B',
                    'process_time_value' => 3,
                    'process_time_unit' => 'hours',
                ],
            ],
        ];

        $json = json_encode($workflowConfig);
        $this->line("✓ Encoded JSON successfully");

        $decoded = json_decode($json, true);
        if (is_array($decoded) && isset($decoded['steps'])) {
            $this->line("✓ Decoded JSON successfully");
            $this->line("✓ Steps count: " . count($decoded['steps']));
        } else {
            $this->error("✗ JSON decode failed");
        }
        $this->newLine();

        // Test 2: Model Casts
        $this->info('Test 2: Model Casts');
        $transaction = new Transaction();
        $casts = $transaction->getCasts();
        if (isset($casts['workflow_snapshot']) && $casts['workflow_snapshot'] === 'array') {
            $this->line("✓ workflow_snapshot cast to array");
        } else {
            $this->error("✗ workflow_snapshot cast not configured");
        }
        $this->newLine();

        // Test 3: Fillable Fields
        $this->info('Test 3: Fillable Fields');
        $fillable = $transaction->getFillable();
        if (in_array('workflow_snapshot', $fillable)) {
            $this->line("✓ workflow_snapshot is fillable");
        } else {
            $this->error("✗ workflow_snapshot not fillable");
        }
        $this->newLine();

        // Test 4: Service Normalization
        $this->info('Test 4: Service Normalization');
        try {
            $service = app(TrasactionService::class);
            $reflection = new \ReflectionClass($service);
            $method = $reflection->getMethod('normalizeWorkflowSteps');
            $method->setAccessible(true);

            $testSteps = [
                [
                    'department_id' => 5,
                    'department_name' => 'Test Dept',
                    'process_time_value' => '7',
                    'process_time_unit' => 'days',
                ],
            ];

            $normalized = $method->invoke($service, $testSteps);
            
            if (isset($normalized[0]['order']) && $normalized[0]['order'] === 1) {
                $this->line("✓ Step order normalized correctly");
            } else {
                $this->error("✗ Step order not normalized");
            }

            if (is_int($normalized[0]['process_time_value']) && $normalized[0]['process_time_value'] === 7) {
                $this->line("✓ Process time cast to integer");
            } else {
                $this->error("✗ Process time not cast properly");
            }
        } catch (\Exception $e) {
            $this->error("✗ Service test failed: " . $e->getMessage());
        }
        $this->newLine();

        // Test 5: Validation Rules
        $this->info('Test 5: Validation Rules');
        try {
            $request = new TransactionRequest();
            $rules = $request->rules();
            
            if (isset($rules['workflow_snapshot']) && $rules['workflow_snapshot'] === 'nullable|json') {
                $this->line("✓ workflow_snapshot validation rule configured");
            } else {
                $this->warn("⚠ workflow_snapshot validation rule: " . ($rules['workflow_snapshot'] ?? 'not set'));
            }
        } catch (\Exception $e) {
            $this->error("✗ Validation test failed: " . $e->getMessage());
        }
        $this->newLine();

        // Test 6: Check first step change detection
        $this->info('Test 6: First Step Change Detection');
        try {
            $service = app(TrasactionService::class);
            $reflection = new \ReflectionClass($service);
            $method = $reflection->getMethod('hasFirstStepChanged');
            $method->setAccessible(true);

            $oldSteps = [
                ['department_id' => 1],
                ['department_id' => 2],
            ];

            $newStepsSame = [
                ['department_id' => 1],
                ['department_id' => 3],
            ];

            $newStepsDifferent = [
                ['department_id' => 3],
                ['department_id' => 2],
            ];

            $unchangedResult = $method->invoke($service, $oldSteps, $newStepsSame);
            $changedResult = $method->invoke($service, $oldSteps, $newStepsDifferent);

            if ($unchangedResult === false && $changedResult === true) {
                $this->line("✓ First step change detection works correctly");
            } else {
                $this->error("✗ First step change detection failed");
            }
        } catch (\Exception $e) {
            $this->error("✗ Change detection test failed: " . $e->getMessage());
        }
        $this->newLine();

        $this->info('✅ All tests completed!');
        return 0;
    }
}

