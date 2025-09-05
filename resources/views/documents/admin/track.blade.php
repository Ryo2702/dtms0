@extends('layouts.app')

@section('title', 'Document Tracking System - Admin')

@section('content')
    <div class="max-w-full mx-auto">
        <!-- Header with Statistics -->
        <div class="mb-6">
            <div class="bg-white shadow-sm rounded-lg p-6">
                <div class="flex justify-between items-center mb-6">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">Document Tracking System</h1>
                        <p class="text-gray-600 mt-1">Monitor all document journeys across departments</p>
                    </div>
                    <div class="flex items-center space-x-2">
                        <span
                            class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                            <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M3 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z"
                                    clip-rule="evenodd" />
                            </svg>
                            {{ $documents->total() }} Documents
                        </span>
                    </div>
                </div>

                <!-- Statistics Cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4 mb-6">
                    <div class="bg-blue-50 p-4 rounded-lg">
                        <div class="flex items-center">
                            <div class="p-2 bg-blue-100 rounded-lg">
                                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                    </path>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-blue-600">Total Documents</p>
                                <p class="text-2xl font-bold text-blue-900">{{ number_format($stats['total_documents']) }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-yellow-50 p-4 rounded-lg">
                        <div class="flex items-center">
                            <div class="p-2 bg-yellow-100 rounded-lg">
                                <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-yellow-600">Pending</p>
                                <p class="text-2xl font-bold text-yellow-900">
                                    {{ number_format($stats['pending_documents']) }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-green-50 p-4 rounded-lg">
                        <div class="flex items-center">
                            <div class="p-2 bg-green-100 rounded-lg">
                                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-green-600">Completed</p>
                                <p class="text-2xl font-bold text-green-900">
                                    {{ number_format($stats['completed_documents']) }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-red-50 p-4 rounded-lg">
                        <div class="flex items-center">
                            <div class="p-2 bg-red-100 rounded-lg">
                                <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z">
                                    </path>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-red-600">Overdue</p>
                                <p class="text-2xl font-bold text-red-900">{{ number_format($stats['overdue_documents']) }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-purple-50 p-4 rounded-lg">
                        <div class="flex items-center">
                            <div class="p-2 bg-purple-100 rounded-lg">
                                <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-purple-600">Avg. Processing</p>
                                <p class="text-2xl font-bold text-purple-900">
                                    {{ $stats['avg_processing_time'] ? number_format($stats['avg_processing_time']) . 'm' : 'N/A' }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Search and Filter Section -->
        <div class="bg-white shadow-sm rounded-lg mb-6">
            <div class="p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Search & Filter Documents</h2>
                <form method="GET" action="{{ route('documents.reviews.admin.track') }}" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                        <!-- Search Input -->
                        <div>
                            <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                            <input type="text" name="search" id="search" value="{{ request('search') }}"
                                placeholder="Document ID, Client, Employee..."
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        </div>

                        <!-- Department Filter -->
                        <div>
                            <label for="department" class="block text-sm font-medium text-gray-700 mb-1">Department</label>
                            <select name="department" id="department"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                <option value="">All Departments</option>
                                @foreach ($departments as $department)
                                    <option value="{{ $department->id }}"
                                        {{ request('department') == $department->id ? 'selected' : '' }}>
                                        {{ $department->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- User Type Filter -->
                        <div>
                            <label for="user_type" class="block text-sm font-medium text-gray-700 mb-1">User Type</label>
                            <select name="user_type" id="user_type"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                <option value="">All Types</option>
                                @foreach ($userTypes as $type)
                                    <option value="{{ $type }}"
                                        {{ request('user_type') == $type ? 'selected' : '' }}>
                                        {{ $type }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Document Type Filter -->
                        <div>
                            <label for="document_type" class="block text-sm font-medium text-gray-700 mb-1">Document
                                Type</label>
                            <select name="document_type" id="document_type"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                <option value="">All Types</option>
                                @foreach ($documentTypes as $type)
                                    <option value="{{ $type }}"
                                        {{ request('document_type') == $type ? 'selected' : '' }}>
                                        {{ $type }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Status Filter -->
                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                            <select name="status" id="status"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                <option value="">All Status</option>
                                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending
                                </option>
                                <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved
                                </option>
                                <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected
                                </option>
                                <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>
                                    Completed</option>
                            </select>
                        </div>

                        <!-- Date From -->
                        <div>
                            <label for="date_from" class="block text-sm font-medium text-gray-700 mb-1">Date From</label>
                            <input type="date" name="date_from" id="date_from" value="{{ request('date_from') }}"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        </div>

                        <!-- Date To -->
                        <div>
                            <label for="date_to" class="block text-sm font-medium text-gray-700 mb-1">Date To</label>
                            <input type="date" name="date_to" id="date_to" value="{{ request('date_to') }}"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        </div>
                    </div>

                    <div class="flex space-x-3">
                        <button type="submit"
                            class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                            Search
                        </button>
                        <a href="{{ route('documents.reviews.admin.track') }}"
                            class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15">
                                </path>
                            </svg>
                            Clear Filters
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Documents Table -->
        <div class="bg-white shadow-sm rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">Document Journey Tracking</h2>
            </div>

            @if ($documents->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Document Details
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Creator Info
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Department Journey
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Status & Timing
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Performance
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach ($documents as $document)
                                <tr class="hover:bg-gray-50">
                                    <!-- Document Details -->
                                    <td class="px-6 py-4">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0">
                                                <div
                                                    class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center">
                                                    <svg class="h-5 w-5 text-blue-600" fill="none" viewBox="0 0 24 24"
                                                        stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                    </svg>
                                                </div>
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900">
                                                    {{ $document->document_type }}
                                                </div>
                                                <div class="text-sm text-gray-500">
                                                    ID: {{ $document->document_id }}
                                                </div>
                                                <div class="text-sm text-gray-500">
                                                    Client: {{ $document->client_name }}
                                                </div>
                                                @if ($document->official_receipt_number)
                                                    <div class="text-xs text-green-600">
                                                        OR: {{ $document->official_receipt_number }}
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Creator Info -->
                                    <td class="px-6 py-4">
                                        <div class="text-sm text-gray-900">{{ $document->creator->name ?? 'N/A' }}</div>
                                        <div class="text-sm text-gray-500">
                                            {{ $document->creator->type ?? 'N/A' }}
                                            @if ($document->creator && $document->creator->employee_id)
                                                ({{ $document->creator->employee_id }})
                                            @endif
                                        </div>
                                        <div class="text-sm text-gray-500">
                                            {{ $document->originalDepartment->name ?? 'N/A' }}
                                        </div>
                                        <div class="text-xs text-gray-400">
                                            Created: {{ $document->created_at->format('M d, Y g:i A') }}
                                        </div>
                                    </td>

                                    <!-- Department Journey -->
                                    <td class="px-6 py-4">
                                        <div class="flex items-center space-x-2">
                                            <span
                                                class="text-sm text-gray-900">{{ $document->originalDepartment->name ?? 'N/A' }}</span>
                                            @if ($document->currentDepartment && $document->currentDepartment->id !== $document->originalDepartment?->id)
                                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M17 8l4 4m0 0l-4 4m4-4H3"></path>
                                                </svg>
                                                <span
                                                    class="text-sm text-blue-600">{{ $document->currentDepartment->name }}</span>
                                            @endif
                                        </div>
                                        <div class="text-sm text-gray-500">
                                            Current: {{ $document->reviewer->name ?? 'N/A' }}
                                        </div>
                                        <div class="text-xs text-gray-400">
                                            {{ $document->journey_steps }} Journey Steps
                                        </div>
                                    </td>

                                    <!-- Status & Timing -->
                                    <td class="px-6 py-4">
                                        @if ($document->status === 'pending')
                                            <span
                                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                <span class="w-1.5 h-1.5 mr-1.5 bg-yellow-400 rounded-full"></span>
                                                Pending
                                            </span>
                                        @elseif ($document->status === 'approved')
                                            @if ($document->downloaded_at)
                                                <span
                                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                    <span class="w-1.5 h-1.5 mr-1.5 bg-green-400 rounded-full"></span>
                                                    Completed
                                                </span>
                                            @else
                                                <span
                                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                    <span class="w-1.5 h-1.5 mr-1.5 bg-blue-400 rounded-full"></span>
                                                    Approved
                                                </span>
                                            @endif
                                        @elseif ($document->status === 'rejected')
                                            <span
                                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                <span class="w-1.5 h-1.5 mr-1.5 bg-red-400 rounded-full"></span>
                                                Rejected
                                            </span>
                                        @endif

                                        @if ($document->is_overdue)
                                            <div class="text-xs text-red-600 mt-1">⚠ Overdue</div>
                                        @endif

                                        @if ($document->due_at)
                                            <div class="text-xs text-gray-500 mt-1">
                                                Due: {{ $document->due_at->format('M d, g:i A') }}
                                            </div>
                                        @endif
                                    </td>

                                    <!-- Performance -->
                                    <td class="px-6 py-4">
                                        @if ($document->processing_time)
                                            <div class="text-sm text-gray-900">{{ $document->processing_time }}m</div>
                                            <div class="text-xs text-gray-500">Total Time</div>
                                        @else
                                            <div class="text-sm text-gray-400">In Progress</div>
                                        @endif

                                        @if ($document->downloaded_at)
                                            @php
                                                $wasOnTime =
                                                    !$document->due_at || $document->downloaded_at <= $document->due_at;
                                            @endphp
                                            <div
                                                class="text-xs {{ $wasOnTime ? 'text-green-600' : 'text-red-600' }} mt-1">
                                                {{ $wasOnTime ? '✓ On Time' : '⚠ Late' }}
                                            </div>
                                        @endif
                                    </td>

                                    <!-- Actions -->
                                    <td class="px-6 py-4 text-sm font-medium">
                                        <div class="flex space-x-2">
                                            <a href="{{ route('documents.reviews.show', $document->id) }}"
                                                class="inline-flex items-center px-3 py-1 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z">
                                                    </path>
                                                </svg>
                                                View Journey
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="px-6 py-3 border-t border-gray-200">
                    {{ $documents->appends(request()->query())->links() }}
                </div>
            @else
                <div class="px-6 py-12 text-center">
                    <div class="mx-auto h-12 w-12 text-gray-400">
                        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">No documents found</h3>
                    <p class="mt-1 text-sm text-gray-500">Try adjusting your search criteria or clear filters.</p>
                </div>
            @endif
        </div>
    </div>
@endsection
