@extends('layouts.app')

@section('title', 'Completed Documents')

@section('content')
    <div class="mx-auto max-w-7xl">
        <div class="bg-white rounded-lg shadow-sm">
            <div class="p-6 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Closed Documents</h1>
                        <p class="mt-1 text-gray-600">Documents that have been fully processed and downloaded
                        </p>
                    </div>
                </div>
            </div>

            @if ($completedReviews->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                                    Document Info
                                </th>
                                <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                                    Processing Details
                                </th>
                                <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                                    Completion Info
                                </th>
                                <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                                    Performance
                                </th>
                                <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach ($completedReviews as $review)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0">
                                                <div
                                                    class="flex items-center justify-center w-10 h-10 bg-green-100 rounded-full">
                                                    <svg class="w-5 h-5 text-green-600" fill="none" viewBox="0 0 24 24"
                                                        stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
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
                                                @if ($review->official_receipt_number)
                                                    <div class="text-sm text-blue-600">
                                                        OR: {{ $review->official_receipt_number }}
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm text-gray-900">
                                            {{ $review->originalDepartment->name ?? 'N/A' }}
                                            @if ($review->currentDepartment && $review->currentDepartment->id !== $review->originalDepartment?->id)
                                                → {{ $review->currentDepartment->name }}
                                            @endif
                                        </div>
                                        <div class="text-sm text-gray-500">
                                            Created by: {{ $review->creator->name ?? 'N/A' }}
                                        </div>
                                        <div class="text-sm text-gray-500">
                                            Reviewed by: {{ $review->reviewer->name ?? 'N/A' }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        @if ($review->downloaded_at)
                                            <div class="text-sm text-gray-900">
                                                Downloaded: {{ $review->downloaded_at->format('M d, Y') }}
                                            </div>
                                            <div class="text-sm text-gray-500">
                                                {{ $review->downloaded_at->format('g:i A') }}
                                            </div>
                                        @endif
                                        @if ($review->reviewed_at)
                                            <div class="text-sm text-gray-500">
                                                Reviewed: {{ $review->reviewed_at->format('M d, Y g:i A') }}
                                            </div>
                                        @endif
                                    </td>
                                                                       <td class="px-6 py-4">
                                        @php
                                            $processingTime =
                                                $review->submitted_at && $review->downloaded_at
                                                    ? $review->submitted_at->diffInMinutes($review->downloaded_at)
                                                    : null;
                                        @endphp

                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                            <span class="w-1.5 h-1.5 mr-1.5 bg-gray-400 rounded-full"></span>
                                            Closed
                                        </span>

                                        @if ($processingTime)
                                            <div class="mt-1 text-xs text-gray-500">
                                                {{ $processingTime }} min total
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-sm font-medium">
                                        <div class="flex space-x-2">
                                            <a href="{{ route('documents.reviews.show', $review->id) }}"
                                                class="inline-flex items-center px-3 py-1 text-sm font-medium leading-4 text-white transition-colors bg-blue-600 border border-transparent rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z">
                                                    </path>
                                                </svg>
                                                View Details
                                            </a>

                                            @if ($review->created_by === $user->id)
                                                <a href="{{ route('documents.reviews.print', $review->id) }}"
                                                    class="inline-flex items-center px-3 py-1 text-sm font-medium leading-4 text-white transition-colors bg-green-600 border border-transparent rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                                        </path>
                                                    </svg>
                                                    @if($review->status === 'approved')
                                                        Re-print
                                                    @else
                                                        Print
                                                    @endif
                                                </a>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="px-6 py-3 border-t border-gray-200">
                    {{ $completedReviews->links() }}
                </div>
            @else
                <div class="px-6 py-12 text-center">
                    <div class="w-12 h-12 mx-auto text-gray-400">
                        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">No completed documents</h3>
                    <p class="mt-1 text-sm text-gray-500">You haven't completed any documents yet.</p>
                </div>
            @endif
        </div>
    </div>
@endsection
