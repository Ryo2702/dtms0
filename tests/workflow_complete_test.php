<?php

/**
 * Complete workflow simulation test - advances transaction through all steps
 */

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Transaction;
use App\Models\TransactionReviewer;
use App\Models\TransactionLog;
use App\Services\Transaction\WorkflowEngineService;

echo "\n=== Complete Workflow Simulation ===\n\n";

// Get the latest in-progress transaction
$transaction = Transaction::where('transaction_status', 'in_progress')->latest()->first();

if (!$transaction) {
    echo "❌ No in-progress transaction found\n";
    exit(1);
}

echo "Starting with transaction: {$transaction->transaction_code}\n";
echo "Total Steps: {$transaction->total_workflow_steps}\n\n";

$workflowEngine = app(WorkflowEngineService::class);

// Loop through all remaining steps
while ($transaction->current_workflow_step < $transaction->total_workflow_steps) {
    echo "=== Step {$transaction->current_workflow_step} of {$transaction->total_workflow_steps} ===\n";
    
    $currentReviewer = $transaction->reviewers()
        ->where('status', 'pending')
        ->first();
    
    if (!$currentReviewer) {
        echo "❌ No pending reviewer found\n";
        break;
    }
    
    echo "Reviewer: {$currentReviewer->reviewer->name} ({$currentReviewer->department->name})\n";
    echo "Current State: {$transaction->current_state}\n";
    
    // Mark as received
    if ($currentReviewer->received_status !== 'received') {
        $currentReviewer->update([
            'received_status' => 'received',
            'received_by' => $currentReviewer->reviewer_id,
            'received_at' => now(),
        ]);
        echo "✓ Marked as received\n";
    }
    
    // Approve
    try {
        $currentReviewer->update([
            'status' => 'approved',
            'reviewed_at' => now(),
        ]);
        
        $workflowEngine->executeAction(
            $transaction,
            'approve',
            $currentReviewer->reviewer,
            "Auto-approval in workflow test"
        );
        
        $transaction->refresh();
        
        // Check if this was the last step
        $isLastStep = $transaction->current_workflow_step >= $transaction->total_workflow_steps;
        
        if ($isLastStep) {
            // Final step - update for completion
            $transaction->update([
                'receiving_status' => 'pending',
                'department_id' => $transaction->origin_department_id,
            ]);
            $transaction->refresh();
            echo "✓ Approved (LAST STEP)\n";
        } else {
            // Not last step - increment and create next reviewer
            $transaction->increment('current_workflow_step');
            $transaction->refresh();
            
            // Assign next reviewer
            $steps = $transaction->getWorkflowSteps();
            $nextStep = $steps[$transaction->current_workflow_step - 1];
            $nextDepartmentId = $nextStep['department_id'];
            
            $nextReviewerUser = \App\Models\User::where('department_id', $nextDepartmentId)
                ->where(function($q) {
                    $q->where('type', 'Head')->orWhere('type', 'Staff');
                })
                ->first();
            
            if ($nextReviewerUser) {
                TransactionReviewer::create([
                    'transaction_id' => $transaction->id,
                    'reviewer_id' => $nextReviewerUser->id,
                    'department_id' => $nextDepartmentId,
                    'status' => 'pending',
                    'received_status' => 'received',
                    'received_by' => $nextReviewerUser->id,
                    'received_at' => now(),
                    'due_date' => now()->addDays(3),
                    'iteration_number' => 1,
                    'previous_reviewer_id' => $currentReviewer->reviewer_id,
                ]);
                
                $transaction->update(['department_id' => $nextDepartmentId]);
                echo "✓ Approved and moved to next step\n";
            }
        }
        
        echo "  New Status: {$transaction->transaction_status}\n";
        echo "  New State: {$transaction->current_state}\n";
        echo "  New Step: {$transaction->current_workflow_step}\n\n";
        
    } catch (\Exception $e) {
        echo "❌ Error: {$e->getMessage()}\n";
        break;
    }
}

// After all approvals, verify final state
$transaction->refresh();
echo "=== Final Transaction State ===\n";
echo "Code: {$transaction->transaction_code}\n";
echo "Status: {$transaction->transaction_status}\n";
echo "Current State: {$transaction->current_state}\n";
echo "Receiving Status: " . ($transaction->receiving_status ?? 'null') . "\n";
echo "Department ID: {$transaction->department_id}\n";
echo "Origin Department ID: {$transaction->origin_department_id}\n\n";

// Test receipt confirmation if completed
if ($transaction->transaction_status === 'completed' && $transaction->receiving_status === 'pending') {
    echo "=== Testing Receipt Confirmation ===\n";
    
    $originUser = \App\Models\User::where('department_id', $transaction->origin_department_id)->first();
    
    if ($originUser) {
        echo "Origin User: {$originUser->name}\n";
        
        // Confirm receipt
        $transaction->update([
            'receiving_status' => 'received',
            'received_at' => now(),
        ]);
        
        TransactionLog::create([
            'transaction_id' => $transaction->id,
            'from_state' => $transaction->current_state,
            'to_state' => 'completed',
            'action' => 'confirm_received',
            'action_by' => $originUser->id,
            'remarks' => 'Transaction received and confirmed by origin department.',
        ]);
        
        $transaction->refresh();
        
        echo "✓ Receipt confirmed\n";
        echo "  Final Receiving Status: {$transaction->receiving_status}\n";
        echo "  Received At: " . $transaction->received_at->format('Y-m-d H:i:s') . "\n\n";
        
        echo "✅ COMPLETE WORKFLOW TEST PASSED!\n";
        echo "Transaction workflow completed successfully.\n";
    }
} else if ($transaction->transaction_status === 'completed') {
    echo "✓ Transaction is completed\n";
    echo "  Receiving Status: {$transaction->receiving_status}\n";
}

echo "\n=== Test Complete ===\n";
