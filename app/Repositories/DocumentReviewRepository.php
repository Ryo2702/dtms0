<?php

namespace App\Repositories;

use App\Models\DocumentReview;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class DocumentReviewRepository
{
    public function getReviewsForUser(User $user): Builder
    {
        $query = DocumentReview::with(['creator', 'reviewer', 'currentDepartment', 'originalDepartment']);

        if ($user->type === 'Head') {
            return $query->where(function ($q) use ($user) {
                $q->where('created_by', $user->id)
                    ->orWhere('assigned_to', $user->id)
                    ->orWhere('current_department_id', $user->department_id);
            });
        }

        return $query->where(function ($q) use ($user) {
            $q->where('created_by', $user->id)
                ->orWhere('assigned_to', $user->id);
        });
    }

    public function getAllReviews(): Builder
    {
        return DocumentReview::with(['creator', 'reviewer', 'currentDepartment', 'originalDepartment']);
    }

    public function getReceivedReviews(User $user): Builder
    {
        return DocumentReview::with(['creator', 'reviewer', 'currentDepartment', 'originalDepartment'])
            ->where('assigned_to', $user->id)
            ->where('created_by', '!=', $user->id);
    }

    public function getSentReviews(User $user): Builder
    {
        return DocumentReview::with(['creator', 'reviewer', 'currentDepartment', 'originalDepartment'])
            ->where('created_by', $user->id)
            ->where('assigned_to', '!=', $user->id);
    }

    public function filterByStatus(Builder $query, string $status): Builder
    {
        return match ($status) {
            'pending' => $query->whereNull('downloaded_at')->whereNull('rejected_at'),
            'completed' => $query->whereNotNull('downloaded_at'),
            'rejected' => $query->whereNotNull('rejected_at'),
            default => $query
        };
    }

    public function filterPending(Builder $query): Builder
    {
        return $query->whereNull('downloaded_at')->whereNull('rejected_at');
    }

    public function filterCompleted(Builder $query): Builder
    {
        return $query->whereNotNull('downloaded_at');
    }

    public function findWithRelations(int $id): ?DocumentReview
    {
        return DocumentReview::with(['creator', 'reviewer', 'currentDepartment', 'originalDepartment'])
            ->find($id);
    }

    public function getPendingCountForUser(User $user): int
    {
        return $this->getReviewsForUser($user)
            ->whereNull('downloaded_at')
            ->whereNull('rejected_at')
            ->count();
    }
}
