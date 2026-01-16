<?php

namespace App\Http\Controllers\Transaction;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Illuminate\Http\Request;

class ArchiveController extends Controller
{
    /**
     * Display archived (completed) transactions
     */
    public function index(Request $request)
    {
        $user = $request->user();
        
        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');
        $search = $request->get('search');
        $status = $request->get('status', 'all');
        
        // Build query for archived transactions
        $query = Transaction::query()
            ->with(['workflow', 'assignStaff', 'department', 'originDepartment', 'creator'])
            ->where(function($q) {
                $q->where('transaction_status', 'completed')
                  ->orWhere('transaction_status', 'cancelled');
            });
        
        // For non-admin users, show only their department's transactions or transactions they created
        if ($user->type !== 'Admin') {
            $query->where(function($q) use ($user) {
                $q->where('department_id', $user->department_id)
                  ->orWhere('created_by', $user->id)
                  ->orWhereHas('reviewers', function($q2) use ($user) {
                      $q2->where('reviewer_id', $user->id);
                  });
            });
        }
        
        // Filter by status
        if ($status !== 'all') {
            $query->where('transaction_status', $status);
        }
        
        // Search functionality
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('transaction_code', 'like', "%{$search}%")
                  ->orWhere('purpose', 'like', "%{$search}%")
                  ->orWhere('subject', 'like', "%{$search}%")
                  ->orWhereHas('creator', function($q2) use ($search) {
                      $q2->where('name', 'like', "%{$search}%");
                  });
            });
        }
        
        // Date filters
        if ($dateFrom) {
            $query->whereDate('completed_at', '>=', $dateFrom);
        }
        if ($dateTo) {
            $query->whereDate('completed_at', '<=', $dateTo);
        }
        
        $transactions = $query->orderBy('completed_at', 'desc')->paginate(15);
        
        // Stats for filters
        $stats = [
            'all' => Transaction::where(function($q) {
                        $q->where('transaction_status', 'completed')
                          ->orWhere('transaction_status', 'cancelled');
                    })
                    ->when($user->type !== 'Admin', function($q) use ($user) {
                        $q->where(function($q2) use ($user) {
                            $q2->where('department_id', $user->department_id)
                               ->orWhere('created_by', $user->id)
                               ->orWhereHas('reviewers', function($q3) use ($user) {
                                   $q3->where('reviewer_id', $user->id);
                               });
                        });
                    })
                    ->count(),
            'completed' => Transaction::where('transaction_status', 'completed')
                    ->when($user->type !== 'Admin', function($q) use ($user) {
                        $q->where(function($q2) use ($user) {
                            $q2->where('department_id', $user->department_id)
                               ->orWhere('created_by', $user->id)
                               ->orWhereHas('reviewers', function($q3) use ($user) {
                                   $q3->where('reviewer_id', $user->id);
                               });
                        });
                    })
                    ->count(),
            'cancelled' => Transaction::where('transaction_status', 'cancelled')
                    ->when($user->type !== 'Admin', function($q) use ($user) {
                        $q->where(function($q2) use ($user) {
                            $q2->where('department_id', $user->department_id)
                               ->orWhere('created_by', $user->id)
                               ->orWhereHas('reviewers', function($q3) use ($user) {
                                   $q3->where('reviewer_id', $user->id);
                               });
                        });
                    })
                    ->count(),
        ];
        
        return view('transactions.archive.index', compact('transactions', 'stats'));
    }
}
