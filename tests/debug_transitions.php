<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Transaction;

$transaction = Transaction::where('transaction_code', 'TTN-20260114-0001-6FD7')->first();
$workflow = $transaction->workflow;

echo "Current State: " . $transaction->current_state . "\n";
echo "Current Step: " . $transaction->current_workflow_step . "\n";
echo "Total Steps: " . $transaction->total_workflow_steps . "\n";
echo "\nWorkflow Transitions:\n";

$transitions = $workflow->getTransition();
var_dump($transitions);
