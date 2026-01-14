<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Models\Report;
use App\Models\ReportTemplate;
use App\Models\ReportResult;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReportController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $reports = Report::where('created_by', $user->id)
            ->orWhere('is_public', true)
            ->with('template', 'creator', 'latestResult')
            ->paginate(15);

        return view('reports.index', compact('reports'));
    }

    public function create()
    {
        $templates = ReportTemplate::where('is_active', true)->get();
        return view('reports.create', compact('templates'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'report_type' => 'required|string|in:transaction,workflow,user,department',
            'template_id' => 'nullable|exists:report_templates,id',
            'filters' => 'nullable|array',
            'columns' => 'nullable|array',
            'sort_by' => 'nullable|string',
            'date_range_start' => 'nullable|date',
            'date_range_end' => 'nullable|date',
            'is_public' => 'boolean',
            'schedule_frequency' => 'nullable|string|in:daily,weekly,monthly',
        ]);

        $validated['created_by'] = Auth::id();
        $validated['is_public'] = $request->has('is_public');

        $report = Report::create($validated);

        return redirect()->route('reports.show', $report)
            ->with('success', 'Report created successfully.');
    }

    public function show(Report $report)
    {
        $this->authorize('view', $report);

        $latestResult = $report->latestResult;
        $results = $report->results()->latest('generated_at')->paginate(10);

        return view('reports.show', compact('report', 'latestResult', 'results'));
    }

    public function edit(Report $report)
    {
        $this->authorize('update', $report);

        $templates = ReportTemplate::where('is_active', true)->get();
        return view('reports.edit', compact('report', 'templates'));
    }

    public function update(Request $request, Report $report)
    {
        $this->authorize('update', $report);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'report_type' => 'required|string|in:transaction,workflow,user,department',
            'template_id' => 'nullable|exists:report_templates,id',
            'filters' => 'nullable|array',
            'columns' => 'nullable|array',
            'sort_by' => 'nullable|string',
            'date_range_start' => 'nullable|date',
            'date_range_end' => 'nullable|date',
            'is_public' => 'boolean',
            'schedule_frequency' => 'nullable|string|in:daily,weekly,monthly',
        ]);

        $validated['is_public'] = $request->has('is_public');

        $report->update($validated);

        return redirect()->route('reports.show', $report)
            ->with('success', 'Report updated successfully.');
    }

    public function destroy(Report $report)
    {
        $this->authorize('delete', $report);

        $report->delete();

        return redirect()->route('reports.index')
            ->with('success', 'Report deleted successfully.');
    }

    public function generate(Report $report)
    {
        $this->authorize('view', $report);

        try {
            $data = $this->generateReportData($report);

            $result = ReportResult::create([
                'report_id' => $report->id,
                'data' => $data['rows'],
                'summary' => $data['summary'],
                'total_records' => count($data['rows']),
                'generated_at' => now(),
            ]);

            $report->update(['last_generated_at' => now()]);

            return redirect()->route('reports.show', $report)
                ->with('success', 'Report generated successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error generating report: ' . $e->getMessage());
        }
    }

    public function export(Report $report)
    {
        $this->authorize('view', $report);

        $result = $report->latestResult;
        if (!$result) {
            return redirect()->back()->with('error', 'No report data to export.');
        }

        $filename = $report->name . '_' . now()->format('Y-m-d_H-i-s') . '.csv';
        
        return response()->stream(function () use ($result) {
            $file = fopen('php://output', 'w');
            
            if (!empty($result->data)) {
                fputcsv($file, array_keys((array) $result->data[0]));
                
                foreach ($result->data as $row) {
                    fputcsv($file, (array) $row);
                }
            }
            
            fclose($file);
        }, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ]);
    }

    private function generateReportData(Report $report)
    {
        $query = $this->getBaseQuery($report->report_type);

        // Apply filters
        if ($report->filters) {
            $query = $this->applyFilters($query, $report->filters, $report->report_type);
        }

        // Apply date range
        if ($report->date_range_start && $report->date_range_end) {
            $query->whereBetween('created_at', [
                $report->date_range_start,
                $report->date_range_end,
            ]);
        }

        // Apply sorting
        if ($report->sort_by) {
            [$column, $direction] = explode('|', $report->sort_by);
            $query->orderBy($column, $direction);
        }

        // Select columns
        $columns = $report->columns ?? $this->getDefaultColumns($report->report_type);
        $rows = $query->select($columns)->get()->toArray();

        // Calculate summary
        $summary = $this->calculateSummary($rows, $report->report_type);

        return [
            'rows' => $rows,
            'summary' => $summary,
        ];
    }

    private function getBaseQuery($reportType)
    {
        return match ($reportType) {
            'transaction' => Transaction::query(),
            'workflow' => \App\Models\Workflow::query(),
            'user' => \App\Models\User::query(),
            'department' => \App\Models\Department::query(),
            default => Transaction::query(),
        };
    }

    private function applyFilters($query, array $filters, string $reportType)
    {
        foreach ($filters as $key => $value) {
            if ($value === null || $value === '') {
                continue;
            }

            match ($reportType) {
                'transaction' => $this->applyTransactionFilter($query, $key, $value),
                'workflow' => $this->applyWorkflowFilter($query, $key, $value),
                'user' => $this->applyUserFilter($query, $key, $value),
                'department' => $this->applyDepartmentFilter($query, $key, $value),
            };
        }

        return $query;
    }

    private function applyTransactionFilter($query, $key, $value)
    {
        return match ($key) {
            'status' => $query->where('transaction_status', $value),
            'department_id' => $query->where('department_id', $value),
            'origin_department_id' => $query->where('origin_department_id', $value),
            'created_by' => $query->where('created_by', $value),
            'workflow_id' => $query->where('workflow_id', $value),
            default => $query,
        };
    }

    private function applyWorkflowFilter($query, $key, $value)
    {
        return match ($key) {
            'department_id' => $query->where('department_id', $value),
            'is_active' => $query->where('is_active', $value),
            default => $query,
        };
    }

    private function applyUserFilter($query, $key, $value)
    {
        return match ($key) {
            'type' => $query->where('type', $value),
            'department_id' => $query->where('department_id', $value),
            default => $query,
        };
    }

    private function applyDepartmentFilter($query, $key, $value)
    {
        return match ($key) {
            'is_active' => $query->where('is_active', $value),
            default => $query,
        };
    }

    private function getDefaultColumns($reportType): array
    {
        return match ($reportType) {
            'transaction' => [
                'id',
                'transaction_code',
                'transaction_status',
                'current_workflow_step',
                'created_at',
                'completed_at',
            ],
            'workflow' => [
                'id',
                'name',
                'department_id',
                'is_active',
                'created_at',
            ],
            'user' => [
                'id',
                'name',
                'email',
                'type',
                'department_id',
                'created_at',
            ],
            'department' => [
                'id',
                'name',
                'is_active',
                'created_at',
            ],
            default => [],
        };
    }

    private function calculateSummary(array $rows, string $reportType): array
    {
        $summary = [
            'total_records' => count($rows),
        ];

        if ($reportType === 'transaction' && !empty($rows)) {
            $statusCounts = array_count_values(array_column($rows, 'transaction_status'));
            $summary['by_status'] = $statusCounts;
        }

        return $summary;
    }
}
