<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== Scanning for Transactions with Rejected Reviewers but Wrong State ===\n\n";

// Find transactions that have rejected reviewers but wrong current_state
$problematicTransactions = \App\Models\Transaction::whereHas('reviewers', function($query) {
    $query->where('status', 'rejected');
})
->where(function($query) {
    $query->whereNull('current_state')
          ->orWhere('current_state', 'not like', 'returned_to_%');
})
->with(['reviewers' => function($query) {
    $query->where('status', 'rejected')->orderBy('reviewed_at', 'desc');
}, 'creator', 'department'])
->get();

if ($problematicTransactions->isEmpty()) {
    echo "✅ No problematic transactions found. All rejected transactions have correct states.\n";
    exit;
}

echo "Found " . $problematicTransactions->count() . " transactions with incorrect states:\n\n";

foreach ($problematicTransactions as $transaction) {
    $rejectedReviewer = $transaction->reviewers->first();
    
    echo "Transaction: {$transaction->transaction_code}\n";
    echo "  Current State: " . ($transaction->current_state ?? 'NULL') . "\n";
    echo "  Current Step: {$transaction->current_workflow_step}\n";
    echo "  Rejected By: " . ($rejectedReviewer ? $rejectedReviewer->reviewer->name : 'N/A') . "\n";
    echo "  Rejected At: " . ($rejectedReviewer ? $rejectedReviewer->reviewed_at : 'N/A') . "\n";
    echo "  Rejection Reason: " . ($rejectedReviewer ? $rejectedReviewer->rejection_reason : 'N/A') . "\n";
    
    // Determine correct state based on current step
    if ($transaction->current_workflow_step == 1) {
        echo "  → Should be: returned_to_creator\n";
        
        // Fix it
        $oldState = $transaction->current_state ?? 'NULL';
        $transaction->update([
            'current_state' => 'returned_to_creator',
            'department_id' => $transaction->origin_department_id
        ]);
        
        // Log the fix
        $creatorId = $transaction->created_by ?? 1;
        \App\Models\TransactionLog::create([
            'transaction_id' => $transaction->id,
            'from_state' => $oldState,
            'to_state' => 'returned_to_creator',
            'action' => 'system_fix',
            'action_by' => $creatorId,
            'remarks' => 'System fix: Corrected state after first-step rejection.'
        ]);
        
        echo "  ✅ Fixed\n";
    } else {
        // For steps > 1, it should go back to previous department
        // This would need more complex logic based on workflow
        echo "  ⚠️  Rejected at step {$transaction->current_workflow_step} - needs manual review\n";
    }
    
    echo "\n";
}

echo "\nFix complete! Transactions should now appear correctly in the Rejected tab.\n";
