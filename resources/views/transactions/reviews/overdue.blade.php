@extends('layouts.app')

@section('title', 'Overdue Reviews')

@section('content')
    <x-container>
        <div class="mb-6">
            <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4">
                <div>
                    <h1 class="text-3xl font-bold text-error">Overdue Reviews</h1>
                    <p class="text-gray-600 mt-1">Reviews that have passed their due date</p>
                </div>
                <div class="flex gap-2">
                    <a href="{{ route('transactions.reviews.index') }}" class="btn btn-outline">
                        <i data-lucide="arrow-left" class="w-4 h-4 mr-2"></i>
                        Back to My Reviews
                    </a>
                </div>
            </div>
        </div>

        {{-- Alert Banner --}}
        @if($overdueReviews->total() > 0)
            <div class="alert alert-error mb-6">
                <i data-lucide="alert-triangle" class="w-5 h-5"></i>
                <div>
                    <h3 class="font-bold">Attention Required</h3>
                    <p>There are {{ $overdueReviews->total() }} overdue review(s) that need immediate attention.</p>
                </div>
            </div>
        @endif

        {{-- Overdue Reviews Table --}}
        <x-card>
            <x-data-table 
                :headers="['Transaction', 'Reviewer', 'Department', 'Due Date', 'Days Overdue', 'Actions']"
                :paginator="$overdueReviews"
                emptyMessage="No overdue reviews found. Great job!"
            >
                @foreach($overdueReviews as $review)
                    @php
                        $daysOverdue = $review->due_date ? $review->due_date->diffInDays(now()) : 0;
                    @endphp
                    <tr class="hover:bg-red-50 bg-red-50/50">
                        <td class="px-4 py-3">
                            <div>
                                <a href="{{ route('transactions.show', $review->transaction) }}" class="font-mono font-bold text-primary hover:underline">
                                    {{ $review->transaction->transaction_code }}
                                </a>
                                <div class="text-sm text-gray-500">{{ $review->transaction->workflow->transaction_name ?? 'N/A' }}</div>
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-2">
                                <div class="avatar placeholder">
                                    <div class="bg-gray-200 text-gray-600 rounded-full w-8">
                                        <span class="text-xs">{{ substr($review->reviewer->full_name ?? 'U', 0, 1) }}</span>
                                    </div>
                                </div>
                                <div>
                                    <div class="font-medium">{{ $review->reviewer->full_name ?? 'Unknown' }}</div>
                                    <div class="text-xs text-gray-500">{{ $review->reviewer->email ?? '' }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            {{ $review->department->name ?? 'N/A' }}
                        </td>
                        <td class="px-4 py-3">
                            <div class="text-error">
                                <i data-lucide="calendar-x" class="w-4 h-4 inline mr-1"></i>
                                {{ $review->due_date ? $review->due_date->format('M d, Y') : 'N/A' }}
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <span class="badge badge-error">{{ $daysOverdue }} day(s)</span>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex gap-1">
                                <a href="{{ route('transactions.show', $review->transaction) }}" 
                                   class="btn btn-sm btn-primary" title="View Transaction">
                                    <i data-lucide="eye" class="w-4 h-4"></i>
                                </a>
                                <button type="button" 
                                        class="btn btn-sm btn-warning" 
                                        onclick="openExtendDueDateModal({{ $review->id }}, '{{ $review->due_date?->format('Y-m-d') }}')"
                                        title="Extend Due Date">
                                    <i data-lucide="calendar-plus" class="w-4 h-4"></i>
                                </button>
                                <button type="button" 
                                        class="btn btn-sm btn-ghost" 
                                        onclick="openReassignModal({{ $review->id }}, '{{ $review->reviewer_id }}')"
                                        title="Reassign">
                                    <i data-lucide="user-plus" class="w-4 h-4"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </x-data-table>
        </x-card>

        {{-- Extend Due Date Modal --}}
        <x-modal id="extendDueDateModal" title="Extend Due Date" size="sm">
            <form method="POST" id="extendDueDateForm">
                @csrf
                @method('PUT')

                <div class="form-control mb-4">
                    <label class="label">
                        <span class="label-text">New Due Date</span>
                    </label>
                    <input type="date" name="due_date" id="new_due_date" class="input input-bordered w-full" required min="{{ now()->addDay()->format('Y-m-d') }}">
                </div>

                <x-slot name="actions">
                    <button type="button" class="btn btn-ghost" onclick="extendDueDateModal.close()">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i data-lucide="save" class="w-4 h-4 mr-2"></i>
                        Update Due Date
                    </button>
                </x-slot>
            </form>
        </x-modal>

        {{-- Reassign Modal --}}
        <x-modal id="reassignModal" title="Reassign Reviewer" size="sm">
            <form method="POST" id="reassignForm">
                @csrf
                @method('PUT')

                <div class="alert alert-warning mb-4">
                    <i data-lucide="alert-triangle" class="w-4 h-4"></i>
                    <span>The current reviewer will be notified of this reassignment.</span>
                </div>

                <div class="form-control mb-4">
                    <label class="label">
                        <span class="label-text">New Reviewer</span>
                    </label>
                    <select name="reviewer_id" id="new_reviewer_id" class="select select-bordered w-full" required>
                        <option value="">-- Select Reviewer --</option>
                        {{-- This would be populated dynamically based on department --}}
                    </select>
                </div>

                <x-slot name="actions">
                    <button type="button" class="btn btn-ghost" onclick="reassignModal.close()">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i data-lucide="user-check" class="w-4 h-4 mr-2"></i>
                        Reassign
                    </button>
                </x-slot>
            </form>
        </x-modal>
    </x-container>
@endsection

@push('scripts')
<script>
    function openExtendDueDateModal(reviewerId, currentDueDate) {
        document.getElementById('extendDueDateForm').action = `/transactions/reviews/${reviewerId}/due-date`;
        if (currentDueDate) {
            document.getElementById('new_due_date').value = '';
        }
        extendDueDateModal.showModal();
    }

    function openReassignModal(reviewerId, currentReviewerId) {
        document.getElementById('reassignForm').action = `/transactions/reviews/${reviewerId}/reassign`;
        document.getElementById('new_reviewer_id').value = '';
        reassignModal.showModal();
    }
</script>
@endpush
