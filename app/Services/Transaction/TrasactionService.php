<?php

namespace App\Services\Transaction;

use App\Models\Transaction;
use App\Models\User;

class TrasactionService
{

    public function __construct(
        protected WorkflowEngineService $workflowEngine
    ) {}

    //paginated transaction with filters
    public function getTransactions(array $filters = [], int $perPage = 10)
    {
        $query = Transaction::with(['workflow', 'creator', 'department', 'assignStaff', 'currentReviewer']);


        if (!empty($filters['status'])) {
            $query->where('transaction_status', $filters['status']);
        }

        if (!empty($filters['urgency'])) {
            $query->where('level_of_urgency', $filters['urgent']);
        }

        if (!empty($filters['department_id'])) {
            $query->where('department_id', $filters['department_id']);
        }

        if (!empty($filters['workflow_id'])) {
            $query->where('workflow_id', $filters['workflow_id']);
        }

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('transaction_code', 'like', "%{$search}%")->orWhereHas('workflow', fn($wq) => $wq->where('transaction_name', 'like', "%{$search}%"));
            });
        }

        if (!empty($filters['date_from'])) {
            $query->whereDate('submitted_at', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->whereDate('submitted_at', '<=', $filters['date_to']);
        }

        return $query->orderBy('submitted_at', 'desc')->paginate($perPage);
    }


    //get transaction for a specific user based on their department
    public function getTransactionForUser(User $user, array $filters = [], int $perPage = 10)
    {
        $query = Transaction::with(['workflow', 'creator', 'department', 'assignStaff', 'currentReviewer']);

        $query->where(function ($q) use ($user) {
            $q->where('department_id', $user->department_id)
                ->orWhere('created_by', $user->id)
                ->orWhereHas('reviewers', fn($rq) => $rq->where('reviewer_id', $user->id));
        });

        // Apply additional filters
        if (!empty($filters['status'])) {
            $query->where('transaction_status', $filters['status']);
        }

        if (!empty($filters['urgency'])) {
            $query->where('level_of_urgency', $filters['urgency']);
        }

        return $query->orderBy('submitted_at', 'desc')->paginate($perPage);
    }

    //create a new transaction
    public function createTransaction(array $data, User $creator)  {
        
    }

}
