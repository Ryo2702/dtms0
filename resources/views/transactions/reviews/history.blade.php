@extends('layouts.app')

@section('title', 'Review History')

@section('content')
    <x-container>
        {{-- Breadcrumb --}}
        <div class="mb-6">
            <div class="flex items-center gap-2 text-sm text-gray-500 mb-2">
                <a href="{{ route('transactions.index') }}" class="hover:text-primary">Transactions</a>
                <i data-lucide="chevron-right" class="w-4 h-4"></i>
                <a href="{{ route('transactions.show', $transaction) }}" class="hover:text-primary">{{ $transaction->transaction_code }}</a>
                <i data-lucide="chevron-right" class="w-4 h-4"></i>
                <span>Review History</span>
            </div>
            <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4">
                <div>
                    <h1 class="text-3xl font-bold">Review History</h1>
                    <p class="text-gray-600 mt-1">All reviewer assignments for {{ $transaction->transaction_code }}</p>
                </div>
                <div class="flex gap-2">
                    <a href="{{ route('transactions.show', $transaction) }}" class="btn btn-outline">
                        <i data-lucide="arrow-left" class="w-4 h-4 mr-2"></i>
                        Back to Transaction
                    </a>
                    <a href="{{ route('transactions.history', $transaction) }}" class="btn btn-outline">
                        <i data-lucide="history" class="w-4 h-4 mr-2"></i>
                        Activity Log
                    </a>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
            {{-- Summary --}}
            <div class="lg:col-span-1">
                <x-card title="Summary">
                    <dl class="space-y-3">
                        <div>
                            <dt class="text-sm text-gray-500">Transaction</dt>
                            <dd class="font-mono font-bold text-primary">{{ $transaction->transaction_code }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm text-gray-500">Total Reviews</dt>
                            <dd class="text-2xl font-bold">{{ $reviewHistory->count() }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm text-gray-500">Approved</dt>
                            <dd class="font-medium text-success">
                                {{ $reviewHistory->where('status', 'approved')->count() }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm text-gray-500">Rejected</dt>
                            <dd class="font-medium text-error">
                                {{ $reviewHistory->where('status', 'rejected')->count() }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm text-gray-500">Pending</dt>
                            <dd class="font-medium text-warning">
                                {{ $reviewHistory->where('status', 'pending')->count() }}
                            </dd>
                        </div>
                    </dl>
                </x-card>

                {{-- Transaction Info --}}
                <x-card title="Transaction Info" class="mt-6">
                    <dl class="space-y-3">
                        <div>
                            <dt class="text-sm text-gray-500">Workflow</dt>
                            <dd class="font-medium">{{ $transaction->workflow->transaction_name ?? 'N/A' }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm text-gray-500">Status</dt>
                            <dd>
                                <x-status-badge 
                                    :status="$transaction->transaction_status" 
                                    :labels="[
                                        'draft' => 'Draft',
                                        'in_progress' => 'In Progress',
                                        'completed' => 'Completed',
                                        'cancelled' => 'Cancelled'
                                    ]"
                                    :variants="[
                                        'draft' => 'badge-ghost',
                                        'in_progress' => 'badge-info',
                                        'completed' => 'badge-success',
                                        'cancelled' => 'badge-error'
                                    ]"
                                />
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm text-gray-500">Current Step</dt>
                            <dd class="font-medium">{{ $transaction->current_workflow_step }} / {{ $transaction->total_workflow_steps }}</dd>
                        </div>
                    </dl>
                </x-card>
            </div>

            {{-- Review History Timeline --}}
            <div class="lg:col-span-3">
                <x-card title="Reviewer Timeline">
                    @if($reviewHistory->count() > 0)
                        <div class="relative">
                            {{-- Timeline line --}}
                            <div class="absolute left-6 top-0 bottom-0 w-0.5 bg-gray-200"></div>

                            <div class="space-y-4">
                                @foreach($reviewHistory as $review)
                                    @php
                                        $statusColors = [
                                            'pending' => 'bg-warning',
                                            'approved' => 'bg-success',
                                            'rejected' => 'bg-error',
                                        ];
                                        $statusIcons = [
                                            'pending' => 'clock',
                                            'approved' => 'check',
                                            'rejected' => 'x',
                                        ];
                                        $bgColor = $statusColors[$review->status] ?? 'bg-gray-400';
                                        $icon = $statusIcons[$review->status] ?? 'user';
                                    @endphp
                                    
                                    <div class="relative flex items-start gap-4 pl-2">
                                        {{-- Status Icon --}}
                                        <div class="relative z-10 flex-shrink-0 w-10 h-10 rounded-full {{ $bgColor }} text-white flex items-center justify-center">
                                            <i data-lucide="{{ $icon }}" class="w-5 h-5"></i>
                                        </div>

                                        {{-- Content --}}
                                        <div class="flex-1 pb-4">
                                            <div class="p-4 bg-white border border-gray-200 rounded-lg shadow-sm {{ $review->is_overdue ? 'border-l-4 border-l-error' : '' }}">
                                                <div class="flex flex-col sm:flex-row justify-between items-start gap-2 mb-3">
                                                    <div class="flex items-center gap-3">
                                                        <div class="avatar placeholder">
                                                            <div class="bg-gray-200 text-gray-600 rounded-full w-10">
                                                                <span>{{ substr($review->reviewer->full_name ?? 'U', 0, 1) }}</span>
                                                            </div>
                                                        </div>
                                                        <div>
                                                            <div class="font-semibold">{{ $review->reviewer->full_name ?? 'Unknown' }}</div>
                                                            <div class="text-sm text-gray-500">{{ $review->department->name ?? 'N/A' }}</div>
                                                        </div>
                                                    </div>
                                                    <div class="flex items-center gap-2">
                                                        <x-status-badge 
                                                            :status="$review->status" 
                                                            :labels="['pending' => 'Pending', 'approved' => 'Approved', 'rejected' => 'Rejected']"
                                                            :variants="['pending' => 'badge-warning', 'approved' => 'badge-success', 'rejected' => 'badge-error']"
                                                        />
                                                        @if($review->is_overdue)
                                                            <span class="badge badge-error badge-sm">Overdue</span>
                                                        @endif
                                                        @if($review->iteration_number > 1)
                                                            <span class="badge badge-ghost badge-sm">Iteration {{ $review->iteration_number }}</span>
                                                        @endif
                                                    </div>
                                                </div>

                                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm">
                                                    <div>
                                                        <span class="text-gray-500">Assigned:</span>
                                                        <span class="ml-1">{{ $review->created_at->format('M d, Y h:i A') }}</span>
                                                    </div>
                                                    @if($review->due_date)
                                                        <div>
                                                            <span class="text-gray-500">Due:</span>
                                                            <span class="ml-1 {{ $review->is_overdue ? 'text-error' : '' }}">
                                                                {{ $review->due_date->format('M d, Y') }}
                                                            </span>
                                                        </div>
                                                    @endif
                                                    @if($review->reviewed_at)
                                                        <div>
                                                            <span class="text-gray-500">Reviewed:</span>
                                                            <span class="ml-1 text-success">{{ $review->reviewed_at->format('M d, Y h:i A') }}</span>
                                                        </div>
                                                    @endif
                                                </div>

                                                @if($review->rejection_reason)
                                                    <div class="mt-3 p-3 bg-red-50 rounded border border-red-200">
                                                        <span class="text-sm text-gray-500 block mb-1">Rejection Reason:</span>
                                                        <p class="text-sm text-error">{{ $review->rejection_reason }}</p>
                                                    </div>
                                                @endif

                                                @if($review->previousReviewer)
                                                    <div class="mt-3 text-sm text-gray-500">
                                                        <i data-lucide="user-minus" class="w-4 h-4 inline mr-1"></i>
                                                        Reassigned from: {{ $review->previousReviewer->full_name }}
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @else
                        <div class="text-center py-8 text-gray-500">
                            <i data-lucide="users" class="w-12 h-12 mx-auto mb-3 opacity-50"></i>
                            <p>No reviewer history found for this transaction.</p>
                        </div>
                    @endif
                </x-card>
            </div>
        </div>
    </x-container>
@endsection
