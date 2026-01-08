<?php

/**
 * Manual Test Script for Workflow Route Changes
 * 
 * Run: php artisan tinker
 * Then copy-paste this code
 */

// Test 1: Verify workflow snapshot JSON parsing
echo "Test 1: JSON Parsing\n";
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
echo "Encoded JSON: " . $json . "\n";

$decoded = json_decode($json, true);
echo "Decoded successfully: " . (is_array($decoded) ? 'Yes' : 'No') . "\n";
echo "Steps count: " . count($decoded['steps']) . "\n\n";

// Test 2: Verify Transaction model casts workflow_snapshot as array
echo "Test 2: Model Casts\n";
$transaction = new \App\Models\Transaction();
$casts = $transaction->getCasts();
echo "workflow_snapshot cast: " . ($casts['workflow_snapshot'] ?? 'Not set') . "\n\n";

// Test 3: Verify fillable fields
echo "Test 3: Fillable Fields\n";
$fillable = $transaction->getFillable();
echo "workflow_snapshot fillable: " . (in_array('workflow_snapshot', $fillable) ? 'Yes' : 'No') . "\n\n";

// Test 4: Test service normalization
echo "Test 4: Service Normalization\n";
$service = app(\App\Services\Transaction\TrasactionService::class);
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
echo "Normalized step order: " . $normalized[0]['order'] . "\n";
echo "Process time is int: " . (is_int($normalized[0]['process_time_value']) ? 'Yes' : 'No') . "\n";
echo "Process time value: " . $normalized[0]['process_time_value'] . "\n\n";

// Test 5: Verify validation rules
echo "Test 5: Validation Rules\n";
$request = new \App\Http\Requests\Transaction\TransactionRequest();
$rules = $request->rules();
echo "workflow_snapshot validation rule: " . ($rules['workflow_snapshot'] ?? 'Not set') . "\n\n";

echo "All manual tests completed!\n";
