<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Transaction;

$transaction = Transaction::where('transaction_code', 'TTN-20260117-0001-8DAE')->first();

if ($transaction) {
    echo "=== Transaction Details ===\n";
    echo "Code: " . $transaction->transaction_code . "\n";
    echo "Current Step: " . $transaction->current_workflow_step . "\n";
    echo "Total Steps: " . $transaction->total_workflow_steps . "\n";
    
    echo "\n=== Workflow Steps ===\n";
    $steps = $transaction->workflow_snapshot['steps'] ?? $transaction->workflow->getWorkflowSteps();
    echo "Steps count: " . count($steps) . "\n";
    echo "Steps: " . json_encode($steps, JSON_PRETTY_PRINT) . "\n";
    
    echo "\n=== Step Calculation ===\n";
    echo "currentStep: " . $transaction->current_workflow_step . "\n";
    echo "count(steps): " . count($steps) . "\n";
    echo "isLastStep (currentStep >= count(steps)): " . ($transaction->current_workflow_step >= count($steps) ? "true" : "false") . "\n";
    
    if (!($transaction->current_workflow_step >= count($steps)) && $transaction->current_workflow_step < count($steps)) {
        $nextStep = $steps[$transaction->current_workflow_step];
        echo "\nNext Step Index: " . $transaction->current_workflow_step . "\n";
        echo "Next Step: " . json_encode($nextStep, JSON_PRETTY_PRINT) . "\n";
    } else {
        echo "\nNo next step available (beyond array bounds or last step)\n";
    }
    
} else {
    echo "Transaction not found\n";
}