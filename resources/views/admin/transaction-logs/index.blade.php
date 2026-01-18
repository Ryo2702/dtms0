@extends('layouts.app')

@section('content')
    <x-container>
        <!-- Page Header -->
        <div class="mb-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Transaction Logs</h1>
                    <p class="text-gray-600 mt-1">Monitor all transaction activities across all departments</p>
                </div>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
            <!-- Total Logs -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Total Logs</p>
                        <p class="text-3xl font-bold text-gray-900 mt-2">{{ number_format($stats['totalLogs']) }}</p>
                    </div>
                    <div class="bg-blue-100 rounded-full p-3">
                        <i data-lucide="file-text" class="w-6 h-6 text-blue-600"></i>
                    </div>
                </div>
            </div>

            <!-- Today's Logs -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Today's Logs</p>
                        <p class="text-3xl font-bold text-gray-900 mt-2">{{ number_format($stats['todayLogs']) }}</p>
                    </div>
                    <div class="bg-green-100 rounded-full p-3">
                        <i data-lucide="calendar" class="w-6 h-6 text-green-600"></i>
                    </div>
                </div>
            </div>

            <!-- This Week -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">This Week</p>
                        <p class="text-3xl font-bold text-gray-900 mt-2">{{ number_format($stats['weeklyLogs']) }}</p>
                    </div>
                    <div class="bg-purple-100 rounded-full p-3">
                        <i data-lucide="trending-up" class="w-6 h-6 text-purple-600"></i>
                    </div>
                </div>
            </div>

            <!-- This Month -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">This Month</p>
                        <p class="text-3xl font-bold text-gray-900 mt-2">{{ number_format($stats['monthlyLogs']) }}</p>
                    </div>
                    <div class="bg-orange-100 rounded-full p-3">
                        <i data-lucide="calendar-days" class="w-6 h-6 text-orange-600"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Breakdown -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                <i data-lucide="activity" class="w-5 h-5 mr-2"></i>
                Action Breakdown
            </h3>
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4">
                @foreach ($stats['actionBreakdown'] as $action => $count)
                    <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                        <div class="flex items-center justify-between">
                            <div class="flex-1">
                                <p class="text-xs font-medium text-gray-600 uppercase">{{ $action }}</p>
                                <p class="text-2xl font-bold text-gray-900 mt-1">{{ number_format($count) }}</p>
                            </div>
                            @if ($action === 'approve')
                                <div class="bg-green-100 rounded-full p-2">
                                    <i data-lucide="check-circle" class="w-4 h-4 text-green-600"></i>
                                </div>
                            @elseif ($action === 'reject')
                                <div class="bg-red-100 rounded-full p-2">
                                    <i data-lucide="x-circle" class="w-4 h-4 text-red-600"></i>
                                </div>
                            @elseif ($action === 'resubmit')
                                <div class="bg-blue-100 rounded-full p-2">
                                    <i data-lucide="rotate-ccw" class="w-4 h-4 text-blue-600"></i>
                                </div>
                            @elseif ($action === 'cancel')
                                <div class="bg-gray-100 rounded-full p-2">
                                    <i data-lucide="ban" class="w-4 h-4 text-gray-600"></i>
                                </div>
                            @else
                                <div class="bg-indigo-100 rounded-full p-2">
                                    <i data-lucide="arrow-right" class="w-4 h-4 text-indigo-600"></i>
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                    <i data-lucide="filter" class="w-5 h-5 mr-2"></i>
                    Filter Logs
                </h3>
                @if (request()->hasAny(['search', 'date_from', 'date_to', 'action', 'department_id', 'user_id']))
                    <a href="{{ route('admin.transaction-logs.index') }}"
                        class="text-sm text-blue-600 hover:text-blue-800 flex items-center">
                        <i data-lucide="x" class="w-4 h-4 mr-1"></i>
                        Clear Filters
                    </a>
                @endif
            </div>

            <form method="GET" action="{{ route('admin.transaction-logs.index') }}" class="space-y-4">
                <!-- Search Bar -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i data-lucide="search" class="w-5 h-5 text-gray-400"></i>
                        </div>
                        <input type="text" name="search" value="{{ request('search') }}"
                            placeholder="Search by transaction code, action, remarks, or user name..."
                            class="pl-10 w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                    <!-- Date From -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Date From</label>
                        <input type="date" name="date_from" value="{{ request('date_from') }}"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    <!-- Date To -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Date To</label>
                        <input type="date" name="date_to" value="{{ request('date_to') }}"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    <!-- Action Filter -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Action</label>
                        <select name="action"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">All Actions</option>
                            @foreach ($actions as $action)
                                <option value="{{ $action }}" {{ request('action') == $action ? 'selected' : '' }}>
                                    {{ ucfirst($action) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Department Filter -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Department</label>
                        <select name="department_id"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">All Departments</option>
                            @foreach ($departments as $department)
                                <option value="{{ $department->id }}"
                                    {{ request('department_id') == $department->id ? 'selected' : '' }}>
                                    {{ $department->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- User Filter -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">User</label>
                        <select name="user_id"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">All Users</option>
                            @foreach ($users as $user)
                                <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                                    {{ $user->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="flex justify-end">
                    <button type="submit"
                        class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 focus:ring-4 focus:ring-blue-300 font-medium flex items-center">
                        <i data-lucide="filter" class="w-4 h-4 mr-2"></i>
                        Apply Filters
                    </button>
                </div>
            </form>
        </div>

        <!-- Transaction Logs Table -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                    <i data-lucide="list" class="w-5 h-5 mr-2"></i>
                    Transaction Activity Logs
                    <span class="ml-2 text-sm font-normal text-gray-600">({{ $logs->total() }} total)</span>
                </h3>
            </div>

            @if ($logs->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Date & Time
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Transaction
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Action
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    User
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Departments
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Status
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Remarks
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach ($logs as $log)
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">
                                            {{ $log->created_at->format('M d, Y') }}
                                        </div>
                                        <div class="text-sm text-gray-500">
                                            {{ $log->created_at->format('h:i A') }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if ($log->transaction)
                                            <div class="text-sm">
                                                <span
                                                    class="font-medium text-blue-600 hover:text-blue-800">{{ $log->transaction->transaction_code }}</span>
                                                <div class="text-xs text-gray-500">
                                                    Created by {{ $log->transaction->creator->name ?? 'Unknown' }}
                                                </div>
                                            </div>
                                        @else
                                            <span class="text-sm text-gray-400 italic">Deleted</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if ($log->action === 'approve')
                                            <span
                                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                <i data-lucide="check-circle" class="w-3 h-3 mr-1"></i>
                                                Approved
                                            </span>
                                        @elseif ($log->action === 'reject')
                                            <span
                                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                <i data-lucide="x-circle" class="w-3 h-3 mr-1"></i>
                                                Rejected
                                            </span>
                                        @elseif ($log->action === 'resubmit')
                                            <span
                                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                <i data-lucide="rotate-ccw" class="w-3 h-3 mr-1"></i>
                                                Resubmitted
                                            </span>
                                        @elseif ($log->action === 'cancel')
                                            <span
                                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                <i data-lucide="ban" class="w-3 h-3 mr-1"></i>
                                                Cancelled
                                            </span>
                                        @else
                                            <span
                                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">
                                                <i data-lucide="arrow-right" class="w-3 h-3 mr-1"></i>
                                                {{ ucfirst($log->action) }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-8 w-8">
                                                <div
                                                    class="h-8 w-8 rounded-full bg-gradient-to-br from-blue-400 to-blue-600 flex items-center justify-center text-white font-semibold text-sm">
                                                    {{ strtoupper(substr($log->actor->name ?? 'U', 0, 1)) }}
                                                </div>
                                            </div>
                                            <div class="ml-3">
                                                <div class="text-sm font-medium text-gray-900">
                                                    {{ $log->actor->name ?? 'Unknown' }}
                                                </div>
                                                <div class="text-sm text-gray-500">
                                                    {{ $log->actor->type ?? 'N/A' }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        @if ($log->transaction)
                                            <div class="text-sm">
                                                @if ($log->transaction->department)
                                                    <div class="flex items-center text-gray-900">
                                                        <i data-lucide="building" class="w-3 h-3 mr-1"></i>
                                                        <span class="font-medium">{{ $log->transaction->department->name }}</span>
                                                    </div>
                                                @endif
                                                @if ($log->transaction->originDepartment && $log->transaction->origin_department_id != $log->transaction->department_id)
                                                    <div class="flex items-center text-gray-500 text-xs mt-1">
                                                        <i data-lucide="arrow-left" class="w-3 h-3 mr-1"></i>
                                                        From: {{ $log->transaction->originDepartment->name }}
                                                    </div>
                                                @endif
                                            </div>
                                        @else
                                            <span class="text-sm text-gray-400">-</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if ($log->transaction)
                                            @if ($log->to_state === 'completed')
                                                <span
                                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                    Completed
                                                </span>
                                            @elseif ($log->to_state === 'pending')
                                                <span
                                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                    Pending
                                                </span>
                                            @elseif ($log->to_state === 'rejected')
                                                <span
                                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                    Rejected
                                                </span>
                                            @elseif ($log->to_state === 'cancelled')
                                                <span
                                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                    Cancelled
                                                </span>
                                            @else
                                                <span
                                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                    {{ ucfirst($log->to_state ?? 'N/A') }}
                                                </span>
                                            @endif
                                        @else
                                            <span class="text-sm text-gray-400">-</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4">
                                        @if ($log->remarks)
                                            <div class="text-sm text-gray-900 max-w-xs truncate"
                                                title="{{ $log->remarks }}">
                                                {{ $log->remarks }}
                                            </div>
                                        @else
                                            <span class="text-sm text-gray-400 italic">No remarks</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
                    {{ $logs->links() }}
                </div>
            @else
                <div class="px-6 py-12 text-center">
                    <i data-lucide="inbox" class="w-16 h-16 mx-auto text-gray-400 mb-4"></i>
                    <h3 class="text-lg font-medium text-gray-900 mb-1">No logs found</h3>
                    <p class="text-gray-500">
                        @if (request()->hasAny(['search', 'date_from', 'date_to', 'action', 'department_id', 'user_id']))
                            Try adjusting your filters to see more results.
                        @else
                            Transaction logs will appear here once activities are recorded.
                        @endif
                    </p>
                </div>
            @endif
        </div>
    </x-container>
@endsection
