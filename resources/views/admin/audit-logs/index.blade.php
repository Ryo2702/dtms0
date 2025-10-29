@extends('layouts.app')

@section('content')
    <x-container>
        <!-- Add the page header with export buttons -->
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Audit Logs</h1>
                <p class="text-gray-600">Track all user activities and system changes</p>
            </div>

            <!-- Export Buttons -->
            <div class="flex space-x-2">
                @if(request()->hasAny(['user_id', 'action', 'date_from', 'date_to', 'search']))
                    <div class="dropdown dropdown-end">
                        <div tabindex="0" role="button" class="btn btn-outline btn-sm">
                            <i data-lucide="download" class="w-4 h-4 mr-1"></i>
                            Export Filtered
                            <i data-lucide="chevron-down" class="w-4 h-4 ml-1"></i>
                        </div>
                        <ul tabindex="0" class="dropdown-content z-1 menu p-2 shadow bg-base-100 rounded-box w-52">
                            <li><a
                                    href="{{ route('admin.audit-logs.export', array_merge(request()->query(), ['format' => 'csv'])) }}">
                                    <i data-lucide="file-text" class="w-4 h-4"></i> Export as CSV
                                </a></li>
                            <li><a
                                    href="{{ route('admin.audit-logs.export', array_merge(request()->query(), ['format' => 'excel'])) }}">
                                    <i data-lucide="file-spreadsheet" class="w-4 h-4"></i> Export as Excel
                                </a></li>
                        </ul>
                    </div>
                @endif

                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open" class="btn btn-primary btn-sm">
                        <i data-lucide="download" class="w-4 h-4 mr-1"></i>
                        Export All
                        <i data-lucide="chevron-down" class="w-4 h-4 ml-1"></i>
                    </button>

                    <div class="flex space-x-2">
                        <a href="{{ route('admin.audit-logs.export', ['format' => 'csv']) }}"
                            class="btn btn-outline btn-sm">
                            <i data-lucide="file-text" class="w-4 h-4 mr-1"></i>
                            CSV
                        </a>
                        <a href="{{ route('admin.audit-logs.export', ['format' => 'excel']) }}"
                            class="btn btn-primary btn-sm">
                            <i data-lucide="file-spreadsheet" class="w-4 h-4 mr-1"></i>
                            Excel
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <x-stat-card title="Total Logs" :value="$stats['totalLogs'] ?? 0" icon-name="file-text" color="text-primary"
                icon-color="text-primary" />

            <x-stat-card title="Today's Logs" :value="$stats['todayLogs'] ?? 0" icon-name="calendar" color="text-secondary"
                icon-color="text-secondary" />

            <x-stat-card title="This Week" :value="$stats['weeklyLogs'] ?? 0" icon-name="trending-up" color="text-accent"
                icon-color="text-accent" />

            <x-stat-card title="Most Common Action" :value="isset($stats['topActions']) && $stats['topActions']->isNotEmpty() ? ucfirst($stats['topActions']->first()->action) : 'N/A'"
                :subtitle="isset($stats['topActions']) && $stats['topActions']->isNotEmpty() ? $stats['topActions']->first()->count . ' times' : '0 times'" icon-name="activity" color="text-info"
                icon-color="text-info" />
        </div>

        <!-- Filters -->
        <x-card class="mb-6">
            <h3 class="text-lg font-medium mb-4">Filter Audit Logs</h3>

            <form method="GET" action="{{ route('admin.audit-logs.index') }}" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                    <!-- User Filter -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">User</label>
                        <select name="user_id" class="select select-bordered w-full">
                            <option value="">All Users</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                                    {{ $user->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Action Filter -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Action</label>
                        <select name="action" class="select select-bordered w-full">
                            <option value="">All Actions</option>
                            <option value="login" {{ request('action') == 'login' ? 'selected' : '' }}>Login</option>
                            <option value="logout" {{ request('action') == 'logout' ? 'selected' : '' }}>Logout</option>
                            <option value="create" {{ request('action') == 'create' ? 'selected' : '' }}>Create</option>
                            <option value="update" {{ request('action') == 'update' ? 'selected' : '' }}>Update</option>
                            <option value="delete" {{ request('action') == 'delete' ? 'selected' : '' }}>Delete</option>
                        </select>
                    </div>

                    <!-- Date From -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Date From</label>
                        <input type="date" name="date_from" value="{{ request('date_from') }}"
                            class="input input-bordered w-full">
                    </div>

                    <!-- Date To -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Date To</label>
                        <input type="date" name="date_to" value="{{ request('date_to') }}"
                            class="input input-bordered w-full">
                    </div>

                    <!-- Filter Actions -->
                    <div class="flex items-end space-x-2">
                        <button type="submit" class="btn btn-primary">
                            <i data-lucide="search" class="w-4 h-4 mr-1"></i>
                            Filter
                        </button>
                        <a href="{{ route('admin.audit-logs.index') }}" class="btn btn-outline">
                            <i data-lucide="x" class="w-4 h-4 mr-1"></i>
                            Clear
                        </a>
                    </div>
                </div>

                <!-- Search -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Search Description</label>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search in descriptions..."
                        class="input input-bordered w-full">
                </div>
            </form>
        </x-card>

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
        document.addEventListener('visibilitychange', function () {
            if (!document.hidden && {{ request()->get('page', 1) }} == 1) {
                window.location.reload();
            }
        });
    </script>
@endpush