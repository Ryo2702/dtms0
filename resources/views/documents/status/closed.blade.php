@extends('layouts.app')

@section('title', 'Completed Documents')

@section('content')
    <div class="container mx-auto max-w-7xl">
        <div class="mb-6">
            <h1 class="text-3xl font-bold">Closed Documents</h1>
            <p class="text-base-content/70">Documents that have been fully processed and downloaded</p>
        </div>

        @if (session('success'))
            <div class="mb-6 alert alert-success">
                <x-lucide-info class="w-6 h-6" />
                <span>{{ session('success') }}</span>
            </div>
        @endif

        <!-- Completed Reviews Table -->
        <div class="shadow-xl card bg-white-secondary">
            <div class="card-body">
                <h2 class="mb-4 card-title">Completed Documents</h2>
                
                <div class="mb-4 alert alert-success">
                    <x-lucide-check-circle class="w-6 h-6" />
                    <span>
                        These documents have been fully processed and downloaded.
                    </span>
                </div>

                <x-data-table 
                    :headers="[
                        'document_id' => 'Document Info',
                        'department' => 'Processing Details',
                        'downloaded_at' => 'Completion Info',
                        'performance' => 'Performance',
                        'actions' => 'Actions'
                    ]"
                    :paginator="$completedReviews"
                    :sortableFields="['document_id', 'downloaded_at']"
                    emptyMessage="No completed documents found."
                >
                    @foreach ($completedReviews as $review)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3">
                                <div class="flex items-center">
                                    <div class="shrink-0">
                                        <div class="flex items-center justify-center w-10 h-10 bg-green-100 rounded-full">
                                            <x-lucide-check-circle class="w-5 h-5 text-green-600" />
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
                            <td class="px-4 py-3">
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
                            <td class="px-4 py-3">
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
                            <td class="px-4 py-3">
                                @php
                                    $processingTime =
                                        $review->submitted_at && $review->downloaded_at
                                            ? $review->submitted_at->diffInMinutes($review->downloaded_at)
                                            : null;
                                @endphp

                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                    <span class="w-1.5 h-1.5 mr-1.5 bg-gray-400 rounded-full"></span>
                                    Closed
                                </span>

                                @if ($processingTime)
                                    <div class="mt-1 text-xs text-gray-500">
                                        {{ $processingTime }} min total
                                    </div>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex gap-2">
                                    <a href="{{ route('documents.reviews.show', $review->id) }}"
                                        class="btn btn-sm btn-primary">
                                        <x-lucide-eye class="w-4 h-4 mr-1" />
                                        View
                                    </a>

                                    @if ($review->created_by === auth()->id())
                                        <a href="{{ route('documents.reviews.print', $review->id) }}"
                                            class="btn btn-sm btn-success">
                                            <x-lucide-printer class="w-4 h-4 mr-1" />
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
                </x-data-table>
            </div>
        </div>
    </div>
@endsection
