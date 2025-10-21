<?php

require_once 'vendor/autoload.php';

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Services\Document\DocumentIdGenerator;
use App\Services\Document\DocumentWorkflowService;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

try {
    // Find a user to authenticate as (preferably a Staff or Head user)
    $user = User::where('type', '!=', 'Admin')->first();
    
    if (!$user) {
        echo "ERROR: No Staff or Head user found\n";
        exit(1);
    }
    
    // Manually set the authenticated user
    Auth::login($user);
    
    echo "Authenticated as: {$user->name} ({$user->type})\n";
    
    // Create document data
    $generator = new DocumentIdGenerator();
    $workflow = new DocumentWorkflowService();
    $documentId = $generator->generate();
    
    echo "Generated Document ID: {$documentId}\n";
    
    // Find a reviewer (Head user different from current user)
    $reviewer = User::where('type', 'Head')->where('id', '!=', $user->id)->first();
    
    if (!$reviewer) {
        echo "ERROR: No Head user found for reviewer\n";
        exit(1);
    }
    
    echo "Reviewer: {$reviewer->name} ({$reviewer->type})\n";
    
    $data = [
        'title' => 'Test Document - ' . now()->format('Y-m-d H:i:s'),
        'document_type' => 'adsad',
        'client_name' => 'Test Client Name',
        'reviewer_id' => $reviewer->id,
        'process_time' => 1440, // 1 day in minutes
        'time_value' => 1,
        'time_unit' => 'days',
        'difficulty' => 'normal',
        'assigned_staff' => 'sdfddsfsd',
        'attachment_path' => null,
        'initial_notes' => 'Test document creation through script'
    ];
    
    $docInfo = ['title' => 'adsad'];
    
    // Create the document review
    $review = $workflow->sendForReview($data, $docInfo, $documentId);
    
    echo "SUCCESS: Document review created!\n";
    echo "Review ID: {$review->id}\n";
    echo "Document ID: {$review->document_id}\n";
    echo "Status: {$review->status}\n";
    echo "Client: {$review->client_name}\n";
    echo "Assigned to: {$review->reviewer->name}\n";
    
    // Check if document appears in pending list
    $pendingCount = \App\Models\DocumentReview::where('status', 'pending')->count();
    echo "Total pending documents: {$pendingCount}\n";
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}