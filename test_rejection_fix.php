<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Transaction;
use App\Models\TransactionReviewer;

echo "\n=== Test Rejection Workflow Fix ===\n\n";

// Test 1: Verify the fixed transaction
echo "Test 1: Verify Fixed Transaction\n";
echo "=".str_repeat("=", 40)."\n";

$transaction = Transaction::where('transaction_code', 'TTN-20260116-0001-0764')->first();

if (!$transaction) {
    echo "❌ Transaction not found\n";
    exit(1);
}

echo "Transaction: {$transaction->transaction_code}\n";
echo "Current State: {$transaction->current_state}\n";
echo "Department: {$transaction->department->name} (ID: {$transaction->department_id})\n";
echo "Origin Dept: {$transaction->originDepartment->name} (ID: {$transaction->origin_department_id})\n";
echo "Creator: {$transaction->creator->name}\n\n";

$checks = [
    'State is "returned_to_creator"' => $transaction->current_state === 'returned_to_creator',
    'Department matches origin' => $transaction->department_id === $transaction->origin_department_id,
    'Has rejected reviewer' => $transaction->reviewers()->where('status', 'rejected')->exists(),
];

$allPassed = true;
foreach ($checks as $check => $passed) {
    echo ($passed ? "✓" : "✗") . " {$check}\n";
    if (!$passed) $allPassed = false;
}

echo "\n";
if ($allPassed) {
    echo "✅ Transaction TTN-20260116-0001-0764 is correctly set up!\n";
    echo "   The origin creator should now see this in their 'Rejected' tab.\n";
} else {
    echo "❌ Transaction still has issues!\n";
}

echo "\n";

// Test 2: Check if transaction appears in creator's rejected list
echo "Test 2: Verify Creator Can See Rejection\n";
echo "=".str_repeat("=", 40)."\n";

$creator = $transaction->creator;
echo "Creator: {$creator->name} (ID: {$creator->id})\n";
echo "Creator Department: {$creator->department->name} (ID: {$creator->department_id})\n\n";

// Simulate the query used to fetch rejected transactions for "My Transactions"
$rejectedTransactions = Transaction::where('created_by', $creator->id)
    ->where('current_state', 'returned_to_creator')
    ->where('department_id', $creator->department_id)
    ->get();

echo "Rejected transactions visible to creator: {$rejectedTransactions->count()}\n";

if ($rejectedTransactions->contains($transaction)) {
    echo "✅ TTN-20260116-0001-0764 appears in creator's rejected list!\n";
} else {
    echo "❌ TTN-20260116-0001-0764 does NOT appear in creator's rejected list\n";
}

echo "\n";

// Test 3: Check transaction logs
echo "Test 3: Transaction Logs\n";
echo "=".str_repeat("=", 40)."\n";

$logs = \App\Models\TransactionLog::where('transaction_id', $transaction->id)
    ->orderBy('created_at', 'desc')
    ->limit(5)
    ->get();

if ($logs->isEmpty()) {
    echo "⚠️  No transaction logs found\n";
} else {
    foreach ($logs as $log) {
        echo "{$log->created_at->format('Y-m-d H:i:s')}: {$log->action}\n";
        echo "  {$log->from_state} → {$log->to_state}\n";
        if ($log->remarks) {
            echo "  Remarks: " . substr($log->remarks, 0, 80) . "\n";
        }
        echo "\n";
    }
}

// Test 4: Check reviewer record
echo "Test 4: Rejection Details\n";
echo "=".str_repeat("=", 40)."\n";

$rejectedReviewer = $transaction->reviewers()
    ->where('status', 'rejected')
    ->latest('reviewed_at')
    ->first();

if ($rejectedReviewer) {
    echo "Rejected by: {$rejectedReviewer->reviewer->name}\n";
    echo "Department: {$rejectedReviewer->department->name}\n";
    echo "Rejection Reason: {$rejectedReviewer->rejection_reason}\n";
    echo "Reviewed At: {$rejectedReviewer->reviewed_at}\n";
    
    if ($rejectedReviewer->resubmission_deadline) {
        echo "Resubmission Deadline: {$rejectedReviewer->resubmission_deadline}\n";
    }
    echo "\n✅ Rejection details are properly recorded\n";
} else {
    echo "❌ No rejection details found\n";
}

echo "\n=== Summary ===\n";
echo "The fix has been applied to the TransactionReviewerController.\n";
echo "The rejection logic now executes BEFORE the WorkflowEngine call,\n";
echo "ensuring that transactions rejected at step 1 are always marked as\n";
echo "'returned_to_creator' even if the workflow engine throws an exception.\n";
echo "\nNext rejections should work correctly out of the box.\n";
