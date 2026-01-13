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
                            <dt class="text-sm text-gray-500">Origin Department</dt>
                            <dd>
                                <span class="badge badge-outline">
                                    <i data-lucide="building-2" class="w-3 h-3 mr-1"></i>
                                    {{ $reviewer->transaction->originDepartment->name ?? 'N/A' }}
                                </span>
                            </dd>
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
                            <dt class="text-sm text-gray-500">Reviewer Department</dt>
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

                        {{-- Receiving Status --}}
                        <div>
                            <dt class="text-sm text-gray-500">Receiving Status</dt>
                            <dd>
                                @if($reviewer->received_status === 'received')
                                    <span class="badge badge-success">
                                        <i data-lucide="package-check" class="w-3 h-3 mr-1"></i>
                                        Received
                                    </span>
                                    @if($reviewer->receivedBy)
                                        <div class="text-xs text-gray-500 mt-1">
                                            by {{ $reviewer->receivedBy->name }} at {{ $reviewer->received_at->format('M d, Y h:i A') }}
                                        </div>
                                    @endif
                                @elseif($reviewer->received_status === 'not_received')
                                    <span class="badge badge-error">
                                        <i data-lucide="package-x" class="w-3 h-3 mr-1"></i>
                                        Not Received
                                    </span>
                                @else
                                    <span class="badge badge-warning">
                                        <i data-lucide="package" class="w-3 h-3 mr-1"></i>
                                        Pending Receipt
                                    </span>
                                @endif
                            </dd>
                        </div>

                        {{-- Time Remaining (Only if received) --}}
                        @if($reviewer->received_status === 'received' && $reviewer->due_date && !$reviewer->reviewed_at)
                            <div class="col-span-full">
                                <dt class="text-sm text-gray-500 mb-2">Time Remaining</dt>
                                <dd>
                                    <div id="countdown-timer" class="p-4 bg-gradient-to-r from-blue-50 to-indigo-50 rounded-lg border-2 border-blue-200">
                                        <div class="flex items-center justify-between mb-2">
                                            <span class="text-sm font-semibold text-blue-900">Review Deadline Countdown</span>
                                            <i data-lucide="timer" class="w-5 h-5 text-blue-600"></i>
                                        </div>
                                        <div id="countdown-display" class="grid grid-cols-4 gap-2 text-center">
                                            <div class="bg-white rounded-lg p-3 shadow">
                                                <div id="days" class="text-2xl font-bold text-blue-600">--</div>
                                                <div class="text-xs text-gray-600 uppercase">Days</div>
                                            </div>
                                            <div class="bg-white rounded-lg p-3 shadow">
                                                <div id="hours" class="text-2xl font-bold text-blue-600">--</div>
                                                <div class="text-xs text-gray-600 uppercase">Hours</div>
                                            </div>
                                            <div class="bg-white rounded-lg p-3 shadow">
                                                <div id="minutes" class="text-2xl font-bold text-blue-600">--</div>
                                                <div class="text-xs text-gray-600 uppercase">Minutes</div>
                                            </div>
                                            <div class="bg-white rounded-lg p-3 shadow">
                                                <div id="seconds" class="text-2xl font-bold text-blue-600">--</div>
                                                <div class="text-xs text-gray-600 uppercase">Seconds</div>
                                            </div>
                                        </div>
                                        <div id="countdown-message" class="mt-3 text-center text-sm"></div>
                                    </div>
                                </dd>
                            </div>
                        @endif

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
                                                <span class="inline-block px-2 py-0.5 bg-blue-600 text-white text-xs font-medium rounded-full mt-1">Current</span>
                                                @if(isset($step['received_by']) && $step['received_by'])
                                                    <div class="text-[10px] text-blue-600 mt-1">
                                                        Received by: {{ is_array($step['received_by']) ? ($step['received_by']['name'] ?? 'Unknown') : $step['received_by'] }}
                                                    </div>
                                                @endif
                                            @elseif($isReturned)
                                                <span class="inline-block px-2 py-0.5 bg-yellow-500 text-white text-xs font-medium rounded-full mt-1">Returned</span>
                                            @elseif($isCompleted)
                                                <span class="text-[10px] text-blue-500 mt-0.5">✓ Done</span>
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

                        {{-- Step Actions/Instructions --}}
                        <div class="mt-6 p-4 bg-gradient-to-r from-blue-50 to-indigo-50 rounded-lg border border-blue-200">
                            <h4 class="font-semibold text-blue-900 mb-3 flex items-center gap-2">
                                <i data-lucide="list-checks" class="w-5 h-5"></i>
                                Review Actions Required
                            </h4>
                            <div class="space-y-3">
                                @foreach($workflowProgress['steps'] as $index => $step)
                                    @if($step['status'] === 'current')
                                        <div class="p-3 bg-white rounded-lg border-l-4 border-blue-600 shadow-sm">
                                            <div class="flex items-start gap-3">
                                                <div class="w-6 h-6 rounded-full bg-blue-600 text-white flex items-center justify-center text-xs font-bold flex-shrink-0 mt-0.5">
                                                    {{ $index + 1 }}
                                                </div>
                                                <div class="flex-1">
                                                    <div class="font-semibold text-gray-900 mb-1">{{ $step['department_name'] ?? 'Unknown' }}</div>
                                                    <div class="text-sm text-gray-700">
                                                        <strong>Action:</strong> {{ $step['action'] ?? 'Review and process this transaction' }}
                                                    </div>
                                                    @if(isset($step['process_time_value']) && isset($step['process_time_unit']))
                                                        <div class="text-xs text-blue-600 mt-2 flex items-center gap-1">
                                                            <i data-lucide="clock" class="w-3 h-3"></i>
                                                            Expected processing time: {{ $step['process_time_value'] }} {{ $step['process_time_unit'] }}
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    @else
                        <p class="text-gray-500 text-center py-4">No workflow steps available.</p>
                    @endif
                </x-card>

                {{-- Document Attachments --}}
                <x-card title="Document Attachments">
                    {{-- Required Attachment Documents --}}
                    @if($reviewer->transaction->workflow && $reviewer->transaction->workflow->documentTags && $reviewer->transaction->workflow->documentTags->count() > 0)
                        <div class="mb-6">
                            <h4 class="text-sm font-semibold text-gray-700 mb-3 flex items-center gap-2">
                                <i data-lucide="paperclip" class="w-4 h-4 text-green-600"></i>
                                Required Attachment Documents
                            </h4>
                            <div class="flex flex-wrap gap-2">
                                @foreach($reviewer->transaction->workflow->documentTags as $tag)
                                    <span class="inline-flex items-center px-3 py-1.5 rounded-lg text-sm font-medium bg-green-100 text-green-800 border border-green-300">
                                        <i data-lucide="file-check" class="w-4 h-4 mr-1.5"></i>
                                        {{ $tag->name }}
                                        @if($tag->pivot && $tag->pivot->is_required)
                                            <span class="ml-1.5 text-xs bg-green-600 text-white px-1.5 py-0.5 rounded">Required</span>
                                        @endif
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- Additional Attachments --}}
                    <div>
                        <h4 class="text-sm font-semibold text-gray-700 mb-3 flex items-center gap-2">
                            <i data-lucide="plus-circle" class="w-4 h-4 text-blue-600"></i>
                            Additional Attachments
                        </h4>
                        @php
                            $customTags = is_string($reviewer->transaction->custom_document_tags) 
                                ? json_decode($reviewer->transaction->custom_document_tags, true) 
                                : $reviewer->transaction->custom_document_tags;
                        @endphp
                        @if(is_array($customTags) && count($customTags) > 0)
                            <div class="flex flex-wrap gap-2">
                                @foreach($customTags as $tag)
                                    <span class="inline-flex items-center px-3 py-1.5 rounded-lg text-sm font-medium bg-blue-100 text-blue-800 border border-blue-300">
                                        <i data-lucide="file-plus" class="w-4 h-4 mr-1.5"></i>
                                        {{ is_array($tag) ? ($tag['name'] ?? 'Unknown') : $tag }}
                                    </span>
                                @endforeach
                            </div>
                        @else
                            <div class="p-4 bg-gray-50 rounded-lg border border-gray-200 text-center">
                                <i data-lucide="inbox" class="w-8 h-8 text-gray-400 mx-auto mb-2"></i>
                                <p class="text-gray-500 text-sm">No additional attachments</p>
                            </div>
                        @endif
                    </div>
                </x-card>

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

    @if($reviewer->received_status === 'received' && $reviewer->due_date && !$reviewer->reviewed_at)
        @push('scripts')
        <script>
            // Countdown Timer - Only starts if transaction is received
            (function() {
                const dueDate = new Date('{{ $reviewer->due_date->toIso8601String() }}').getTime();
                
                function updateCountdown() {
                    const now = new Date().getTime();
                    const distance = dueDate - now;
                    
                    if (distance < 0) {
                        // Timer expired
                        document.getElementById('countdown-timer').classList.remove('from-blue-50', 'to-indigo-50', 'border-blue-200');
                        document.getElementById('countdown-timer').classList.add('from-red-50', 'to-red-100', 'border-red-300');
                        
                        document.getElementById('days').textContent = '00';
                        document.getElementById('hours').textContent = '00';
                        document.getElementById('minutes').textContent = '00';
                        document.getElementById('seconds').textContent = '00';
                        
                        document.querySelectorAll('#countdown-display .text-blue-600').forEach(el => {
                            el.classList.remove('text-blue-600');
                            el.classList.add('text-red-600');
                        });
                        
                        document.getElementById('countdown-message').innerHTML = 
                            '<span class="text-red-600 font-bold"><i data-lucide="alert-circle" class="w-4 h-4 inline mr-1"></i>DEADLINE EXCEEDED!</span>';
                        
                        // Re-render lucide icons
                        if (typeof lucide !== 'undefined') {
                            lucide.createIcons();
                        }
                        
                        clearInterval(countdownInterval);
                        return;
                    }
                    
                    // Calculate time units
                    const days = Math.floor(distance / (1000 * 60 * 60 * 24));
                    const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                    const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                    const seconds = Math.floor((distance % (1000 * 60)) / 1000);
                    
                    // Update display
                    document.getElementById('days').textContent = String(days).padStart(2, '0');
                    document.getElementById('hours').textContent = String(hours).padStart(2, '0');
                    document.getElementById('minutes').textContent = String(minutes).padStart(2, '0');
                    document.getElementById('seconds').textContent = String(seconds).padStart(2, '0');
                    
                    // Update message based on time remaining
                    let message = '';
                    let colorClass = 'text-blue-600';
                    
                    if (days === 0 && hours < 1) {
                        message = '<span class="text-red-600 font-semibold animate-pulse">⚠️ URGENT: Less than 1 hour remaining!</span>';
                        document.getElementById('countdown-timer').classList.remove('from-blue-50', 'to-indigo-50', 'border-blue-200');
                        document.getElementById('countdown-timer').classList.add('from-red-50', 'to-red-100', 'border-red-300');
                        
                        document.querySelectorAll('#countdown-display .text-blue-600').forEach(el => {
                            el.classList.remove('text-blue-600');
                            el.classList.add('text-red-600');
                        });
                    } else if (days === 0 && hours < 6) {
                        message = '<span class="text-orange-600 font-semibold">⏰ Less than 6 hours remaining</span>';
                        document.getElementById('countdown-timer').classList.remove('from-blue-50', 'to-indigo-50', 'border-blue-200');
                        document.getElementById('countdown-timer').classList.add('from-orange-50', 'to-yellow-50', 'border-orange-200');
                        
                        document.querySelectorAll('#countdown-display .text-blue-600').forEach(el => {
                            el.classList.remove('text-blue-600');
                            el.classList.add('text-orange-600');
                        });
                    } else if (days === 0) {
                        message = '<span class="text-yellow-600 font-semibold">📅 Due today</span>';
                    } else if (days === 1) {
                        message = '<span class="text-blue-600">📅 Due tomorrow</span>';
                    } else {
                        message = `<span class="${colorClass}">Timer started since receipt on {{ $reviewer->received_at->format('M d, Y h:i A') }}</span>`;
                    }
                    
                    document.getElementById('countdown-message').innerHTML = message;
                }
                
                // Update immediately
                updateCountdown();
                
                // Update every second
                const countdownInterval = setInterval(updateCountdown, 1000);
            })();
        </script>
        @endpush
    @endif
@endsection
