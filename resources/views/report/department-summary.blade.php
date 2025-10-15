@extends('layouts.app')

@section('title', 'Department Summary Report')

@section('content')
    <div class="p-4 sm:p-6">
        <x-page-header title="Department Summary Report" subtitle="Comprehensive department performance overview" />

        <!-- Back Button and Date Range -->
        <div class="flex flex-wrap justify-between items-center mb-6 gap-4">
            <a href="{{ route('head.report.index') }}" class="btn btn-ghost btn-sm">
                <i data-lucide="arrow-left" class="w-4 h-4 mr-1"></i>
                Back to Reports
            </a>
            
            <div class="flex flex-wrap gap-4 items-end">
                <form method="GET" action="{{ route('head.report.department-summary') }}" class="flex flex-wrap gap-4 items-end">
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
                
                <a href="{{ route('head.report.export', 'department-summary') }}?date_from={{ $dateFrom }}&date_to={{ $dateTo }}" 
                   class="btn btn-success btn-sm">
                    <i data-lucide="download" class="w-4 h-4 mr-1"></i>
                    Export CSV
                </a>
            </div>
        </div>

        <!-- Department Info -->
        <div class="card bg-base-100 shadow-xl mb-8">
            <div class="card-body">
                <h3 class="card-title">
                    <i data-lucide="building" class="w-5 h-5"></i>
                    {{ $summary['department']->name }}
                </h3>
                <p class="text-sm opacity-70">{{ $summary['department']->description ?? 'Department Summary' }}</p>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-6">
                    <!-- Key Metrics -->
                    <div class="space-y-4">
                        <h4 class="font-semibold text-lg">Key Metrics</h4>
                        <div class="stats stats-vertical shadow">
                            <div class="stat">
                                <div class="stat-title">Efficiency Rate</div>
                                <div class="stat-value text-2xl text-success">{{ $summary['efficiency_rate'] }}%</div>
                                <div class="stat-desc">Documents completed on time</div>
                            </div>
                            <div class="stat">
                                <div class="stat-title">Approval Rate</div>
                                <div class="stat-value text-2xl text-primary">{{ $summary['approval_rate'] }}%</div>
                                <div class="stat-desc">Documents approved vs total</div>
                            </div>
                        </div>
                    </div>

                    <!-- Performance Overview -->
                    <div class="space-y-4">
                        <h4 class="font-semibold text-lg">Performance Overview</h4>
                        <div class="space-y-3">
                            <div class="flex justify-between items-center">
                                <span class="text-sm">Total Documents</span>
                                <div class="badge badge-primary">{{ $summary['stats']['total_documents'] }}</div>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-sm">Average Processing Time</span>
                                <div class="badge badge-neutral">{{ round($summary['stats']['average_processing_time']) }} min</div>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-sm">On Time Completion</span>
                                <div class="badge badge-success">{{ $summary['stats']['completed_on_time'] }}</div>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-sm">Overdue Documents</span>
                                <div class="badge badge-error">{{ $summary['stats']['overdue_documents'] }}</div>
                            </div>
                        </div>
                    </div>

                    <!-- Status Distribution -->
                    <div class="space-y-4">
                        <h4 class="font-semibold text-lg">Status Distribution</h4>
                        <div class="space-y-3">
                            <div>
                                <div class="flex justify-between text-sm mb-1">
                                    <span>Approved</span>
                                    <span>{{ $summary['stats']['approved_documents'] }}</span>
                                </div>
                                <div class="w-full bg-base-300 rounded-full h-2">
                                    <div class="bg-success h-2 rounded-full" 
                                         style="width: {{ $summary['stats']['total_documents'] > 0 ? ($summary['stats']['approved_documents'] / $summary['stats']['total_documents']) * 100 : 0 }}%"></div>
                                </div>
                            </div>
                            <div>
                                <div class="flex justify-between text-sm mb-1">
                                    <span>Pending</span>
                                    <span>{{ $summary['stats']['pending_documents'] }}</span>
                                </div>
                                <div class="w-full bg-base-300 rounded-full h-2">
                                    <div class="bg-warning h-2 rounded-full" 
                                         style="width: {{ $summary['stats']['total_documents'] > 0 ? ($summary['stats']['pending_documents'] / $summary['stats']['total_documents']) * 100 : 0 }}%"></div>
                                </div>
                            </div>
                            <div>
                                <div class="flex justify-between text-sm mb-1">
                                    <span>Rejected</span>
                                    <span>{{ $summary['stats']['rejected_documents'] }}</span>
                                </div>
                                <div class="w-full bg-base-300 rounded-full h-2">
                                    <div class="bg-error h-2 rounded-full" 
                                         style="width: {{ $summary['stats']['total_documents'] > 0 ? ($summary['stats']['rejected_documents'] / $summary['stats']['total_documents']) * 100 : 0 }}%"></div>
                                </div>
                            </div>
                            <div>
                                <div class="flex justify-between text-sm mb-1">
                                    <span>Canceled</span>
                                    <span>{{ $summary['stats']['canceled_documents'] }}</span>
                                </div>
                                <div class="w-full bg-base-300 rounded-full h-2">
                                    <div class="bg-neutral h-2 rounded-full" 
                                         style="width: {{ $summary['stats']['total_documents'] > 0 ? ($summary['stats']['canceled_documents'] / $summary['stats']['total_documents']) * 100 : 0 }}%"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Monthly Trends -->
        @if($summary['monthly_trends']->count() > 0)
            <div class="card bg-base-100 shadow-xl mb-8">
                <div class="card-body">
                    <h3 class="card-title">
                        <i data-lucide="trending-up" class="w-5 h-5"></i>
                        Monthly Trends
                    </h3>
                    
                    <div class="overflow-x-auto">
                        <table class="table table-zebra">
                            <thead>
                                <tr>
                                    <th>Month</th>
                                    <th class="text-center">Total Documents</th>
                                    <th class="text-center">Approved</th>
                                    <th class="text-center">Approval Rate</th>
                                    <th class="text-center">Avg Time (min)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($summary['monthly_trends'] as $trend)
                                    <tr>
                                        <td class="font-medium">
                                            {{ \Carbon\Carbon::createFromFormat('Y-m', $trend->month)->format('F Y') }}
                                        </td>
                                        <td class="text-center">
                                            <div class="badge badge-primary">{{ $trend->total }}</div>
                                        </td>
                                        <td class="text-center">
                                            <div class="badge badge-success">{{ $trend->approved }}</div>
                                        </td>
                                        <td class="text-center">
                                            @php
                                                $approvalRate = $trend->total > 0 ? round(($trend->approved / $trend->total) * 100, 1) : 0;
                                            @endphp
                                            <div class="badge {{ $approvalRate >= 80 ? 'badge-success' : ($approvalRate >= 60 ? 'badge-warning' : 'badge-error') }}">
                                                {{ $approvalRate }}%
                                            </div>
                                        </td>
                                        <td class="text-center font-mono">
                                            {{ $trend->avg_time ? round($trend->avg_time, 1) : '-' }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif

        <!-- Performance Indicators -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Efficiency Analysis -->
            <div class="card bg-base-100 shadow-xl">
                <div class="card-body">
                    <h3 class="card-title">
                        <i data-lucide="gauge" class="w-5 h-5"></i>
                        Efficiency Analysis
                    </h3>
                    
                    <div class="space-y-4">
                        <!-- On-time Performance -->
                        <div>
                            <div class="flex justify-between items-center mb-2">
                                <span class="text-sm font-medium">On-time Performance</span>
                                <span class="text-lg font-bold text-success">{{ $summary['efficiency_rate'] }}%</span>
                            </div>
                            <div class="w-full bg-base-300 rounded-full h-3">
                                <div class="bg-gradient-to-r from-success to-success h-3 rounded-full" 
                                     style="width: {{ $summary['efficiency_rate'] }}%"></div>
                            </div>
                            <div class="text-xs opacity-70 mt-1">
                                {{ $summary['stats']['completed_on_time'] }} out of {{ $summary['stats']['total_documents'] }} documents
                            </div>
                        </div>

                        <!-- Quality Rating -->
                        <div>
                            <div class="flex justify-between items-center mb-2">
                                <span class="text-sm font-medium">Quality Rating (Approval Rate)</span>
                                <span class="text-lg font-bold text-primary">{{ $summary['approval_rate'] }}%</span>
                            </div>
                            <div class="w-full bg-base-300 rounded-full h-3">
                                <div class="bg-gradient-to-r from-primary to-primary h-3 rounded-full" 
                                     style="width: {{ $summary['approval_rate'] }}%"></div>
                            </div>
                            <div class="text-xs opacity-70 mt-1">
                                {{ $summary['stats']['approved_documents'] }} approved out of {{ $summary['stats']['total_documents'] }} total
                            </div>
                        </div>

                        <!-- Overall Score -->
                        @php
                            $overallScore = ($summary['efficiency_rate'] + $summary['approval_rate']) / 2;
                        @endphp
                        <div>
                            <div class="flex justify-between items-center mb-2">
                                <span class="text-sm font-medium">Overall Score</span>
                                <span class="text-lg font-bold {{ $overallScore >= 80 ? 'text-success' : ($overallScore >= 60 ? 'text-warning' : 'text-error') }}">
                                    {{ round($overallScore, 1) }}%
                                </span>
                            </div>
                            <div class="w-full bg-base-300 rounded-full h-3">
                                <div class="h-3 rounded-full {{ $overallScore >= 80 ? 'bg-gradient-to-r from-success to-success' : ($overallScore >= 60 ? 'bg-gradient-to-r from-warning to-warning' : 'bg-gradient-to-r from-error to-error') }}" 
                                     style="width: {{ $overallScore }}%"></div>
                            </div>
                            <div class="text-xs opacity-70 mt-1">
                                Based on efficiency and quality metrics
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recommendations -->
            <div class="card bg-base-100 shadow-xl">
                <div class="card-body">
                    <h3 class="card-title">
                        <i data-lucide="lightbulb" class="w-5 h-5"></i>
                        Recommendations
                    </h3>
                    
                    <div class="space-y-3">
                        @if($summary['efficiency_rate'] < 80)
                            <div class="alert alert-warning">
                                <i data-lucide="clock" class="w-4 h-4"></i>
                                <span class="text-sm">Consider reviewing processing workflows to improve on-time completion rate.</span>
                            </div>
                        @endif

                        @if($summary['approval_rate'] < 80)
                            <div class="alert alert-error">
                                <i data-lucide="x-circle" class="w-4 h-4"></i>
                                <span class="text-sm">High rejection rate detected. Review document quality standards and staff training.</span>
                            </div>
                        @endif

                        @if($summary['stats']['average_processing_time'] > 1440) {{-- More than 1 day --}}
                            <div class="alert alert-info">
                                <i data-lucide="timer" class="w-4 h-4"></i>
                                <span class="text-sm">Average processing time is {{ round($summary['stats']['average_processing_time'] / 1440, 1) }} days. Consider process optimization.</span>
                            </div>
                        @endif

                        @if($summary['stats']['pending_documents'] > $summary['stats']['total_documents'] * 0.3)
                            <div class="alert alert-warning">
                                <i data-lucide="file-clock" class="w-4 h-4"></i>
                                <span class="text-sm">High number of pending documents. Consider increasing staff capacity.</span>
                            </div>
                        @endif

                        @if($summary['efficiency_rate'] >= 80 && $summary['approval_rate'] >= 80)
                            <div class="alert alert-success">
                                <i data-lucide="check-circle" class="w-4 h-4"></i>
                                <span class="text-sm">Excellent performance! Department is meeting efficiency and quality targets.</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection