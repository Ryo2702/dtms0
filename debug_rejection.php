<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Transaction;

echo "\n=== Debug Rejection Issue ===\n\n";

$transactionCode = 'TTN-20260116-0001-0764';
$transaction = Transaction::where('transaction_code', $transactionCode)->first();

if (!$transaction) {
    echo "❌ Transaction $transactionCode not found\n";
    exit(1);
}

echo "Transaction: {$transaction->transaction_code}\n";
echo "Current State: {$transaction->current_state}\n";
echo "Current Step: {$transaction->current_workflow_step}\n\n";

// Check the workflow configuration
$workflow = $transaction->workflow;
echo "Workflow: {$workflow->transaction_name}\n";
echo "Has Configuration: " . ($workflow->hasWorkConfigured() ? 'Yes' : 'No') . "\n\n";

// Check transitions
$transitions = $workflow->getTransition();
echo "=== Workflow Transitions ===\n";
echo "Current state ({$transaction->current_state}) has these actions:\n";

if (isset($transitions[$transaction->current_state])) {
    foreach ($transitions[$transaction->current_state] as $action => $nextState) {
        echo "  - {$action} → {$nextState}\n";
    }
} else {
    echo "  ❌ No transitions defined for current state!\n";
}

echo "\n=== Attempting Fix ===\n";

try {
    // Manually fix the transaction state
    $oldState = $transaction->current_state;
    
    $transaction->update([
        'current_state' => 'returned_to_creator',
        'department_id' => $transaction->origin_department_id,
    ]);
    
    // Log the fix
    \App\Models\TransactionLog::create([
        'transaction_id' => $transaction->id,
        'from_state' => $oldState,
        'to_state' => 'returned_to_creator',
        'action' => 'system_fix',
        'action_by' => $transaction->created_by,
        'remarks' => 'System fix: Corrected state after first-step rejection for origin creator visibility.',
    ]);
    
    echo "✅ Transaction state updated to 'returned_to_creator'\n";
    echo "✅ Transaction returned to origin department (ID: {$transaction->origin_department_id})\n";
    echo "✅ Log entry created\n\n";
    
    echo "The origin creator ({$transaction->creator->name}) should now see this transaction in the 'Rejected' tab.\n";
    
} catch (\Exception $e) {
    echo "❌ Error fixing transaction: {$e->getMessage()}\n";
}

echo "\n";
