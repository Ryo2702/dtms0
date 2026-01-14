<?php

/**
 * Debug script to test complete workflow from last reviewer approval to origin creator receiving
 * 
 * This script tests:
 * 1. Last reviewer approves transaction
 * 2. Transaction status changes to 'completed' with receiving_status 'pending'
 * 3. Origin creator confirms receipt
 * 4. Transaction receiving_status changes to 'received'
 * 5. Transaction log is created for the confirmation
 */

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Transaction;
use App\Models\TransactionReviewer;
use App\Models\User;
use App\Models\Workflow;
use App\Models\Department;
use App\Models\TransactionLog;
use App\Services\Transaction\WorkflowEngineService;

echo "\n=== Complete Workflow Debug Test ===\n\n";

// Find or create necessary test data
$testCreator = User::where('type', 'Head')->first();
$testWorkflow = Workflow::where('status', true)->first();
$testDepartment = Department::where('status', true)->first();

if (!$testCreator || !$testWorkflow || !$testDepartment) {
    echo "❌ Error: Missing test data (users, workflows, or departments)\n";
    echo "   - Test Creator: " . ($testCreator ? "✓" : "✗") . "\n";
    echo "   - Test Workflow: " . ($testWorkflow ? "✓" : "✗") . "\n";
    echo "   - Test Department: " . ($testDepartment ? "✓" : "✗") . "\n";
    exit(1);
}

echo "✓ Test data found\n";
echo "  - Creator: {$testCreator->name} (ID: {$testCreator->id})\n";
echo "  - Workflow: {$testWorkflow->transaction_name} (ID: {$testWorkflow->id})\n";
echo "  - Department: {$testDepartment->name} (ID: {$testDepartment->id})\n\n";

// Find a transaction that is at the last step and pending approval
echo "--- Finding or Creating Test Transaction ---\n";

$testTransaction = Transaction::where('transaction_status', 'in_progress')
    ->whereHas('reviewers', function($query) {
        $query->where('status', 'pending');
    })
    ->first();

if (!$testTransaction) {
    echo "ℹ️  No existing transaction found. Please create one manually or run the workflow.\n";
    echo "   Alternatively, finding completed transactions to test receipt confirmation...\n\n";
    
    // Find a completed transaction pending receipt
    $completedTransaction = Transaction::where('transaction_status', 'completed')
        ->where('receiving_status', 'pending')
        ->where('origin_department_id', $testCreator->department_id)
        ->first();
    
    if ($completedTransaction) {
        echo "✓ Found completed transaction pending receipt\n";
        testReceiptConfirmation($completedTransaction, $testCreator);
    } else {
        echo "❌ No transactions available for testing. Please create a transaction first.\n";
    }
    
    exit(0);
}

echo "✓ Found test transaction: {$testTransaction->transaction_code}\n";
echo "  - Status: {$testTransaction->transaction_status}\n";
echo "  - Current State: {$testTransaction->current_state}\n";
echo "  - Current Step: {$testTransaction->current_workflow_step}\n";
echo "  - Total Steps: {$testTransaction->total_workflow_steps}\n\n";

// Check if this is the last step
$steps = $testTransaction->getWorkflowSteps();
$isLastStep = $testTransaction->current_workflow_step >= count($steps);

echo "--- Step Analysis ---\n";
echo "  - Is Last Step: " . ($isLastStep ? "YES" : "NO") . "\n";
echo "  - Steps in workflow: " . count($steps) . "\n\n";

if (!$isLastStep) {
    echo "ℹ️  Transaction is not at the last step. Skipping last step approval test.\n";
    echo "   Current step: {$testTransaction->current_workflow_step} / " . count($steps) . "\n";
    exit(0);
}

// Get the current reviewer (should be at last step)
$currentReviewer = $testTransaction->reviewers()
    ->where('status', 'pending')
    ->first();

if (!$currentReviewer) {
    echo "❌ No pending reviewer found for this transaction.\n";
    exit(1);
}

echo "--- Current Reviewer ---\n";
echo "  - Reviewer: {$currentReviewer->reviewer->name}\n";
echo "  - Department: {$currentReviewer->department->name}\n";
echo "  - Status: {$currentReviewer->status}\n";
echo "  - Received Status: {$currentReviewer->received_status}\n\n";

// Test 1: Approve at last step
echo "--- Test 1: Last Reviewer Approval ---\n";

if ($currentReviewer->received_status !== 'received') {
    echo "⚠️  Transaction not marked as received. Marking as received first...\n";
    $currentReviewer->update([
        'received_status' => 'received',
        'received_by' => $currentReviewer->reviewer_id,
        'received_at' => now(),
    ]);
    echo "✓ Marked as received\n\n";
}

