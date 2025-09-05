@extends('layouts.app')

@section('title', 'Sent Documents')

@section('content')
    <div class="max-w-7xl mx-auto">
        <div class="bg-white shadow-sm rounded-lg">
            <div class="p-6 border-b border-gray-200">
                <div class="flex justify-between items-center">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Sent Documents</h1>
                        <p class="text-gray-600 mt-1">Documents sent to other departments</p>
                    </div>
                    <div class="flex items-center space-x-2">
                        <span
                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800">
                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M17 8l4 4m0 0l-4 4m4-4H3" clip-rule="evenodd" />
                            </svg>
                            {{ $sentReviews->total() }} Total
                        </span>
                    </div>
                </div>
            </div>

            @if ($sentReviews->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Document Info
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    To Department
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Status
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Due Date
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach ($sentReviews as $review)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0">
                                                <div
                                                    class="h-10 w-10 rounded-full bg-orange-100 flex items-center justify-center">
                                                    <svg class="h-5 w-5 text-orange-600" fill="none" viewBox="0 0 24 24"
                                                        stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                    </svg>
                                                </div>
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900">
                                                    {{ $review->document_type }}
                                                </div>
                                                <div class="text-sm text-gray-500">
                                                    ID: {{ $review->document_id }}
                                                </div>
                                                <div class="text-sm text-gray-500">
                                                    Client: {{ $review->client_name }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm text-gray-900">{{ $review->currentDepartment->name ?? 'N/A' }}
                                        </div>
                                        <div class="text-sm text-gray-500">
                                            Assigned to: {{ $review->reviewer->name ?? 'N/A' }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        @if ($review->status === 'pending')
                                            <span
                                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                <span class="w-1.5 h-1.5 mr-1.5 bg-yellow-400 rounded-full"></span>
                                                Under Review
                                            </span>
                                        @elseif ($review->status === 'approved')
                                            @if ($review->downloaded_at)
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
                                        @elseif ($review->status === 'rejected')
                                            <span
                                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                <span class="w-1.5 h-1.5 mr-1.5 bg-red-400 rounded-full"></span>
                                                Rejected
                                            </span>
                                        @endif

                                        @if ($review->due_status === 'overdue')
                                            <div class="text-xs text-red-600 mt-1">Overdue</div>
                                        @elseif ($review->due_status === 'due_soon')
                                            <div class="text-xs text-orange-600 mt-1">Due Soon</div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4">
                                        @if ($review->due_at)
                                            <div class="text-sm text-gray-900">
                                                {{ $review->due_at->format('M d, Y') }}
                                            </div>
                                            <div class="text-sm text-gray-500">
                                                {{ $review->due_at->format('g:i A') }}
                                            </div>
                                        @else
                                            <span class="text-sm text-gray-400">No deadline</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-sm font-medium">
                                        <div class="flex space-x-2">
                                            <a href="{{ route('documents.reviews.show', $review->id) }}"
                                                class="inline-flex items-center px-3 py-1 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z">
                                                    </path>
                                                </svg>
                                                Track
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
                    {{ $sentReviews->links() }}
                </div>
            @else
                <div class="px-6 py-12 text-center">
                    <div class="mx-auto h-12 w-12 text-gray-400">
                        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 8l4 4m0 0l-4 4m4-4H3" />
                        </svg>
                    </div>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">No sent documents</h3>
                    <p class="mt-1 text-sm text-gray-500">You haven't sent any documents to other departments yet.</p>
                </div>
            @endif
        </div>
    </div>
@endsection
