@extends('layouts.app')

@section('content')
<x-container>
    <x-page-header
        title="Audit Logs"
        subtitle="Track all user activities and system changes"
    >
        <div class="flex space-x-2">
            @if(request()->hasAny(['user_id', 'action', 'date_from', 'date_to', 'search']))
                <a href="{{ route('admin.audit-logs.export', request()->query()) }}" 
                   class="btn btn-outline btn-sm">
                    <i data-lucide="download" class="w-4 h-4 mr-1"></i>
                    Export Filtered
                </a>
            @endif
            <a href="{{ route('admin.audit-logs.export') }}" 
               class="btn btn-primary btn-sm">
                <i data-lucide="download" class="w-4 h-4 mr-1"></i>
                Export All
            </a>
        </div>
    </x-page-header>

    <!-- Statistics -->
    <x-audit-log-stats
        :totalLogs="$stats['totalLogs']"
        :todayLogs="$stats['todayLogs']"
        :weeklyLogs="$stats['weeklyLogs']"
        :topActions="$stats['topActions']"
    />

    <!-- Filters -->
    <x-audit-log-filters :users="$users" />

    <!-- Results Summary -->
    @if(request()->hasAny(['user_id', 'action', 'date_from', 'date_to', 'search']))
        <x-card compact="true" class="mb-4">
            <div class="flex items-center justify-between">
                <div class="text-sm text-gray-600">
                    Showing {{ $logs->count() }} of {{ $logs->total() }} filtered results
                    @if(request('search'))
                        for "{{ request('search') }}"
                    @endif
                </div>
                <a href="{{ route('admin.audit-logs.index') }}" class="text-sm text-blue-600 hover:text-blue-800">
                    Clear all filters
                </a>
            </div>
        </x-card>
    @endif

    <!-- Audit Logs Table -->
    <x-card>
        <x-audit-log-table :logs="$logs" :paginator="$logs" />
    </x-card>

    @if($logs->isEmpty() && !request()->hasAny(['user_id', 'action', 'date_from', 'date_to', 'search']))
        <x-card>
            <div class="py-8 text-center">
                <i data-lucide="file-search" class="w-16 h-16 mx-auto mb-4 text-gray-400"></i>
                <h3 class="mb-2 text-lg font-medium text-gray-900">No Audit Logs Yet</h3>
                <p class="mb-4 text-gray-500">Audit logs will appear here as users perform actions in the system.</p>
                <p class="text-sm text-gray-400">
                    The audit logging system is now active and will track all user activities.
                </p>
            </div>
        </x-card>
    @endif
</x-container>
@endsection

@push('scripts')
<script>
// Auto-refresh every 30 seconds if on the first page
@if(request()->get('page', 1) == 1)
setTimeout(() => {
    if (!document.hidden) {
        window.location.reload();
    }
}, 30000);
@endif

// Handle visibility change to refresh when tab becomes active
document.addEventListener('visibilitychange', function() {
    if (!document.hidden && {{ request()->get('page', 1) }} == 1) {
        window.location.reload();
    }
});
</script>
@endpush