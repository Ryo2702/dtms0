@extends('layouts.app')

@section('title', 'Document Performance Report')

@section('content')
    <div class="p-4 sm:p-6">
        <x-page-header title="Document Performance Report" subtitle="Detailed analysis of document processing efficiency" />

        <!-- Back Button and Date Range -->
        <div class="flex flex-wrap justify-between items-center mb-6 gap-4">
            <a href="{{ route('head.report.index') }}" class="btn btn-ghost btn-sm">
                <i data-lucide="arrow-left" class="w-4 h-4 mr-1"></i>
                Back to Reports
            </a>
            
            <div class="flex flex-wrap gap-4 items-end">
                <form method="GET" action="{{ route('head.report.document-performance') }}" class="flex flex-wrap gap-4 items-end">
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
                
                <a href="{{ route('head.report.export', 'document-performance') }}?date_from={{ $dateFrom }}&date_to={{ $dateTo }}" 
                   class="btn btn-success btn-sm">
                    <i data-lucide="download" class="w-4 h-4 mr-1"></i>
                    Export CSV
                </a>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
            <x-stat-card 
                bgColor="bg-stat-primary" 
                title="Total Documents" 
                :value="$performance['summary']['total_documents']" 
                icon="file-text" />

            <x-stat-card 
                bgColor="bg-stat-secondary" 
                title="Average Time" 
                :value="round($performance['summary']['average_processing_time']) . ' min'" 
                icon="timer" />

            <x-stat-card 
                bgColor="bg-stat-success" 
                title="On Time" 
                :value="$performance['summary']['completed_on_time']" 
                icon="check-circle" />

            <x-stat-card 
                bgColor="bg-stat-danger" 
                title="Overdue" 
                :value="$performance['summary']['overdue_documents']" 
                icon="alert-circle" />
        </div>

        <!-- Performance by Document Type -->
        <div class="card bg-base-100 shadow-xl mb-8">
            <div class="card-body">
                <h3 class="card-title">
                    <i data-lucide="bar-chart" class="w-5 h-5"></i>
                    Performance by Document Type
                </h3>
                
                @if($performance['performance_by_type']->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="table table-zebra">
                            <thead>
                                <tr>
                                    <th>Document Type</th>
                                    <th class="text-center">Total</th>
                                    <th class="text-center">Avg Time (min)</th>
                                    <th class="text-center">On Time</th>
                                    <th class="text-center">Approved</th>
                                    <th class="text-center">Rejected</th>
                                    <th class="text-center">Success Rate</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($performance['performance_by_type'] as $perf)
                                    <tr>
                                        <td class="font-medium">{{ $perf->document_type }}</td>
                                        <td class="text-center">
                                            <div class="badge badge-primary">{{ $perf->total }}</div>
                                        </td>
                                        <td class="text-center font-mono">
                                            {{ $perf->avg_time ? round($perf->avg_time, 1) : '-' }}
                                        </td>
                                        <td class="text-center">
                                            <div class="badge badge-success">{{ $perf->on_time }}</div>
                                        </td>
                                        <td class="text-center">
                                            <div class="badge badge-success">{{ $perf->approved }}</div>
                                        </td>
                                        <td class="text-center">
                                            <div class="badge badge-error">{{ $perf->rejected }}</div>
                                        </td>
                                        <td class="text-center">
                                            @php
                                                $successRate = $perf->total > 0 ? round(($perf->approved / $perf->total) * 100, 1) : 0;
                                            @endphp
                                            <div class="badge {{ $successRate >= 80 ? 'badge-success' : ($successRate >= 60 ? 'badge-warning' : 'badge-error') }}">
                                                {{ $successRate }}%
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-8">
                        <i data-lucide="file-x" class="w-12 h-12 mx-auto text-base-300 mb-4"></i>
                        <p class="text-base-content/60">No documents found for the selected period.</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Recent Documents -->
        <div class="card bg-base-100 shadow-xl">
            <div class="card-body">
                <h3 class="card-title">
                    <i data-lucide="file-text" class="w-5 h-5"></i>
                    Recent Documents
                </h3>
                
                @if($performance['documents']->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="table table-zebra">
                            <thead>
                                <tr>
                                    <th>Document ID</th>
                                    <th>Type</th>
                                    <th>Client</th>
                                    <th>Created</th>
                                    <th>Status</th>
                                    <th>Processing Time</th>
                                    <th>On Time</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($performance['documents'] as $doc)
                                    <tr>
                                        <td class="font-mono">{{ $doc->document_id }}</td>
                                        <td>{{ $doc->document_type }}</td>
                                        <td>{{ $doc->client_name }}</td>
                                        <td>{{ $doc->created_at->format('M d, Y') }}</td>
                                        <td>
                                            <div class="badge 
                                                {{ $doc->status === 'approved' ? 'badge-success' : '' }}
                                                {{ $doc->status === 'rejected' ? 'badge-error' : '' }}
                                                {{ $doc->status === 'pending' ? 'badge-warning' : '' }}
                                                {{ $doc->status === 'canceled' ? 'badge-neutral' : '' }}">
                                                {{ ucfirst($doc->status) }}
                                            </div>
                                        </td>
                                        <td class="font-mono">
                                            @if($doc->process_time_minutes)
                                                {{ round($doc->process_time_minutes / 60, 1) }}h
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>
                                            @if($doc->completed_on_time !== null)
                                                <div class="badge {{ $doc->completed_on_time ? 'badge-success' : 'badge-error' }}">
                                                    {{ $doc->completed_on_time ? 'Yes' : 'No' }}
                                                </div>
                                            @else
                                                <div class="badge badge-neutral">Pending</div>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Pagination -->
                    <div class="mt-6">
                        {{ $performance['documents']->appends(request()->query())->links() }}
                    </div>
                @else
                    <div class="text-center py-8">
                        <i data-lucide="file-x" class="w-12 h-12 mx-auto text-base-300 mb-4"></i>
                        <p class="text-base-content/60">No documents found for the selected period.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection