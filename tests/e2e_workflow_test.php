<?php

/**
 * Create a new transaction and test the complete workflow end-to-end
 */

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Transaction;
use App\Models\Workflow;
use App\Models\User;
use App\Models\Department;
use App\Services\Transaction\TrasactionService;
use App\Services\Transaction\WorkflowEngineService;

echo "\n=== End-to-End Workflow Test ===\n\n";

// Get test data
$creator = User::where('type', 'Head')->first();
$workflow = Workflow::where('status', true)->first();
$department = $creator->department;

$assignStaff = \App\Models\AssignStaff::first();

if (!$creator || !$workflow || !$department || !$assignStaff) {
    echo "❌ Missing test data\n";
    exit(1);
}

echo "Creating new transaction...\n";
echo "  Creator: {$creator->name}\n";
echo "  Department: {$department->name}\n";
echo "  Workflow: {$workflow->transaction_name}\n\n";

// Create transaction
$transactionService = app(TrasactionService::class);

try {
    $transaction = $transactionService->createTransaction([
        'workflow_id' => $workflow->id,
        'assign_staff_id' => $assignStaff->id,
        'department_id' => $department->id,
        'level_of_urgency' => 'normal',
    ], $creator);
    
    echo "✓ Transaction created: {$transaction->transaction_code}\n";
    echo "  Status: {$transaction->transaction_status}\n";
    echo "  Current Step: {$transaction->current_workflow_step}\n";
    echo "  Total Steps: {$transaction->total_workflow_steps}\n\n";
    
    // Now test approvals through all steps
    $workflowEngine = app(WorkflowEngineService::class);
    $stepCount = 1;
    
    while ($transaction->current_workflow_step <= $transaction->total_workflow_steps) {
        echo "=== Processing Step {$stepCount} of {$transaction->total_workflow_steps} ===\n";
        
        $currentReviewer = $transaction->reviewers()
            ->where('status', 'pending')
            ->latest('id')
            ->first();
        
        if (!$currentReviewer) {
            echo "❌ No pending reviewer found\n";
            break;
        }
        
        echo "Reviewer: {$currentReviewer->reviewer->name}\n";
        echo "Department: {$currentReviewer->department->name}\n";
        echo "Current State: {$transaction->current_state}\n";
        
        // Mark as received
        $currentReviewer->update([
            'received_status' => 'received',
            'received_by' => $currentReviewer->reviewer_id,
            'received_at' => now(),
        ]);
        echo "✓ Marked as received\n";
        
        // Check if last step
        $isLastStep = $transaction->current_workflow_step >= $transaction->total_workflow_steps;
        
        // Approve
        $currentReviewer->update([
            'status' => 'approved',
            'reviewed_at' => now(),
        ]);
        
        $workflowEngine->executeAction(
            $transaction,
            'approve',
            $currentReviewer->reviewer,
            "Test approval at step {$stepCount}"
        );
        
        $transaction->refresh();
        
        if ($isLastStep) {
            // Last step - set receiving status
            $transaction->update([
                'receiving_status' => 'pending',
                'department_id' => $transaction->origin_department_id,
            ]);
            $transaction->refresh();
            echo "✓ Approved (FINAL STEP)\n";
        } else {
            // Not last step - move to next
            $transaction->increment('current_workflow_step');
            $transaction->refresh();
            
            $steps = $transaction->getWorkflowSteps();
            $nextStep = $steps[$transaction->current_workflow_step - 1];
            $nextDepartmentId = $nextStep['department_id'];
            
            $nextReviewerUser = User::where('department_id', $nextDepartmentId)
                ->where(function($q) {
                    $q->where('type', 'Head')->orWhere('type', 'Staff');
                })
                ->first();
            
            if ($nextReviewerUser) {
                \App\Models\TransactionReviewer::create([
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
                echo "✓ Moved to next step\n";
            }
        }
        
        echo "  New Status: {$transaction->transaction_status}\n";
        echo "  New State: {$transaction->current_state}\n";
        echo "  New Step: {$transaction->current_workflow_step}\n\n";
        
        $stepCount++;
        
        if ($transaction->transaction_status === 'completed') {
            break;
        }
    }
    
    // Verify final state
    $transaction->refresh();
    echo "=== Final State ===\n";
    echo "Code: {$transaction->transaction_code}\n";
    echo "Status: {$transaction->transaction_status}\n";
    echo "Current State: {$transaction->current_state}\n";
    echo "Receiving Status: " . ($transaction->receiving_status ?? 'null') . "\n";
    echo "Department: {$transaction->department->name}\n\n";
    
    // Test receipt confirmation
    if ($transaction->transaction_status === 'completed' && $transaction->receiving_status === 'pending') {
        echo "=== Testing Receipt Confirmation ===\n";
        
        $originUser = User::where('department_id', $transaction->origin_department_id)->first();
        
        if ($originUser) {
            $transaction->update([
                'receiving_status' => 'received',
                'received_at' => now(),
            ]);
            
            \App\Models\TransactionLog::create([
                'transaction_id' => $transaction->id,
                'from_state' => $transaction->current_state,
                'to_state' => 'completed',
                'action' => 'confirm_received',
                'action_by' => $originUser->id,
                'remarks' => 'Transaction received and confirmed.',
            ]);
            
            $transaction->refresh();
            
            echo "✓ Receipt confirmed by: {$originUser->name}\n";
            echo "  Final Receiving Status: {$transaction->receiving_status}\n";
            echo "  Received At: " . $transaction->received_at->format('Y-m-d H:i:s') . "\n\n";
            
            echo "✅ COMPLETE WORKFLOW TEST PASSED!\n";
        }
    }
    
} catch (\Exception $e) {
    echo "❌ Error: {$e->getMessage()}\n";
    echo "Trace:\n{$e->getTraceAsString()}\n";
}

echo "\n=== Test Complete ===\n";
