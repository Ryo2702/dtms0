@extends('layouts.app')

@section('content')
    <div class="container mx-auto max-w-7xl">
        <div class="mb-6">
            <h1 class="text-3xl font-bold">Rejected Documents</h1>
            <p class="text-base-content/70">View and manage documents that have been rejected</p>
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
                <h2 class="mb-4 card-title">Rejected Document Reviews</h2>

                <div class="mb-4 alert alert-error">
                    <x-lucide-alert-circle class="w-6 h-6" />
                    <span>
                        These documents have been rejected and require attention or revision.
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
                        'reviewed_by' => 'Reviewed By',
                        'rejected_at' => 'Rejected At',
                        'actions' => 'Actions'
                    ]"
                    :paginator="$rejectedReviews"
                    :sortableFields="['document_id', 'document_type', 'client_name', 'status', 'rejected_at']"
                    emptyMessage="No rejected document reviews found."
                >
                    @foreach ($rejectedReviews as $review)
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
                                <span class="badge badge-error">
                                    {{ ucfirst($review->status) }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <div>{{ $review->creator->name }}</div>
                                <div class="text-xs text-base-content/70">
                                    {{ $review->creator->department?->name }}</div>
                            </td>
                            <td class="px-4 py-3">
                                <div>{{ $review->reviewer?->name ?? 'N/A' }}</div>
                                <div class="text-xs text-base-content/70">
                                    {{ $review->reviewer?->department?->name ?? '' }}</div>
                            </td>
                            <td class="px-4 py-3">
                                <div>{{ $review->updated_at->format('M d, Y') }}</div>
                                <div class="text-xs text-base-content/70">
                                    {{ $review->updated_at->format('H:i') }}</div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex gap-2">
                                    <a href="{{ route('documents.reviews.show', $review->id) }}"
                                        class="btn btn-sm btn-primary">
                                        <x-lucide-eye class="w-4 h-4 mr-1" />
                                        View
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </x-data-table>
            </div>
        </div>
    </div>
@endsection