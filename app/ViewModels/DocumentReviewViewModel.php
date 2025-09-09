<?php

namespace App\ViewModels;

use App\Models\DocumentReview;
use Illuminate\Pagination\LengthAwarePaginator;

class DocumentReviewViewModel
{
    public function transform(DocumentReview $review): DocumentReview
    {
        $review->is_overdue = $this->isOverdue($review);
        $review->due_status = $this->getDueStatus($review);

        return $review;
    }

    public function transformCollection(LengthAwarePaginator $reviews): LengthAwarePaginator
    {
        $reviews->getCollection()->transform(function ($review) {
            return $this->transform($review);
        });

        return $reviews;
    }

    private function isOverdue(DocumentReview $review): bool
    {
        return $review->due_at &&
            now()->greaterThan($review->due_at) &&
            !$review->downloaded_at;
    }

    private function getDueStatus(DocumentReview $review): string
    {
        if (!$review->due_at) {
            return 'no_due_date';
        }

        $now = now();
        $dueAt = $review->due_at;

        if ($review->downloaded_at) {
            return 'completed';
        }

        if ($review->rejected_at) {
            return 'rejected';
        }

        if ($now->lessThan($dueAt)) {
            return 'on_time';
        }

        return 'overdue';
    }
}
