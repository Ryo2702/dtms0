@extends('layouts.app')

@section('title', 'Document Tracking System - Admin')

@section('content')
    <div class="p-4 sm:p-6">
        <x-page-header title="Document Tracking System" subtitle="Monitor all document journeys across departments" />

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4 mb-6">
            <x-stat-card bgColor="bg-stat-primary" title="Total Documents" :value="$stats['total_documents']" />

            <x-stat-card bgColor="bg-stat-accent" title="Pending" :value="$stats['pending_documents']" />

            <x-stat-card bgColor="bg-stat-secondary" title="Completed" :value="$stats['completed_documents']" />

            <x-stat-card bgColor="bg-stat-danger" title="Overdue" :value="$stats['overdue_documents']" />

            <x-stat-card bgColor="bg-stat-info" title="Avg. Processing" :value="$stats['avg_processing_time'] ? number_format($stats['avg_processing_time']) . 'm' : 'N/A'" />
        </div>

        {{-- Enhanced Filters --}}
        <div class="bg-white-secondary shadow-sm rounded-lg mb-6">
            <div class="p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Search & Filter Documents</h2>
                <form method="GET" action="{{ route('admin.documents.track') }}" class="space-y-4">
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
                            <i data-lucide="search" class="w-4 h-4 mr-2"></i>
                            Search
                        </button>
                        <a href="{{ route('admin.documents.track') }}"
                            class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            <i data-lucide="refresh-ccw" class="h-4 w-4 mr-2"></i>
                            Clear Filters
                        </a>
                    </div>
                </form>
            </div>
        </div>

        {{-- Document Journey Table --}}
        <x-data-table :headers="[
            'Document Details',
            'Creator Info',
            'Department Journey',
            'Status & Timing',
            'Performance',
            'Actions',
        ]" :paginator="$documents"
            emptyMessage="No documents found. Try adjusting your search criteria or clear filters."
            title="Document Journey Tracking">

            @foreach ($documents as $document)
                <tr class="hover:bg-gray-50">
                    <!-- Document Details -->
                    <td class="px-6 py-4">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center">
                                    <i data-lucide="file-check" class="h-5 w-5"></i>
                                </div>
                            </div>
                            <div class="ml-4">
                                <div class="text-sm font-medium text-gray-900">{{ $document->document_type }}</div>
                                <div class="text-sm text-gray-500">ID: {{ $document->document_id }}</div>
                                <div class="text-sm text-gray-500">Client: {{ $document->client_name }}</div>
                                @if ($document->official_receipt_number)
                                    <div class="text-xs text-green-600">OR: {{ $document->official_receipt_number }}</div>
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
                        <div class="text-sm text-gray-500">{{ $document->originalDepartment->name ?? 'N/A' }}</div>
                        <div class="text-xs text-gray-400">Created: {{ $document->created_at->format('M d, Y g:i A') }}
                        </div>
                    </td>

                    <!-- Department Journey -->
                    <td class="px-6 py-4">
                        <div class="flex items-center space-x-2">
                            <span class="text-sm text-gray-900">{{ $document->originalDepartment->name ?? 'N/A' }}</span>
                            @if ($document->currentDepartment && $document->currentDepartment->id !== $document->originalDepartment?->id)
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M17 8l4 4m0 0l-4 4m4-4H3"></path>
                                </svg>
                                <span class="text-sm text-blue-600">{{ $document->currentDepartment->name }}</span>
                            @endif
                        </div>
                        <div class="text-sm text-gray-500">Current: {{ $document->reviewer->name ?? 'N/A' }}</div>
                        <div class="text-xs text-gray-400">{{ $document->journey_steps }} Journey Steps</div>
                    </td>

                    <!-- Status & Timing -->
                    <td class="px-6 py-4">
                        @if ($document->status === 'pending')
                            <x-status-badge status="pending" />
                        @elseif ($document->status === 'approved')
                            @if ($document->downloaded_at)
                                <x-status-badge status="completed" />
                            @else
                                <x-status-badge status="approved" />
                            @endif
                        @elseif ($document->status === 'rejected')
                            <x-status-badge status="rejected" />
                        @endif

                        @if ($document->is_overdue)
                            <div class="text-xs text-red-600 mt-1">⚠ Overdue</div>
                        @endif

                        @if ($document->due_at)
                            <div class="text-xs text-gray-500 mt-1">Due: {{ $document->due_at->format('M d, g:i A') }}
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
                                $wasOnTime = !$document->due_at || $document->downloaded_at <= $document->due_at;
                            @endphp
                            <div class="text-xs {{ $wasOnTime ? 'text-green-600' : 'text-red-600' }} mt-1">
                                {{ $wasOnTime ? '✓ On Time' : '⚠ Late' }}
                            </div>
                        @endif
                    </td>

                    <!-- Actions -->
                    <td class="px-6 py-4 text-sm font-medium">
                        <div class="flex space-x-2">
                            <a href="{{ route('documents.reviews.show', $document->id) }}"
                                class="inline-flex items-center px-3 py-1 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                                <i data-lucide="eye" class="h-4 w-4 mr-1"></i>
                                View log
                            </a>
                        </div>
                    </td>
                </tr>
            @endforeach
        </x-data-table>
    </div>
@endsection
