<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Transaction;
use Illuminate\Support\Facades\DB;

$transaction = Transaction::where('transaction_code', 'TTN-20260117-0001-8DAE')->first();

if ($transaction) {
    echo "=== Transaction Details ===\n";
    echo "Code: " . $transaction->transaction_code . "\n";
    echo "Current Step: " . $transaction->current_workflow_step . "\n";
    echo "Current State: " . $transaction->current_state . "\n";
    
    echo "\n=== All ReviewersRecords ===\n";
    $reviewers = DB::table('transaction_reviewers')
        ->where('transaction_id', $transaction->id)
        ->get();
    
    foreach ($reviewers as $reviewer) {
        $user = DB::table('users')->where('id', $reviewer->reviewer_id)->first();
        $dept = DB::table('departments')->where('id', $reviewer->department_id)->first();
        
        echo "\nReviewer ID: " . $reviewer->id . "\n";
        echo "  - User: " . ($user ? $user->name : "Unknown") . "\n";
        echo "  - Department: " . ($dept ? $dept->name : "Unknown") . "\n";
        echo "  - Status: " . $reviewer->status . "\n";
        echo "  - Received: " . $reviewer->received_status . "\n";
        echo "  - Reviewed At: " . $reviewer->reviewed_at . "\n";
        echo "  - Iteration: " . $reviewer->iteration_number . "\n";
    }
    
    echo "\n=== Latest Pending Reviewer ===\n";
    $latestPending = DB::table('transaction_reviewers')
        ->where('transaction_id', $transaction->id)
        ->where('status', 'pending')
        ->latest('id')
        ->first();
    
    if ($latestPending) {
        $user = DB::table('users')->where('id', $latestPending->reviewer_id)->first();
        $dept = DB::table('departments')->where('id', $latestPending->department_id)->first();
        
        echo "Reviewer: " . ($user ? $user->name : "Unknown") . "\n";
        echo "Department: " . ($dept ? $dept->name : "Unknown") . "\n";
        echo "Status: " . $latestPending->status . "\n";
        echo "Received: " . $latestPending->received_status . "\n";
    } else {
        echo "No pending reviewers found\n";
    }
    
} else {
    echo "Transaction not found\n";
}