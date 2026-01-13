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
                    <a href="{{ route('transactions.reviews.index') }}" class="btn btn-outline btn-primary">
                        <i data-lucide="arrow-left" class="w-4 h-4 mr-2"></i>
                        Back to Reviews
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
                            <dt class="text-sm text-gray-500">Origin Department</dt>
                            <dd class="font-medium">{{ $reviewer->transaction->originDepartment->name ?? 'N/A' }}</dd>
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
                        @if($reviewer->due_date)
                        <div>
                            <dt class="text-sm text-gray-500">Time Remaining</dt>
                            <dd>
                                @if($reviewer->isOverdue())
                                    <span class="badge badge-error gap-1">
                                        <i data-lucide="alert-circle" class="w-3 h-3"></i>
                                        Overdue by {{ $reviewer->due_date->diffForHumans(null, true) }}
                                    </span>
                                @else
                                    <span class="badge badge-info gap-1">
                                        <i data-lucide="clock" class="w-3 h-3"></i>
                                        <span id="countdown">{{ $reviewer->due_date->diffForHumans() }}</span>
                                    </span>
                                @endif
                            </dd>
                        </div>
                        @endif
                    </div>

                    {{-- Document Tags --}}
                    @if($reviewer->transaction->workflow && $reviewer->transaction->workflow->documentTags->count() > 0)
                        <div class="mt-4 pt-4 border-t">
                            <dt class="text-sm text-gray-500 mb-2">Required Document Tags</dt>
                            <dd class="flex flex-wrap gap-2">
                                @foreach($reviewer->transaction->workflow->documentTags as $tag)
                                    <span class="badge badge-success bg-success text-white gap-1">
                                        <i data-lucide="tag" class="w-3 h-3"></i>
                                        {{ $tag->name }}
                                        @if($tag->pivot->is_required)
                                            <span class="text-xs">*</span>
                                        @endif
                                    </span>
                                @endforeach
                            </dd>
                        </div>
                    @endif

                    {{-- Custom Document Tags --}}
                    @if($reviewer->transaction->custom_document_tags && is_array($reviewer->transaction->custom_document_tags) && count($reviewer->transaction->custom_document_tags) > 0)
                        <div class="mt-4 pt-4 border-t">
                            <dt class="text-sm text-gray-500 mb-2">Custom Document Tags</dt>
                            <dd class="flex flex-wrap gap-2">
                                @foreach($reviewer->transaction->custom_document_tags as $customTag)
                                    @if(is_string($customTag))
                                        <span class="badge badge-info bg-info text-white gap-1">
                                            <i data-lucide="bookmark" class="w-3 h-3"></i>
                                            {{ $customTag }}
                                        </span>
                                    @elseif(is_array($customTag) && isset($customTag['name']))
                                        <span class="badge badge-info bg-info text-white gap-1">
                                            <i data-lucide="bookmark" class="w-3 h-3"></i>
                                            {{ $customTag['name'] }}
                                        </span>
                                    @endif
                                @endforeach
                            </dd>
                        </div>
                    @endif

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
                    <div class="space-y-4">
                        {{-- Approval Path Actions --}}
                        <div class="p-4 border-2 border-success rounded-lg bg-success/10">
                            <h3 class="text-lg font-semibold text-success flex items-center gap-2 mb-3">
                                <i data-lucide="check-circle" class="w-5 h-5"></i>
                                Approval Path (Forward-Moving Actions)
                            </h3>
                            <p class="text-sm text-gray-600 mb-4">
                                Select the appropriate action to move this transaction forward in the workflow.
                            </p>
                            <form action="{{ route('transactions.reviews.approve', $reviewer) }}" method="POST" id="approve-form">
                                @csrf
                                
                                {{-- Action Type Selection --}}
                                <div class="form-control mb-4">
                                    <label class="label">
                                        <span class="label-text font-medium">Action Type <span class="text-error">*</span></span>
                                    </label>
                                    <select name="action_type" class="select select-bordered" required id="action_type">
                                        <option value="">Select action type...</option>
                                        <option value="review">Review - Checks correctness without assuming liability</option>
                                        <option value="validate">Validate - Confirms compliance with rules, plans, or law</option>
                                        <option value="endorse">Endorse - Passes responsibility upward with recommendation</option>
                                        <option value="approve">Approve - Exercises legal authority (binding signature)</option>
                                        <option value="certify">Certify - Attests to a specific fact (funds, delivery, inspection)</option>
                                        <option value="lock">Lock - Freezes content after approval/certification</option>
                                        <option value="release">Release - Makes document actionable by next office</option>
                                        <option value="complete">Complete - Marks transaction finished for this stage</option>
                                    </select>
                                    <label class="label">
                                        <span class="label-text-alt text-gray-500" id="action_description">Choose the action that best describes your decision</span>
                                    </label>
                                </div>

                                {{-- Remarks Field --}}
                                <div class="form-control mb-4">
                                    <label class="label">
                                        <span class="label-text font-medium">Remarks</span>
                                    </label>
                                    <textarea name="remarks" class="textarea textarea-bordered" rows="3" 
                                        placeholder="Add any comments, notes, or recommendations..."></textarea>
                                </div>
                                <button type="submit" class="w-full px-4 py-2.5 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg transition-colors duration-200 flex items-center justify-center gap-2">
                                    <i data-lucide="check" class="w-4 h-4"></i>
                                    Submit Decision
                                </button>
                            </form>
                        </div>

                        <div class="divider">OR</div>

                        {{-- Rejection / Return Actions --}}
                        <div class="p-4 border-2 border-error rounded-lg bg-error/10">
                            <h3 class="text-lg font-semibold text-error flex items-center gap-2 mb-3">
                                <i data-lucide="x-circle" class="w-5 h-5"></i>
                                Rejection / Resubmission Path (Corrective Actions)
                            </h3>
                            <p class="text-sm text-gray-600 mb-4">
                                Return the transaction for corrections or reject it.
                            </p>
                            <form action="{{ route('transactions.reviews.reject', $reviewer) }}" method="POST" id="reject-form">
                                @csrf
                                
                                {{-- Rejection Action Type --}}
                                <div class="form-control mb-4">
                                    <label class="label">
                                        <span class="label-text font-medium">Action Type <span class="text-error">*</span></span>
                                    </label>
                                    <select name="action_type" class="select select-bordered select-error" required>
                                        <option value="">Select action type...</option>
                                        <option value="return_revision">Return for Revision - Send back with findings (keeps alive)</option>
                                        <option value="resubmit">Require Resubmit - Re-enter approval path after corrections</option>
                                    </select>
                                </div>

                                {{-- Rejection Reason --}}
                                <div class="form-control mb-4">
                                    <label class="label">
                                        <span class="label-text font-medium">Reason for Return/Rejection <span class="text-error">*</span></span>
                                    </label>
                                    <textarea name="rejection_reason" class="textarea textarea-bordered textarea-error" rows="3" 
                                        placeholder="Please explain what needs to be corrected or why this is being returned..." required></textarea>
                                    @error('rejection_reason')
                                        <label class="label">
                                            <span class="label-text-alt text-error">{{ $message }}</span>
                                        </label>
                                    @enderror
                                </div>

                                {{-- Resubmission Deadline --}}
                                <div class="form-control mb-4">
                                    <label class="label">
                                        <span class="label-text font-medium">Resubmission Deadline (Optional)</span>
                                    </label>
                                    <input type="date" name="resubmission_deadline" class="input input-bordered" 
                                        min="{{ now()->addDay()->format('Y-m-d') }}">
                                    <label class="label">
                                        <span class="label-text-alt text-gray-500">Set a deadline for corrections and resubmission</span>
                                    </label>
                                </div>
                                <button type="submit" class="w-full px-4 py-2.5 bg-red-600 hover:bg-red-700 text-white font-medium rounded-lg transition-colors duration-200 flex items-center justify-center gap-2">
                                    <i data-lucide="corner-up-left" class="w-4 h-4"></i>
                                    Return Transaction
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
                                    @if(!$reviewer->isOverdue())
                                        <div class="mt-1">
                                            <span class="badge badge-sm {{ $reviewer->due_date->isToday() ? 'badge-warning' : 'badge-info' }} gap-1">
                                                <i data-lucide="clock" class="w-3 h-3"></i>
                                                <span id="sidebar-countdown">{{ $reviewer->due_date->diffForHumans() }}</span>
                                            </span>
                                        </div>
                                    @else
                                        <div class="mt-1">
                                            <span class="badge badge-sm badge-error gap-1">
                                                <i data-lucide="alert-circle" class="w-3 h-3"></i>
                                                Overdue
                                            </span>
                                        </div>
                                    @endif
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

                {{-- Workflow Progress (Vertical) --}}
                <x-card title="Workflow Progress">
                    @if(isset($workflowProgress['steps']) && count($workflowProgress['steps']) > 0)
                        <div class="relative">
                            @foreach($workflowProgress['steps'] as $index => $step)
                                @php
                                    $isCompleted = $step['status'] === 'completed';
                                    $isCurrent = $step['status'] === 'current';
                                    $isReturned = $step['status'] === 'returned';
                                    $isPending = !$isCompleted && !$isCurrent && !$isReturned;
                                @endphp
                                
                                <div class="flex gap-3 {{ $index < count($workflowProgress['steps']) - 1 ? 'mb-4' : '' }}">
                                    {{-- Vertical Line & Circle --}}
                                    <div class="flex flex-col items-center">
                                        {{-- Circle --}}
                                        <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold border-2 shadow-sm flex-shrink-0
                                            {{ $isCompleted ? 'bg-blue-500 border-blue-500 text-white' : '' }}
                                            {{ $isCurrent ? 'bg-blue-600 border-blue-600 text-white ring-4 ring-blue-200' : '' }}
                                            {{ $isReturned ? 'bg-yellow-500 border-yellow-500 text-white' : '' }}
                                            {{ $isPending ? 'bg-gray-100 border-gray-300 text-gray-400' : '' }}">
                                            @if($isCompleted)
                                                <i data-lucide="check" class="w-4 h-4"></i>
                                            @elseif($isReturned)
                                                <i data-lucide="rotate-ccw" class="w-3 h-3"></i>
                                            @else
                                                {{ $index + 1 }}
                                            @endif
                                        </div>
                                        
                                        {{-- Vertical Line --}}
                                        @if($index < count($workflowProgress['steps']) - 1)
                                            <div class="w-0.5 flex-1 h-8 {{ $isCompleted ? 'bg-blue-400' : 'bg-gray-200' }}"></div>
                                        @endif
                                    </div>
                                    
                                    {{-- Step Content --}}
                                    <div class="flex-1 pb-2">
                                        <div class="font-medium text-sm
                                            {{ $isCompleted ? 'text-blue-700' : '' }}
                                            {{ $isCurrent ? 'text-blue-800' : '' }}
                                            {{ $isReturned ? 'text-yellow-700' : '' }}
                                            {{ $isPending ? 'text-gray-400' : '' }}">
                                            {{ $step['department_name'] ?? 'Unknown' }}
                                        </div>
                                        
                                        @if($isCurrent)
                                            <span class="inline-block px-2 py-0.5 bg-blue-600 text-white text-xs font-medium rounded-full mt-1">Current Step</span>
                                            @if(isset($step['action']) && $step['action'])
                                                <div class="text-xs text-gray-600 mt-1">
                                                    {{ $step['action'] }}
                                                </div>
                                            @endif
                                            @if(isset($step['received_by']) && $step['received_by'])
                                                <div class="text-xs text-blue-600 mt-1">
                                                    <i data-lucide="user-check" class="w-3 h-3 inline"></i>
                                                    {{ is_array($step['received_by']) ? ($step['received_by']['name'] ?? 'Unknown') : $step['received_by'] }}
                                                </div>
                                            @endif
                                        @elseif($isReturned)
                                            <span class="inline-block px-2 py-0.5 bg-yellow-500 text-white text-xs font-medium rounded-full mt-1">Returned</span>
                                        @elseif($isCompleted)
                                            <div class="text-xs text-blue-500 mt-1">
                                                <i data-lucide="check" class="w-3 h-3 inline"></i>
                                                Completed
                                            </div>
                                        @else
                                            <div class="text-xs text-gray-400 mt-1">Pending</div>
                                        @endif
                                        
                                        @if(isset($step['process_time_value']) && isset($step['process_time_unit']) && ($isCurrent || $isPending))
                                            <div class="text-xs text-gray-500 mt-1 flex items-center gap-1">
                                                <i data-lucide="clock" class="w-3 h-3"></i>
                                                {{ $step['process_time_value'] }} {{ $step['process_time_unit'] }}
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-gray-500 text-sm text-center py-4">No workflow steps available.</p>
                    @endif
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
            </div>
        </div>
    </x-container>

    {{-- Confirmation Modals --}}
    <script>
        // Action type descriptions
        const actionDescriptions = {
            'review': 'Checks correctness without assuming liability',
            'validate': 'Confirms compliance with rules, plans, or law',
            'endorse': 'Passes responsibility upward with recommendation',
            'approve': 'Exercises legal authority. This is a binding signature',
            'certify': 'Attests to a specific fact (funds, delivery, inspection)',
            'lock': 'Freezes content automatically after approval/certification',
            'release': 'Makes the document actionable by the next office',
            'complete': 'Marks the transaction finished for that stage'
        };

        // Update action description on select change
        document.getElementById('action_type')?.addEventListener('change', function(e) {
            const description = actionDescriptions[e.target.value] || 'Choose the action that best describes your decision';
            document.getElementById('action_description').textContent = description;
        });

        // Countdown timer
        @if($reviewer->due_date && !$reviewer->isOverdue())
        function updateCountdown() {
            const dueDate = new Date('{{ $reviewer->due_date->toIso8601String() }}');
            const now = new Date();
            const diff = dueDate - now;

            if (diff <= 0) {
                const countdownEl = document.getElementById('countdown');
                const sidebarCountdownEl = document.getElementById('sidebar-countdown');
                if (countdownEl) countdownEl.textContent = 'Due now';
                if (sidebarCountdownEl) sidebarCountdownEl.textContent = 'Due now';
                return;
            }

            const days = Math.floor(diff / (1000 * 60 * 60 * 24));
            const hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));

            let countdownText = '';
            if (days > 0) countdownText += `${days}d `;
            if (hours > 0) countdownText += `${hours}h `;
            countdownText += `${minutes}m remaining`;

            const countdownEl = document.getElementById('countdown');
            const sidebarCountdownEl = document.getElementById('sidebar-countdown');
            if (countdownEl) countdownEl.textContent = countdownText;
            if (sidebarCountdownEl) sidebarCountdownEl.textContent = countdownText;
        }

        // Update countdown every minute
        updateCountdown();
        setInterval(updateCountdown, 60000);
        @endif

        document.getElementById('approve-form').addEventListener('submit', function(e) {
            const actionType = document.querySelector('#approve-form select[name="action_type"]').selectedOptions[0].text;
            if (!confirm(`Are you sure you want to ${actionType.split(' - ')[0]}? This action cannot be undone.`)) {
                e.preventDefault();
            }
        });

        document.getElementById('reject-form').addEventListener('submit', function(e) {
            if (!confirm('Are you sure you want to RETURN this transaction? The submitter will be notified.')) {
                e.preventDefault();
            }
        });
    </script>
@endsection