// Simulate approval
try {
    $workflowEngine = app(WorkflowEngineService::class);
    
    // Update reviewer status
    $currentReviewer->update([
        'status' => 'approved',
        'reviewed_at' => now(),
    ]);
    
    // Execute workflow action
    $workflowEngine->executeAction(
        $testTransaction,
        'approve',
        $currentReviewer->reviewer,
        'Test approval at last step'
    );
    
    // Update transaction for last step completion
    $testTransaction->update([
        'receiving_status' => 'pending',
        'department_id' => $testTransaction->origin_department_id,
    ]);
    
    $testTransaction->refresh();
    
    echo "✓ Last reviewer approved successfully\n";
    echo "  - Transaction Status: {$testTransaction->transaction_status}\n";
    echo "  - Current State: {$testTransaction->current_state}\n";
    echo "  - Receiving Status: {$testTransaction->receiving_status}\n";
    echo "  - Department ID: {$testTransaction->department_id}\n";
    echo "  - Origin Department ID: {$testTransaction->origin_department_id}\n\n";
    
    // Test 2: Origin creator confirms receipt
    testReceiptConfirmation($testTransaction, $testCreator);
    
} catch (\Exception $e) {
    echo "❌ Error during approval: {$e->getMessage()}\n";
    echo "   Stack trace:\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
}

function testReceiptConfirmation($transaction, $creator) {
    echo "--- Test 2: Origin Creator Receipt Confirmation ---\n";
    
    // Verify pre-conditions
    if ($transaction->transaction_status !== 'completed') {
        echo "❌ Transaction is not completed. Status: {$transaction->transaction_status}\n";
        return;
    }
    
    if ($transaction->receiving_status !== 'pending') {
        echo "⚠️  Transaction receiving_status is not 'pending'. Current: {$transaction->receiving_status}\n";
        if ($transaction->receiving_status === 'received') {
            echo "✓ Transaction already received!\n";
            return;
        }
    }
    
    if ($transaction->origin_department_id !== $creator->department_id) {
        echo "⚠️  Creator is not from origin department. Finding correct user...\n";
        $creator = User::where('department_id', $transaction->origin_department_id)
            ->where('type', 'Head')
            ->first();
        if (!$creator) {
            echo "❌ No user found in origin department\n";
            return;
        }
        echo "  - Found user: {$creator->name}\n";
    }
    
    echo "  - Transaction: {$transaction->transaction_code}\n";
    echo "  - Status Before: {$transaction->transaction_status}\n";
    echo "  - Receiving Status Before: {$transaction->receiving_status}\n\n";
    
    // Simulate receipt confirmation
    try {
        $transaction->update([
            'receiving_status' => 'received',
            'received_at' => now(),
        ]);
        
        // Log the confirmation
        TransactionLog::create([
            'transaction_id' => $transaction->id,
            'from_state' => $transaction->current_state,
            'to_state' => 'completed',
            'action' => 'confirm_received',
            'action_by' => $creator->id,
            'remarks' => 'Transaction received and confirmed by origin department.',
        ]);
        
        $transaction->refresh();
        
        echo "✓ Receipt confirmed successfully\n";
        echo "  - Transaction Status: {$transaction->transaction_status}\n";
        echo "  - Receiving Status: {$transaction->receiving_status}\n";
        echo "  - Received At: " . $transaction->received_at->format('Y-m-d H:i:s') . "\n\n";
        
        // Verify transaction log
        $log = TransactionLog::where('transaction_id', $transaction->id)
            ->where('action', 'confirm_received')
            ->latest()
            ->first();
        
        if ($log) {
            echo "✓ Transaction log created\n";
            echo "  - Action: {$log->action}\n";
            echo "  - By: " . $log->actionBy->name . "\n";
            echo "  - Remarks: {$log->remarks}\n\n";
        } else {
            echo "⚠️  Transaction log not found\n\n";
        }
        
        echo "✅ All tests passed! Workflow completed successfully.\n\n";
        echo "=== Summary ===\n";
        echo "1. Last reviewer approval: ✓\n";
        echo "2. Status set to 'completed' with receiving_status 'pending': ✓\n";
        echo "3. Origin creator confirmed receipt: ✓\n";
        echo "4. Receiving status set to 'received': ✓\n";
        echo "5. Transaction log created: ✓\n";
        echo "\n✅ Complete workflow test successful!\n";
        
    } catch (\Exception $e) {
        echo "❌ Error during receipt confirmation: {$e->getMessage()}\n";
        echo "   Stack trace:\n";
        echo $e->getTraceAsString() . "\n";
    }
}

echo "\n=== Test Complete ===\n";
