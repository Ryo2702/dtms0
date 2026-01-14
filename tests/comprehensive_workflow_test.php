<?php

/**
 * Comprehensive test showing the complete workflow for transaction TTN-20260114-0001-6FD7
 * This demonstrates the fix and verifies the transaction is in the correct state
 */

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Transaction;
use App\Models\TransactionLog;

echo "\n";
echo "╔════════════════════════════════════════════════════════════════════════════════╗\n";
echo "║         TRANSACTION WORKFLOW COMPLETION TEST - TTN-20260114-0001-6FD7          ║\n";
echo "╚════════════════════════════════════════════════════════════════════════════════╝\n\n";

$transaction = Transaction::where('transaction_code', 'TTN-20260114-0001-6FD7')->first();

if (!$transaction) {
    echo "❌ Transaction not found\n";
    exit(1);
}

// Section 1: Transaction Details
echo "┌─ TRANSACTION DETAILS ──────────────────────────────────────────────────────────┐\n";
echo "│ Code:                  {$transaction->transaction_code}\n";
echo "│ Workflow:              {$transaction->workflow->transaction_name}\n";
echo "│ Status:                {$transaction->transaction_status}\n";
echo "│ Current State:         {$transaction->current_state}\n";
echo "│ Progress:              {$transaction->current_workflow_step} / {$transaction->total_workflow_steps}\n";
echo "│ Created:               {$transaction->created_at->format('Y-m-d H:i:s')}\n";
echo "│ Completed:             " . ($transaction->completed_at ? $transaction->completed_at->format('Y-m-d H:i:s') : 'N/A') . "\n";
echo "└────────────────────────────────────────────────────────────────────────────────┘\n\n";

// Section 2: Workflow Progress
echo "┌─ WORKFLOW PROGRESS ────────────────────────────────────────────────────────────┐\n";
$steps = $transaction->workflow->getWorkflowSteps();
foreach ($steps as $index => $step) {
    $stepNum = $index + 1;
    $completed = $stepNum <= $transaction->current_workflow_step;
    $symbol = $completed ? "✅" : "⭕";
    echo "│ {$symbol} Step {$stepNum}: {$step['department_name']}\n";
}
echo "└────────────────────────────────────────────────────────────────────────────────┘\n\n";

// Section 3: Reviewer Approvals
echo "┌─ REVIEWER APPROVALS ───────────────────────────────────────────────────────────┐\n";
$reviewers = $transaction->reviewers()->orderBy('id')->get();
foreach ($reviewers as $reviewer) {
    $status = $reviewer->status === 'approved' ? '✅ APPROVED' : '⏳ ' . strtoupper($reviewer->status);
    echo "│ {$status} | {$reviewer->reviewer->name} ({$reviewer->department->name})\n";
    if ($reviewer->reviewed_at) {
        echo "│            Reviewed: {$reviewer->reviewed_at->format('Y-m-d H:i:s')}\n";
    }
}
echo "└────────────────────────────────────────────────────────────────────────────────┘\n\n";

// Section 4: Workflow Logs
echo "┌─ WORKFLOW EXECUTION LOGS ──────────────────────────────────────────────────────┐\n";
$logs = TransactionLog::where('transaction_id', $transaction->id)->orderBy('id')->get();
foreach ($logs as $index => $log) {
    $num = $index + 1;
    echo "│ #{$num} Action: {$log->action}\n";
    echo "│     Path: {$log->from_state} → {$log->to_state}\n";
    echo "│     Time: {$log->created_at->format('Y-m-d H:i:s')}\n";
}
echo "└────────────────────────────────────────────────────────────────────────────────┘\n\n";

// Section 5: Receipt Status
echo "┌─ RECEIPT STATUS ───────────────────────────────────────────────────────────────┐\n";
echo "│ Status:                {$transaction->receiving_status}\n";
echo "│ Expected Status:       pending (awaiting confirmation)\n";
if ($transaction->received_at) {
    echo "│ Received At:           {$transaction->received_at->format('Y-m-d H:i:s')}\n";
} else {
    echo "│ Received At:           Not yet confirmed\n";
}
echo "│ Origin Department:     {$transaction->originDepartment->name}\n";
echo "│ Current Department:    {$transaction->department->name}\n";
echo "└────────────────────────────────────────────────────────────────────────────────┘\n\n";

// Section 6: Creator Actions
echo "┌─ CREATOR ACTIONS ──────────────────────────────────────────────────────────────┐\n";
$creator = $transaction->creator;
echo "│ Creator Name:          {$creator->name}\n";
echo "│ Creator Department:    {$creator->department->name}\n";
echo "│ Department ID:         {$creator->department_id}\n";
echo "│ Origin Department ID:  {$transaction->origin_department_id}\n";

// Check if creator can see the Confirm Receipt button
$canConfirmReceipt = (
    $transaction->transaction_status === 'completed' &&
    $transaction->receiving_status === 'pending' &&
    $creator->department_id === $transaction->origin_department_id
);

echo "│\n";
if ($canConfirmReceipt) {
    echo "│ ✅ CREATOR CAN CONFIRM RECEIPT\n";
    echo "│\n";
    echo "│ Available Action Button:\n";
    echo "│ • Button Text: \"Confirm Receipt\" (with ✓ icon)\n";
    echo "│ • Location: \"My Transactions\" → \"Pending Receipt\" tab\n";
    echo "│ • Action: Click to confirm transaction has been received\n";
} else {
    echo "│ ❌ CREATOR CANNOT CONFIRM RECEIPT\n";
    echo "│ Reason: Not all conditions are met\n";
    echo "│   - Status is 'completed': " . ($transaction->transaction_status === 'completed' ? '✓' : '✗') . "\n";
    echo "│   - Receiving is 'pending': " . ($transaction->receiving_status === 'pending' ? '✓' : '✗') . "\n";
    echo "│   - Department matches: " . ($creator->department_id === $transaction->origin_department_id ? '✓' : '✗') . "\n";
}
echo "└────────────────────────────────────────────────────────────────────────────────┘\n\n";

// Final Summary
echo "╔════════════════════════════════════════════════════════════════════════════════╗\n";
if ($transaction->transaction_status === 'completed' && 
    $transaction->receiving_status === 'pending' && 
    $canConfirmReceipt) {
    echo "║                         ✅ ALL CONDITIONS MET                                  ║\n";
    echo "║                                                                              ║\n";
    echo "║  Transaction has successfully completed the approval workflow and is now     ║\n";
    echo "║  awaiting confirmation from the original creator. The action button for      ║\n";
    echo "║  confirming receipt is available in the creator's \"My Transactions\" view.   ║\n";
} else {
    echo "║                       ❌ ISSUES DETECTED                                     ║\n";
}
echo "╚════════════════════════════════════════════════════════════════════════════════╝\n\n";
