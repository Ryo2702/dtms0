@extends('layouts.app')

@section('title', 'Transaction Details')

@section('content')
    <x-container>
        {{-- Breadcrumb --}}
        <div class="mb-6">
            <div class="flex items-center gap-2 text-sm text-gray-500 mb-2">
                <a href="{{ route('transactions.index') }}" class="hover:text-primary">Transactions</a>
                <i data-lucide="chevron-right" class="w-4 h-4"></i>
                <span>{{ $transaction->transaction_code }}</span>
            </div>
            <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4">
                <div>
                    <h1 class="text-3xl font-bold">{{ $transaction->transaction_code }}</h1>
                    <p class="text-gray-600 mt-1">{{ $transaction->workflow->transaction_name ?? 'Transaction Details' }}</p>
                </div>
                <div class="flex gap-2">
                    <a href="{{ route('transactions.tracker', $transaction) }}" class="btn btn-outline btn-primary">
                        <i data-lucide="map-pin" class="w-4 h-4 mr-2"></i>
                        Track Progress
                    </a>
                    <a href="{{ route('transactions.history', $transaction) }}" class="btn btn-outline">
                        <i data-lucide="history" class="w-4 h-4 mr-2"></i>
                        History
                    </a>
                    @if(!$transaction->isCompleted() && !$transaction->isCancelled())
                        <a href="{{ route('transactions.edit', $transaction) }}" class="btn btn-primary">
                            <i data-lucide="edit" class="w-4 h-4 mr-2"></i>
                            Edit
                        </a>
                    @endif
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Main Content --}}
            <div class="lg:col-span-2 space-y-6">
                {{-- Transaction Info --}}
                <x-card title="Transaction Information">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <dt class="text-sm text-gray-500">Transaction Code</dt>
                            <dd class="font-mono font-bold text-primary">{{ $transaction->transaction_code }}</dd>
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
                                        'cancelled' => 'Cancelled',
                                        'overdue' => 'Overdue'
                                    ]"
                                    :variants="[
                                        'draft' => 'badge-ghost',
                                        'in_progress' => 'badge-info',
                                        'completed' => 'badge-success',
                                        'cancelled' => 'badge-error',
                                        'overdue' => 'badge-warning'
                                    ]"
                                />
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm text-gray-500">Level of Urgency</dt>
                            <dd>
                                <x-status-badge 
                                    :status="$transaction->level_of_urgency" 
                                    :labels="[
                                        'normal' => 'Normal',
                                        'urgent' => 'Urgent',
                                        'highly_urgent' => 'Highly Urgent'
                                    ]"
                                    :variants="[
                                        'normal' => 'badge-ghost',
                                        'urgent' => 'badge-warning',
                                        'highly_urgent' => 'badge-error'
                                    ]"
                                />
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm text-gray-500">Current Step</dt>
                            <dd class="font-medium">{{ $transaction->current_workflow_step }} / {{ $transaction->total_workflow_steps }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm text-gray-500">Current State</dt>
                            <dd class="font-medium capitalize">{{ str_replace('_', ' ', $transaction->current_state ?? 'N/A') }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm text-gray-500">Current Department</dt>
                            <dd class="font-medium">{{ $transaction->department->name ?? 'N/A' }}</dd>
                        </div>
                        @if($transaction->assignStaff)
                            <div>
                                <dt class="text-sm text-gray-500">Assigned Staff</dt>
                                <dd class="font-medium">{{ $transaction->assignStaff->full_name }}</dd>
                            </div>
                        @endif
                        <div>
                            <dt class="text-sm text-gray-500">Created By</dt>
                            <dd class="font-medium">{{ $transaction->creator->full_name ?? 'N/A' }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm text-gray-500">Created At</dt>
                            <dd class="font-medium">{{ $transaction->created_at->format('M d, Y h:i A') }}</dd>
                        </div>
                        @if($transaction->submitted_at)
                            <div>
                                <dt class="text-sm text-gray-500">Submitted At</dt>
                                <dd class="font-medium">{{ $transaction->submitted_at->format('M d, Y h:i A') }}</dd>
                            </div>
                        @endif
                        @if($transaction->completed_at)
                            <div>
                                <dt class="text-sm text-gray-500">Completed At</dt>
                                <dd class="font-medium">{{ $transaction->completed_at->format('M d, Y h:i A') }}</dd>
                            </div>
                        @endif
                    </div>
                </x-card>

                {{-- Workflow Progress --}}
                <x-card title="Workflow Progress">
                    @if(isset($workflowProgress['steps']) && count($workflowProgress['steps']) > 0)
                        <div class="space-y-3">
                            @foreach($workflowProgress['steps'] as $index => $step)
                                @php
                                    $isCompleted = $step['status'] === 'completed';
                                    $isCurrent = $step['status'] === 'current';
                                    $isPending = $step['status'] === 'pending';
                                @endphp
                                <div class="flex items-start gap-3 p-3 rounded-lg {{ $isCurrent ? 'bg-primary/10 border border-primary' : ($isCompleted ? 'bg-green-50' : 'bg-gray-50') }}">
                                    <div class="flex-shrink-0 w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold
                                        {{ $isCompleted ? 'bg-success text-white' : ($isCurrent ? 'bg-primary text-white' : 'bg-gray-300 text-gray-600') }}">
                                        @if($isCompleted)
                                            <i data-lucide="check" class="w-4 h-4"></i>
                                        @else
                                            {{ $index + 1 }}
                                        @endif
                                    </div>
                                    <div class="flex-1">
                                        <div class="font-medium {{ $isCurrent ? 'text-primary' : '' }}">
                                            {{ $step['department_name'] ?? 'Unknown' }}
                                        </div>
                                        <div class="text-sm text-gray-500">
                                            @if($isCompleted && isset($step['completed_at']))
                                                Completed: {{ \Carbon\Carbon::parse($step['completed_at'])->format('M d, Y h:i A') }}
                                            @elseif($isCurrent)
                                                <span class="text-primary font-medium">Currently processing</span>
                                            @else
                                                Pending
                                            @endif
                                        </div>
                                    </div>
                                    @if($isCurrent)
                                        <span class="badge badge-primary badge-sm">Current</span>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-gray-500 text-center py-4">No workflow steps available.</p>
                    @endif
                </x-card>

                {{-- Available Actions --}}
                @if(count($availableActions) > 0)
                    <x-card title="Available Actions">
                        <form action="{{ route('transactions.execute-action', $transaction) }}" method="POST" id="actionForm">
                            @csrf
                            
                            <div class="form-control mb-4">
                                <label class="label">
                                    <span class="label-text">Select Action</span>
                                </label>
                                <div class="flex flex-wrap gap-2">
                                    @foreach($availableActions as $action)
                                        <label class="cursor-pointer">
                                            <input type="radio" name="action" value="{{ $action }}" class="hidden peer" required>
                                            <span class="btn peer-checked:btn-primary peer-checked:text-white
                                                {{ $action === 'approve' ? 'btn-success' : '' }}
                                                {{ $action === 'reject' ? 'btn-error' : '' }}
                                                {{ $action === 'resubmit' ? 'btn-warning' : '' }}
                                                {{ $action === 'cancel' ? 'btn-ghost' : '' }}
                                            ">
                                                @if($action === 'approve')
                                                    <i data-lucide="check" class="w-4 h-4 mr-1"></i>
                                                @elseif($action === 'reject')
                                                    <i data-lucide="x" class="w-4 h-4 mr-1"></i>
                                                @elseif($action === 'resubmit')
                                                    <i data-lucide="refresh-cw" class="w-4 h-4 mr-1"></i>
                                                @elseif($action === 'cancel')
                                                    <i data-lucide="ban" class="w-4 h-4 mr-1"></i>
                                                @endif
                                                {{ ucfirst($action) }}
                                            </span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>

                            <div class="form-control mb-4">
                                <x-form.textarea 
                                    name="remarks" 
                                    label="Remarks (Optional)" 
                                    placeholder="Add any comments or notes..."
                                    class="h-24"
                                />
                            </div>

                            <button type="submit" class="btn btn-primary">
                                <i data-lucide="send" class="w-4 h-4 mr-2"></i>
                                Submit Action
                            </button>
                        </form>
                    </x-card>
                @endif
            </div>

            {{-- Sidebar --}}
            <div class="space-y-6">
                {{-- Current Reviewer --}}
                @if($transaction->currentReviewer)
                    <x-card title="Current Reviewer">
                        <div class="space-y-3">
                            <div class="flex items-center gap-3">
                                <div class="avatar placeholder">
                                    <div class="bg-primary text-white rounded-full w-10">
                                        <span>{{ substr($transaction->currentReviewer->reviewer->full_name ?? 'U', 0, 1) }}</span>
                                    </div>
                                </div>
                                <div>
                                    <div class="font-medium">{{ $transaction->currentReviewer->reviewer->full_name ?? 'Unknown' }}</div>
                                    <div class="text-sm text-gray-500">{{ $transaction->currentReviewer->department->name ?? '' }}</div>
                                </div>
                            </div>
                            @if($transaction->currentReviewer->due_date)
                                <div class="flex items-center gap-2 text-sm {{ $transaction->currentReviewer->isOverdue() ? 'text-error' : 'text-gray-500' }}">
                                    <i data-lucide="clock" class="w-4 h-4"></i>
                                    <span>Due: {{ $transaction->currentReviewer->due_date->format('M d, Y') }}</span>
                                    @if($transaction->currentReviewer->isOverdue())
                                        <span class="badge badge-error badge-sm">Overdue</span>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </x-card>
                @endif

                {{-- Quick Links --}}
                <x-card title="Quick Links">
                    <div class="space-y-2">
                        <a href="{{ route('transactions.tracker', $transaction) }}" class="btn btn-ghost btn-block justify-start">
                            <i data-lucide="map-pin" class="w-4 h-4 mr-2"></i>
                            Track Progress
                        </a>
                        <a href="{{ route('transactions.history', $transaction) }}" class="btn btn-ghost btn-block justify-start">
                            <i data-lucide="history" class="w-4 h-4 mr-2"></i>
                            View History
                        </a>
                        <a href="{{ route('transactions.review-history', $transaction) }}" class="btn btn-ghost btn-block justify-start">
                            <i data-lucide="users" class="w-4 h-4 mr-2"></i>
                            Review History
                        </a>
                    </div>
                </x-card>

                {{-- Revision Info --}}
                @if($transaction->revision_number > 0)
                    <x-card title="Revision Info">
                        <div class="text-center">
                            <div class="text-3xl font-bold text-primary">{{ $transaction->revision_number }}</div>
                            <div class="text-sm text-gray-500">Revisions Made</div>
                        </div>
                    </x-card>
                @endif
            </div>
        </div>
    </x-container>
@endsection
