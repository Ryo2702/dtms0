@extends('layouts.app')

@section('title', 'Review Transaction')

@section('content')
    <x-container>
        {{-- Breadcrumb --}}
        <div class="mb-6">
            <div class="flex items-center gap-2 text-sm text-gray-500 mb-2">
                <a href="{{ route('transactions.reviews.index') }}" class="hover:text-primary">My Reviews</a>
                <i data-lucide="chevron-right" class="w-4 h-4"></i>
                <span>Review Transaction</span>
            </div>
            <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4">
                <div>
                    <h1 class="text-3xl font-bold">Review Transaction</h1>
                    <p class="text-gray-600 mt-1">{{ $reviewer->transaction->transaction_code }}</p>
                </div>
                <div class="flex gap-2">
                    <a href="{{ route('transactions.reviews.index') }}" class="btn btn-outline">
                        <i data-lucide="arrow-left" class="w-4 h-4 mr-2"></i>
                        Back to Reviews
                    </a>
                    <a href="{{ route('transactions.show', $reviewer->transaction) }}" class="btn btn-outline btn-primary">
                        <i data-lucide="file-text" class="w-4 h-4 mr-2"></i>
                        View Full Transaction
                    </a>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Main Content --}}
            <div class="lg:col-span-2 space-y-6">
                {{-- Transaction Summary --}}
                <x-card title="Transaction Details">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <dt class="text-sm text-gray-500">Transaction Code</dt>
                            <dd class="font-mono font-bold text-primary text-lg">{{ $reviewer->transaction->transaction_code }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm text-gray-500">Workflow</dt>
                            <dd class="font-medium">{{ $reviewer->transaction->workflow->transaction_name ?? 'N/A' }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm text-gray-500">Submitted By</dt>
                            <dd class="flex items-center gap-2 mt-1">
                                <div class="avatar">
                                    <div class="w-8 rounded-full">
                                        <img src="https://ui-avatars.com/api/?name={{ urlencode($reviewer->transaction->creator->name ?? 'Unknown') }}&background=random" alt="" />
                                    </div>
                                </div>
                                <div>
                                    <div class="font-medium">{{ $reviewer->transaction->creator->name ?? 'Unknown' }}</div>
                                    <div class="text-xs text-gray-500">{{ $reviewer->transaction->department->name ?? '' }}</div>
                                </div>
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm text-gray-500">Level of Urgency</dt>
                            <dd>
                                <x-status-badge 
                                    :status="$reviewer->transaction->level_of_urgency" 
                                    :labels="['normal' => 'Normal', 'urgent' => 'Urgent', 'highly_urgent' => 'Highly Urgent']"
                                    :variants="['normal' => 'badge-ghost', 'urgent' => 'badge-warning', 'highly_urgent' => 'badge-error']"
                                />
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm text-gray-500">Current Step</dt>
                            <dd class="font-medium">{{ $reviewer->transaction->current_workflow_step }} / {{ $reviewer->transaction->total_workflow_steps }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm text-gray-500">Current State</dt>
                            <dd class="font-medium capitalize">{{ str_replace('_', ' ', $reviewer->transaction->current_state ?? 'N/A') }}</dd>
                        </div>
                    </div>

                    @if($reviewer->iteration_number > 1)
                        <div class="mt-4 p-3 bg-info/10 border border-info/30 rounded-lg">
                            <div class="flex items-center gap-2 text-info">
                                <i data-lucide="refresh-cw" class="w-5 h-5"></i>
                                <span class="font-medium">This is resubmission #{{ $reviewer->iteration_number }}</span>
                            </div>
                            <p class="text-sm text-gray-600 mt-1">This transaction was previously rejected and has been resubmitted for review.</p>
                        </div>
                    @endif
                </x-card>

                {{-- Review Due Date --}}
                @if($reviewer->due_date)
                    <div class="alert {{ $reviewer->isOverdue() ? 'alert-error' : ($reviewer->due_date->isToday() ? 'alert-warning' : 'alert-info') }}">
                        <i data-lucide="clock" class="w-5 h-5"></i>
                        <div>
                            <div class="font-medium">
                                @if($reviewer->isOverdue())
                                    Review Overdue!
                                @elseif($reviewer->due_date->isToday())
                                    Due Today!
                                @else
                                    Due: {{ $reviewer->due_date->format('M d, Y') }}
                                @endif
                            </div>
                            <div class="text-sm">{{ $reviewer->due_date->diffForHumans() }}</div>
                        </div>
                    </div>
                @endif

                {{-- Review Actions --}}
                <x-card title="Your Decision">
                    <div class="space-y-6">
                        {{-- Approve Section --}}
                        <div class="p-4 border-2 border-success/30 rounded-lg bg-success/5">
                            <h3 class="text-lg font-semibold text-success flex items-center gap-2 mb-3">
                                <i data-lucide="check-circle" class="w-5 h-5"></i>
                                Approve Transaction
                            </h3>
                            <p class="text-sm text-gray-600 mb-4">
                                Approving this transaction will move it to the next step in the workflow.
                            </p>
                            <form action="{{ route('transactions.reviews.approve', $reviewer) }}" method="POST" id="approve-form">
                                @csrf
                                <div class="form-control mb-4">
                                    <label class="label">
                                        <span class="label-text">Remarks (Optional)</span>
                                    </label>
                                    <textarea name="remarks" class="textarea textarea-bordered" rows="2" 
                                        placeholder="Add any comments or notes..."></textarea>
                                </div>
                                <button type="submit" class="btn btn-success w-full">
                                    <i data-lucide="check" class="w-4 h-4 mr-2"></i>
                                    Approve Transaction
                                </button>
                            </form>
                        </div>

                        <div class="divider">OR</div>

                        {{-- Reject Section --}}
                        <div class="p-4 border-2 border-error/30 rounded-lg bg-error/5">
                            <h3 class="text-lg font-semibold text-error flex items-center gap-2 mb-3">
                                <i data-lucide="x-circle" class="w-5 h-5"></i>
                                Reject Transaction
                            </h3>
                            <p class="text-sm text-gray-600 mb-4">
                                Rejecting this transaction will send it back to the submitter for corrections.
                            </p>
                            <form action="{{ route('transactions.reviews.reject', $reviewer) }}" method="POST" id="reject-form">
                                @csrf
                                <div class="form-control mb-4">
                                    <label class="label">
                                        <span class="label-text">Reason for Rejection <span class="text-error">*</span></span>
                                    </label>
                                    <textarea name="rejection_reason" class="textarea textarea-bordered textarea-error" rows="3" 
                                        placeholder="Please explain why this transaction is being rejected..." required></textarea>
                                    @error('rejection_reason')
                                        <label class="label">
                                            <span class="label-text-alt text-error">{{ $message }}</span>
                                        </label>
                                    @enderror
                                </div>
                                <div class="form-control mb-4">
                                    <label class="label">
                                        <span class="label-text">Resubmission Deadline (Optional)</span>
                                    </label>
                                    <input type="date" name="resubmission_deadline" class="input input-bordered" 
                                        min="{{ now()->addDay()->format('Y-m-d') }}">
                                    <label class="label">
                                        <span class="label-text-alt text-gray-500">Set a deadline for the submitter to fix and resubmit</span>
                                    </label>
                                </div>
                                <button type="submit" class="btn btn-error w-full">
                                    <i data-lucide="x" class="w-4 h-4 mr-2"></i>
                                    Reject Transaction
                                </button>
                            </form>
                        </div>
                    </div>
                </x-card>
            </div>

            {{-- Sidebar --}}
            <div class="space-y-6">
                {{-- Review Info --}}
                <x-card title="Review Information">
                    <dl class="space-y-3">
                        <div>
                            <dt class="text-sm text-gray-500">Assigned To</dt>
                            <dd class="flex items-center gap-2 mt-1">
                                <div class="avatar placeholder">
                                    <div class="bg-primary text-white rounded-full w-8">
                                        <span>{{ substr(auth()->user()->name ?? 'U', 0, 1) }}</span>
                                    </div>
                                </div>
                                <span class="font-medium">{{ auth()->user()->name }}</span>
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm text-gray-500">Department</dt>
                            <dd class="font-medium">{{ $reviewer->department->name ?? 'N/A' }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm text-gray-500">Assigned Date</dt>
                            <dd class="text-sm">{{ $reviewer->created_at->format('M d, Y h:i A') }}</dd>
                        </div>
                        @if($reviewer->due_date)
                            <div>
                                <dt class="text-sm text-gray-500">Due Date</dt>
                                <dd class="text-sm {{ $reviewer->isOverdue() ? 'text-error font-bold' : '' }}">
                                    {{ $reviewer->due_date->format('M d, Y h:i A') }}
                                </dd>
                            </div>
                        @endif
                        <div>
                            <dt class="text-sm text-gray-500">Iteration</dt>
                            <dd>
                                @if($reviewer->iteration_number > 1)
                                    <span class="badge badge-info">Resubmission #{{ $reviewer->iteration_number }}</span>
                                @else
                                    <span class="badge badge-ghost">First Review</span>
                                @endif
                            </dd>
                        </div>
                    </dl>
                </x-card>

                {{-- Previous Rejection (if resubmission) --}}
                @if($reviewer->iteration_number > 1 && $reviewer->previousReviewer)
                    <x-card title="Previous Review">
                        <div class="p-3 bg-red-50 rounded-lg border border-red-200">
                            <div class="flex items-center gap-2 text-error mb-2">
                                <i data-lucide="x-circle" class="w-4 h-4"></i>
                                <span class="font-medium">Previously Rejected</span>
                            </div>
                            @if($reviewer->previousReviewer->rejection_reason)
                                <p class="text-sm text-gray-700">{{ $reviewer->previousReviewer->rejection_reason }}</p>
                            @endif
                        </div>
                    </x-card>
                @endif

                {{-- Quick Links --}}
                <x-card title="Quick Links">
                    <div class="space-y-2">
                        <a href="{{ route('transactions.show', $reviewer->transaction) }}" class="btn btn-outline btn-sm w-full justify-start">
                            <i data-lucide="file-text" class="w-4 h-4 mr-2"></i>
                            View Transaction Details
                        </a>
                        <a href="{{ route('transactions.tracker', $reviewer->transaction) }}" class="btn btn-outline btn-sm w-full justify-start">
                            <i data-lucide="map-pin" class="w-4 h-4 mr-2"></i>
                            Track Progress
                        </a>
                        <a href="{{ route('transactions.history', $reviewer->transaction) }}" class="btn btn-outline btn-sm w-full justify-start">
                            <i data-lucide="history" class="w-4 h-4 mr-2"></i>
                            View History
                        </a>
                    </div>
                </x-card>
            </div>
        </div>
    </x-container>

    {{-- Confirmation Modals --}}
    <script>
        document.getElementById('approve-form').addEventListener('submit', function(e) {
            if (!confirm('Are you sure you want to APPROVE this transaction? This action cannot be undone.')) {
                e.preventDefault();
            }
        });

        document.getElementById('reject-form').addEventListener('submit', function(e) {
            if (!confirm('Are you sure you want to REJECT this transaction? The submitter will be notified.')) {
                e.preventDefault();
            }
        });
    </script>
@endsection
