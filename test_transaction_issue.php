<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Transaction;
use Illuminate\Support\Facades\DB;

$transaction = Transaction::where('transaction_code', 'TTN-20260118-0002-9EC8')->first();

if ($transaction) {
    echo "=== Transaction Details ===\n";
    echo "Code: " . $transaction->transaction_code . "\n";
    echo "Status: " . $transaction->status . "\n";
    echo "Current Step: " . $transaction->current_workflow_step . "\n";
    echo "Total Steps: " . $transaction->total_workflow_steps . "\n";
    echo "Current State: " . $transaction->current_state . "\n";
    echo "Cancelled At: " . ($transaction->cancelled_at ?? 'Not cancelled') . "\n";
    
    echo "\n=== Workflow Steps ===\n";
    $steps = $transaction->workflow_snapshot['steps'] ?? $transaction->workflow->getWorkflowSteps();
    echo "Steps count: " . count($steps) . "\n";
    foreach ($steps as $index => $step) {
        echo "Step " . ($index + 1) . ": " . $step['department_name'] . " (Dept ID: " . $step['department_id'] . ")\n";
    }
    
    echo "\n=== All Reviewer Records ===\n";
    $reviewers = DB::table('transaction_reviewers')
        ->where('transaction_id', $transaction->id)
        ->orderBy('id')
        ->get();
    
    foreach ($reviewers as $reviewer) {
        $user = DB::table('users')->where('id', $reviewer->reviewer_id)->first();
        $dept = DB::table('departments')->where('id', $reviewer->department_id)->first();
        
        echo "\nReviewer ID: " . $reviewer->id . "\n";
        echo "  - User: " . ($user ? $user->name : "Unknown") . "\n";
        echo "  - Department: " . ($dept ? $dept->name : "Unknown") . " (ID: " . $reviewer->department_id . ")\n";
        echo "  - Status: " . $reviewer->status . "\n";
        echo "  - Received: " . $reviewer->received_status . "\n";
        echo "  - Reviewed At: " . ($reviewer->reviewed_at ?? 'Not reviewed') . "\n";
        echo "  - Iteration: " . $reviewer->iteration_number . "\n";
        echo "  - Created At: " . $reviewer->created_at . "\n";
    }
    
    echo "\n=== Current/Pending Reviewers ===\n";
    $currentReviewers = DB::table('transaction_reviewers')
        ->where('transaction_id', $transaction->id)
        ->where('status', 'pending')
        ->get();
    
    if (count($currentReviewers) > 0) {
        foreach ($currentReviewers as $reviewer) {
            $user = DB::table('users')->where('id', $reviewer->reviewer_id)->first();
            $dept = DB::table('departments')->where('id', $reviewer->department_id)->first();
            echo "Pending for: " . ($user ? $user->name : "Unknown") . " at " . ($dept ? $dept->name : "Unknown") . "\n";
        }
    } else {
        echo "No pending reviewers\n";
    }
    
} else {
    echo "Transaction not found\n";
}
