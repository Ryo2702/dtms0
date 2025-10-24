<?php

namespace App\Http\Controllers\Admin\AuditLog;

use App\Exports\AuditLogsExport;
use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class AuditLogController extends Controller
{
    /**
     * Display audit logs
     */
    public function index(Request $request)
    {
        $query = AuditLog::with('user')->latest();

        // Apply filters
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        if ($request->filled('search')) {
            $query->where('description', 'like', '%'.$request->search.'%');
        }

        $logs = $query->paginate(20)->withQueryString();

        $users = User::orderBy('name')->get(['id', 'name']);

        $stats = $this->getStatistics();

        return view('admin.audit-logs.index', compact('logs', 'users', 'stats'));
    }

    /**
     * Get audit log statistics
     */
    private function getStatistics(): array
    {
        $totalLogs = AuditLog::count();
        $todayLogs = AuditLog::whereDate('created_at', today())->count();
        $weeklyLogs = AuditLog::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count();

        // Get top actions
        $topActions = AuditLog::select('action', DB::raw('count(*) as count'))
            ->groupBy('action')
            ->orderByDesc('count')
            ->get();

        return [
            'totalLogs' => $totalLogs,
            'todayLogs' => $todayLogs,
            'weeklyLogs' => $weeklyLogs,
            'topActions' => $topActions,
        ];
    }

    /**
     * Show a specific audit log entry
     */
    public function show(AuditLog $auditLog)
    {
        $auditLog->load('user');

        return view('admin.audit-logs.show', compact('auditLog'));
    }

    /**
     * Export audit logs (optional feature)
     */
    public function export(Request $request)
    {
        $format = $request->get('format', 'csv');

        if ($format === 'excel') {
            return $this->exportExcel($request);
        }

        return $this->exportCsv($request);
    }




    //Export as Excel
    private function exportExcel(Request $request)  {
        $filename = 'audit_logs_'.now()->format('Y_m_d_H_i_s').'.xlsx';

        return Excel::download(new AuditLogsExport($request), $filename);
    }

   private function exportCsv(Request $request)
    {
        $query = AuditLog::with('user')->latest();

        // Apply same filters as index
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        if ($request->filled('search')) {
            $query->where('description', 'like', '%'.$request->search.'%');
        }

        $logs = $query->get();

        $filename = 'audit_logs_'.now()->format('Y_m_d_H_i_s').'.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ];

        $callback = function () use ($logs) {
            $file = fopen('php://output', 'w');

            // Add CSV headers
            fputcsv($file, [
                'ID',
                'User',
                'Action',
                'Description',
                'Model Type',
                'Model ID',
                'IP Address',
                'User Agent',
                'URL',
                'Method',
                'Date & Time',
            ]);

            // Add data rows
            foreach ($logs as $log) {
                fputcsv($file, [
                    $log->id,
                    $log->user ? $log->user->name : 'System',
                    $log->action,
                    $log->description,
                    $log->model_type,
                    $log->model_id,
                    $log->ip_address,
                    $log->user_agent,
                    $log->url,
                    $log->method,
                    $log->created_at->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
