<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Transaction;
use App\Models\TransactionReviewer;

echo "\n=== Transaction Status Check ===\n\n";

// Accept transaction code from CLI args, fallback to default
$transactionCode = $argv[1] ?? 'TTN-20260116-0001-0764';
$transaction = Transaction::where('transaction_code', $transactionCode)->first();

if (!$transaction) {
    echo "❌ Transaction $transactionCode not found\n";
    exit(1);
}

echo "Transaction: {$transaction->transaction_code}\n";
echo "Status: {$transaction->transaction_status}\n";
echo "Current State: {$transaction->current_state}\n";
echo "Current Step: {$transaction->current_workflow_step}\n";
echo "Department ID: {$transaction->department_id}\n";
echo "Origin Dept ID: {$transaction->origin_department_id}\n";
echo "Created By: {$transaction->created_by}\n";
echo "Creator: {$transaction->creator->name}\n";

// Show workflow progress visualization data
$engine = app(\App\Services\Transaction\WorkflowEngineService::class);
$progress = $engine->getWorkflowProgress($transaction);

echo "\n=== Workflow Progress (computed) ===\n";
foreach ($progress['steps'] as $step) {
    $order = $step['order'] ?? '?';
    $dept = $step['department_name'] ?? 'Unknown';
    $status = $step['status'] ?? 'pending';
    $action = $step['action'] ?? 'n/a';
    echo sprintf("Step %s: %s | status=%s, action=%s\n", $order, $dept, $status, $action);
}

echo "\n=== Reviewers ===\n";
foreach ($transaction->reviewers as $reviewer) {
    echo "\nReviewer: {$reviewer->reviewer->name} (ID: {$reviewer->reviewer_id})\n";
    echo "  Department: {$reviewer->department->name} (ID: {$reviewer->department_id})\n";
    echo "  Status: {$reviewer->status}\n";
    echo "  Iteration: {$reviewer->iteration_number}\n";
    echo "  Reviewed At: " . ($reviewer->reviewed_at ?? 'Not yet') . "\n";
    if ($reviewer->rejection_reason) {
        echo "  Rejection Reason: {$reviewer->rejection_reason}\n";
    }
    if ($reviewer->resubmission_deadline) {
        echo "  Resubmission Deadline: {$reviewer->resubmission_deadline}\n";
    }
    echo "  Received Status: {$reviewer->received_status}\n";
}

echo "\n=== Transaction Logs (last 10) ===\n";
$logs = \App\Models\TransactionLog::where('transaction_id', $transaction->id)
    ->orderBy('created_at', 'desc')
    ->limit(10)
    ->get();

foreach ($logs as $log) {
    echo "\n{$log->created_at}: {$log->action}\n";
    echo "  From: {$log->from_state} → To: {$log->to_state}\n";
    echo "  By: " . ($log->actionBy->name ?? 'System') . "\n";
    if ($log->remarks) {
        echo "  Remarks: {$log->remarks}\n";
    }
}

echo "\n=== Analysis ===\n";
if ($transaction->current_state === 'returned_to_creator') {
    echo "✓ Transaction is correctly marked as 'returned_to_creator'\n";
    echo "✓ Creator should see this in their 'Rejected' tab\n";
} else {
    echo "❌ Transaction state is '{$transaction->current_state}' instead of 'returned_to_creator'\n";
    echo "⚠️  This might be why the creator doesn't see the rejection\n";
}

if ($transaction->department_id === $transaction->origin_department_id) {
    echo "✓ Transaction is assigned to origin department\n";
} else {
    echo "❌ Transaction is NOT assigned to origin department\n";
    echo "   Current: {$transaction->department_id}, Origin: {$transaction->origin_department_id}\n";
}

echo "\n";
