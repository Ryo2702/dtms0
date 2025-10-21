@extends('layouts.app')

@section('content')
    <div class="container mx-auto max-w-7xl">
        <div class="mb-6">
            <h1 class="text-3xl font-bold">Pending Documents</h1>
            <p class="text-base-content/70">Review documents awaiting your action and download completed documents</p>
        </div>

        @if (session('success'))
            <div class="mb-6 alert alert-success">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 stroke-current shrink-0" fill="none"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>{{ session('success') }}</span>
            </div>
        @endif


        <!-- Reviews Table -->
        <div class="shadow-xl card bg-white-secondary">
            <div class="card-body">
                <h2 class="mb-4 card-title">Documents Requiring Action</h2>
                
                <div class="mb-4 alert alert-info">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="w-6 h-6 stroke-current shrink-0">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span>
                        This page shows documents that need your attention: documents pending your review and completed documents ready for download.
                    </span>
                </div>

                @if ($reviews->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="table w-full table-zebra">
                            <thead>
                                <tr>
                                    <th>Document ID</th>
                                    <th>Type</th>
                                    <th>Client</th>
                                    <th>Status</th>
                                    <th>Created By</th>
                                    <th>Assigned To</th>
                                    <th>Submitted</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($reviews as $review)
                                    <tr>
                                        <td>
                                            <div class="font-mono text-sm">{{ $review->document_id }}</div>
                                        </td>
                                        <td>
                                            <div class="font-semibold">{{ $review->document_type }}</div>
                                        </td>
                                        <td>{{ $review->client_name }}</td>
                                        <td>
                                            @if ($review->status === 'pending')
                                                <span class="badge badge-warning">
                                                    {{ ucfirst($review->status) }}
                                                </span>
                                            @elseif($review->status === 'approved' && !$review->downloaded_at)
                                                <span class="badge badge-success">
                                                    Ready for Download
                                                </span>
                                            @elseif($review->status === 'approved')
                                                <span class="badge badge-success">
                                                    {{ ucfirst($review->status) }}
                                                </span>
                                            @else
                                                <span class="badge badge-info">
                                                    {{ ucfirst($review->status) }}
                                                </span>
                                            @endif
                                            @if ($review->status === 'pending' && $review->is_overdue)
                                                <span class="ml-1 badge badge-error badge-sm">Overdue</span>
                                            @elseif($review->status === 'pending' && $review->due_status === 'due_soon')
                                                <span class="ml-1 badge badge-warning badge-sm">Due Soon</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div>{{ $review->creator->name }}</div>
                                            <div class="text-xs text-base-content/70">
                                                {{ $review->creator->department?->name }}</div>
                                        </td>
                                        <td>
                                            <div>{{ $review->reviewer?->name }}</div>
                                            <div class="text-xs text-base-content/70">
                                                {{ $review->reviewer?->department?->name }}</div>
                                        </td>
                                        <td>
                                            <div>{{ ($review->submitted_at ?? $review->created_at)->format('M d, Y') }}</div>
                                            <div class="text-xs text-base-content/70">
                                                {{ ($review->submitted_at ?? $review->created_at)->format('H:i') }}</div>
                                        </td>
                                        <td>
                                            <div class="flex gap-2">
                                                <a href="{{ route('documents.reviews.show', $review->id) }}"
                                                    class="btn btn-sm btn-primary">
                                                    View
                                                </a>

                                               @if ($review->created_by === auth()->id())
                                                    <a href="{{ route('documents.reviews.print', $review->id) }}"
                                                        class="btn btn-sm btn-success">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3M3 17V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v10a2 2 0 01-2 2H5a2 2 0 01-2-2z" />
                                                        </svg>
                                                        @if($review->status === 'approved' && !$review->downloaded_at)
                                                            Print
                                                        @elseif($review->status === 'approved')
                                                            Re-print
                                                        @else
                                                            Print Draft
                                                        @endif
                                                    </a>
                                                @elseif ($review->status === 'pending' && $review->assigned_to === auth()->id())
                                                    <span class="badge badge-info badge-sm">Awaiting Review</span>
                                                @elseif ($review->status === 'pending' && $review->created_by === auth()->id())
                                                    <span class="badge badge-warning badge-sm">Under Review</span>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="mt-6">
                        {{ $reviews->links() }}
                    </div>
                @else
                    <div class="py-8 text-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-12 h-12 mx-auto text-base-content/40"
                            fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <p class="mt-2 text-base-content/70">No pending document reviews found.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
