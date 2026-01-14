<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Transaction;
use App\Models\TransactionReviewer;
use App\Models\TransactionLog;

echo "\n=== Review Approval History ===\n\n";

$transaction = Transaction::where('transaction_code', 'TTN-20260114-0001-6FD7')->first();

echo "Transaction: {$transaction->transaction_code}\n";
echo "Current State: {$transaction->current_state}\n";
echo "Current Step: {$transaction->current_workflow_step} / {$transaction->total_workflow_steps}\n\n";

// Get all logs
$logs = TransactionLog::where('transaction_id', $transaction->id)->orderBy('id')->get();

echo "=== Transaction Logs ===\n";
if ($logs->count() === 0) {
    echo "No logs found\n";
} else {
    foreach ($logs as $log) {
        echo "  #{$log->id}: {$log->action} | {$log->from_state} -> {$log->to_state}\n";
        $userName = $log->actionByUser ? $log->actionByUser->name : 'Unknown';
        echo "       By: {$userName}\n";
        if ($log->remarks) {
            echo "       Remarks: {$log->remarks}\n";
        }
    }
}

echo "\n=== Reviewer Details ===\n";
$reviewers = $transaction->reviewers()->orderBy('id')->get();
foreach ($reviewers as $reviewer) {
    echo "\nReviewer #{$reviewer->id}: {$reviewer->reviewer->name}\n";
    echo "  Department: {$reviewer->department->name}\n";
    echo "  Status: {$reviewer->status}\n";
    echo "  Received: {$reviewer->received_status}\n";
    if ($reviewer->reviewed_at) {
        echo "  Reviewed At: {$reviewer->reviewed_at->format('Y-m-d H:i:s')}\n";
    }
}

echo "\n";
