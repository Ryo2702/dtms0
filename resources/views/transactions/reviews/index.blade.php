@extends('layouts.app')

@section('title', 'Reviews')

@section('content')
    <x-container>
        <div class="mb-6">
            <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4">
                <div>
                    <h1 class="text-3xl font-bold">My Reviews</h1>
                    <p class="text-gray-600 mt-1">Transactions received for your review</p>
                </div>
                <div class="flex gap-2">
                    <a href="{{ route('transactions.index') }}" class="btn btn-outline">
                        <i data-lucide="arrow-left" class="w-4 h-4 mr-2"></i>
                        Back to Transactions
                    </a>
                    <a href="{{ route('transactions.reviews.overdue') }}" class="btn btn-error btn-outline">
                        <i data-lucide="alert-triangle" class="w-4 h-4 mr-2"></i>
                        Overdue Reviews
                    </a>
                </div>
            </div>
        </div>

        {{-- Tabs --}}
        <div class="bg-white rounded-lg shadow mb-6 border border-gray-200">
            <div class="flex border-b border-gray-200">
                <a href="{{ route('transactions.reviews.index', ['tab' => 'pending']) }}"
                    class="flex items-center gap-2 px-6 py-3 text-sm font-medium border-b-2 transition-colors {{ $tab === 'pending' ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    <i data-lucide="inbox" class="w-4 h-4"></i>
                    <span>Pending Reviews</span>
                    @if ($stats['pending'] > 0)
                        <span class="inline-flex items-center justify-center px-2 py-0.5 text-xs font-bold leading-none text-white bg-blue-600 rounded-full">{{ $stats['pending'] }}</span>
                    @endif
                </a>
                <a href="{{ route('transactions.reviews.index', ['tab' => 'resubmissions']) }}"
                    class="flex items-center gap-2 px-6 py-3 text-sm font-medium border-b-2 transition-colors {{ $tab === 'resubmissions' ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    <i data-lucide="refresh-cw" class="w-4 h-4"></i>
                    <span>Resubmissions</span>
                    @if ($stats['resubmissions'] > 0)
                        <span class="inline-flex items-center justify-center px-2 py-0.5 text-xs font-bold leading-none text-white bg-cyan-600 rounded-full">{{ $stats['resubmissions'] }}</span>
                    @endif
                </a>
                <a href="{{ route('transactions.reviews.index', ['tab' => 'reviewed']) }}"
                    class="flex items-center gap-2 px-6 py-3 text-sm font-medium border-b-2 transition-colors {{ $tab === 'reviewed' ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    <i data-lucide="check-circle" class="w-4 h-4"></i>
                    <span>Already Reviewed</span>
                </a>
            </div>
        </div>

        {{-- Date Filter Section --}}
        <x-card class="mb-6">
            <div class="flex items-center gap-2 mb-4 pb-4 border-b">
                <i data-lucide="list-filter" class="w-5 h-5 text-primary"></i>
                <h3 class="text-lg font-semibold">Filter Reviews</h3>
            </div>
            <form method="GET" action="{{ route('transactions.reviews.index') }}" class="flex flex-col md:flex-row gap-4 items-end">
                <input type="hidden" name="tab" value="{{ $tab }}" />
                <div class="flex-1">
                    <label for="review_date_from" class="label">
                        <span class="label-text">From Date</span>
                    </label>
                    <input type="date" id="review_date_from" name="date_from" 
                           value="{{ request('date_from') }}"
                           class="input input-bordered w-full" />
                </div>
                <div class="flex-1">
                    <label for="review_date_to" class="label">
                        <span class="label-text">To Date</span>
                    </label>
                    <input type="date" id="review_date_to" name="date_to" 
                           value="{{ request('date_to') }}"
                           class="input input-bordered w-full" />
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i data-lucide="search" class="w-4 h-4 mr-2"></i>
                        Filter
                    </button>
                    @if(request('date_from') || request('date_to'))
                        <a href="{{ route('transactions.reviews.index', ['tab' => $tab]) }}" class="btn btn-outline">
                            <i data-lucide="x" class="w-4 h-4"></i>
                        </a>
                    @endif
                </div>
            </form>
        </x-card>

        {{-- Content based on active tab --}}
        @if ($tab === 'pending')
            {{-- Pending Reviews --}}
            <x-card>
                <div class="p-4 border-b">
                    <h2 class="text-lg font-semibold flex items-center gap-2">
                        <i data-lucide="inbox" class="w-5 h-5 text-primary"></i>
                        Transactions Awaiting Your Review
                    </h2>
                    <p class="text-sm text-gray-500 mt-1">These transactions have been sent to your department for review
                    </p>
                </div>

                @if ($pendingReviews->isEmpty())
                    <div class="p-8 text-center text-gray-500">
                        <i data-lucide="check-circle" class="w-12 h-12 mx-auto mb-4 text-success"></i>
                        <p class="text-lg font-medium">All caught up!</p>
                        <p class="text-sm">No pending reviews at the moment.</p>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="table w-full">
                            <thead class="bg-primary text-white">
                                <tr>
                                    <th class="px-4 py-3">Transaction</th>
                                    <th class="px-4 py-3">Workflow</th>
                                    <th class="px-4 py-3">Origin Department</th>
                                    <th class="px-4 py-3">From</th>
                                    <th class="px-4 py-3">Current Step</th>
                                    <th class="px-4 py-3">Urgency</th>
                                    <th class="px-4 py-3">Due Date</th>
                                    <th class="px-4 py-3">Receiving Status</th>
                                    <th class="px-4 py-3">Review Status</th>
                                    <th class="px-4 py-3">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($pendingReviews as $review)
                                    <tr class="hover:bg-gray-50 {{ $review->isOverdue() ? 'bg-red-50' : '' }}">
                                        <td class="px-4 py-3">
                                            <a href="{{ route('transactions.show', $review->transaction) }}"
                                                class="font-mono font-bold text-primary hover:underline">
                                                {{ $review->transaction->transaction_code }}
                                            </a>
                                            @if ($review->iteration_number > 1)
                                                <span class="badge badge-info badge-sm ml-1">Resubmission
                                                    #{{ $review->iteration_number }}</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3">
                                            <div class="font-medium">
                                                {{ $review->transaction->workflow->transaction_name ?? 'N/A' }}</div>
                                        </td>
                                        <td class="px-4 py-3">
                                            <span class="badge badge-outline">
                                                <i data-lucide="building-2" class="w-3 h-3 mr-1"></i>
                                                {{ $review->transaction->originDepartment->name ?? 'N/A' }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3">
                                            @php
                                                // Determine who forwarded this transaction
                                                $forwarder = null;
                                                $forwarderDepartment = null;
                                                
                                                if ($review->previousReviewer) {
                                                    // If previous_reviewer_id is set, use it
                                                    $forwarder = $review->previousReviewer;
                                                    $forwarderDepartment = $review->previousReviewer->department;
                                                } else {
                                                    // Find the last approved reviewer before this one
                                                    $lastApprovedReviewer = $review->transaction->reviewers()
                                                        ->where('status', 'approved')
                                                        ->where('created_at', '<', $review->created_at)
                                                        ->latest('reviewed_at')
                                                        ->first();
                                                    
                                                    if ($lastApprovedReviewer) {
                                                        $forwarder = $lastApprovedReviewer->reviewer;
                                                        $forwarderDepartment = $lastApprovedReviewer->department;
                                                    } else {
                                                        // First step - from creator
                                                        $forwarder = $review->transaction->creator;
                                                        $forwarderDepartment = $review->transaction->originDepartment;
                                                    }
                                                }
                                                
                                                $forwarderName = $forwarder->name ?? 'Unknown';
                                                $forwarderDeptName = $forwarderDepartment->name ?? 'N/A';
                                            @endphp
                                            <div class="flex items-center gap-2">
                                                <div class="avatar">
                                                    <div class="w-8 rounded-full">
                                                        <img src="https://ui-avatars.com/api/?name={{ urlencode($forwarderName) }}&background=random"
                                                            alt="" />
                                                    </div>
                                                </div>
                                                <div>
                                                    <div class="text-sm font-medium">
                                                        {{ $forwarderName }}</div>
                                                    <div class="text-xs text-gray-500">
                                                        <span class="badge badge-outline badge-xs">
                                                            <i data-lucide="building-2" class="w-3 h-3 mr-1"></i>
                                                            {{ $forwarderDeptName }}
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3">
                                            @if ($review->transaction->current_workflow_step !== null)
                                                @php
                                                    $currentStepIndex = $review->transaction->current_workflow_step - 1;
                                                    $workflowSteps = $review->transaction->workflow_snapshot['steps'] ?? $review->transaction->workflow->getWorkflowSteps();
                                                    $currentStepData = $workflowSteps[$currentStepIndex] ?? null;
                                                    $currentStepDept = $currentStepData['department_name'] ?? 'Unknown';
                                                @endphp
                                                <div class="text-sm font-medium">Step
                                                    {{ $review->transaction->current_workflow_step }}</div>
                                                <div class="text-xs text-gray-500">
                                                    {{ $currentStepDept }}</div>
                                            @else
                                                <span class="text-gray-400">-</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3">
                                            @if ($review->transaction->urgency === 'high')
                                                <span class="badge badge-error">High</span>
                                            @elseif($review->transaction->urgency === 'medium')
                                                <span class="badge badge-warning">Medium</span>
                                            @elseif($review->transaction->urgency === 'low')
                                                <span class="badge badge-info">Low</span>
                                            @else
                                                <span class="badge badge-ghost">Normal</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3">
                                            @if ($review->due_date)
                                                <div
                                                    class="flex items-center gap-2 {{ $review->isOverdue() ? 'text-error' : ($review->due_date->isToday() ? 'text-warning' : '') }}">
                                                    <i data-lucide="calendar" class="w-4 h-4"></i>
                                                    {{ $review->due_date->format('M d, Y') }}
                                                    @if ($review->isOverdue())
                                                        <span class="badge badge-error badge-sm">Overdue</span>
                                                    @elseif($review->due_date->isToday())
                                                        <span class="badge badge-warning badge-sm">Today</span>
                                                    @endif
                                                </div>
                                            @else
                                                <span class="text-gray-400">No deadline</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3">
                                            @if ($review->received_status === 'received')
                                                <span class="badge badge-success">
                                                    <i data-lucide="package-check" class="w-3 h-3 mr-1"></i>
                                                    Received
                                                </span>
                                                @if ($review->receivedBy)
                                                    <div class="text-xs text-gray-500 mt-1">
                                                        by {{ $review->receivedBy->name }}
                                                    </div>
                                                @endif
                                            @elseif($review->received_status === 'not_received')
                                                <span class="badge badge-error">
                                                    <i data-lucide="package-x" class="w-3 h-3 mr-1"></i>
                                                    Not Received
                                                </span>
                                                @if ($review->receivedBy)
                                                    <div class="text-xs text-gray-500 mt-1">
                                                        by {{ $review->receivedBy->name }}
                                                    </div>
                                                @endif
                                            @else
                                                <span class="badge badge-warning">
                                                    <i data-lucide="package" class="w-3 h-3 mr-1"></i>
                                                    Pending
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3">
                                            @if ($review->status === 'pending')
                                                <span class="badge badge-warning">Pending Review</span>
                                            @elseif($review->status === 'approved')
                                                <span class="badge badge-success">
                                                    <i data-lucide="check" class="w-3 h-3 mr-1"></i>
                                                    Approved
                                                </span>
                                            @elseif($review->status === 'rejected')
                                                <span class="badge badge-error">
                                                    <i data-lucide="x" class="w-3 h-3 mr-1"></i>
                                                    Rejected
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3">
                                            <div class="flex gap-1">
                                                @if ($review->received_status === 'pending')
                                                    @if (auth()->user()->isHead() || auth()->user()->type === 'Staff')
                                                        @if ($review->iteration_number > 1)
                                                            {{-- Resubmit button for resubmissions --}}
                                                            <button type="button"
                                                                onclick="showResubmitModal('{{ $review->id }}', '{{ $review->transaction->transaction_code }}', {{ $review->iteration_number }})"
                                                                class="btn btn-sm btn-info" title="Mark as Resubmitted">
                                                                <i data-lucide="refresh-cw" class="w-4 h-4 mr-1"></i>
                                                                Resubmit
                                                            </button>
                                                        @else
                                                            {{-- Regular Receive/Not Received for first submissions --}}
                                                            <button type="button"
                                                                onclick="showReceiveModal('{{ $review->id }}', '{{ $review->transaction->transaction_code }}')"
                                                                class="btn btn-sm btn-success" title="Mark as Received">
                                                                <i data-lucide="package-check" class="w-4 h-4 mr-1"></i>
                                                                Receive
                                                            </button>
                                                            <button type="button"
                                                                onclick="showNotReceivedModal('{{ $review->id }}', '{{ $review->transaction->transaction_code }}')"
                                                                class="btn btn-sm btn-error" title="Mark as Not Received">
                                                                <i data-lucide="package-x" class="w-4 h-4 mr-1"></i>
                                                                Not Received
                                                            </button>
                                                        @endif
                                                    @else
                                                        <span class="badge badge-warning">
                                                            <i data-lucide="lock" class="w-3 h-3 mr-1"></i>
                                                            Awaiting Receipt by Head/Staff
                                                        </span>
                                                    @endif
                                                @elseif($review->received_status === 'not_received')
                                                    @if (auth()->user()->isHead() || auth()->user()->type === 'Staff')
                                                        <button type="button"
                                                            onclick="showReceiveModal('{{ $review->id }}', '{{ $review->transaction->transaction_code }}')"
                                                            class="btn btn-sm btn-success" title="Mark as Received (Arrived Late)">
                                                            <i data-lucide="package-check" class="w-4 h-4 mr-1"></i>
                                                            Mark Received
                                                        </button>
                                                    @endif
                                                @elseif($review->status === 'pending' && $review->received_status === 'received')
                                                    <a href="{{ route('transactions.reviews.review', $review) }}"
                                                        class="btn btn-sm btn-primary" title="Review">
                                                        <i data-lucide="clipboard-check" class="w-4 h-4 mr-1"></i>
                                                        Review
                                                    </a>
                                                @endif
                                                <a href="{{ route('transactions.reviews.show', $review) }}"
                                                    class="btn btn-sm btn-ghost" title="View Review Details">
                                                    <i data-lucide="eye" class="w-4 h-4"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </x-card>
        @elseif($tab === 'resubmissions')
            {{-- Resubmissions --}}
            <x-card>
                <div class="p-4 border-b">
                    <h2 class="text-lg font-semibold flex items-center gap-2">
                        <i data-lucide="refresh-cw" class="w-5 h-5 text-info"></i>
                        Resubmitted Transactions
                    </h2>
                    <p class="text-sm text-gray-500 mt-1">Transactions that were previously rejected and resubmitted for
                        re-review</p>
                </div>

                @if ($resubmissions->isEmpty())
                    <div class="p-8 text-center text-gray-500">
                        <i data-lucide="inbox" class="w-12 h-12 mx-auto mb-4"></i>
                        <p class="text-lg font-medium">No resubmissions</p>
                        <p class="text-sm">No transactions have been resubmitted for re-review.</p>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="table w-full">
                            <thead class="bg-info text-white">
                                <tr>
                                    <th class="px-4 py-3">Transaction Number</th>
                                    <th class="px-4 py-3">Workflow</th>
                                    <th class="px-4 py-3">Origin Department</th>
                                    <th class="px-4 py-3">From</th>
                                    <th class="px-4 py-3">Current Step</th>
                                    <th class="px-4 py-3">Urgency</th>
                                    <th class="px-4 py-3">Iteration</th>
                                    <th class="px-4 py-3">Previous Review</th>
                                    <th class="px-4 py-3">Due Date</th>
                                    <th class="px-4 py-3">Receiving Status</th>
                                    <th class="px-4 py-3">Review Status</th>
                                    <th class="px-4 py-3">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($resubmissions as $review)
                                    <tr class="hover:bg-gray-50 {{ $review->isOverdue() ? 'bg-red-50' : '' }}">
                                        <td class="px-4 py-3">
                                            <a href="{{ route('transactions.show', $review->transaction) }}"
                                                class="font-mono font-bold text-primary hover:underline">
                                                {{ $review->transaction->transaction_code }}
                                            </a>
                                        </td>
                                        <td class="px-4 py-3">
                                            <div class="font-medium">
                                                {{ $review->transaction->workflow->transaction_name ?? 'N/A' }}</div>
                                        </td>
                                        <td class="px-4 py-3">
                                            <span class="badge badge-outline">
                                                <i data-lucide="building-2" class="w-3 h-3 mr-1"></i>
                                                {{ $review->transaction->originDepartment->name ?? 'N/A' }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3">
                                            @php
                                                // Determine who forwarded this transaction
                                                $forwarder = null;
                                                $forwarderDepartment = null;
                                                
                                                if ($review->previousReviewer) {
                                                    // If previous_reviewer_id is set, use it
                                                    $forwarder = $review->previousReviewer;
                                                    $forwarderDepartment = $review->previousReviewer->department;
                                                } else {
                                                    // Find the last approved reviewer before this one
                                                    $lastApprovedReviewer = $review->transaction->reviewers()
                                                        ->where('status', 'approved')
                                                        ->where('created_at', '<', $review->created_at)
                                                        ->latest('reviewed_at')
                                                        ->first();
                                                    
                                                    if ($lastApprovedReviewer) {
                                                        $forwarder = $lastApprovedReviewer->reviewer;
                                                        $forwarderDepartment = $lastApprovedReviewer->department;
                                                    } else {
                                                        // First step or resubmission - from creator
                                                        $forwarder = $review->transaction->creator;
                                                        $forwarderDepartment = $review->transaction->originDepartment;
                                                    }
                                                }
                                                
                                                $forwarderName = $forwarder->name ?? 'Unknown';
                                                $forwarderDeptName = $forwarderDepartment->name ?? 'N/A';
                                            @endphp
                                            <div class="flex items-center gap-2">
                                                <div class="avatar">
                                                    <div class="w-8 rounded-full">
                                                        <img src="https://ui-avatars.com/api/?name={{ urlencode($forwarderName) }}&background=random"
                                                            alt="" />
                                                    </div>
                                                </div>
                                                <div>
                                                    <div class="text-sm font-medium">
                                                        {{ $forwarderName }}</div>
                                                    <div class="text-xs text-gray-500">
                                                        <span class="badge badge-outline badge-xs">
                                                            <i data-lucide="building-2" class="w-3 h-3 mr-1"></i>
                                                            {{ $forwarderDeptName }}
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3">
                                            @if ($review->transaction->current_workflow_step !== null)
                                                @php
                                                    $currentStepIndex = $review->transaction->current_workflow_step - 1;
                                                    $workflowSteps = $review->transaction->workflow_snapshot['steps'] ?? $review->transaction->workflow->getWorkflowSteps();
                                                    $currentStepData = $workflowSteps[$currentStepIndex] ?? null;
                                                    $currentStepDept = $currentStepData['department_name'] ?? 'Unknown';
                                                @endphp
                                                <div class="text-sm font-medium">Step
                                                    {{ $review->transaction->current_workflow_step }}</div>
                                                <div class="text-xs text-gray-500">
                                                    {{ $currentStepDept }}</div>
                                            @else
                                                <span class="text-gray-400">-</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3">
                                            @if ($review->transaction->urgency === 'high')
                                                <span class="badge badge-error">High</span>
                                            @elseif($review->transaction->urgency === 'medium')
                                                <span class="badge badge-warning">Medium</span>
                                            @elseif($review->transaction->urgency === 'low')
                                                <span class="badge badge-info">Low</span>
                                            @else
                                                <span class="badge badge-ghost">Normal</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3">
                                            <span class="badge badge-info">Resubmission
                                                #{{ $review->iteration_number }}</span>
                                        </td>
                                        <td class="px-4 py-3">
                                            @php
                                                // Check if current user rejected this in a previous iteration
                                                $myPreviousRejection = $review->transaction->reviewers()
                                                    ->where('reviewer_id', auth()->id())
                                                    ->where('status', 'rejected')
                                                    ->where('iteration_number', '<', $review->iteration_number)
                                                    ->orderBy('iteration_number', 'desc')
                                                    ->first();
                                            @endphp
                                            
                                            @if($myPreviousRejection)
                                                <div class="space-y-1">
                                                    <div class="flex items-center gap-1">
                                                        <i data-lucide="alert-circle" class="w-3 h-3 text-red-600"></i>
                                                        <span class="text-xs font-semibold text-red-700">You rejected (Iteration #{{ $myPreviousRejection->iteration_number }})</span>
                                                    </div>
                                                    @if($myPreviousRejection->rejection_reason)
                                                        <div class="text-xs text-gray-700 bg-red-50 p-2 rounded border border-red-200">
                                                            <span class="font-medium text-red-800">Reason:</span> {{ Str::limit($myPreviousRejection->rejection_reason, 60) }}
                                                        </div>
                                                    @endif
                                                </div>
                                            @else
                                                <span class="text-xs text-gray-400">N/A</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3">
                                            @if ($review->due_date)
                                                <div
                                                    class="flex items-center gap-2 {{ $review->isOverdue() ? 'text-error' : ($review->due_date->isToday() ? 'text-warning' : '') }}">
                                                    <i data-lucide="calendar" class="w-4 h-4"></i>
                                                    {{ $review->due_date->format('M d, Y') }}
                                                    @if ($review->isOverdue())
                                                        <span class="badge badge-error badge-sm">Overdue</span>
                                                    @elseif($review->due_date->isToday())
                                                        <span class="badge badge-warning badge-sm">Today</span>
                                                    @endif
                                                </div>
                                            @else
                                                <span class="text-gray-400">No deadline</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3">
                                            @if ($review->received_status === 'received')
                                                <span class="badge badge-success">
                                                    <i data-lucide="package-check" class="w-3 h-3 mr-1"></i>
                                                    Received
                                                </span>
                                                @if ($review->receivedBy)
                                                    <div class="text-xs text-gray-500 mt-1">
                                                        by {{ $review->receivedBy->name }}
                                                    </div>
                                                @endif
                                            @elseif($review->received_status === 'not_received')
                                                <span class="badge badge-error">
                                                    <i data-lucide="package-x" class="w-3 h-3 mr-1"></i>
                                                    Not Received
                                                </span>
                                                @if ($review->receivedBy)
                                                    <div class="text-xs text-gray-500 mt-1">
                                                        by {{ $review->receivedBy->name }}
                                                    </div>
                                                @endif
                                            @else
                                                <span class="badge badge-warning">
                                                    <i data-lucide="package" class="w-3 h-3 mr-1"></i>
                                                    Pending
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3">
                                            @if ($review->status === 'pending')
                                                <span class="badge badge-warning">Pending Review</span>
                                            @elseif($review->status === 'approved')
                                                <span class="badge badge-success">
                                                    <i data-lucide="check" class="w-3 h-3 mr-1"></i>
                                                    Approved
                                                </span>
                                            @elseif($review->status === 'rejected')
                                                <span class="badge badge-error">
                                                    <i data-lucide="x" class="w-3 h-3 mr-1"></i>
                                                    Rejected
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3">
                                            <div class="flex gap-1">
                                                @if ($review->received_status === 'pending')
                                                    @if (auth()->user()->isHead() || auth()->user()->type === 'Staff')
                                                        <button type="button"
                                                            onclick="showResubmitModal('{{ $review->id }}', '{{ $review->transaction->transaction_code }}', {{ $review->iteration_number }})"
                                                            class="px-3 py-2 text-sm font-medium bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors" title="Mark as Resubmitted">
                                                            <i data-lucide="refresh-cw" class="w-4 h-4 mr-1"></i>
                                                            Resubmit
                                                        </button>
                                                    @else
                                                        <span class="badge badge-warning">
                                                            <i data-lucide="lock" class="w-3 h-3 mr-1"></i>
                                                            Awaiting Resubmission by Head/Staff
                                                        </span>
                                                    @endif
                                                @elseif($review->received_status === 'not_received')
                                                    @if (auth()->user()->isHead() || auth()->user()->type === 'Staff')
                                                        <button type="button"
                                                            onclick="showResubmitModal('{{ $review->id }}', '{{ $review->transaction->transaction_code }}', {{ $review->iteration_number }})"
                                                            class="px-3 py-2 text-sm font-medium bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors" title="Mark as Resubmitted">
                                                            <i data-lucide="refresh-cw" class="w-4 h-4 mr-1"></i>
                                                            Resubmit
                                                        </button>
                                                    @endif
                                                @elseif($review->status === 'pending' && $review->received_status === 'received')
                                                    <a href="{{ route('transactions.reviews.review', $review) }}"
                                                        class="px-3 py-2 text-sm font-medium bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors" title="Re-Review">
                                                        <i data-lucide="clipboard-check" class="w-4 h-4 mr-1"></i>
                                                        Re-Review
                                                    </a>
                                                @endif
                                                <a href="{{ route('transactions.reviews.show', $review) }}"
                                                    class="px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 rounded-lg transition-colors" title="View Review Details">
                                                    <i data-lucide="eye" class="w-4 h-4\"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </x-card>
        @elseif($tab === 'reviewed')
            {{-- Already Reviewed --}}
            <x-card>
                <div class="p-4 border-b">
                    <h2 class="text-lg font-semibold flex items-center gap-2">
                        <i data-lucide="check-circle" class="w-5 h-5 text-success"></i>
                        Transactions You've Reviewed
                    </h2>
                    <p class="text-sm text-gray-500 mt-1">History of transactions you have approved or rejected</p>
                </div>

                @if ($reviewedByMe->isEmpty())
                    <div class="p-8 text-center text-gray-500">
                        <i data-lucide="file-text" class="w-12 h-12 mx-auto mb-4"></i>
                        <p class="text-lg font-medium">No review history</p>
                        <p class="text-sm">You haven't reviewed any transactions yet.</p>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="table w-full">
                            <thead class="bg-success text-white">
                                <tr>
                                    <th class="px-4 py-3">Transaction</th>
                                    <th class="px-4 py-3">Workflow</th>
                                    <th class="px-4 py-3">Origin Department</th>
                                    <th class="px-4 py-3">From</th>
                                    <th class="px-4 py-3">Current Step</th>
                                    <th class="px-4 py-3">Urgency</th>
                                    <th class="px-4 py-3">Reviewed At</th>
                                    <th class="px-4 py-3">Receiving Status</th>
                                    <th class="px-4 py-3">Review Status</th>
                                    <th class="px-4 py-3">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($reviewedByMe as $review)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-4 py-3">
                                            <a href="{{ route('transactions.show', $review->transaction) }}"
                                                class="font-mono font-bold text-primary hover:underline">
                                                {{ $review->transaction->transaction_code }}
                                            </a>
                                            @if ($review->iteration_number > 1)
                                                <span class="badge badge-info badge-sm ml-1">Resubmission
                                                    #{{ $review->iteration_number }}</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3">
                                            <div class="font-medium">
                                                {{ $review->transaction->workflow->transaction_name ?? 'N/A' }}</div>
                                        </td>
                                        <td class="px-4 py-3">
                                            <span class="badge badge-outline">
                                                <i data-lucide="building-2" class="w-3 h-3 mr-1"></i>
                                                {{ $review->transaction->originDepartment->name ?? 'N/A' }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3">
                                            @php
                                                // Determine who forwarded this transaction
                                                $forwarder = null;
                                                $forwarderDepartment = null;
                                                
                                                if ($review->previousReviewer) {
                                                    // If previous_reviewer_id is set, use it
                                                    $forwarder = $review->previousReviewer;
                                                    $forwarderDepartment = $review->previousReviewer->department;
                                                } else {
                                                    // Find the last approved reviewer before this one
                                                    $lastApprovedReviewer = $review->transaction->reviewers()
                                                        ->where('status', 'approved')
                                                        ->where('created_at', '<', $review->created_at)
                                                        ->latest('reviewed_at')
                                                        ->first();
                                                    
                                                    if ($lastApprovedReviewer) {
                                                        $forwarder = $lastApprovedReviewer->reviewer;
                                                        $forwarderDepartment = $lastApprovedReviewer->department;
                                                    } else {
                                                        // First step - from creator
                                                        $forwarder = $review->transaction->creator;
                                                        $forwarderDepartment = $review->transaction->originDepartment;
                                                    }
                                                }
                                                
                                                $forwarderName = $forwarder->name ?? 'Unknown';
                                                $forwarderDeptName = $forwarderDepartment->name ?? 'N/A';
                                            @endphp
                                            <div class="flex items-center gap-2">
                                                <div class="avatar">
                                                    <div class="w-8 rounded-full">
                                                        <img src="https://ui-avatars.com/api/?name={{ urlencode($forwarderName) }}&background=random"
                                                            alt="" />
                                                    </div>
                                                </div>
                                                <div>
                                                    <div class="text-sm font-medium">
                                                        {{ $forwarderName }}</div>
                                                    <div class="text-xs text-gray-500">
                                                        <span class="badge badge-outline badge-xs">
                                                            <i data-lucide="building-2" class="w-3 h-3 mr-1"></i>
                                                            {{ $forwarderDeptName }}
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3">
                                            @if ($review->transaction->current_workflow_step !== null)
                                                @php
                                                    $currentStepIndex = $review->transaction->current_workflow_step - 1;
                                                    $workflowSteps = $review->transaction->workflow_snapshot['steps'] ?? $review->transaction->workflow->getWorkflowSteps();
                                                    $currentStepData = $workflowSteps[$currentStepIndex] ?? null;
                                                    $currentStepDept = $currentStepData['department_name'] ?? 'Unknown';
                                                @endphp
                                                <div class="text-sm font-medium">Step
                                                    {{ $review->transaction->current_workflow_step }}</div>
                                                <div class="text-xs text-gray-500">
                                                    {{ $currentStepDept }}</div>
                                            @else
                                                <span class="text-gray-400">-</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3">
                                            @if ($review->transaction->urgency === 'high')
                                                <span class="badge badge-error">High</span>
                                            @elseif($review->transaction->urgency === 'medium')
                                                <span class="badge badge-warning">Medium</span>
                                            @elseif($review->transaction->urgency === 'low')
                                                <span class="badge badge-info">Low</span>
                                            @else
                                                <span class="badge badge-ghost">Normal</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3">
                                            @if ($review->reviewed_at)
                                                <div class="text-sm">{{ $review->reviewed_at->format('M d, Y') }}</div>
                                                <div class="text-xs text-gray-500">
                                                    {{ $review->reviewed_at->format('h:i A') }}</div>
                                            @else
                                                <span class="text-gray-400">-</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3">
                                            @if ($review->received_status === 'received')
                                                <span class="badge badge-success badge-sm">
                                                    <i data-lucide="package-check" class="w-3 h-3 mr-1"></i>
                                                    Received
                                                </span>
                                            @elseif($review->received_status === 'not_received')
                                                <span class="badge badge-error badge-sm">
                                                    <i data-lucide="package-x" class="w-3 h-3 mr-1"></i>
                                                    Not Received
                                                </span>
                                            @else
                                                <span class="badge badge-warning badge-sm">
                                                    <i data-lucide="package" class="w-3 h-3 mr-1"></i>
                                                    Pending
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3">
                                            @if ($review->status === 'approved')
                                                <span class="badge badge-success">
                                                    <i data-lucide="check" class="w-3 h-3 mr-1"></i>
                                                    Approved
                                                </span>
                                            @elseif($review->status === 'rejected')
                                                <div>
                                                    <span class="badge badge-error">
                                                        <i data-lucide="x" class="w-3 h-3 mr-1"></i>
                                                        Rejected
                                                    </span>
                                                    @if ($review->rejection_reason)
                                                        <div class="text-xs text-gray-500 mt-1 max-w-xs truncate"
                                                            title="{{ $review->rejection_reason }}">
                                                            {{ Str::limit($review->rejection_reason, 50) }}
                                                        </div>
                                                    @endif
                                                </div>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3">
                                            <a href="{{ route('transactions.reviews.show', $review) }}"
                                                class="px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 rounded-lg transition-colors" title="View Review Details">
                                                <i data-lucide="eye" class="w-4 h-4 mr-1"></i>
                                                View
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </x-card>
        @endif
    </x-container>

    {{-- Receive Confirmation Modal --}}
    <x-modal id="receiveModal" title="Confirm Receipt of Transaction" size="md">
        <form id="receiveForm" method="POST">
            @csrf
            <input type="hidden" name="received_status" value="received">

            <div class="space-y-4">
                <div class="flex items-center gap-3 p-4 bg-green-50 rounded-lg border border-green-200">
                    <i data-lucide="package-check" class="w-8 h-8 text-green-600"></i>
                    <div class="flex-1">
                        <p class="font-semibold text-green-900">Mark as Received</p>
                        <p class="text-sm text-green-700">Transaction: <span id="receiveTransactionCode"
                                class="font-mono font-bold"></span></p>
                    </div>
                </div>

                <div class="p-4 bg-blue-50 rounded-lg border border-blue-200">
                    <div class="flex items-start gap-3">
                        <i data-lucide="user-check" class="w-5 h-5 text-blue-600 flex-shrink-0 mt-0.5"></i>
                        <div class="flex-1">
                            <p class="text-sm font-semibold text-blue-900 mb-1">Received By:</p>
                            <div class="flex items-center gap-2">
                                <div class="avatar placeholder">
                                    <div class="bg-blue-600 text-white rounded-full w-8">
                                        <span class="text-xs">{{ substr(auth()->user()->name ?? 'U', 0, 1) }}</span>
                                    </div>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-blue-900">{{ auth()->user()->name }}</p>
                                    <p class="text-xs text-blue-700">{{ auth()->user()->email }}</p>
                                    @if (auth()->user()->isHead())
                                        <span class="badge badge-sm bg-blue-600 text-white border-0 mt-0.5">Department
                                            Head</span>
                                    @elseif(auth()->user()->type === 'Staff')
                                        <span class="badge badge-sm bg-blue-600 text-white border-0 mt-0.5">Staff</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="p-4 bg-yellow-50 rounded-lg border border-yellow-200">
                    <div class="flex gap-2">
                        <i data-lucide="alert-triangle" class="w-5 h-5 text-yellow-600 flex-shrink-0 mt-0.5"></i>
                        <div class="text-sm text-yellow-800">
                            <p class="font-semibold mb-1">Important:</p>
                            <ul class="list-disc list-inside space-y-1">
                                <li>The review timer will start immediately after receiving</li>
                                <li>You must complete the review before the deadline</li>
                                <li>This action cannot be undone</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <p class="text-sm text-gray-600">
                    By clicking "Confirm Receipt", you acknowledge that you have received this transaction and the review
                    timer will begin.
                </p>
            </div>

            <x-slot name="actions">
                <button type="button" class="px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 rounded-lg transition-colors close-receive-modal">
                    Cancel
                </button>
                <button type="submit" form="receiveForm" class="px-4 py-2 text-sm font-medium bg-green-600 hover:bg-green-700 text-white rounded-lg transition-colors">
                    <i data-lucide="check" class="w-4 h-4 mr-1"></i>
                    Confirm Receipt
                </button>
            </x-slot>
        </form>
    </x-modal>

    {{-- Not Received Modal --}}
    <x-modal id="notReceivedModal" title="Mark as Not Received" size="md">
        <form id="notReceivedForm" method="POST">
            @csrf
            <input type="hidden" name="received_status" value="not_received">

            <div class="space-y-4">
                <div class="flex items-center gap-3 p-4 bg-red-50 rounded-lg border border-red-200">
                    <i data-lucide="package-x" class="w-8 h-8 text-red-600"></i>
                    <div class="flex-1">
                        <p class="font-semibold text-red-900">Mark as Not Received</p>
                        <p class="text-sm text-red-700">Transaction: <span id="notReceivedTransactionCode"
                                class="font-mono font-bold"></span></p>
                    </div>
                </div>

                <div class="p-4 bg-blue-50 rounded-lg border border-blue-200">
                    <div class="flex items-start gap-3">
                        <i data-lucide="user" class="w-5 h-5 text-blue-600 flex-shrink-0 mt-0.5"></i>
                        <div class="flex-1">
                            <p class="text-sm font-semibold text-blue-900 mb-1">Marked By:</p>
                            <div class="flex items-center gap-2">
                                <div class="avatar placeholder">
                                    <div class="bg-blue-600 text-white rounded-full w-8">
                                        <span class="text-xs">{{ substr(auth()->user()->name ?? 'U', 0, 1) }}</span>
                                    </div>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-blue-900">{{ auth()->user()->name }}</p>
                                    <p class="text-xs text-blue-700">{{ auth()->user()->email }}</p>
                                    @if (auth()->user()->isHead())
                                        <span class="badge badge-sm bg-blue-600 text-white border-0 mt-0.5">Department
                                            Head</span>
                                    @elseif(auth()->user()->type === 'Staff')
                                        <span class="badge badge-sm bg-blue-600 text-white border-0 mt-0.5">Staff</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="p-4 bg-gray-50 rounded-lg border border-gray-200">
                    <p class="text-sm text-gray-700">
                        This transaction will be marked as not received. The review timer will <strong>not start</strong>
                        until the transaction is marked as received.
                    </p>
                </div>

                <p class="text-sm text-gray-600">
                    Are you sure you want to mark this transaction as not received?
                </p>
            </div>

            <x-slot name="actions">
                <button type="button" class="px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 rounded-lg transition-colors close-not-received-modal">
                    Cancel
                </button>
                <button type="submit" form="notReceivedForm" class="px-4 py-2 text-sm font-medium bg-red-600 hover:bg-red-700 text-white rounded-lg transition-colors">
                    <i data-lucide="x" class="w-4 h-4 mr-1"></i>
                    Mark Not Received
                </button>
            </x-slot>
        </form>
    </x-modal>

    {{-- Resubmit Modal --}}
    <x-modal id="resubmitModal" title="Confirm Resubmission" size="md">
        <form id="resubmitForm" method="POST">
            @csrf

            <div class="space-y-4">
                <div class="flex items-center gap-3 p-4 bg-cyan-50 rounded-lg border border-cyan-200">
                    <i data-lucide="refresh-cw" class="w-8 h-8 text-cyan-600"></i>
                    <div class="flex-1">
                        <p class="font-semibold text-cyan-900">Mark as Resubmitted</p>
                        <p class="text-sm text-cyan-700">Transaction: <span id="resubmitTransactionCode"
                                class="font-mono font-bold"></span></p>
                        <p class="text-xs text-cyan-600 mt-1">Resubmission #<span id="resubmitIterationNumber"></span></p>
                    </div>
                </div>

                <div class="p-4 bg-blue-50 rounded-lg border border-blue-200">
                    <div class="flex items-start gap-3">
                        <i data-lucide="user-check" class="w-5 h-5 text-blue-600 flex-shrink-0 mt-0.5"></i>
                        <div class="flex-1">
                            <p class="text-sm font-semibold text-blue-900 mb-1">Resubmitted By:</p>
                            <div class="flex items-center gap-2">
                                <div class="avatar placeholder">
                                    <div class="bg-blue-600 text-white rounded-full w-8">
                                        <span class="text-xs">{{ substr(auth()->user()->name ?? 'U', 0, 1) }}</span>
                                    </div>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-blue-900">{{ auth()->user()->name }}</p>
                                    <p class="text-xs text-blue-700">{{ auth()->user()->email }}</p>
                                    @if (auth()->user()->isHead())
                                        <span class="badge badge-sm bg-blue-600 text-white border-0 mt-0.5">Department
                                            Head</span>
                                    @elseif(auth()->user()->type === 'Staff')
                                        <span class="badge badge-sm bg-blue-600 text-white border-0 mt-0.5">Staff</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="p-4 bg-yellow-50 rounded-lg border border-yellow-200">
                    <div class="flex gap-2">
                        <i data-lucide="alert-triangle" class="w-5 h-5 text-yellow-600 flex-shrink-0 mt-0.5"></i>
                        <div class="text-sm text-yellow-800">
                            <p class="font-semibold mb-1">Important:</p>
                            <ul class="list-disc list-inside space-y-1">
                                <li>This transaction has been corrected and is ready for re-review</li>
                                <li>The review timer will start immediately after resubmission</li>
                                <li>You must complete the review before the deadline</li>
                                <li>This action cannot be undone</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <p class="text-sm text-gray-600">
                    By clicking "Confirm Resubmission", you acknowledge that the corrections have been made and the transaction
                    is ready for the next review cycle.
                </p>
            </div>

            <x-slot name="actions">
                <button type="button" class="px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 rounded-lg transition-colors close-resubmit-modal">
                    Cancel
                </button>
                <button type="submit" form="resubmitForm" class="px-4 py-2 text-sm font-medium bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors">
                    <i data-lucide="refresh-cw" class="w-4 h-4 mr-1"></i>
                    Confirm Resubmission
                </button>
            </x-slot>
        </form>
    </x-modal>

    @push('scripts')
        <script>
            function showReceiveModal(reviewId, transactionCode) {
                // Set transaction code and form action
                const codeElement = document.getElementById('receiveTransactionCode');
                const formElement = document.getElementById('receiveForm');

                if (codeElement && formElement) {
                    codeElement.textContent = transactionCode;
                    formElement.action = `/transactions/reviews/${reviewId}/receive`;
                }

                // Show modal
                if (typeof receiveModal !== 'undefined' && receiveModal.showModal) {
                    receiveModal.showModal();
                } else {
                    // Fallback: directly show the modal
                    const modalElement = document.getElementById('receiveModal');
                    if (modalElement) {
                        modalElement.classList.remove('hidden');
                        document.body.style.overflow = 'hidden';
                    }
                }

                // Re-render lucide icons in modal
                setTimeout(() => {
                    if (typeof lucide !== 'undefined') {
                        lucide.createIcons();
                    }
                }, 100);
            }

            function showNotReceivedModal(reviewId, transactionCode) {
                // Set transaction code and form action
                const codeElement = document.getElementById('notReceivedTransactionCode');
                const formElement = document.getElementById('notReceivedForm');

                if (codeElement && formElement) {
                    codeElement.textContent = transactionCode;
                    formElement.action = `/transactions/reviews/${reviewId}/receive`;
                }

                // Show modal
                if (typeof notReceivedModal !== 'undefined' && notReceivedModal.showModal) {
                    notReceivedModal.showModal();
                } else {
                    // Fallback: directly show the modal
                    const modalElement = document.getElementById('notReceivedModal');
                    if (modalElement) {
                        modalElement.classList.remove('hidden');
                        document.body.style.overflow = 'hidden';
                    }
                }

                // Re-render lucide icons in modal
                setTimeout(() => {
                    if (typeof lucide !== 'undefined') {
                        lucide.createIcons();
                    }
                }, 100);
            }

            function showResubmitModal(reviewId, transactionCode, iterationNumber) {
                // Set transaction code and form action
                const codeElement = document.getElementById('resubmitTransactionCode');
                const iterationElement = document.getElementById('resubmitIterationNumber');
                const formElement = document.getElementById('resubmitForm');

                if (codeElement && formElement && iterationElement) {
                    codeElement.textContent = transactionCode;
                    iterationElement.textContent = iterationNumber;
                    formElement.action = `/transactions/reviews/${reviewId}/resubmit`;
                }

                // Show modal
                if (typeof resubmitModal !== 'undefined' && resubmitModal.showModal) {
                    resubmitModal.showModal();
                } else {
                    // Fallback: directly show the modal
                    const modalElement = document.getElementById('resubmitModal');
                    if (modalElement) {
                        modalElement.classList.remove('hidden');
                        document.body.style.overflow = 'hidden';
                    }
                }

                // Re-render lucide icons in modal
                setTimeout(() => {
                    if (typeof lucide !== 'undefined') {
                        lucide.createIcons();
                    }
                }, 100);
            }

            // Initialize lucide icons on page load
            document.addEventListener('DOMContentLoaded', function() {
                if (typeof lucide !== 'undefined') {
                    lucide.createIcons();
                }

                // Add event listeners for close buttons
                const closeReceiveBtn = document.querySelector('.close-receive-modal');
                if (closeReceiveBtn) {
                    closeReceiveBtn.addEventListener('click', function() {
                        const modalElement = document.getElementById('receiveModal');
                        if (modalElement) {
                            modalElement.classList.add('hidden');
                            document.body.style.overflow = '';
                        }
                    });
                }

                const closeNotReceivedBtn = document.querySelector('.close-not-received-modal');
                if (closeNotReceivedBtn) {
                    closeNotReceivedBtn.addEventListener('click', function() {
                        const modalElement = document.getElementById('notReceivedModal');
                        if (modalElement) {
                            modalElement.classList.add('hidden');
                            document.body.style.overflow = '';
                        }
                    });
                }

                const closeResubmitBtn = document.querySelector('.close-resubmit-modal');
                if (closeResubmitBtn) {
                    closeResubmitBtn.addEventListener('click', function() {
                        const modalElement = document.getElementById('resubmitModal');
                        if (modalElement) {
                            modalElement.classList.add('hidden');
                            document.body.style.overflow = '';
                        }
                    });
                }

                // Close modal when clicking backdrop (the close button in modal component)
                document.querySelectorAll('[onclick*="classList.add(\'hidden\')"]').forEach(element => {
                    element.addEventListener('click', function() {
                        document.body.style.overflow = '';
                    });
                });
            });
        </script>
    @endpush
@endsection
