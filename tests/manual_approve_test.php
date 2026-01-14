<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Transaction;
use App\Models\TransactionReviewer;
use App\Services\Transaction\WorkflowEngineService;

echo "\n=== Manual Approval Test ===\n\n";

$transaction = Transaction::where('transaction_code', 'TTN-20260114-0001-6FD7')->first();

if (!$transaction) {
    echo "❌ Transaction not found\n";
    exit(1);
}

echo "Transaction: {$transaction->transaction_code}\n";
echo "Current State: {$transaction->current_state}\n";
echo "Transaction Status (Before): {$transaction->transaction_status}\n";
echo "Receiving Status (Before): {$transaction->receiving_status}\n";
echo "Current Step: {$transaction->current_workflow_step} / {$transaction->total_workflow_steps}\n\n";

// Get the last reviewer (should be at pending_general_services_office_review)
$lastReviewer = $transaction->reviewers()
    ->where('status', 'approved')
    ->latest('id')
    ->first();

if (!$lastReviewer) {
    echo "❌ No approved reviewer found (trying latest)\n";
    $lastReviewer = $transaction->reviewers()->latest('id')->first();
}

if (!$lastReviewer) {
    echo "❌ No reviewer found\n";
    exit(1);
}

echo "Reviewer: {$lastReviewer->reviewer->name}\n";
echo "Reviewer Status: {$lastReviewer->status}\n";
echo "Department: {$lastReviewer->department->name}\n\n";

// Now execute the workflow action manually
try {
    $workflowEngine = app(WorkflowEngineService::class);
    
    echo "--- Executing Workflow Action: approve ---\n";
    $result = $workflowEngine->executeAction(
        $transaction,
        'approve',
        $lastReviewer->reviewer,
        'Manual test approval'
    );
    
    echo "✓ Workflow action executed. Result: " . ($result ? 'true' : 'false') . "\n";
    
    // Refresh and check state
    $transaction->refresh();
    
    echo "\nTransaction After executeAction:\n";
    echo "  Current State: {$transaction->current_state}\n";
    echo "  Transaction Status: {$transaction->transaction_status}\n";
    echo "  Receiving Status: {$transaction->receiving_status}\n";
    
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack Trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\n=== Test Complete ===\n";
