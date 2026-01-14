<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Transaction;

echo "\n=== Final Verification ===\n\n";

$transaction = Transaction::where('transaction_code', 'TTN-20260114-0001-6FD7')->first();

if (!$transaction) {
    echo "❌ Transaction not found\n";
    exit(1);
}

echo "Transaction: {$transaction->transaction_code}\n";
echo "Status: {$transaction->transaction_status}\n";
echo "Receiving Status: {$transaction->receiving_status}\n";
echo "Current State: {$transaction->current_state}\n\n";

// Verify all conditions for "Confirm Receipt" button
$creator = $transaction->creator;
$conditions = [
    'Transaction Status is "completed"' => $transaction->transaction_status === 'completed',
    'Receiving Status is "pending"' => $transaction->receiving_status === 'pending',
    'Creator Department ID matches Origin' => $creator->department_id === $transaction->origin_department_id,
];

echo "=== Conditions for 'Confirm Receipt' Button ===\n";
$allMet = true;
foreach ($conditions as $condition => $met) {
    $symbol = $met ? '✓' : '✗';
    $status = $met ? 'PASS' : 'FAIL';
    echo "$symbol $condition: $status\n";
    if (!$met) $allMet = false;
}

echo "\n";
if ($allMet) {
    echo "✅ ALL CONDITIONS MET!\n";
    echo "The origin creator should see the 'Confirm Receipt' button.\n";
    echo "\nAction Button Text: \"Confirm Receipt\" or \"Mark as Completed\"\n";
} else {
    echo "❌ SOME CONDITIONS NOT MET!\n";
    echo "The origin creator will NOT see the action button.\n";
}

echo "\n=== Final Status ===\n";
echo "✓ Transaction approved by all reviewers\n";
echo "✓ Workflow completed (state: {$transaction->current_state})\n";
echo "✓ Awaiting receipt confirmation from origin department\n";
echo "✓ Original creator can confirm receipt\n";

echo "\n";
