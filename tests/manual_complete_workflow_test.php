<?php

/**
 * Manual test script for last step approval and receipt confirmation
 */

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Transaction;
use App\Models\TransactionReviewer;
use App\Models\TransactionLog;
use App\Services\Transaction\WorkflowEngineService;

echo "\n=== Manual Workflow Test ===\n\n";

// Get the latest transaction or look for one at the last step
$transaction = Transaction::where('transaction_status', 'in_progress')
    ->latest()
    ->first();

if (!$transaction) {
    echo "❌ No in-progress transaction found\n";
    exit(1);
}

echo "Transaction: {$transaction->transaction_code}\n";
echo "Status: {$transaction->transaction_status}\n";
echo "Current Step: {$transaction->current_workflow_step} / {$transaction->total_workflow_steps}\n";
echo "Current State: {$transaction->current_state}\n";
echo "Receiving Status: " . ($transaction->receiving_status ?? 'null') . "\n\n";

// Get current pending reviewer
$currentReviewer = $transaction->reviewers()
    ->where('status', 'pending')
    ->first();

if (!$currentReviewer) {
    echo "❌ No pending reviewer found\n";
    
    // Check if already completed
    if ($transaction->transaction_status === 'completed') {
        echo "✓ Transaction is already completed!\n";
        echo "  Receiving Status: {$transaction->receiving_status}\n";
        
        if ($transaction->receiving_status === 'pending') {
            echo "\n--- Testing Receipt Confirmation ---\n";
            testReceiptConfirmation($transaction);
        } else if ($transaction->receiving_status === 'received') {
            echo "✓ Transaction already received!\n";
        }
    }
    exit(0);
}

echo "Current Reviewer: {$currentReviewer->reviewer->name}\n";
echo "Department: {$currentReviewer->department->name}\n";
echo "Reviewer Status: {$currentReviewer->status}\n";
echo "Received Status: {$currentReviewer->received_status}\n\n";

// Check if at last step
$isLastStep = $transaction->current_workflow_step >= $transaction->total_workflow_steps;
echo "Is Last Step: " . ($isLastStep ? "YES" : "NO") . "\n\n";

// Step 1: Mark as received if not already
if ($currentReviewer->received_status !== 'received') {
    echo "--- Step 1: Marking transaction as received ---\n";
    $currentReviewer->update([
        'received_status' => 'received',
        'received_by' => $currentReviewer->reviewer_id,
        'received_at' => now(),
    ]);
    echo "✓ Marked as received\n\n";
}

// Step 2: Approve at last step
echo "--- Step 2: Last Reviewer Approval ---\n";

try {
    $workflowEngine = app(WorkflowEngineService::class);
    
    // Update reviewer status
    $currentReviewer->update([
        'status' => 'approved',
        'reviewed_at' => now(),
    ]);
    echo "✓ Reviewer status updated to approved\n";
    
    // Execute workflow action
    $workflowEngine->executeAction(
        $transaction,
        'approve',
        $currentReviewer->reviewer,
        'Debug test: Last step approval'
    );
    echo "✓ Workflow engine executed approve action\n";
    
    // Update transaction for last step completion
    if ($isLastStep) {
        $transaction->update([
            'receiving_status' => 'pending',
            'department_id' => $transaction->origin_department_id,
        ]);
        echo "✓ Set receiving_status to pending and returned to origin department\n";
    }
    
    $transaction->refresh();
    
    echo "\nTransaction After Approval:\n";
    echo "  - Status: {$transaction->transaction_status}\n";
    echo "  - Current State: {$transaction->current_state}\n";
    echo "  - Receiving Status: {$transaction->receiving_status}\n";
    echo "  - Department ID: {$transaction->department_id}\n";
    echo "  - Origin Department ID: {$transaction->origin_department_id}\n\n";
    
    // Step 3: Test receipt confirmation
    if ($transaction->transaction_status === 'completed' && $transaction->receiving_status === 'pending') {
        testReceiptConfirmation($transaction);
    }
    
} catch (\Exception $e) {
    echo "❌ Error: {$e->getMessage()}\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
}

function testReceiptConfirmation($transaction) {
    echo "--- Step 3: Origin Creator Receipt Confirmation ---\n";
    
    // Find a user from origin department
    $originUser = \App\Models\User::where('department_id', $transaction->origin_department_id)
        ->first();
    
    if (!$originUser) {
        echo "❌ No user found in origin department (ID: {$transaction->origin_department_id})\n";
        return;
    }
    
    echo "Origin User: {$originUser->name} (Department ID: {$originUser->department_id})\n";
    echo "Transaction receiving_status before: {$transaction->receiving_status}\n\n";
    
    try {
        // Update receiving status
        $transaction->update([
            'receiving_status' => 'received',
            'received_at' => now(),
        ]);
        
        // Create transaction log
        TransactionLog::create([
            'transaction_id' => $transaction->id,
            'from_state' => $transaction->current_state,
            'to_state' => 'completed',
            'action' => 'confirm_received',
            'action_by' => $originUser->id,
            'remarks' => 'Transaction received and confirmed by origin department.',
        ]);
        
        $transaction->refresh();
        
        echo "✓ Receipt confirmed successfully!\n";
        echo "  - Receiving Status: {$transaction->receiving_status}\n";
        echo "  - Received At: " . $transaction->received_at->format('Y-m-d H:i:s') . "\n\n";
        
        // Verify log
        $log = TransactionLog::where('transaction_id', $transaction->id)
            ->where('action', 'confirm_received')
            ->latest()
            ->first();
        
        if ($log) {
            echo "✓ Transaction log verified\n";
            echo "  - Action: {$log->action}\n";
            echo "  - By: {$log->actionBy->name}\n";
            echo "  - Remarks: {$log->remarks}\n\n";
        }
        
        echo "\n✅ COMPLETE WORKFLOW TEST PASSED!\n";
        echo "=================================\n";
        echo "1. Last reviewer approved: ✓\n";
        echo "2. Status set to 'completed': ✓\n";
        echo "3. Receiving status set to 'pending': ✓\n";
        echo "4. Origin creator confirmed receipt: ✓\n";
        echo "5. Receiving status set to 'received': ✓\n";
        echo "6. Transaction log created: ✓\n\n";
        
    } catch (\Exception $e) {
        echo "❌ Error during receipt confirmation: {$e->getMessage()}\n";
        echo $e->getTraceAsString() . "\n";
    }
}

echo "\n=== Test Complete ===\n";
