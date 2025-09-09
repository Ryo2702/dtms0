<?php

namespace App\Services;

use App\Models\DocumentReview;
use App\Models\User;
use App\Repositories\DocumentReviewRepository;
use App\ViewModels\DocumentReviewViewModel;
use Illuminate\Pagination\LengthAwarePaginator;

class DocumentReviewService
{
    public function __construct(
        private DocumentReviewRepository $reviewRepository,
        private DocumentReviewViewModel $viewModel
    ) {}

    public function getPendingReviews(User $user, ?string $status = null): LengthAwarePaginator
    {
        $query = $this->reviewRepository->getReviewsForUser($user);

        if ($status) {
            $query = $this->reviewRepository->filterByStatus($query, $status);
        } else {
            $query = $this->reviewRepository->filterPending($query);
        }

        $reviews = $query->orderBy('created_at', 'desc')->paginate(10);

        return $this->viewModel->transformCollection($reviews);
    }

    public function getCompletedReviews(User $user): LengthAwarePaginator
    {
        $query = $this->reviewRepository->getReviewsForUser($user);
        $query = $this->reviewRepository->filterCompleted($query);

        $reviews = $query->orderBy('downloaded_at', 'desc')->paginate(10);

        return $this->viewModel->transformCollection($reviews);
    }

    public function getReceivedReviews(User $user): LengthAwarePaginator
    {
        $query = $this->reviewRepository->getReceivedReviews($user);
        $reviews = $query->orderBy('created_at', 'desc')->paginate(10);

        return $this->viewModel->transformCollection($reviews);
    }

    public function getSentReviews(User $user): LengthAwarePaginator
    {
        $query = $this->reviewRepository->getSentReviews($user);
        $reviews = $query->orderBy('created_at', 'desc')->paginate(10);

        return $this->viewModel->transformCollection($reviews);
    }

    public function getAdminTrackingReviews(): LengthAwarePaginator
    {
        $query = $this->reviewRepository->getAllReviews();
        $reviews = $query->orderBy('created_at', 'desc')->paginate(15);

        return $this->viewModel->transformCollection($reviews);
    }

    public function findReviewForUser(int $reviewId, User $user): ?DocumentReview
    {
        $review = $this->reviewRepository->findWithRelations($reviewId);

        if (!$review || !$this->canUserViewReview($review, $user)) {
            return null;
        }

        return $this->viewModel->transform($review);
    }

    public function getPendingCount(User $user): int
    {
        return $this->reviewRepository->getPendingCountForUser($user);
    }

    private function canUserViewReview(DocumentReview $review, User $user): bool
    {
        return $review->created_by === $user->id ||
            $review->assigned_to === $user->id ||
            $user->type === 'Admin';
    }
}
