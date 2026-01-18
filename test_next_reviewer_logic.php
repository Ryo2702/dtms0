<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Transaction;
use App\Models\TransactionReviewer;
use Illuminate\Support\Facades\DB;

echo "\n=== TEST: Next Reviewer Logic ===\n";

$transaction = Transaction::where('transaction_code', 'TTN-20260117-0001-8DAE')->first();

if (!$transaction) {
    echo "ERROR: Transaction not found\n";
    exit;
}

echo "Transaction: " . $transaction->transaction_code . "\n";
echo "Current Step: " . $transaction->current_workflow_step . "\n";
echo "Total Steps: " . $transaction->total_workflow_steps . "\n";
echo "Current State: " . $transaction->current_state . "\n";

// Simulate the review() method logic
$steps = $transaction->workflow_snapshot['steps'] ?? $transaction->workflow->getWorkflowSteps();
$currentStep = $transaction->current_workflow_step;
$isLastStep = $currentStep >= count($steps);

echo "\n=== Step Calculation ===\n";
echo "Current Step: " . $currentStep . "\n";
echo "Total Steps (array count): " . count($steps) . "\n";
echo "Is Last Step: " . ($isLastStep ? "YES" : "NO") . "\n";

$nextReviewer = null;
$nextReviewerRecord = null;

if (!$isLastStep && $currentStep < count($steps)) {
    echo "\n=== Next Reviewer Calculation ===\n";
    echo "Will get next step...\n";
    
    // This is the fixed logic
    $nextStepIndex = $currentStep - 1 + 1;  // Simplifies to $currentStep
    echo "Next Step Index: " . $nextStepIndex . "\n";
    
    if (isset($steps[$nextStepIndex])) {
        $nextStep = $steps[$nextStepIndex];
        echo "Next Step Order: " . $nextStep['order'] . "\n";
        echo "Next Step Department: " . $nextStep['department_name'] . "\n";
        
        $nextDepartmentId = $nextStep['department_id'];
        $nextReviewer = \App\Models\User::where('department_id', $nextDepartmentId)
            ->where(function($q) {
                $q->where('type', 'Head')->orWhere('type', 'Staff');
            })
            ->first();
        
        if ($nextReviewer) {
            echo "Next Reviewer Found: " . $nextReviewer->name . "\n";
            
            // Look for existing TransactionReviewer record
            $nextReviewerRecord = $transaction->reviewers()
                ->where('reviewer_id', $nextReviewer->id)
                ->where('status', 'pending')
                ->first();
            
            if ($nextReviewerRecord) {
                echo "Next Reviewer Record Found\n";
                echo "  - Status: " . $nextReviewerRecord->status . "\n";
                echo "  - Received Status: " . $nextReviewerRecord->received_status . "\n";
            } else {
                echo "Next Reviewer Record NOT FOUND (will need to be created)\n";
            }
        } else {
            echo "ERROR: No next reviewer user found for department " . $nextDepartmentId . "\n";
        }
    } else {
        echo "ERROR: Next step index " . $nextStepIndex . " out of bounds (max: " . (count($steps) - 1) . ")\n";
    }
} else {
    echo "\nNo next reviewer available (last step or invalid index)\n";
}

echo "\n=== Summary ===\n";
echo "Will Show Next Reviewer Section: " . ($nextReviewer ? "YES" : "NO") . "\n";
echo "Next Reviewer: " . ($nextReviewer ? $nextReviewer->name : "N/A") . "\n";
echo "Has Received Record: " . ($nextReviewerRecord ? "YES" : "NO") . "\n";

if ($nextReviewerRecord) {
    echo "Received Status: " . $nextReviewerRecord->received_status . "\n";
}
