<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\TransactionLog;
use App\Models\User;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TransactionLogController extends Controller
{
    public function index(Request $request)
    {
        // Build query
        $query = TransactionLog::with(['transaction.creator', 'transaction.department', 'transaction.originDepartment', 'actor']);

        // Search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('transaction', function ($tq) use ($search) {
                    $tq->where('transaction_code', 'like', "%{$search}%");
                })
                ->orWhere('action', 'like', "%{$search}%")
                ->orWhere('remarks', 'like', "%{$search}%")
                ->orWhereHas('actor', function ($uq) use ($search) {
                    $uq->where('name', 'like', "%{$search}%");
                });
            });
        }

        // Date range filter
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Action filter
        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        // Department filter
        if ($request->filled('department_id')) {
            $query->whereHas('transaction', function ($tq) use ($request) {
                $tq->where('department_id', $request->department_id)
                  ->orWhere('origin_department_id', $request->department_id);
            });
        }

        // User filter
        if ($request->filled('user_id')) {
            $query->where('action_by', $request->user_id);
        }

        // Get paginated results
        $logs = $query->orderBy('created_at', 'desc')->paginate(20)->withQueryString();

        // Statistics
        $stats = [
            'totalLogs' => TransactionLog::count(),
            'todayLogs' => TransactionLog::whereDate('created_at', today())->count(),
            'weeklyLogs' => TransactionLog::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
            'monthlyLogs' => TransactionLog::whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count(),
            'topActions' => TransactionLog::select('action', DB::raw('count(*) as count'))
                ->groupBy('action')
                ->orderBy('count', 'desc')
                ->limit(5)
                ->get(),
            'actionBreakdown' => TransactionLog::select('action', DB::raw('count(*) as count'))
                ->groupBy('action')
                ->get()
                ->pluck('count', 'action'),
        ];

        // Get departments and users for filters
        $departments = Department::orderBy('name')->get();
        $users = User::orderBy('name')->get();
        $actions = TransactionLog::select('action')->distinct()->pluck('action');

        return view('admin.transaction-logs.index', compact('logs', 'stats', 'departments', 'users', 'actions'));
    }
}
