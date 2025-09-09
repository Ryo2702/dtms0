<?php

namespace App\Services;

use App\Models\DocumentReview;
use App\Http\Requests\ReviewUpdateRequest;
use App\Events\DocumentReviewForwarded;
use App\Events\DocumentReviewCompleted;
use App\Events\DocumentReviewRejected;
use Illuminate\Support\Facades\Auth;

class DocumentWorkflowService
{
    public function processReviewAction(DocumentReview $review, ReviewUpdateRequest $request): bool
    {
        return match ($request->action) {
            'approve' => $this->approveReview($review, $request->notes),
            'reject' => $this->rejectReview($review, $request->notes),
            'forward' => $this->forwardReview($review, $request),
            default => false
        };
    }

    private function approveReview(DocumentReview $review, ?string $notes): bool
    {
        $review->update([
            'status' => 'completed',
            'reviewer_notes' => $notes,
            'reviewed_at' => now(),
            'reviewer_id' => Auth::id(),
        ]);

        event(new DocumentReviewCompleted($review));

        return true;
    }

    private function rejectReview(DocumentReview $review, ?string $notes): bool
    {
        $review->update([
            'status' => 'rejected',
            'reviewer_notes' => $notes,
            'rejected_at' => now(),
            'reviewer_id' => Auth::id(),
        ]);

        event(new DocumentReviewRejected($review));

        return true;
    }

    private function forwardReview(DocumentReview $review, ReviewUpdateRequest $request): bool
    {
        $review->update([
            'current_department_id' => $request->forward_to,
            'assigned_to' => $this->getNextReviewer($request->forward_to),
            'reviewer_notes' => $request->notes,
            'forwarded_at' => now(),
            'reviewer_id' => Auth::id(),
        ]);

        event(new DocumentReviewForwarded($review));

        return true;
    }

    private function getNextReviewer(int $departmentId): ?int
    {
        // Logic to determine next reviewer based on department
        // This could be the department head or a specific role
        return \App\Models\User::where('department_id', $departmentId)
            ->where('type', 'Head')
            ->first()?->id;
    }
}
