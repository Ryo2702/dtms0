@extends('layouts.app')

@section('title', 'Department Reports')

@section('content')
    <div class="p-4 sm:p-6">
        <x-page-header title="Department Reports" subtitle="Monitor department performance and analytics" />

        <!-- Date Range Filter -->
        <div class="mb-6">
            <form method="GET" action="{{ route('head.report.index') }}" class="flex flex-wrap gap-4 items-end">
                <div class="form-control">
                    <label class="label">
                        <span class="label-text">From Date</span>
                    </label>
                    <input type="date" 
                           name="date_from" 
                           value="{{ $dateFrom }}" 
                           class="input input-bordered input-sm w-full max-w-xs">
                </div>
                <div class="form-control">
                    <label class="label">
                        <span class="label-text">To Date</span>
                    </label>
                    <input type="date" 
                           name="date_to" 
                           value="{{ $dateTo }}" 
                           class="input input-bordered input-sm w-full max-w-xs">
                </div>
                <button type="submit" class="btn btn-primary btn-sm">
                    <i data-lucide="filter" class="w-4 h-4 mr-1"></i>
                    Filter
                </button>
            </form>
        </div>

        <!-- Statistics Overview -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
            <x-stat-card 
                bgColor="bg-stat-primary" 
                title="Total Documents" 
                :value="$departmentStats['total_documents']" 
                icon="file-text" />

            <x-stat-card 
                bgColor="bg-stat-accent" 
                title="Pending" 
                :value="$departmentStats['pending_documents']" 
                icon="clock" />

            <x-stat-card 
                bgColor="bg-stat-secondary" 
                title="Approved" 
                :value="$departmentStats['approved_documents']" 
                icon="check-circle" />

            <x-stat-card 
                bgColor="bg-stat-danger" 
                title="Overdue" 
                :value="$departmentStats['overdue_documents']" 
                icon="alert-circle" />
        </div>

        <!-- Quick Performance Metrics -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
            <!-- Processing Time -->
            <div class="card bg-base-100 shadow-xl">
                <div class="card-body">
                    <h3 class="card-title text-lg">
                        <i data-lucide="timer" class="w-5 h-5"></i>
                        Processing Performance
                    </h3>
                    <div class="stats shadow">
                        <div class="stat">
                            <div class="stat-title">Average Processing Time</div>
                            <div class="stat-value text-2xl">
                                {{ round($departmentStats['average_processing_time']) }} min
                            </div>
                        </div>
                    </div>
                    <div class="mt-4">
                        <div class="text-sm opacity-70">On-time completion rate</div>
                        <div class="text-2xl font-bold text-success">
                            {{ $departmentStats['total_documents'] > 0 ? round(($departmentStats['completed_on_time'] / $departmentStats['total_documents']) * 100, 1) : 0 }}%
                        </div>
                    </div>
                </div>
            </div>

            <!-- Document Status Breakdown -->
            <div class="card bg-base-100 shadow-xl">
                <div class="card-body">
                    <h3 class="card-title text-lg">
                        <i data-lucide="pie-chart" class="w-5 h-5"></i>
                        Status Distribution
                    </h3>
                    <div class="space-y-3">
                        <div class="flex justify-between items-center">
                            <span class="text-sm">Approved</span>
                            <div class="flex items-center gap-2">
                                <div class="w-20 bg-base-300 rounded-full h-2">
                                    <div class="bg-success h-2 rounded-full" 
                                         style="width: {{ $departmentStats['total_documents'] > 0 ? ($departmentStats['approved_documents'] / $departmentStats['total_documents']) * 100 : 0 }}%"></div>
                                </div>
                                <span class="text-sm font-mono">{{ $departmentStats['approved_documents'] }}</span>
                            </div>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm">Rejected</span>
                            <div class="flex items-center gap-2">
                                <div class="w-20 bg-base-300 rounded-full h-2">
                                    <div class="bg-error h-2 rounded-full" 
                                         style="width: {{ $departmentStats['total_documents'] > 0 ? ($departmentStats['rejected_documents'] / $departmentStats['total_documents']) * 100 : 0 }}%"></div>
                                </div>
                                <span class="text-sm font-mono">{{ $departmentStats['rejected_documents'] }}</span>
                            </div>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm">Pending</span>
                            <div class="flex items-center gap-2">
                                <div class="w-20 bg-base-300 rounded-full h-2">
                                    <div class="bg-warning h-2 rounded-full" 
                                         style="width: {{ $departmentStats['total_documents'] > 0 ? ($departmentStats['pending_documents'] / $departmentStats['total_documents']) * 100 : 0 }}%"></div>
                                </div>
                                <span class="text-sm font-mono">{{ $departmentStats['pending_documents'] }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card bg-base-100 shadow-xl">
                <div class="card-body">
                    <h3 class="card-title text-lg">
                        <i data-lucide="download" class="w-5 h-5"></i>
                        Quick Reports
                    </h3>
                    <div class="space-y-2">
                        <a href="{{ route('head.report.export', 'department-summary') }}?date_from={{ $dateFrom }}&date_to={{ $dateTo }}" 
                           class="btn btn-outline btn-sm w-full">
                            <i data-lucide="file-text" class="w-4 h-4 mr-2"></i>
                            Export Department Summary
                        </a>
                        <a href="{{ route('head.report.export', 'document-performance') }}?date_from={{ $dateFrom }}&date_to={{ $dateTo }}" 
                           class="btn btn-outline btn-sm w-full">
                            <i data-lucide="bar-chart" class="w-4 h-4 mr-2"></i>
                            Export Document Performance
                        </a>
                        <a href="{{ route('head.report.export', 'staff-productivity') }}?date_from={{ $dateFrom }}&date_to={{ $dateTo }}" 
                           class="btn btn-outline btn-sm w-full">
                            <i data-lucide="users" class="w-4 h-4 mr-2"></i>
                            Export Staff Productivity
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Detailed Reports Navigation -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Document Performance -->
            <div class="card bg-base-100 shadow-xl hover:shadow-2xl transition-shadow">
                <div class="card-body">
                    <h3 class="card-title">
                        <i data-lucide="trending-up" class="w-5 h-5"></i>
                        Document Performance
                    </h3>
                    <p class="text-sm opacity-70">Analyze document processing efficiency by type and timeline</p>
                    
                    <!-- Preview Stats -->
                    <div class="mt-4 space-y-2">
                        @if($documentPerformance->count() > 0)
                            @foreach($documentPerformance->take(3) as $doc)
                                <div class="flex justify-between text-sm">
                                    <span class="truncate">{{ $doc->document_type }}</span>
                                    <span class="font-mono">{{ $doc->total }}</span>
                                </div>
                            @endforeach
                            @if($documentPerformance->count() > 3)
                                <div class="text-xs opacity-50">+{{ $documentPerformance->count() - 3 }} more types</div>
                            @endif
                        @else
                            <div class="text-sm opacity-50">No documents in selected period</div>
                        @endif
                    </div>
                    
                    <div class="card-actions justify-end mt-4">
                        <a href="{{ route('head.report.document-performance') }}?date_from={{ $dateFrom }}&date_to={{ $dateTo }}" 
                           class="btn btn-primary btn-sm">
                            View Details
                        </a>
                    </div>
                </div>
            </div>

            <!-- Department Summary -->
            <div class="card bg-base-100 shadow-xl hover:shadow-2xl transition-shadow">
                <div class="card-body">
                    <h3 class="card-title">
                        <i data-lucide="building" class="w-5 h-5"></i>
                        Department Summary
                    </h3>
                    <p class="text-sm opacity-70">Comprehensive department performance overview and trends</p>
                    
                    <!-- Preview Stats -->
                    <div class="mt-4 grid grid-cols-2 gap-2 text-sm">
                        <div>
                            <div class="font-mono text-lg">{{ $departmentStats['total_documents'] }}</div>
                            <div class="opacity-70">Total Documents</div>
                        </div>
                        <div>
                            <div class="font-mono text-lg text-success">
                                {{ $departmentStats['total_documents'] > 0 ? round(($departmentStats['approved_documents'] / $departmentStats['total_documents']) * 100, 1) : 0 }}%
                            </div>
                            <div class="opacity-70">Approval Rate</div>
                        </div>
                    </div>
                    
                    <div class="card-actions justify-end mt-4">
                        <a href="{{ route('head.report.department-summary') }}?date_from={{ $dateFrom }}&date_to={{ $dateTo }}" 
                           class="btn btn-primary btn-sm">
                            View Details
                        </a>
                    </div>
                </div>
            </div>

            <!-- Staff Productivity -->
            <div class="card bg-base-100 shadow-xl hover:shadow-2xl transition-shadow">
                <div class="card-body">
                    <h3 class="card-title">
                        <i data-lucide="users" class="w-5 h-5"></i>
                        Staff Productivity
                    </h3>
                    <p class="text-sm opacity-70">Monitor individual staff performance and workload distribution</p>
                    
                    <!-- Preview Stats -->
                    <div class="mt-4">
                        <div class="text-sm opacity-70">Most Active Staff</div>
                        @if($staffProductivity->count() > 0)
                            @foreach($staffProductivity->take(2) as $staff)
                                <div class="flex justify-between text-sm mt-1">
                                    <span class="truncate">{{ $staff->name }}</span>
                                    <span class="font-mono">{{ $staff->documents_handled }} docs</span>
                                </div>
                            @endforeach
                        @else
                            <div class="text-sm opacity-50 mt-1">No activity in selected period</div>
                        @endif
                    </div>
                    
                    <div class="card-actions justify-end mt-4">
                        <a href="{{ route('head.report.staff-productivity') }}?date_from={{ $dateFrom }}&date_to={{ $dateTo }}" 
                           class="btn btn-primary btn-sm">
                            View Details
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
