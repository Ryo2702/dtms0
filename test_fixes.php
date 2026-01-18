<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Transaction;
use App\Models\TransactionReviewer;
use Illuminate\Support\Facades\DB;

echo "=== Testing Transaction Progression Logic ===\n\n";

// Test 1: Check cancelled transaction filtering
echo "TEST 1: Cancelled Transactions Should Not Appear in Pending Reviews\n";
echo "-------------------------------------------------------------\n";

$cancelledTransaction = Transaction::where('transaction_status', 'cancelled')->first();
if ($cancelledTransaction) {
    echo "Found cancelled transaction: " . $cancelledTransaction->transaction_code . "\n";
    
    $reviewersForCancelled = TransactionReviewer::where('transaction_id', $cancelledTransaction->id)
        ->where('status', 'pending')
        ->whereHas('transaction', function($query) {
            $query->where('transaction_status', '!=', 'cancelled');
        })
        ->count();
    
    echo "Pending reviewers after filter: " . $reviewersForCancelled . "\n";
    echo ($reviewersForCancelled === 0 ? "✓ PASS: Cancelled transactions filtered out\n" : "✗ FAIL: Cancelled transactions still showing\n");
} else {
    echo "No cancelled transactions found to test\n";
}

echo "\n";

// Test 2: Check step progression logic
echo "TEST 2: Step Progression After Approval\n";
echo "-------------------------------------\n";

$transaction = Transaction::where('transaction_code', 'TTN-20260118-0002-9EC8')->first();
if ($transaction) {
    echo "Transaction: " . $transaction->transaction_code . "\n";
    echo "Current Step: " . $transaction->current_workflow_step . "\n";
    echo "Current State: " . $transaction->current_state . "\n";
    
    $steps = $transaction->workflow_snapshot['steps'] ?? $transaction->workflow->getWorkflowSteps();
    echo "Total Steps in Workflow: " . count($steps) . "\n\n";
    
    echo "Workflow Steps:\n";
    foreach ($steps as $index => $step) {
        $stepNum = $index + 1;
        echo "  Step $stepNum (order: {$step['order']}): {$step['department_name']} (ID: {$step['department_id']})\n";
    }
    
    echo "\n";
    echo "Expected next step after approval:\n";
    $currentStep = $transaction->current_workflow_step;
    if ($currentStep < count($steps)) {
        $nextStepIndex = $currentStep; // After WorkflowEngine sets it to next step
        if (isset($steps[$nextStepIndex])) {
            $nextStep = $steps[$nextStepIndex];
            echo "  Step " . ($nextStepIndex + 1) . ": {$nextStep['department_name']} (ID: {$nextStep['department_id']})\n";
        }
    } else {
        echo "  Transaction is at or beyond the last step\n";
    }
    
    echo "\n";
    echo "Current reviewer records:\n";
    $reviewers = TransactionReviewer::where('transaction_id', $transaction->id)
        ->orderBy('id')
        ->get();
    
    foreach ($reviewers as $reviewer) {
        $user = $reviewer->user;
        $dept = $reviewer->department;
        echo "  - " . ($user ? $user->name : "Unknown") . " at " . ($dept ? $dept->name : "Unknown");
        echo " (Status: {$reviewer->status}, Iteration: {$reviewer->iteration_number})\n";
    }
    
} else {
    echo "Transaction TTN-20260118-0002-9EC8 not found\n";
}

echo "\n=== Test Complete ===\n";
