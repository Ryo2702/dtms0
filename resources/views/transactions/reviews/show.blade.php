@extends('layouts.app')

@section('title', 'Review Details')

@section('content')
    <x-container>
        {{-- Breadcrumb --}}
        <div class="mb-6">
            <div class="flex items-center gap-2 text-sm text-gray-500 mb-2">
                <a href="{{ route('transactions.reviews.index') }}" class="hover:text-primary">My Reviews</a>
                <i data-lucide="chevron-right" class="w-4 h-4"></i>
                <span>Review Details</span>
            </div>
            <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4">
                <div>
                    <h1 class="text-3xl font-bold">Review Details</h1>
                    <p class="text-gray-600 mt-1">Transaction: {{ $reviewer->transaction->transaction_code }}</p>
                </div>
                <div class="flex gap-2">
                    <a href="{{ route('transactions.reviews.index') }}" class="btn btn-outline">
                        <i data-lucide="arrow-left" class="w-4 h-4 mr-2"></i>
                        Back to Reviews
                    </a>
                    <a href="{{ route('transactions.show', $reviewer->transaction) }}" class="btn btn-primary">
                        <i data-lucide="eye" class="w-4 h-4 mr-2"></i>
                        View Transaction
                    </a>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Main Content --}}
            <div class="lg:col-span-2 space-y-6">
                {{-- Review Information --}}
                <x-card title="Review Information">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <dt class="text-sm text-gray-500">Transaction Code</dt>
                            <dd>
                                <a href="{{ route('transactions.show', $reviewer->transaction) }}" class="font-mono font-bold text-primary hover:underline">
                                    {{ $reviewer->transaction->transaction_code }}
                                </a>
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm text-gray-500">Workflow</dt>
                            <dd class="font-medium">{{ $reviewer->transaction->workflow->transaction_name ?? 'N/A' }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm text-gray-500">Status</dt>
                            <dd>
                                <x-status-badge 
                                    :status="$reviewer->status" 
                                    :labels="['pending' => 'Pending', 'approved' => 'Approved', 'rejected' => 'Rejected']"
                                    :variants="['pending' => 'badge-warning', 'approved' => 'badge-success', 'rejected' => 'badge-error']"
                                />
                                @if($reviewer->isOverdue())
                                    <span class="badge badge-error ml-1">Overdue</span>
                                @endif
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm text-gray-500">Department</dt>
                            <dd class="font-medium">{{ $reviewer->department->name ?? 'N/A' }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm text-gray-500">Due Date</dt>
                            <dd class="{{ $reviewer->isOverdue() ? 'text-error font-bold' : '' }}">
                                @if($reviewer->due_date)
                                    <i data-lucide="calendar" class="w-4 h-4 inline mr-1"></i>
                                    {{ $reviewer->due_date->format('M d, Y h:i A') }}
                                    @if($reviewer->isOverdue())
                                        <span class="text-sm ml-2">({{ $reviewer->due_date->diffForHumans() }})</span>
                                    @endif
                                @else
                                    <span class="text-gray-400">No deadline set</span>
                                @endif
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm text-gray-500">Iteration</dt>
                            <dd class="font-medium">{{ $reviewer->iteration_number ?? 1 }}</dd>
                        </div>
                        @if($reviewer->reviewed_at)
                            <div>
                                <dt class="text-sm text-gray-500">Reviewed At</dt>
                                <dd class="font-medium">{{ $reviewer->reviewed_at->format('M d, Y h:i A') }}</dd>
                            </div>
                        @endif
                        <div>
                            <dt class="text-sm text-gray-500">Created At</dt>
                            <dd class="text-sm">{{ $reviewer->created_at->format('M d, Y h:i A') }}</dd>
                        </div>
                    </div>
                </x-card>

                {{-- Rejection Reason (if rejected) --}}
                @if($reviewer->rejection_reason)
                    <x-card title="Rejection Reason">
                        <div class="p-4 bg-red-50 rounded-lg border border-red-200">
                            <p class="text-error">{{ $reviewer->rejection_reason }}</p>
                        </div>
                        @if($reviewer->resubmission_deadline)
                            <div class="mt-3 flex items-center gap-2 text-sm text-gray-600">
                                <i data-lucide="clock" class="w-4 h-4"></i>
                                <span>Resubmission deadline: {{ $reviewer->resubmission_deadline->format('M d, Y') }}</span>
                            </div>
                        @endif
                    </x-card>
                @endif

                {{-- Previous Reviewer Info (if reassigned) --}}
                @if($reviewer->previousReviewer)
                    <x-card title="Previous Reviewer">
                        <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg">
                            <div class="avatar placeholder">
                                <div class="bg-gray-200 text-gray-600 rounded-full w-10">
                                    <span>{{ substr($reviewer->previousReviewer->full_name ?? 'U', 0, 1) }}</span>
                                </div>
                            </div>
                            <div>
                                <div class="font-medium">{{ $reviewer->previousReviewer->full_name }}</div>
                                <div class="text-sm text-gray-500">{{ $reviewer->previousReviewer->email ?? '' }}</div>
                            </div>
                        </div>
                        <p class="mt-2 text-sm text-gray-500">This review was reassigned from the previous reviewer.</p>
                    </x-card>
                @endif

                {{-- Quick Actions --}}
                @if($reviewer->isPending())
                    <x-card title="Quick Actions">
                        <div class="flex flex-wrap gap-3">
                            <a href="{{ route('transactions.show', $reviewer->transaction) }}" class="btn btn-primary">
                                <i data-lucide="clipboard-check" class="w-4 h-4 mr-2"></i>
                                Process Review
                            </a>
                        </div>
                    </x-card>
                @endif
            </div>

            {{-- Sidebar --}}
            <div class="space-y-6">
                {{-- Reviewer Info --}}
                <x-card title="Reviewer">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="avatar placeholder">
                            <div class="bg-primary text-white rounded-full w-12">
                                <span class="text-lg">{{ substr($reviewer->reviewer->full_name ?? 'U', 0, 1) }}</span>
                            </div>
                        </div>
                        <div>
                            <div class="font-semibold">{{ $reviewer->reviewer->full_name ?? 'Unknown' }}</div>
                            <div class="text-sm text-gray-500">{{ $reviewer->reviewer->email ?? '' }}</div>
                        </div>
                    </div>
                    <dl class="space-y-2 text-sm">
                        @if($reviewer->reviewer->position)
                            <div class="flex justify-between">
                                <dt class="text-gray-500">Position</dt>
                                <dd>{{ $reviewer->reviewer->position }}</dd>
                            </div>
                        @endif
                        @if($reviewer->reviewer->department)
                            <div class="flex justify-between">
                                <dt class="text-gray-500">Department</dt>
                                <dd>{{ $reviewer->reviewer->department->name }}</dd>
                            </div>
                        @endif
                    </dl>
                </x-card>

                {{-- Transaction Summary --}}
                <x-card title="Transaction Summary">
                    <dl class="space-y-3">
                        <div>
                            <dt class="text-sm text-gray-500">Transaction Status</dt>
                            <dd>
                                <x-status-badge 
                                    :status="$reviewer->transaction->transaction_status" 
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
                            <dd class="font-medium">
                                {{ $reviewer->transaction->current_workflow_step }} / {{ $reviewer->transaction->total_workflow_steps }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm text-gray-500">Urgency</dt>
                            <dd>
                                <x-status-badge 
                                    :status="$reviewer->transaction->level_of_urgency" 
                                    :labels="['normal' => 'Normal', 'urgent' => 'Urgent', 'highly_urgent' => 'Highly Urgent']"
                                    :variants="['normal' => 'badge-ghost', 'urgent' => 'badge-warning', 'highly_urgent' => 'badge-error']"
                                />
                            </dd>
                        </div>
                    </dl>
                </x-card>

                {{-- Time Info --}}
                <x-card title="Timeline">
                    <div class="space-y-3">
                        <div class="flex items-start gap-3">
                            <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center">
                                <i data-lucide="plus" class="w-4 h-4 text-blue-600"></i>
                            </div>
                            <div>
                                <div class="text-sm font-medium">Review Assigned</div>
                                <div class="text-xs text-gray-500">{{ $reviewer->created_at->format('M d, Y h:i A') }}</div>
                            </div>
                        </div>
                        @if($reviewer->due_date)
                            <div class="flex items-start gap-3">
                                <div class="w-8 h-8 rounded-full {{ $reviewer->isOverdue() ? 'bg-red-100' : 'bg-yellow-100' }} flex items-center justify-center">
                                    <i data-lucide="clock" class="w-4 h-4 {{ $reviewer->isOverdue() ? 'text-red-600' : 'text-yellow-600' }}"></i>
                                </div>
                                <div>
                                    <div class="text-sm font-medium">Due Date</div>
                                    <div class="text-xs {{ $reviewer->isOverdue() ? 'text-error' : 'text-gray-500' }}">
                                        {{ $reviewer->due_date->format('M d, Y h:i A') }}
                                    </div>
                                </div>
                            </div>
                        @endif
                        @if($reviewer->reviewed_at)
                            <div class="flex items-start gap-3">
                                <div class="w-8 h-8 rounded-full bg-green-100 flex items-center justify-center">
                                    <i data-lucide="check" class="w-4 h-4 text-green-600"></i>
                                </div>
                                <div>
                                    <div class="text-sm font-medium">Reviewed</div>
                                    <div class="text-xs text-gray-500">{{ $reviewer->reviewed_at->format('M d, Y h:i A') }}</div>
                                </div>
                            </div>
                        @endif
                    </div>
                </x-card>
            </div>
        </div>
    </x-container>
@endsection
