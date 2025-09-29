@extends('layouts.app')

@section('content')
    <div class="container mx-auto max-w-7xl">
        <div class="mb-6">
            <h1 class="text-3xl font-bold">Canceled Documents</h1>
            <p class="text-base-content/70">View and manage documents that have been canceled</p>
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
        <div class="shadow-xl card bg-base-100">
            <div class="card-body">
                <h2 class="mb-4 card-title">Canceled Document Reviews</h2>

                @if ($canceledReviews->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="table w-full table-zebra">
                            <thead>
                                <tr>
                                    <th>Document ID</th>
                                    <th>Type</th>
                                    <th>Client</th>
                                    <th>Status</th>
                                    <th>Created By</th>
                                    <th>Canceled By</th>
                                    <th>Canceled At</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($canceledReviews as $review)
                                    <tr>
                                        <td>
                                            <div class="font-mono text-sm">{{ $review->document_id }}</div>
                                        </td>
                                        <td>
                                            <div class="font-semibold">{{ $review->document_type }}</div>
                                        </td>
                                        <td>{{ $review->client_name }}</td>
                                        <td>
                                            <span class="badge badge-neutral">
                                                {{ ucfirst($review->status) }}
                                            </span>
                                        </td>
                                        <td>
                                            <div>{{ $review->creator->name }}</div>
                                            <div class="text-xs text-base-content/70">
                                                {{ $review->creator->department?->name }}</div>
                                        </td>
                                        <td>
                                            <div>{{ $review->reviewer?->name ?? 'N/A' }}</div>
                                            <div class="text-xs text-base-content/70">
                                                {{ $review->reviewer?->department?->name ?? '' }}</div>
                                        </td>
                                        <td>
                                            <div>{{ $review->updated_at->format('M d, Y') }}</div>
                                            <div class="text-xs text-base-content/70">
                                                {{ $review->updated_at->format('H:i') }}</div>
                                        </td>
                                        <td>
                                            <div class="flex gap-2">
                                                <a href="{{ route('documents.reviews.show', $review->id) }}"
                                                    class="btn btn-sm btn-primary">
                                                    View
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="mt-6">
                        {{ $canceledReviews->links() }}
                    </div>
                @else
                    <div class="py-8 text-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-12 h-12 mx-auto text-base-content/40"
                            fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728L5.636 5.636m12.728 12.728L18.364 5.636M5.636 18.364l12.728-12.728" />
                        </svg>
                        <p class="mt-2 text-base-content/70">No canceled document reviews found.</p>
                        <p class="text-sm text-base-content/50">All documents are still active in the workflow!</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection