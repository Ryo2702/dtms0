<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Transaction;
use App\Models\TransactionReviewer;

echo "\n=== Origin Creator Actions Check ===\n\n";

$transaction = Transaction::where('transaction_code', 'TTN-20260114-0001-6FD7')->first();

if (!$transaction) {
    echo "❌ Transaction not found\n";
    exit(1);
}

echo "Transaction: {$transaction->transaction_code}\n";
echo "Status: {$transaction->transaction_status}\n";
echo "Current State: {$transaction->current_state}\n";
echo "Receiving Status: {$transaction->receiving_status}\n";
echo "Department ID: {$transaction->department_id}\n";
echo "Origin Department ID: {$transaction->origin_department_id}\n\n";

$creator = $transaction->creator;
echo "Creator: {$creator->name}\n";
echo "Creator Department ID: {$creator->department_id}\n\n";

// Check if creator is in the origin department
if ($transaction->department_id == $creator->department_id) {
    echo "✓ Creator is in the correct department to receive\n";
} else {
    echo "❌ Creator is not in the origin department\n";
}

// Check available actions
echo "\n=== Available Actions ===\n";
if ($transaction->transaction_status === 'completed' && $transaction->receiving_status === 'pending') {
    echo "✓ Transaction is completed and waiting for receipt confirmation\n";
    echo "  Available Action: Confirm Receipt / Complete\n";
    echo "  Button Text: 'Confirm Receipt' or 'Mark as Completed'\n";
} else {
    echo "⚠️  Transaction is not in the correct state for receipt confirmation\n";
    echo "  Status: {$transaction->transaction_status}\n";
    echo "  Receiving Status: {$transaction->receiving_status}\n";
}

echo "\n";
