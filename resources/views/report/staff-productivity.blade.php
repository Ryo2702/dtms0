@extends('layouts.app')

@section('title', 'Staff Productivity Report')

@section('content')
    <div class="p-4 sm:p-6">
        <x-page-header title="Staff Productivity Report" subtitle="Monitor individual staff performance and workload distribution" />

        <!-- Back Button and Date Range -->
        <div class="flex flex-wrap justify-between items-center mb-6 gap-4">
            <a href="{{ route('head.report.index') }}" class="btn btn-ghost btn-sm">
                <i data-lucide="arrow-left" class="w-4 h-4 mr-1"></i>
                Back to Reports
            </a>
            
            <div class="flex flex-wrap gap-4 items-end">
                <form method="GET" action="{{ route('head.report.staff-productivity') }}" class="flex flex-wrap gap-4 items-end">
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
                
                <a href="{{ route('head.report.export', 'staff-productivity') }}?date_from={{ $dateFrom }}&date_to={{ $dateTo }}" 
                   class="btn btn-success btn-sm">
                    <i data-lucide="download" class="w-4 h-4 mr-1"></i>
                    Export CSV
                </a>
            </div>
        </div>

        <!-- Overview Stats -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
            <x-stat-card 
                bgColor="bg-stat-primary" 
                title="Total Staff" 
                :value="$productivity['total_staff']" 
                icon="users" />

            <x-stat-card 
                bgColor="bg-stat-secondary" 
                title="Active Staff" 
                :value="$productivity['active_staff']" 
                icon="user-check" />

            <x-stat-card 
                bgColor="bg-stat-accent" 
                title="Total Documents Handled" 
                :value="$productivity['staff_productivity']->sum('documents_handled')" 
                icon="file-text" />

            <x-stat-card 
                bgColor="bg-stat-info" 
                title="Avg Documents/Staff" 
                :value="$productivity['active_staff'] > 0 ? round($productivity['staff_productivity']->sum('documents_handled') / $productivity['active_staff'], 1) : 0" 
                icon="trending-up" />
        </div>

        <!-- Staff Performance Table -->
        <div class="card bg-base-100 shadow-xl mb-8">
            <div class="card-body">
                <h3 class="card-title">
                    <i data-lucide="user-check" class="w-5 h-5"></i>
                    Staff Performance Analysis
                </h3>
                
                @if($productivity['staff_productivity']->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="table table-zebra">
                            <thead>
                                <tr>
                                    <th>Staff Name</th>
                                    <th class="text-center">Documents Handled</th>
                                    <th class="text-center">Avg Processing Time</th>
                                    <th class="text-center">On Time Completion</th>
                                    <th class="text-center">Approved Documents</th>
                                    <th class="text-center">Success Rate</th>
                                    <th class="text-center">Performance</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($productivity['staff_productivity'] as $staff)
                                    <tr>
                                        <td class="font-medium">
                                            <div class="flex items-center gap-2">
                                                <div class="avatar placeholder">
                                                    <div class="bg-neutral text-neutral-content rounded-full w-8">
                                                        <span class="text-xs">{{ substr($staff->name, 0, 2) }}</span>
                                                    </div>
                                                </div>
                                                {{ $staff->name }}
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <div class="badge badge-primary">{{ $staff->documents_handled }}</div>
                                        </td>
                                        <td class="text-center font-mono">
                                            @if($staff->avg_processing_time)
                                                {{ round($staff->avg_processing_time / 60, 1) }}h
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <div class="badge badge-success">{{ $staff->on_time_completion }}</div>
                                        </td>
                                        <td class="text-center">
                                            <div class="badge badge-success">{{ $staff->approved_documents }}</div>
                                        </td>
                                        <td class="text-center">
                                            @php
                                                $successRate = $staff->documents_handled > 0 ? round(($staff->approved_documents / $staff->documents_handled) * 100, 1) : 0;
                                            @endphp
                                            <div class="badge {{ $successRate >= 80 ? 'badge-success' : ($successRate >= 60 ? 'badge-warning' : 'badge-error') }}">
                                                {{ $successRate }}%
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            @php
                                                $onTimeRate = $staff->documents_handled > 0 ? ($staff->on_time_completion / $staff->documents_handled) * 100 : 0;
                                                $performanceScore = ($successRate + $onTimeRate) / 2;
                                            @endphp
                                            <div class="radial-progress {{ $performanceScore >= 80 ? 'text-success' : ($performanceScore >= 60 ? 'text-warning' : 'text-error') }}" 
                                                 style="--value:{{ round($performanceScore) }}; --size:3rem;">
                                                <span class="text-xs">{{ round($performanceScore) }}%</span>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-8">
                        <i data-lucide="users-x" class="w-12 h-12 mx-auto text-base-300 mb-4"></i>
                        <p class="text-base-content/60">No staff activity found for the selected period.</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Performance Distribution -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            <!-- Workload Distribution -->
            <div class="card bg-base-100 shadow-xl">
                <div class="card-body">
                    <h3 class="card-title">
                        <i data-lucide="pie-chart" class="w-5 h-5"></i>
                        Workload Distribution
                    </h3>
                    
                    @if($productivity['staff_productivity']->count() > 0)
                        <div class="space-y-3">
                            @php
                                $totalDocuments = $productivity['staff_productivity']->sum('documents_handled');
                                $sortedStaff = $productivity['staff_productivity']->sortByDesc('documents_handled');
                            @endphp
                            
                            @foreach($sortedStaff->take(5) as $staff)
                                @php
                                    $percentage = $totalDocuments > 0 ? ($staff->documents_handled / $totalDocuments) * 100 : 0;
                                @endphp
                                <div>
                                    <div class="flex justify-between items-center mb-1">
                                        <span class="text-sm font-medium truncate">{{ $staff->name }}</span>
                                        <div class="flex items-center gap-2">
                                            <span class="text-sm">{{ $staff->documents_handled }}</span>
                                            <span class="text-xs opacity-70">({{ round($percentage, 1) }}%)</span>
                                        </div>
                                    </div>
                                    <div class="w-full bg-base-300 rounded-full h-2">
                                        <div class="bg-primary h-2 rounded-full" style="width: {{ $percentage }}%"></div>
                                    </div>
                                </div>
                            @endforeach
                            
                            @if($sortedStaff->count() > 5)
                                <div class="text-xs opacity-50 text-center mt-2">
                                    +{{ $sortedStaff->count() - 5 }} more staff members
                                </div>
                            @endif
                        </div>
                    @else
                        <div class="text-center py-4">
                            <p class="text-sm opacity-60">No workload data available</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Performance Rankings -->
            <div class="card bg-base-100 shadow-xl">
                <div class="card-body">
                    <h3 class="card-title">
                        <i data-lucide="trophy" class="w-5 h-5"></i>
                        Performance Rankings
                    </h3>
                    
                    @if($productivity['staff_productivity']->count() > 0)
                        <div class="space-y-3">
                            @php
                                $rankedStaff = $productivity['staff_productivity']->map(function($staff) {
                                    $successRate = $staff->documents_handled > 0 ? ($staff->approved_documents / $staff->documents_handled) * 100 : 0;
                                    $onTimeRate = $staff->documents_handled > 0 ? ($staff->on_time_completion / $staff->documents_handled) * 100 : 0;
                                    $staff->performance_score = ($successRate + $onTimeRate) / 2;
                                    return $staff;
                                })->sortByDesc('performance_score');
                            @endphp
                            
                            @foreach($rankedStaff->take(5) as $index => $staff)
                                <div class="flex items-center justify-between p-3 rounded-lg {{ $index === 0 ? 'bg-yellow-50 border border-yellow-200' : ($index === 1 ? 'bg-gray-50 border border-gray-200' : ($index === 2 ? 'bg-orange-50 border border-orange-200' : 'bg-base-200')) }}">
                                    <div class="flex items-center gap-3">
                                        <div class="badge {{ $index === 0 ? 'badge-warning' : ($index === 1 ? 'badge-neutral' : ($index === 2 ? 'badge-accent' : 'badge-ghost')) }}">
                                            #{{ $index + 1 }}
                                        </div>
                                        <div>
                                            <div class="font-medium">{{ $staff->name }}</div>
                                            <div class="text-xs opacity-70">{{ $staff->documents_handled }} documents</div>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <div class="font-bold {{ $staff->performance_score >= 80 ? 'text-success' : ($staff->performance_score >= 60 ? 'text-warning' : 'text-error') }}">
                                            {{ round($staff->performance_score, 1) }}%
                                        </div>
                                        <div class="text-xs opacity-70">Performance</div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-4">
                            <p class="text-sm opacity-60">No performance data available</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Department Staff Overview -->
        <div class="card bg-base-100 shadow-xl">
            <div class="card-body">
                <h3 class="card-title">
                    <i data-lucide="users" class="w-5 h-5"></i>
                    Department Staff Overview
                </h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                    @foreach($productivity['department_staff'] as $staff)
                        @php
                            $staffData = $productivity['staff_productivity']->firstWhere('id', $staff->id);
                            $documentsHandled = $staffData ? $staffData->documents_handled : 0;
                            $successRate = $staffData && $staffData->documents_handled > 0 ? round(($staffData->approved_documents / $staffData->documents_handled) * 100, 1) : 0;
                        @endphp
                        
                        <div class="card bg-base-200 shadow {{ $documentsHandled > 0 ? 'border-l-4 border-primary' : 'opacity-75' }}">
                            <div class="card-body p-4">
                                <div class="flex items-center gap-3 mb-3">
                                    <div class="avatar placeholder">
                                        <div class="bg-neutral text-neutral-content rounded-full w-10">
                                            <span class="text-sm">{{ substr($staff->name, 0, 2) }}</span>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="font-medium">{{ $staff->name }}</div>
                                        <div class="text-xs opacity-70">{{ ucfirst($staff->type) }}</div>
                                    </div>
                                </div>
                                
                                <div class="space-y-2">
                                    <div class="flex justify-between text-sm">
                                        <span>Documents:</span>
                                        <span class="font-mono">{{ $documentsHandled }}</span>
                                    </div>
                                    
                                    @if($documentsHandled > 0)
                                        <div class="flex justify-between text-sm">
                                            <span>Success Rate:</span>
                                            <span class="font-mono {{ $successRate >= 80 ? 'text-success' : ($successRate >= 60 ? 'text-warning' : 'text-error') }}">
                                                {{ $successRate }}%
                                            </span>
                                        </div>
                                        
                                        <div class="flex justify-between text-sm">
                                            <span>On Time:</span>
                                            <span class="font-mono">{{ $staffData->on_time_completion }}</span>
                                        </div>
                                    @else
                                        <div class="text-xs opacity-50 text-center py-2">No activity this period</div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
@endsection