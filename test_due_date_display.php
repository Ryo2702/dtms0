<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Transaction;
use App\Models\User;

echo "=== Testing Due Date Display ===\n\n";

// Get a user to test with
$user = User::first();
if (!$user) {
    echo "No users found in database.\n";
    exit(1);
}

echo "Testing for user: {$user->name}\n\n";

// Get transactions for this user
$transactions = Transaction::where('created_by', $user->id)
    ->with(['reviewers' => function($query) {
        $query->where('status', 'pending')->orderBy('created_at', 'desc');
    }])
    ->limit(5)
    ->get();

if ($transactions->isEmpty()) {
    echo "No transactions found for this user.\n";
    exit(0);
}

echo "Found {$transactions->count()} transaction(s):\n\n";

foreach ($transactions as $transaction) {
    echo "Transaction: {$transaction->transaction_code}\n";
    echo "Status: {$transaction->transaction_status}\n";
    
    // Test the due_date accessor
    $dueDate = $transaction->due_date;
    
    if ($dueDate) {
        echo "Due Date: " . $dueDate->format('M d, Y') . "\n";
        echo "Relative: " . $dueDate->diffForHumans() . "\n";
        
        if ($dueDate->isPast() && $transaction->transaction_status !== 'completed') {
            echo "Status: OVERDUE (displayed in red)\n";
        } else {
            echo "Status: On track\n";
        }
    } else {
        echo "Due Date: No due date (should display 'No due date' in gray)\n";
    }
    
    // Check pending reviewers
    $pendingReviewers = $transaction->reviewers->where('status', 'pending');
    echo "Pending Reviewers: {$pendingReviewers->count()}\n";
    
    if ($pendingReviewers->isNotEmpty()) {
        foreach ($pendingReviewers as $reviewer) {
            echo "  - Reviewer ID: {$reviewer->reviewer_id}, Due: " . $reviewer->due_date->format('M d, Y') . "\n";
        }
    }
    
    echo "\n" . str_repeat('-', 60) . "\n\n";
}

echo "✓ Test completed successfully\n";
echo "\nExpected behavior in the view:\n";
echo "- If due_date exists: Display formatted date (red if overdue)\n";
echo "- If no due_date: Display 'No due date' in gray\n";
