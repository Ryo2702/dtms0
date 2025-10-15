<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use App\Models\DocumentReview;
use App\Models\User;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportController extends Controller
{
    /**
     * Display the main reports dashboard
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        // Only Heads can access reports
        if ($user->type !== 'Head') {
            abort(403, 'Unauthorized access to reports.');
        }

        $dateFrom = $request->get('date_from', now()->startOfMonth()->format('Y-m-d'));
        $dateTo = $request->get('date_to', now()->format('Y-m-d'));

        // Get department summary data
        $departmentStats = $this->getDepartmentStats($user->department_id, $dateFrom, $dateTo);
        
        // Get document performance data
        $documentPerformance = $this->getDocumentPerformanceStats($user->department_id, $dateFrom, $dateTo);
        
        // Get staff productivity data
        $staffProductivity = $this->getStaffProductivityStats($user->department_id, $dateFrom, $dateTo);

        return view('report.index', compact(
            'departmentStats',
            'documentPerformance', 
            'staffProductivity',
            'dateFrom',
            'dateTo'
        ));
    }

    /**
     * Document performance detailed report
     */
    public function documentPerformance(Request $request)
    {
        $user = Auth::user();
        $dateFrom = $request->get('date_from', now()->startOfMonth()->format('Y-m-d'));
        $dateTo = $request->get('date_to', now()->format('Y-m-d'));

        $performance = $this->getDetailedDocumentPerformance($user->department_id, $dateFrom, $dateTo);

        return view('report.document-performance', compact('performance', 'dateFrom', 'dateTo'));
    }

    /**
     * Department summary report
     */
    public function departmentSummary(Request $request)
    {
        $user = Auth::user();
        $dateFrom = $request->get('date_from', now()->startOfMonth()->format('Y-m-d'));
        $dateTo = $request->get('date_to', now()->format('Y-m-d'));

        $summary = $this->getDetailedDepartmentSummary($user->department_id, $dateFrom, $dateTo);

        return view('report.department-summary', compact('summary', 'dateFrom', 'dateTo'));
    }

    /**
     * Staff productivity report
     */
    public function staffProductivity(Request $request)
    {
        $user = Auth::user();
        $dateFrom = $request->get('date_from', now()->startOfMonth()->format('Y-m-d'));
        $dateTo = $request->get('date_to', now()->format('Y-m-d'));

        $productivity = $this->getDetailedStaffProductivity($user->department_id, $dateFrom, $dateTo);

        return view('report.staff-productivity', compact('productivity', 'dateFrom', 'dateTo'));
    }

    /**
     * Export reports
     */
    public function export(Request $request, $type)
    {
        $user = Auth::user();
        $dateFrom = $request->get('date_from', now()->startOfMonth()->format('Y-m-d'));
        $dateTo = $request->get('date_to', now()->format('Y-m-d'));

        switch ($type) {
            case 'department-summary':
                return $this->exportDepartmentSummary($user->department_id, $dateFrom, $dateTo);
            case 'document-performance':
                return $this->exportDocumentPerformance($user->department_id, $dateFrom, $dateTo);
            case 'staff-productivity':
                return $this->exportStaffProductivity($user->department_id, $dateFrom, $dateTo);
            default:
                abort(404, 'Export type not found');
        }
    }

    // Helper methods for data retrieval

    private function getDepartmentStats($departmentId, $dateFrom, $dateTo)
    {
        $query = DocumentReview::where(function ($q) use ($departmentId) {
            $q->where('current_department_id', $departmentId)
              ->orWhere('original_department_id', $departmentId);
        })->whereBetween('created_at', [$dateFrom, $dateTo]);

        return [
            'total_documents' => $query->count(),
            'pending_documents' => $query->where('status', 'pending')->count(),
            'approved_documents' => $query->where('status', 'approved')->count(),
            'rejected_documents' => $query->where('status', 'rejected')->count(),
            'canceled_documents' => $query->where('status', 'canceled')->count(),
            'completed_on_time' => $query->where('completed_on_time', true)->count(),
            'overdue_documents' => $query->where('completed_on_time', false)->whereNotNull('downloaded_at')->count(),
            'average_processing_time' => $query->whereNotNull('process_time_minutes')->avg('process_time_minutes') ?? 0,
        ];
    }

    private function getDocumentPerformanceStats($departmentId, $dateFrom, $dateTo)
    {
        return DocumentReview::select('document_type', 
            DB::raw('COUNT(*) as total'),
            DB::raw('AVG(process_time_minutes) as avg_time'),
            DB::raw('COUNT(CASE WHEN completed_on_time = 1 THEN 1 END) as on_time'),
            DB::raw('COUNT(CASE WHEN status = "approved" THEN 1 END) as approved'),
            DB::raw('COUNT(CASE WHEN status = "rejected" THEN 1 END) as rejected')
        )
        ->where(function ($q) use ($departmentId) {
            $q->where('current_department_id', $departmentId)
              ->orWhere('original_department_id', $departmentId);
        })
        ->whereBetween('created_at', [$dateFrom, $dateTo])
        ->groupBy('document_type')
        ->orderByDesc('total')
        ->get();
    }

    private function getStaffProductivityStats($departmentId, $dateFrom, $dateTo)
    {
        return User::select('users.id', 'users.name',
            DB::raw('COUNT(document_reviews.id) as documents_handled'),
            DB::raw('AVG(document_reviews.process_time_minutes) as avg_processing_time'),
            DB::raw('COUNT(CASE WHEN document_reviews.completed_on_time = 1 THEN 1 END) as on_time_completion'),
            DB::raw('COUNT(CASE WHEN document_reviews.status = "approved" THEN 1 END) as approved_documents')
        )
        ->leftJoin('document_reviews', function ($join) use ($dateFrom, $dateTo) {
            $join->on('users.id', '=', 'document_reviews.assigned_to')
                 ->whereBetween('document_reviews.created_at', [$dateFrom, $dateTo]);
        })
        ->where('users.department_id', $departmentId)
        ->whereIn('users.type', ['Head', 'Staff'])
        ->where('users.status', 1)
        ->groupBy('users.id', 'users.name')
        ->orderByDesc('documents_handled')
        ->get();
    }

    private function getDetailedDocumentPerformance($departmentId, $dateFrom, $dateTo)
    {
        $documents = DocumentReview::with(['creator', 'reviewer', 'currentDepartment'])
            ->where(function ($q) use ($departmentId) {
                $q->where('current_department_id', $departmentId)
                  ->orWhere('original_department_id', $departmentId);
            })
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $performanceByType = $this->getDocumentPerformanceStats($departmentId, $dateFrom, $dateTo);

        return [
            'documents' => $documents,
            'performance_by_type' => $performanceByType,
            'summary' => $this->getDepartmentStats($departmentId, $dateFrom, $dateTo)
        ];
    }

    private function getDetailedDepartmentSummary($departmentId, $dateFrom, $dateTo)
    {
        $department = Department::findOrFail($departmentId);
        $stats = $this->getDepartmentStats($departmentId, $dateFrom, $dateTo);
        
        // Monthly trends
        $monthlyTrends = DocumentReview::select(
            DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'),
            DB::raw('COUNT(*) as total'),
            DB::raw('COUNT(CASE WHEN status = "approved" THEN 1 END) as approved'),
            DB::raw('AVG(process_time_minutes) as avg_time')
        )
        ->where(function ($q) use ($departmentId) {
            $q->where('current_department_id', $departmentId)
              ->orWhere('original_department_id', $departmentId);
        })
        ->whereBetween('created_at', [
            Carbon::parse($dateFrom)->startOfMonth(),
            Carbon::parse($dateTo)->endOfMonth()
        ])
        ->groupBy('month')
        ->orderBy('month')
        ->get();

        return [
            'department' => $department,
            'stats' => $stats,
            'monthly_trends' => $monthlyTrends,
            'efficiency_rate' => $stats['total_documents'] > 0 ? 
                round(($stats['completed_on_time'] / $stats['total_documents']) * 100, 2) : 0,
            'approval_rate' => $stats['total_documents'] > 0 ? 
                round(($stats['approved_documents'] / $stats['total_documents']) * 100, 2) : 0
        ];
    }

    private function getDetailedStaffProductivity($departmentId, $dateFrom, $dateTo)
    {
        $staff = $this->getStaffProductivityStats($departmentId, $dateFrom, $dateTo);
        
        // Get department staff list
        $departmentStaff = User::where('department_id', $departmentId)
            ->whereIn('type', ['Head', 'Staff'])
            ->where('status', 1)
            ->get();

        return [
            'staff_productivity' => $staff,
            'department_staff' => $departmentStaff,
            'total_staff' => $departmentStaff->count(),
            'active_staff' => $staff->where('documents_handled', '>', 0)->count()
        ];
    }

    private function exportDepartmentSummary($departmentId, $dateFrom, $dateTo)
    {
        $summary = $this->getDetailedDepartmentSummary($departmentId, $dateFrom, $dateTo);
        
        $filename = "department_summary_{$dateFrom}_to_{$dateTo}.csv";
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function() use ($summary) {
            $file = fopen('php://output', 'w');
            
            // Headers
            fputcsv($file, ['Department Summary Report']);
            fputcsv($file, ['Period', request()->get('date_from') . ' to ' . request()->get('date_to')]);
            fputcsv($file, []);
            
            // Stats
            fputcsv($file, ['Metric', 'Value']);
            fputcsv($file, ['Total Documents', $summary['stats']['total_documents']]);
            fputcsv($file, ['Pending Documents', $summary['stats']['pending_documents']]);
            fputcsv($file, ['Approved Documents', $summary['stats']['approved_documents']]);
            fputcsv($file, ['Rejected Documents', $summary['stats']['rejected_documents']]);
            fputcsv($file, ['Canceled Documents', $summary['stats']['canceled_documents']]);
            fputcsv($file, ['Completed on Time', $summary['stats']['completed_on_time']]);
            fputcsv($file, ['Overdue Documents', $summary['stats']['overdue_documents']]);
            fputcsv($file, ['Average Processing Time (minutes)', round($summary['stats']['average_processing_time'], 2)]);
            fputcsv($file, ['Efficiency Rate (%)', $summary['efficiency_rate']]);
            fputcsv($file, ['Approval Rate (%)', $summary['approval_rate']]);
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    private function exportDocumentPerformance($departmentId, $dateFrom, $dateTo)
    {
        $performance = $this->getDocumentPerformanceStats($departmentId, $dateFrom, $dateTo);
        
        $filename = "document_performance_{$dateFrom}_to_{$dateTo}.csv";
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function() use ($performance) {
            $file = fopen('php://output', 'w');
            
            // Headers
            fputcsv($file, ['Document Performance Report']);
            fputcsv($file, ['Period', request()->get('date_from') . ' to ' . request()->get('date_to')]);
            fputcsv($file, []);
            
            fputcsv($file, ['Document Type', 'Total', 'Average Time (minutes)', 'On Time', 'Approved', 'Rejected']);
            
            foreach ($performance as $perf) {
                fputcsv($file, [
                    $perf->document_type,
                    $perf->total,
                    round($perf->avg_time, 2),
                    $perf->on_time,
                    $perf->approved,
                    $perf->rejected
                ]);
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    private function exportStaffProductivity($departmentId, $dateFrom, $dateTo)
    {
        $productivity = $this->getStaffProductivityStats($departmentId, $dateFrom, $dateTo);
        
        $filename = "staff_productivity_{$dateFrom}_to_{$dateTo}.csv";
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function() use ($productivity) {
            $file = fopen('php://output', 'w');
            
            // Headers
            fputcsv($file, ['Staff Productivity Report']);
            fputcsv($file, ['Period', request()->get('date_from') . ' to ' . request()->get('date_to')]);
            fputcsv($file, []);
            
            fputcsv($file, ['Staff Name', 'Documents Handled', 'Average Processing Time (minutes)', 'On Time Completion', 'Approved Documents']);
            
            foreach ($productivity as $staff) {
                fputcsv($file, [
                    $staff->name,
                    $staff->documents_handled,
                    round($staff->avg_processing_time, 2),
                    $staff->on_time_completion,
                    $staff->approved_documents
                ]);
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
