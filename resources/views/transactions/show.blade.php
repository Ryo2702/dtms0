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
                    @if($transaction->transaction_status === 'in_progress')
                        <button onclick="window['cancel-modal'].showModal()" class="btn btn-error text-white">
                            <i data-lucide="x-circle" class="w-4 h-4 mr-2"></i>
                            Cancel Transaction
                        </button>
                    @elseif(!$transaction->isCompleted() && !$transaction->isCancelled())
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
                        @if($transaction->workflow && $transaction->workflow->documentTags && $transaction->workflow->documentTags->count() > 0)
                            <div class="md:col-span-2">
                                <dt class="text-sm text-gray-500">Document Attachment</dt>
                                <dd class="flex flex-wrap gap-2 mt-1">
                                    @foreach($transaction->workflow->documentTags as $tag)
                                        <span class="badge badge-primary badge-sm" style="background-color: #10b981; color: white;">
                                            <i data-lucide="tag" class="w-3 h-3 mr-1"></i>
                                            {{ $tag->name }}
                                        </span>
                                    @endforeach
                                </dd>
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
                        <div class="flex flex-col lg:flex-row items-start lg:items-center gap-2 lg:gap-0 w-full overflow-x-auto py-4">
                            @foreach($workflowProgress['steps'] as $index => $step)
                                @php
                                    $isCompleted = $step['status'] === 'completed';
                                    $isCurrent = $step['status'] === 'current';
                                    $isReturned = $step['status'] === 'returned';
                                @endphp
                                
                                {{-- Step Item --}}
                                <div class="flex items-center {{ $index < count($workflowProgress['steps']) - 1 ? 'flex-1' : '' }}">
                                    <div class="flex flex-col items-center min-w-[120px]">
                                        {{-- Step Circle --}}
                                        <div class="w-10 h-10 rounded-full flex items-center justify-center text-sm font-bold border-2 shadow-sm
                                            {{ $isCompleted ? 'bg-blue-500 border-blue-500 text-white' : '' }}
                                            {{ $isCurrent ? 'bg-blue-600 border-blue-600 text-white ring-4 ring-blue-200 animate-pulse' : '' }}
                                            {{ $isReturned ? 'bg-yellow-500 border-yellow-500 text-white' : '' }}
                                            {{ !$isCompleted && !$isCurrent && !$isReturned ? 'bg-gray-100 border-gray-300 text-gray-400' : '' }}">
                                            @if($isCompleted)
                                                <i data-lucide="check" class="w-5 h-5"></i>
                                            @elseif($isReturned)
                                                <i data-lucide="rotate-ccw" class="w-4 h-4"></i>
                                            @else
                                                {{ $index + 1 }}
                                            @endif
                                        </div>
                                        
                                        {{-- Step Label --}}
                                        <div class="mt-2 px-3 py-1.5 rounded-lg text-center max-w-[140px]
                                            {{ $isCompleted ? 'bg-blue-50 border border-blue-200' : '' }}
                                            {{ $isCurrent ? 'bg-blue-100 border-2 border-blue-400 shadow-md' : '' }}
                                            {{ $isReturned ? 'bg-yellow-50 border border-yellow-300' : '' }}
                                            {{ !$isCompleted && !$isCurrent && !$isReturned ? 'bg-gray-50 border border-gray-200' : '' }}">
                                            <div class="font-medium text-xs leading-tight
                                                {{ $isCompleted ? 'text-blue-700' : '' }}
                                                {{ $isCurrent ? 'text-blue-800' : '' }}
                                                {{ $isReturned ? 'text-yellow-700' : '' }}
                                                {{ !$isCompleted && !$isCurrent && !$isReturned ? 'text-gray-400' : '' }}">
                                                {{ $step['department_name'] ?? 'Unknown' }}
                                            </div>
                                            @if($isCurrent)
                                                <span class="badge badge-primary badge-xs mt-1">Current</span>
                                                @if(isset($step['received_by']) && $step['received_by'])
                                                    <div class="text-[10px] text-blue-600 mt-1">
                                                        Received by: {{ $step['received_by'] }}
                                                    </div>
                                                @endif
                                            @elseif($isReturned)
                                                <span class="badge badge-warning badge-xs mt-1">Returned</span>
                                                @if(isset($step['received_by']) && $step['received_by'])
                                                    <div class="text-[10px] text-yellow-600 mt-1">
                                                        Received by: {{ $step['received_by'] }}
                                                    </div>
                                                @endif
                                            @elseif($isCompleted)
                                                <span class="text-[10px] text-blue-500 mt-0.5">✓ Done</span>
                                                @if(isset($step['received_by']) && $step['received_by'])
                                                    <div class="text-[10px] text-blue-600 mt-1">
                                                        Received by: {{ $step['received_by'] }}
                                                    </div>
                                                @endif
                                            @endif
                                        </div>
                                    </div>
                                    
                                    {{-- Arrow Connector --}}
                                    @if($index < count($workflowProgress['steps']) - 1)
                                        <div class="hidden lg:flex flex-1 items-center justify-center px-2">
                                            <div class="h-0.5 flex-1 {{ $isCompleted ? 'bg-blue-400' : 'bg-gray-200' }}"></div>
                                            <i data-lucide="chevron-right" class="w-5 h-5 mx-1 {{ $isCompleted ? 'text-blue-400' : 'text-gray-300' }}"></i>
                                            <div class="h-0.5 flex-1 {{ $isCompleted ? 'bg-blue-400' : 'bg-gray-200' }}"></div>
                                        </div>
                                        {{-- Mobile Arrow --}}
                                        <div class="lg:hidden flex justify-center w-full py-1">
                                            <i data-lucide="chevron-down" class="w-5 h-5 {{ $isCompleted ? 'text-blue-400' : 'text-gray-300' }}"></i>
                                        </div>
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
                                <div class="avatar">
                                    @if($transaction->currentReviewer->department->logo)
                                        <div class="w-12 h-12 rounded-full ring ring-primary ring-offset-base-100 ring-offset-2">
                                            <img src="{{ asset('storage/' . $transaction->currentReviewer->department->logo) }}" 
                                                 alt="{{ $transaction->currentReviewer->department->name ?? 'Department' }} Logo" 
                                                 class="rounded-full object-cover" />
                                        </div>
                                    @else
                                        <div class="placeholder">
                                            <div class="bg-primary text-white rounded-full w-12 h-12 flex items-center justify-center">
                                                <span class="text-xl">{{ substr($transaction->currentReviewer->department->name ?? 'D', 0, 1) }}</span>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                                <div>
                                    <div class="font-medium">{{ $transaction->currentReviewer->reviewer->full_name ?? 'Unknown' }}</div>
                                    <div class="text-sm text-gray-500">{{ $transaction->currentReviewer->department->name ?? '' }}</div>
                                </div>
                            </div>
                            @if($transaction->currentReviewer->due_date)
                                <div class="space-y-2">
                                    <div class="flex items-center gap-2 text-sm {{ $transaction->currentReviewer->isOverdue() ? 'text-error' : 'text-gray-500' }}">
                                        <i data-lucide="clock" class="w-4 h-4"></i>
                                        <span>Due: {{ $transaction->currentReviewer->due_date->format('M d, Y h:i A') }}</span>
                                        @if($transaction->currentReviewer->isOverdue())
                                            <span class="badge badge-error badge-sm">Overdue</span>
                                        @endif
                                    </div>
                                    <div class="bg-base-200 rounded-lg p-3">
                                        <div class="text-xs text-gray-500 mb-1">Time Remaining</div>
                                        <div id="countdown-timer" 
                                             data-due-date="{{ $transaction->currentReviewer->due_date->toIso8601String() }}"
                                             class="font-mono font-semibold {{ $transaction->currentReviewer->isOverdue() ? 'text-error' : 'text-primary' }}">
                                            <span class="loading loading-spinner loading-xs"></span> Calculating...
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </x-card>
                @endif

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

        {{-- Cancel Transaction Modal --}}
        @if($transaction->transaction_status === 'in_progress')
            <x-modal id="cancel-modal" size="md">
                <h3 class="font-bold text-lg text-error mb-4">Cancel Transaction</h3>
                <p class="mb-2">Are you sure you want to cancel this transaction?</p>
                <p class="text-sm text-gray-500 mb-4">Transaction: <strong>{{ $transaction->transaction_code }}</strong></p>
                
                <form action="{{ route('transactions.cancel', $transaction) }}" method="POST">
                    @csrf
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Reason for Cancellation <span class="text-error">*</span>
                        </label>
                        <textarea 
                            name="reason" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent" 
                            rows="3" 
                            placeholder="Explain why you are cancelling this transaction..." 
                            required></textarea>
                    </div>
                    
                    <div class="flex justify-end gap-3 mt-6">
                        <button type="button" class="btn btn-ghost" onclick="window['cancel-modal'].close()">
                            Cancel
                        </button>
                        <button type="submit" class="btn btn-error">
                            <i data-lucide="x-circle" class="w-4 h-4 mr-2"></i>
                            Confirm Cancellation
                        </button>
                    </div>
                </form>
            </x-modal>
        @endif
    </x-container>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const countdownElement = document.getElementById('countdown-timer');
            
            if (!countdownElement) return;
            
            const dueDate = new Date(countdownElement.dataset.dueDate);
            
            function updateCountdown() {
                const now = new Date();
                const diff = dueDate - now;
                
                if (diff <= 0) {
                    countdownElement.innerHTML = '<span class="text-error font-bold">⏰ OVERDUE</span>';
                    countdownElement.classList.remove('text-primary');
                    countdownElement.classList.add('text-error');
                    return;
                }
                
                const days = Math.floor(diff / (1000 * 60 * 60 * 24));
                const hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
                const seconds = Math.floor((diff % (1000 * 60)) / 1000);
                
                let timeString = '';
                
                if (days > 0) {
                    timeString += `<span class="text-lg">${days}</span><span class="text-xs ml-0.5">d</span> `;
                }
                if (days > 0 || hours > 0) {
                    timeString += `<span class="text-lg">${hours}</span><span class="text-xs ml-0.5">h</span> `;
                }
                if (days === 0) {
                    timeString += `<span class="text-lg">${minutes}</span><span class="text-xs ml-0.5">m</span> `;
                    timeString += `<span class="text-lg">${seconds}</span><span class="text-xs ml-0.5">s</span>`;
                }
                
                countdownElement.innerHTML = timeString || '0s';
                
                // Change color based on urgency
                if (days === 0 && hours < 6) {
                    countdownElement.classList.remove('text-primary');
                    countdownElement.classList.add('text-error');
                } else if (days === 0 && hours < 24) {
                    countdownElement.classList.remove('text-primary');
                    countdownElement.classList.add('text-warning');
                } else {
                    countdownElement.classList.remove('text-error', 'text-warning');
                    countdownElement.classList.add('text-primary');
                }
            }
            
            // Update immediately
            updateCountdown();
            
            // Update every second
            setInterval(updateCountdown, 1000);
        });
    </script>
    @endpush
@endsection
