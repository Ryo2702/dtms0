@extends('layouts.app')

@section('content')
    <div class="container mx-auto max-w-7xl">
        <div class="mb-6">
            <h1 class="text-3xl font-bold">Pending Documents</h1>
            <p class="text-base-content/70">Review documents awaiting your action and download completed documents</p>
        </div>

        @if (session('success'))
            <div class="mb-6 alert alert-success">
                <x-lucide-info class="w-6 h-6" />
                <span>{{ session('success') }}</span>
            </div>
        @endif

        <!-- Reviews Table -->
        <div class="shadow-xl card bg-white-secondary">
            <div class="card-body">
                <h2 class="mb-4 card-title">Documents Requiring Action</h2>
                
                <div class="mb-4 alert alert-info">
                    <x-lucide-info class="w-6 h-6" />
                    <span>
                        This page shows documents that need your attention: documents pending your review.
                    </span>
                </div>

                <x-data-table 
                    :headers="[
                        'document_id' => 'Document ID',
                        'document_type' => 'Type',
                        'client_name' => 'Client',
                        'priority' => 'Priority',
                        'status' => 'Status',
                        'created_by' => 'Created By',
                        'assigned_to' => 'Assigned To',
                        'submitted_at' => 'Submitted',
                        'actions' => 'Actions'
                    ]"
                    :paginator="$reviews"
                    :sortableFields="['document_id', 'document_type', 'client_name', 'status', 'submitted_at']"
                    emptyMessage="No pending document reviews found."
                >
                    @foreach ($reviews as $review)
                        <tr>
                            <td class="px-4 py-3">
                                <div class="font-mono text-sm">{{ $review->document_id }}</div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="font-semibold">{{ $review->document_type }}</div>
                            </td>
                            <td class="px-4 py-3">{{ $review->client_name }}</td>
                            <td class="px-4 py-3">
                                <span class="badge badge-{{ $review->priority_badge_class }}">
                                    {{ ucfirst($review->display_priority) }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                @if ($review->status === 'pending')
                                    <span class="badge badge-warning">
                                        {{ ucfirst($review->status) }}
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
                            <td class="px-4 py-3">
                                <div>{{ $review->creator->name }}</div>
                                <div class="text-xs text-base-content/70">
                                    {{ $review->creator->department?->name }}</div>
                            </td>
                            <td class="px-4 py-3">
                                <div>{{ $review->reviewer?->name }}</div>
                                <div class="text-xs text-base-content/70">
                                    {{ $review->reviewer?->department?->name }}</div>
                            </td>
                            <td class="px-4 py-3">
                                <div>{{ ($review->submitted_at ?? $review->created_at)->format('M d, Y') }}</div>
                                <div class="text-xs text-base-content/70">
                                    {{ ($review->submitted_at ?? $review->created_at)->format('H:i') }}</div>
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
                                            @if($review->status === 'approved' && !$review->downloaded_at)
                                                Print
                                            @elseif($review->status === 'approved')
                                                Re-print
                                            @else
                                                Print Draft
                                            @endif
                                        </a>
                                        
                                        @if($review->status === 'approved' && !$review->downloaded_at)
                                            <form method="POST" action="{{ route('documents.reviews.markDone', $review->id) }}" class="inline">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="btn btn-sm btn-success" 
                                                        onclick="return confirm('Mark this document as done? This will move it to closed status.')">
                                                    <x-lucide-check class="w-4 h-4 mr-1" />
                                                    Done
                                                </button>
                                            </form>
                                        @endif
                                    @elseif ($review->status === 'pending' && $review->assigned_to === auth()->id())
                                        <span class="badge badge-info badge-sm">Awaiting Review</span>
                                    @elseif ($review->status === 'pending' && $review->created_by === auth()->id())
                                        <span class="badge badge-warning badge-sm">Under Review</span>
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