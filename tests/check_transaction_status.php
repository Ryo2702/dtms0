<?php

/**
 * Test script to check transaction TTN-20260114-0001-6FD7 status
 */

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Transaction;
use App\Models\TransactionReviewer;
use App\Models\User;

echo "\n=== Transaction Status Check ===\n\n";

// Search for the transaction
$transaction = Transaction::where('transaction_code', 'TTN-20260114-0001-6FD7')->first();

if (!$transaction) {
    echo "❌ Transaction TTN-20260114-0001-6FD7 not found\n";
    exit(1);
}

echo "✓ Transaction Found\n";
echo "  Code: {$transaction->transaction_code}\n";
echo "  Status: {$transaction->transaction_status}\n";
echo "  Current State: {$transaction->current_state}\n";
echo "  Receiving Status: {$transaction->receiving_status}\n";
echo "  Current Step: {$transaction->current_workflow_step} / {$transaction->total_workflow_steps}\n";
echo "  Created By: {$transaction->creator->name}\n";
echo "  Origin Department: {$transaction->originDepartment->name}\n";
echo "  Current Department: {$transaction->department->name}\n\n";

// Check reviewers
echo "=== Reviewers Status ===\n";
$reviewers = $transaction->reviewers()->orderBy('id')->get();

if ($reviewers->count() === 0) {
    echo "❌ No reviewers found\n";
} else {
    foreach ($reviewers as $reviewer) {
        echo "  #{$reviewer->id}: {$reviewer->reviewer->name} ({$reviewer->department->name})\n";
        echo "    Status: {$reviewer->status}\n";
        echo "    Received: {$reviewer->received_status}\n";
        if ($reviewer->reviewed_at) {
            echo "    Reviewed At: {$reviewer->reviewed_at->format('Y-m-d H:i:s')}\n";
        }
        echo "\n";
    }
}

// Check workflow snapshot
echo "=== Workflow Snapshot ===\n";
if ($transaction->workflow_snapshot) {
    echo "  Steps in Snapshot: " . count($transaction->workflow_snapshot['steps'] ?? []) . "\n";
}

// Check if completed
echo "=== Completion Status ===\n";
if ($transaction->transaction_status === 'completed') {
    echo "✓ Transaction is COMPLETED\n";
    if ($transaction->receiving_status === 'pending') {
        echo "⚠️  Waiting for receipt confirmation from origin department\n";
        
        // Check available actions for origin creator
        $originCreator = $transaction->creator;
        echo "\n=== Available Actions for Origin Creator ===\n";
        echo "  User: {$originCreator->name}\n";
        echo "  Department: {$originCreator->department->name}\n";
        
        if ($transaction->department_id === $originCreator->department_id && $transaction->receiving_status === 'pending') {
            echo "  ✓ Can confirm receipt\n";
            echo "  Action Button: 'Confirm Receipt' or 'Complete'\n";
        }
    } elseif ($transaction->receiving_status === 'received') {
        echo "✓ Transaction receipt has been confirmed\n";
    }
} else {
    echo "⚠️  Transaction status is: {$transaction->transaction_status}\n";
    echo "  Expected: 'completed' with 'receiving_status' = 'pending'\n";
    echo "  Problem: Transaction has not reached final completion state\n";
}

echo "\n=== Test Complete ===\n";
