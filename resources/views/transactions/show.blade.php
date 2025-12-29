@extends('layouts.app')

@section('title', 'Transaction ' . $transaction->transaction_code)

@section('content')
    <div class="container mx-auto px-4 py-6">
        {{-- Header --}}
        <div class="mb-6">
            <div class="flex items-center gap-2 text-sm text-gray-500 mb-2">
                <a href="{{ route('transactions.index') }}" class="hover:text-primary">Transactions</a>
                <i data-lucide="chevron-right" class="w-4 h-4"></i>
                <span>{{ $transaction->transaction_code }}</span>
            </div>
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">{{ $transaction->transaction_code }}</h1>
                    <p class="text-gray-600 mt-1">{{ $transaction->workflow->transaction_name ?? 'Transaction' }}</p>
                </div>
                <div class="flex items-center gap-2">
                    @if(!$transaction->isCompleted() && !$transaction->isCancelled())
                        <a href="{{ route('transactions.edit', $transaction) }}" class="btn btn-outline btn-sm">
                            <i data-lucide="edit" class="w-4 h-4 mr-1"></i>
                            Edit
                        </a>
                    @endif
                </div>
            </div>
        </div>

        {{-- Flash Messages --}}
        @if(session('success'))
            <div class="alert alert-success mb-6">
                <i data-lucide="check-circle" class="w-5 h-5"></i>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-error mb-6">
                <i data-lucide="alert-circle" class="w-5 h-5"></i>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Main Content --}}
            <div class="lg:col-span-2 space-y-6">
                {{-- Transaction Status --}}
                <div class="card bg-base-100 shadow-md">
                    <div class="card-body">
                        <h2 class="card-title text-lg mb-4">
                            <i data-lucide="activity" class="w-5 h-5 text-primary"></i>
                            Status
                        </h2>
                        
                        <div class="flex items-center gap-4 flex-wrap">
                            <div class="flex items-center gap-2">
                                <span class="text-gray-500">Transaction Status:</span>
                                @php
                                    $statusClass = match($transaction->transaction_status) {
                                        'completed' => 'badge-success',
                                        'overdue' => 'badge-error',
                                        default => 'badge-info'
                                    };
                                @endphp
                                <span class="badge {{ $statusClass }}">
                                    {{ ucfirst(str_replace('_', ' ', $transaction->transaction_status)) }}
                                </span>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="text-gray-500">Urgency:</span>
                                @php
                                    $urgencyClass = match($transaction->level_of_urgency) {
                                        'highly_urgent' => 'badge-error',
                                        'urgent' => 'badge-warning',
                                        default => 'badge-ghost'
                                    };
                                @endphp
                                <span class="badge {{ $urgencyClass }}">
                                    {{ ucfirst(str_replace('_', ' ', $transaction->level_of_urgency)) }}
                                </span>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="text-gray-500">Current State:</span>
                                <span class="font-medium">{{ ucfirst(str_replace('_', ' ', $transaction->current_state)) }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Workflow Progress --}}
                <div class="card bg-base-100 shadow-md">
                    <div class="card-body">
                        <h2 class="card-title text-lg mb-4">
                            <i data-lucide="git-branch" class="w-5 h-5 text-primary"></i>
                            Workflow Progress
                        </h2>
                        
                        @php
                            $steps = $transaction->getWorkflowSteps();
                            $currentStep = $transaction->current_workflow_step;
                        @endphp

                        <div class="space-y-4">
                            @foreach($steps as $index => $step)
                                @php
                                    $stepNumber = $index + 1;
                                    $isCompleted = $stepNumber < $currentStep;
                                    $isCurrent = $stepNumber === $currentStep;
                                    $isPending = $stepNumber > $currentStep;
                                @endphp
                                <div class="flex items-start gap-4">
                                    {{-- Step Indicator --}}
                                    <div class="flex-shrink-0">
                                        @if($isCompleted)
                                            <span class="flex items-center justify-center w-8 h-8 rounded-full bg-success text-success-content">
                                                <i data-lucide="check" class="w-4 h-4"></i>
                                            </span>
                                        @elseif($isCurrent)
                                            <span class="flex items-center justify-center w-8 h-8 rounded-full bg-primary text-primary-content animate-pulse">
                                                {{ $stepNumber }}
                                            </span>
                                        @else
                                            <span class="flex items-center justify-center w-8 h-8 rounded-full bg-base-200 text-gray-400">
                                                {{ $stepNumber }}
                                            </span>
                                        @endif
                                    </div>

                                    {{-- Step Content --}}
                                    <div class="flex-1 pb-4 {{ !$loop->last ? 'border-l-2 border-base-300 -ml-4 pl-8' : '' }}">
                                        <div class="flex items-center justify-between">
                                            <h4 class="font-medium {{ $isPending ? 'text-gray-400' : '' }}">
                                                {{ $step['department_name'] ?? 'Department ' . $stepNumber }}
                                            </h4>
                                            @if($isCompleted)
                                                <span class="badge badge-success badge-sm">Completed</span>
                                            @elseif($isCurrent)
                                                <span class="badge badge-primary badge-sm">In Progress</span>
                                            @else
                                                <span class="badge badge-ghost badge-sm">Pending</span>
                                            @endif
                                        </div>
                                        <p class="text-sm text-gray-500 mt-1">
                                            Processing time: {{ $step['process_time_value'] ?? 1 }} {{ $step['process_time_unit'] ?? 'days' }}
                                        </p>
                                        @if(!empty($step['notes']))
                                            <p class="text-sm text-gray-400 mt-1 italic">{{ $step['notes'] }}</p>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- Available Actions --}}
                @if(!empty($availableActions) && !$transaction->isCompleted() && !$transaction->isCancelled())
                    <div class="card bg-base-100 shadow-md">
                        <div class="card-body">
                            <h2 class="card-title text-lg mb-4">
                                <i data-lucide="zap" class="w-5 h-5 text-primary"></i>
                                Actions
                            </h2>
                            
                            <form action="{{ route('transactions.execute-action', $transaction) }}" method="POST">
                                @csrf
                                <div class="space-y-4">
                                    <div class="form-control">
                                        <label class="label">
                                            <span class="label-text font-medium">Remarks (Optional)</span>
                                        </label>
                                        <textarea name="remarks" rows="2" class="textarea textarea-bordered" placeholder="Add any remarks or notes..."></textarea>
                                    </div>
                                    
                                    <div class="flex gap-2 flex-wrap">
                                        @foreach($availableActions as $action => $label)
                                            @php
                                                $btnClass = match($action) {
                                                    'approve' => 'btn-success',
                                                    'reject' => 'btn-error',
                                                    'cancel' => 'btn-ghost',
                                                    default => 'btn-primary'
                                                };
                                            @endphp
                                            <button type="submit" name="action" value="{{ $action }}" class="btn {{ $btnClass }}">
                                                {{ $label }}
                                            </button>
                                        @endforeach
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                @endif

                {{-- Transaction Logs --}}
                @if($transaction->transactionLogs->count() > 0)
                    <div class="card bg-base-100 shadow-md">
                        <div class="card-body">
                            <h2 class="card-title text-lg mb-4">
                                <i data-lucide="history" class="w-5 h-5 text-primary"></i>
                                Activity Log
                            </h2>
                            
                            <div class="overflow-x-auto">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Action</th>
                                            <th>By</th>
                                            <th>From → To</th>
                                            <th>Remarks</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($transaction->transactionLogs as $log)
                                            <tr>
                                                <td class="text-sm">{{ $log->created_at->format('M d, Y H:i') }}</td>
                                                <td>
                                                    <span class="badge badge-sm">{{ ucfirst($log->action) }}</span>
                                                </td>
                                                <td>{{ $log->actionBy->name ?? 'System' }}</td>
                                                <td class="text-sm">
                                                    {{ ucfirst(str_replace('_', ' ', $log->from_state)) }} 
                                                    → 
                                                    {{ ucfirst(str_replace('_', ' ', $log->to_state)) }}
                                                </td>
                                                <td class="text-sm text-gray-500">{{ $log->remarks ?? '-' }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            {{-- Sidebar --}}
            <div class="space-y-6">
                {{-- Transaction Details --}}
                <div class="card bg-base-100 shadow-md">
                    <div class="card-body">
                        <h2 class="card-title text-lg mb-4">
                            <i data-lucide="info" class="w-5 h-5 text-primary"></i>
                            Details
                        </h2>

                        <div class="space-y-3 text-sm">
                            <div class="flex justify-between">
                                <span class="text-gray-500">Created by</span>
                                <span class="font-medium">{{ $transaction->creator->name ?? 'Unknown' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-500">Department</span>
                                <span class="font-medium">{{ $transaction->department->name ?? 'N/A' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-500">Assigned Staff</span>
                                <span class="font-medium">{{ $transaction->assignStaff->full_name ?? 'N/A' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-500">Submitted</span>
                                <span class="font-medium">{{ $transaction->submitted_at?->format('M d, Y H:i') ?? 'N/A' }}</span>
                            </div>
                            @if($transaction->completed_at)
                                <div class="flex justify-between">
                                    <span class="text-gray-500">Completed</span>
                                    <span class="font-medium">{{ $transaction->completed_at->format('M d, Y H:i') }}</span>
                                </div>
                            @endif
                            <div class="flex justify-between">
                                <span class="text-gray-500">Revision</span>
                                <span class="font-medium">{{ $transaction->revision_number }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Current Reviewer --}}
                @if($transaction->currentReviewer)
                    <div class="card bg-base-100 shadow-md">
                        <div class="card-body">
                            <h2 class="card-title text-lg mb-4">
                                <i data-lucide="user" class="w-5 h-5 text-primary"></i>
                                Current Reviewer
                            </h2>

                            <div class="space-y-3 text-sm">
                                <div class="flex justify-between">
                                    <span class="text-gray-500">Reviewer</span>
                                    <span class="font-medium">{{ $transaction->currentReviewer->reviewer->name ?? 'N/A' }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-500">Department</span>
                                    <span class="font-medium">{{ $transaction->currentReviewer->department->name ?? 'N/A' }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-500">Due Date</span>
                                    <span class="font-medium {{ $transaction->currentReviewer->is_overdue ? 'text-error' : '' }}">
                                        {{ $transaction->currentReviewer->due_date?->format('M d, Y H:i') ?? 'N/A' }}
                                    </span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-500">Status</span>
                                    <span class="badge badge-sm">{{ ucfirst(str_replace('_', ' ', $transaction->currentReviewer->status)) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Workflow Info --}}
                <div class="card bg-base-100 shadow-md">
                    <div class="card-body">
                        <h2 class="card-title text-lg mb-4">
                            <i data-lucide="workflow" class="w-5 h-5 text-primary"></i>
                            Workflow
                        </h2>

                        <div class="space-y-3 text-sm">
                            <div class="flex justify-between">
                                <span class="text-gray-500">Workflow ID</span>
                                <span class="font-mono font-medium">{{ $transaction->workflow_id }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-500">Name</span>
                                <span class="font-medium">{{ $transaction->workflow->transaction_name ?? 'N/A' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-500">Total Steps</span>
                                <span class="font-medium">{{ $transaction->total_workflow_steps }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-500">Current Step</span>
                                <span class="font-medium">{{ $transaction->current_workflow_step }} of {{ $transaction->total_workflow_steps }}</span>
                            </div>
                        </div>

                        {{-- Progress Bar --}}
                        <div class="mt-4">
                            @php
                                $progress = $transaction->total_workflow_steps > 0 
                                    ? round(($transaction->current_workflow_step / $transaction->total_workflow_steps) * 100) 
                                    : 0;
                            @endphp
                            <div class="flex justify-between text-xs text-gray-500 mb-1">
                                <span>Progress</span>
                                <span>{{ $progress }}%</span>
                            </div>
                            <progress class="progress progress-primary w-full" value="{{ $progress }}" max="100"></progress>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
