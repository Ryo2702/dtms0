@extends('layouts.app')

@section('content')
    <div class="container max-w-7xl mx-auto">
        <div class="mb-6">
            <h1 class="text-3xl font-bold">Document Reviews</h1>
            <p class="text-base-content/70">Manage and track document review process</p>
        </div>

        @if (session('success'))
            <div class="alert alert-success mb-6">
                <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        <!-- Quick Stats -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div class="stat bg-base-100 shadow">
                <div class="stat-figure text-primary">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div class="stat-title">Pending Reviews</div>
                <div class="stat-value text-primary">{{ $reviews->where('status', 'pending')->count() }}</div>
            </div>

            <div class="stat bg-base-100 shadow">
                <div class="stat-figure text-success">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div class="stat-title">Approved</div>
                <div class="stat-value text-success">{{ $reviews->where('status', 'approved')->count() }}</div>
            </div>

            <div class="stat bg-base-100 shadow">
                <div class="stat-figure text-error">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </div>
                <div class="stat-title">Rejected</div>
                <div class="stat-value text-error">{{ $reviews->where('status', 'rejected')->count() }}</div>
            </div>

            <div class="stat bg-base-100 shadow">
                <div class="stat-figure text-info">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </div>
                <div class="stat-title">Total Documents</div>
                <div class="stat-value text-info">{{ $reviews->total() }}</div>
            </div>
        </div>

        <!-- Reviews Table -->
        <div class="card bg-base-100 shadow-xl">
            <div class="card-body">
                <h2 class="card-title mb-4">Document Reviews</h2>

                @if ($reviews->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="table table-zebra w-full">
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
                                            <span
                                                class="badge 
                                            @if ($review->status === 'pending') badge-warning
                                            @elseif($review->status === 'approved') badge-success
                                            @elseif($review->status === 'rejected') badge-error
                                            @elseif($review->status === 'canceled') badge-neutral
                                            @else badge-info @endif">
                                                {{ ucfirst($review->status) }}
                                            </span>
                                            @if ($review->status === 'pending' && $review->is_overdue)
                                                <span class="badge badge-error badge-sm ml-1">Overdue</span>
                                            @elseif($review->status === 'pending' && $review->due_status === 'due_soon')
                                                <span class="badge badge-warning badge-sm ml-1">Due Soon</span>
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
                                            <div>{{ $review->submitted_at->format('M d, Y') }}</div>
                                            <div class="text-xs text-base-content/70">
                                                {{ $review->submitted_at->format('H:i') }}</div>
                                        </td>
                                        <td>
                                            <div class="flex gap-2">
                                                <a href="{{ route('documents.reviews.show', $review->id) }}"
                                                    class="btn btn-sm btn-primary">
                                                    View
                                                </a>

                                                @if ($review->status === 'approved' && $review->created_by === auth()->id())
                                                    <a href="{{ route('documents.reviews.download', $review->id) }}"
                                                        class="btn btn-sm btn-success">
                                                        Download
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
                    <div class="mt-6">
                        {{ $reviews->links() }}
                    </div>
                @else
                    <div class="text-center py-8">
                        <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto h-12 w-12 text-base-content/40"
                            fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <p class="mt-2 text-base-content/70">No document reviews found.</p>
                        <a href="{{ route('documents.index') }}" class="btn btn-primary mt-4">
                            Create New Document
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
