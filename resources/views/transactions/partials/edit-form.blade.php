<form action="{{ route('transactions.update', $transaction) }}" method="POST" id="edit-transaction-form">
    @csrf
    @method('PUT')
    
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Main Form Section --}}
        <div class="lg:col-span-2 space-y-6">
            {{-- Workflow Route Details --}}
            <div class="card bg-base-100 border border-base-300">
                <div class="card-body">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="card-title text-lg">
                            <i data-lucide="git-branch" class="w-5 h-5 text-primary"></i>
                            Workflow Route
                        </h3>
                        @if(!$canEditWorkflow)
                            <span class="badge badge-warning badge-sm">
                                <i data-lucide="lock" class="w-3 h-3 mr-1"></i>
                                Locked (In Progress)
                            </span>
                        @endif
                    </div>
                    
                    @if($canEditWorkflow)
                        <p class="text-sm text-gray-500 mb-4">
                            You can adjust the workflow route while the transaction is at the initial step.
                        </p>
                    @else
                        <p class="text-sm text-gray-500 mb-4">
                            Workflow route cannot be modified once the transaction has progressed beyond the first step.
                        </p>
                    @endif

                    <div id="workflow-steps" class="space-y-4">
                        @foreach($workflowSteps as $index => $step)
                            @php
                                $stepNumber = $index + 1;
                                $isCompleted = $stepNumber < $transaction->current_workflow_step;
                                $isCurrent = $stepNumber === $transaction->current_workflow_step;
                            @endphp
                            <div class="step-item border border-base-300 rounded-lg p-4 {{ $isCompleted ? 'bg-success/10 border-success/30' : ($isCurrent ? 'bg-primary/10 border-primary/30' : 'bg-base-50') }}" data-index="{{ $index }}">
                                <div class="flex items-start gap-4">
                                    {{-- Step Number/Status --}}
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
                                            <span class="flex items-center justify-center w-8 h-8 rounded-full bg-base-200 text-gray-600">
                                                {{ $stepNumber }}
                                            </span>
                                        @endif
                                    </div>

                                    {{-- Step Details --}}
                                    <div class="flex-1 grid grid-cols-1 md:grid-cols-2 gap-4">
                                        {{-- Department (Read-only) --}}
                                        <div>
                                            <label class="label">
                                                <span class="label-text font-medium">Department</span>
                                            </label>
                                            <input type="text" 
                                                   value="{{ $step['department_name'] ?? 'Unknown Department' }}" 
                                                   class="input input-bordered w-full bg-base-200" 
                                                   readonly>
                                            @if($canEditWorkflow)
                                                <input type="hidden" 
                                                       name="workflow_snapshot[steps][{{ $index }}][department_id]" 
                                                       value="{{ $step['department_id'] }}">
                                                <input type="hidden" 
                                                       name="workflow_snapshot[steps][{{ $index }}][department_name]" 
                                                       value="{{ $step['department_name'] ?? '' }}">
                                            @endif
                                        </div>

                                        {{-- Processing Time --}}
                                        <div>
                                            <label class="label">
                                                <span class="label-text font-medium">Processing Time</span>
                                            </label>
                                            @if($canEditWorkflow)
                                                <div class="flex gap-2">
                                                    <input type="number" 
                                                           name="workflow_snapshot[steps][{{ $index }}][process_time_value]" 
                                                           value="{{ $step['process_time_value'] ?? 1 }}" 
                                                           min="1"
                                                           class="input input-bordered w-20">
                                                    <select name="workflow_snapshot[steps][{{ $index }}][process_time_unit]" 
                                                            class="select select-bordered flex-1">
                                                        <option value="minutes" {{ ($step['process_time_unit'] ?? '') === 'minutes' ? 'selected' : '' }}>Minutes</option>
                                                        <option value="hours" {{ ($step['process_time_unit'] ?? '') === 'hours' ? 'selected' : '' }}>Hours</option>
                                                        <option value="days" {{ ($step['process_time_unit'] ?? 'days') === 'days' ? 'selected' : '' }}>Days</option>
                                                        <option value="weeks" {{ ($step['process_time_unit'] ?? '') === 'weeks' ? 'selected' : '' }}>Weeks</option>
                                                    </select>
                                                </div>
                                            @else
                                                <input type="text" 
                                                       value="{{ ($step['process_time_value'] ?? 1) . ' ' . ($step['process_time_unit'] ?? 'days') }}" 
                                                       class="input input-bordered w-full bg-base-200" 
                                                       readonly>
                                            @endif
                                        </div>

                                        {{-- Notes --}}
                                        <div class="md:col-span-2">
                                            <label class="label">
                                                <span class="label-text font-medium">Notes</span>
                                            </label>
                                            @if($canEditWorkflow)
                                                <input type="text" 
                                                       name="workflow_snapshot[steps][{{ $index }}][notes]" 
                                                       value="{{ $step['notes'] ?? '' }}" 
                                                       class="input input-bordered w-full"
                                                       placeholder="Additional notes for this step">
                                            @else
                                                <input type="text" 
                                                       value="{{ $step['notes'] ?? '-' }}" 
                                                       class="input input-bordered w-full bg-base-200" 
                                                       readonly>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        {{-- Sidebar --}}
        <div class="space-y-6">
            {{-- Transaction Settings --}}
            <div class="card bg-base-100 border border-base-300">
                <div class="card-body">
                    <h3 class="card-title text-lg mb-4">
                        <i data-lucide="settings" class="w-5 h-5 text-primary"></i>
                        Transaction Settings
                    </h3>

                    {{-- Assign Staff --}}
                    <div class="form-control mb-4">
                        <label class="label">
                            <span class="label-text font-medium">Assign Staff <span class="text-error">*</span></span>
                        </label>
                        <select name="assign_staff_id" class="select select-bordered w-full" required>
                            <option value="">Select staff member</option>
                            @foreach($assignStaff as $staff)
                                <option value="{{ $staff->id }}" {{ old('assign_staff_id', $transaction->assign_staff_id) == $staff->id ? 'selected' : '' }}>
                                    {{ $staff->full_name }} - {{ $staff->position }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Level of Urgency --}}
                    <div class="form-control mb-4">
                        <label class="label">
                            <span class="label-text font-medium">Level of Urgency</span>
                        </label>
                        <select name="level_of_urgency" class="select select-bordered w-full">
                            <option value="normal" {{ old('level_of_urgency', $transaction->level_of_urgency) === 'normal' ? 'selected' : '' }}>
                                Normal
                            </option>
                            <option value="urgent" {{ old('level_of_urgency', $transaction->level_of_urgency) === 'urgent' ? 'selected' : '' }}>
                                Urgent
                            </option>
                            <option value="highly_urgent" {{ old('level_of_urgency', $transaction->level_of_urgency) === 'highly_urgent' ? 'selected' : '' }}>
                                Highly Urgent
                            </option>
                        </select>
                    </div>
                </div>
            </div>

            {{-- Transaction Info --}}
            <div class="card bg-base-100 border border-base-300">
                <div class="card-body">
                    <h3 class="card-title text-lg mb-4">
                        <i data-lucide="info" class="w-5 h-5 text-primary"></i>
                        Information
                    </h3>

                    <div class="space-y-3 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-500">Transaction Code</span>
                            <span class="font-mono font-medium">{{ $transaction->transaction_code }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Current Step</span>
                            <span class="font-medium">{{ $transaction->current_workflow_step }} of {{ $transaction->total_workflow_steps }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Status</span>
                            <span class="badge badge-sm">{{ ucfirst(str_replace('_', ' ', $transaction->transaction_status)) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Revision</span>
                            <span class="font-medium">{{ $transaction->revision_number }}</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Submit Buttons --}}
            <div class="flex flex-col gap-3">
                <button type="submit" class="btn btn-primary w-full">
                    <i data-lucide="save" class="w-4 h-4 mr-2"></i>
                    Save Changes
                </button>
                <button type="button" onclick="window['edit-transaction-modal'].close()" class="btn btn-ghost w-full">
                    Cancel
                </button>
            </div>
        </div>
    </div>
</form>
