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
    echo "Total Steps: " . $transaction->total_workflow_steps . "\n";
    echo "Current State: " . $transaction->current_state . "\n";
    
    echo "\n=== Checking Column Names ===\n";
    $columns = DB::getSchemaBuilder()->getColumnListing('transaction_reviewers');
    echo "Columns: " . implode(", ", $columns) . "\n";
    
    echo "\n=== Sample Record ===\n";
    $sample = DB::table('transaction_reviewers')->where('transaction_id', $transaction->id)->first();
    if ($sample) {
        echo json_encode($sample, JSON_PRETTY_PRINT) . "\n";
    }
    
} else {
    echo "Transaction not found\n";
}
