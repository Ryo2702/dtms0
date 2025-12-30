<div class="space-y-6">
    {{-- Transaction Header --}}
    <div class="flex items-start justify-between">
        <div>
            <h3 class="text-xl font-bold">{{ $transaction->transaction_code }}</h3>
            <p class="text-gray-600 mt-1">{{ $transaction->workflow->transaction_name ?? 'Transaction' }}</p>
        </div>
        <div class="flex items-center gap-2">
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
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Main Content --}}
        <div class="lg:col-span-2 space-y-6">
            {{-- Transaction Status --}}
            <div class="card bg-base-100 border border-base-300">
                <div class="card-body">
                    <h4 class="card-title text-base mb-3">
                        <i data-lucide="activity" class="w-4 h-4 text-primary"></i>
                        Status
                    </h4>
                    
                    <div class="flex items-center gap-4 flex-wrap text-sm">
                        <div class="flex items-center gap-2">
                            <span class="text-gray-500">Urgency:</span>
                            @php
                                $urgencyClass = match($transaction->level_of_urgency) {
                                    'highly_urgent' => 'badge-error',
                                    'urgent' => 'badge-warning',
                                    default => 'badge-ghost'
                                };
                            @endphp
                            <span class="badge {{ $urgencyClass }} badge-sm">
                                {{ ucfirst(str_replace('_', ' ', $transaction->level_of_urgency)) }}
                            </span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-gray-500">State:</span>
                            <span class="font-medium">{{ ucfirst(str_replace('_', ' ', $transaction->current_state)) }}</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Workflow Progress --}}
            <div class="card bg-base-100 border border-base-300">
                <div class="card-body">
                    <h4 class="card-title text-base mb-3">
                        <i data-lucide="git-branch" class="w-4 h-4 text-primary"></i>
                        Workflow Progress
                    </h4>
                    
                    @php
                        $steps = $transaction->getWorkflowSteps();
                        $currentStep = $transaction->current_workflow_step;
                    @endphp

                    <div class="space-y-3">
                        @foreach($steps as $index => $step)
                            @php
                                $stepNumber = $index + 1;
                                $isCompleted = $stepNumber < $currentStep;
                                $isCurrent = $stepNumber === $currentStep;
                                $isPending = $stepNumber > $currentStep;
                            @endphp
                            <div class="flex items-start gap-3">
                                {{-- Step Indicator --}}
                                <div class="flex-shrink-0">
                                    @if($isCompleted)
                                        <span class="flex items-center justify-center w-6 h-6 rounded-full bg-success text-success-content text-xs">
                                            <i data-lucide="check" class="w-3 h-3"></i>
                                        </span>
                                    @elseif($isCurrent)
                                        <span class="flex items-center justify-center w-6 h-6 rounded-full bg-primary text-primary-content animate-pulse text-xs">
                                            {{ $stepNumber }}
                                        </span>
                                    @else
                                        <span class="flex items-center justify-center w-6 h-6 rounded-full bg-base-200 text-gray-400 text-xs">
                                            {{ $stepNumber }}
                                        </span>
                                    @endif
                                </div>

                                {{-- Step Content --}}
                                <div class="flex-1 {{ !$loop->last ? 'border-l border-base-300 -ml-3 pl-6 pb-3' : '' }}">
                                    <div class="flex items-center justify-between">
                                        <h5 class="font-medium text-sm {{ $isPending ? 'text-gray-400' : '' }}">
                                            {{ $step['department_name'] ?? 'Department ' . $stepNumber }}
                                        </h5>
                                        @if($isCompleted)
                                            <span class="badge badge-success badge-xs">Completed</span>
                                        @elseif($isCurrent)
                                            <span class="badge badge-primary badge-xs">In Progress</span>
                                        @else
                                            <span class="badge badge-ghost badge-xs">Pending</span>
                                        @endif
                                    </div>
                                    <p class="text-xs text-gray-500 mt-0.5">
                                        {{ $step['process_time_value'] ?? 1 }} {{ $step['process_time_unit'] ?? 'days' }}
                                    </p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Available Actions --}}
            @if(!empty($availableActions) && !$transaction->isCompleted() && !$transaction->isCancelled())
                <div class="card bg-base-100 border border-base-300">
                    <div class="card-body">
                        <h4 class="card-title text-base mb-3">
                            <i data-lucide="zap" class="w-4 h-4 text-primary"></i>
                            Actions
                        </h4>
                        
                        <form action="{{ route('transactions.execute-action', $transaction) }}" method="POST">
                            @csrf
                            <div class="space-y-4">
                                <div class="form-control">
                                    <label class="label">
                                        <span class="label-text font-medium">Remarks (Optional)</span>
                                    </label>
                                    <textarea name="remarks" rows="2" class="textarea textarea-bordered textarea-sm" placeholder="Add any remarks or notes..."></textarea>
                                </div>
                                
                                <div class="flex gap-2 flex-wrap">
                                    @foreach($availableActions as $action => $label)
                                        @php
                                            $btnClass = match($action) {
                                                'approve' => 'btn-success btn-sm',
                                                'reject' => 'btn-error btn-sm',
                                                'cancel' => 'btn-ghost btn-sm',
                                                default => 'btn-primary btn-sm'
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
        </div>

        {{-- Sidebar --}}
        <div class="space-y-6">
            {{-- Transaction Details --}}
            <div class="card bg-base-100 border border-base-300">
                <div class="card-body">
                    <h4 class="card-title text-base mb-3">
                        <i data-lucide="info" class="w-4 h-4 text-primary"></i>
                        Details
                    </h4>

                    <div class="space-y-2 text-sm">
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
                    </div>
                </div>
            </div>

            {{-- Workflow Info --}}
            <div class="card bg-base-100 border border-base-300">
                <div class="card-body">
                    <h4 class="card-title text-base mb-3">
                        <i data-lucide="workflow" class="w-4 h-4 text-primary"></i>
                        Workflow
                    </h4>

                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-500">Name</span>
                            <span class="font-medium">{{ $transaction->workflow->transaction_name ?? 'N/A' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Progress</span>
                            <span class="font-medium">{{ $transaction->current_workflow_step }} of {{ $transaction->total_workflow_steps }}</span>
                        </div>
                    </div>

                    {{-- Progress Bar --}}
                    <div class="mt-3">
                        @php
                            $progress = $transaction->total_workflow_steps > 0 
                                ? round(($transaction->current_workflow_step / $transaction->total_workflow_steps) * 100) 
                                : 0;
                        @endphp
                        <div class="flex justify-between text-xs text-gray-500 mb-1">
                            <span>Progress</span>
                            <span>{{ $progress }}%</span>
                        </div>
                        <progress class="progress progress-primary w-full h-2" value="{{ $progress }}" max="100"></progress>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Action Buttons --}}
    <div class="flex justify-end gap-3 pt-4 border-t">
        <button type="button" onclick="window['view-transaction-modal'].close()" class="btn btn-ghost">
            Close
        </button>
        @if(!$transaction->isCompleted() && !$transaction->isCancelled())
            <button type="button" onclick="window['view-transaction-modal'].close(); openEditModal({{ $transaction->id }});" class="btn btn-outline">
                <i data-lucide="edit" class="w-4 h-4 mr-1"></i>
                Edit
            </button>
        @endif
        <a href="{{ route('transactions.show', $transaction) }}" class="btn btn-primary">
            <i data-lucide="external-link" class="w-4 h-4 mr-1"></i>
            Full View
        </a>
    </div>
</div>
