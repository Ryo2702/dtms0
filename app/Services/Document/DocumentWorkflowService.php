<?php

namespace App\Services\Document;

use App\Helpers\NotificationHelper;
use App\Models\DocumentReview;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class DocumentWorkflowService
{
    public function sendForReview(array $data, array $docInfo, string $documentId): DocumentReview
    {
        $user = Auth::user();

        if ($user->type !== 'Head') {
            throw new \Exception('You do not have permission to send documents for review.');
        }

        $reviewer = User::findOrFail($data['reviewer_id']);

        if ($reviewer->type !== 'Head') {
            throw new \Exception('Documents can only be sent to Department Heads for review.');
        }

        $clientName = $data['client_name'] ?? $data['name'] ?? $data['resident_name'] ?? 'Unknown';
        $processTime = (int) $data['process_time'];

        $review = DocumentReview::create([
            'document_id' => $documentId,
            'document_type' => $docInfo['title'],
            'client_name' => $clientName,
            'document_data' => $data,
            'created_by' => $user->id,
            'assigned_to' => $data['reviewer_id'],
            'current_department_id' => $reviewer->department_id,
            'original_department_id' => $user->department_id,
            'status' => 'pending',
            'review_notes' => $data['initial_notes'] ?? null,
            'process_time_minutes' => $processTime,
            'submitted_at' => now(),
            'due_at' => now()->addMinutes($processTime),
            'priority' => $data['priority'] ?? 'low',
            'time_value' => $data['time_value'] ?? null,
            'time_unit' => $data['time_unit'] ?? 'minutes',
            'attachment_path' => $data['attachment_path'] ?? null,
            'assigned_staff' => $data['assigned_staff'] ?? null,
            'forwarding_chain' => [
                [
                    'step' => 1,
                    'action' => 'created',
                    'from_user_id' => $user->id,
                    'from_user_name' => $user->name,
                    'from_user_type' => $user->type,
                    'from_department' => $user->department?->name,
                    'to_user_id' => null,
                    'to_user_name' => null,
                    'to_department' => null,
                    'notes' => 'Document created and prepared for review',
                    'process_time' => null,
                    'timestamp' => now()->toISOString(),
                    'status' => 'completed',
                ],
                [
                    'step' => 2,
                    'action' => 'submitted_for_review',
                    'from_user_id' => $user->id,
                    'from_user_name' => $user->name,
                    'from_user_type' => $user->type,
                    'from_department' => $user->department?->name,
                    'to_user_id' => $reviewer->id,
                    'to_user_name' => $reviewer->name,
                    'to_user_type' => $reviewer->type,
                    'to_department' => $reviewer->department?->name,
                    'notes' => $data['initial_notes'] ?? 'Initial document review request',
                    'process_time' => $processTime,
                    'timestamp' => now()->toISOString(),
                    'status' => 'pending',
                    'due_at' => now()->addMinutes($processTime)->toISOString(),
                ],
            ],
        ]);

        // Send notification to the reviewer about the pending document
        NotificationHelper::send(
            $reviewer,
            'pending',
            'New Document for Review',
            "You have received a new {$docInfo['title']} document from {$user->name} that requires your review.",
            $review->id,
            null,
            'pending'
        );

        return $review;
    }

    public function forwardReview(DocumentReview $review, User $currentUser, User $forwardTo, string $notes, int $processTime): void
    {
        if ($forwardTo->type !== 'Head') {
            throw new \Exception('Documents can only be forwarded to Department Heads.');
        }

        $review->addToForwardingChain(
            'forwarded',
            $currentUser,
            $forwardTo,
            $notes,
            $processTime
        );

        $review->update([
            'assigned_to' => $forwardTo->id,
            'current_department_id' => $forwardTo->department_id,
            'process_time_minutes' => $processTime,
            'due_at' => now()->addMinutes($processTime),
        ]);

        // Send notification to the person receiving the forwarded document
        NotificationHelper::send(
            $forwardTo,
            'received',
            'Document Forwarded to You',
            "{$currentUser->name} has forwarded a {$review->document_type} document to you for review.",
            $review->id,
            null,
            'in_progress'
        );
    }

    public function completeReview(DocumentReview $review, User $currentUser, string $notes): void
    {
        $originalCreator = User::find($review->created_by);

        if (! $originalCreator) {
            throw new \Exception('Original document creator not found.');
        }

        $wasOnTime = ! $review->due_at || now()->lessThanOrEqualTo($review->due_at);
        $timeStatus = $wasOnTime ? 'completed on time' : 'completed overdue';

        $completionMessage = $notes ?? 'Document review completed successfully.';
        $completionMessage .= "\n\nStatus: ".ucfirst($timeStatus);

        $completionMessage .= "\n\nDocument is ready for download and client signature.";

        $review->addToForwardingChain(
            'completed',
            $currentUser,
            $originalCreator,
            $completionMessage,
            2
        );

        $review->update([
            'assigned_to' => $originalCreator->id,
            'current_department_id' => $originalCreator->department_id,
            'process_time_minutes' => 2,
            'due_at' => now()->addMinutes(2),
            'status' => 'approved',
            'is_final_review' => true,
            'review_notes' => $notes,
            'reviewed_at' => now(),
            'completed_on_time' => $wasOnTime,
        ]);

        // Send notification to the original creator
        NotificationHelper::send(
            $originalCreator,
            'approved',
            'Document Approved',
            "Your {$review->document_type} document has been approved by {$currentUser->name} and is ready for download.",
            $review->id,
            null,
            'approved'
        );
    }

    public function rejectReview(DocumentReview $review, User $currentUser, string $notes): void
    {
        $originalCreator = User::find($review->created_by);

        if (! $originalCreator) {
            throw new \Exception('Original document creator not found.');
        }

        $review->addToForwardingChain(
            'rejected',
            $currentUser,
            $originalCreator,
            $notes,
            null
        );

        $review->update([
            'status' => 'rejected',
            'review_notes' => $notes,
            'reviewed_at' => now(),
            'assigned_to' => $originalCreator->id,
            'current_department_id' => $originalCreator->department_id,
        ]);

        // Send notification to the original creator
        NotificationHelper::send(
            $originalCreator,
            'rejected',
            'Document Rejected',
            "Your {$review->document_type} document has been rejected by {$currentUser->name}. Please review the notes and resubmit.",
            $review->id,
            null,
            'rejected'
        );
    }

    public function cancelReview(DocumentReview $review, User $currentUser, string $notes): void
    {
        $originalCreator = User::find($review->created_by);

        if (! $originalCreator) {
            throw new \Exception('Original document creator not found.');
        }

        $review->addToForwardingChain(
            'canceled',
            $currentUser,
            $originalCreator,
            $notes,
            null
        );

        $review->update([
            'status' => 'canceled',
            'review_notes' => $notes,
            'reviewed_at' => now(),
            'assigned_to' => $originalCreator->id,
            'current_department_id' => $originalCreator->department_id,
        ]);

        // Send notification to the original creator
        NotificationHelper::send(
            $originalCreator,
            'canceled',
            'Document Canceled',
            "Your {$review->document_type} document has been canceled by {$currentUser->name}.",
            $review->id,
            null,
            'cancelled'
        );
    }
}
