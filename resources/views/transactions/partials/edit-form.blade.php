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
                                                       value="{{ $step['department_id'] }}"
                                                       class="step-dept-id">
                                                <input type="hidden" 
                                                       name="workflow_snapshot[steps][{{ $index }}][department_name]" 
                                                       value="{{ $step['department_name'] ?? '' }}"
                                                       class="step-dept-name">
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
                                                           class="input input-bordered w-20 step-time-value">
                                                    <select name="workflow_snapshot[steps][{{ $index }}][process_time_unit]" 
                                                            class="select select-bordered flex-1 step-time-unit">
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
                                                       class="input input-bordered w-full step-notes"
                                                       placeholder="Additional notes for this step">
                                            @else
                                                <input type="text" 
                                                       value="{{ $step['notes'] ?? '-' }}" 
                                                       class="input input-bordered w-full bg-base-200" 
                                                       readonly>
                                            @endif
                                        </div>
                                    </div>

                                    {{-- Step Actions (Move/Remove) --}}
                                    @if($canEditWorkflow)
                                        <div class="flex flex-col gap-1">
                                            <button type="button" 
                                                    class="btn btn-ghost btn-xs btn-move-up" 
                                                    title="Move up"
                                                    {{ $index === 0 ? 'disabled' : '' }}>
                                                <i data-lucide="chevron-up" class="w-4 h-4"></i>
                                            </button>
                                            <button type="button" 
                                                    class="btn btn-ghost btn-xs btn-move-down" 
                                                    title="Move down"
                                                    {{ $index === count($workflowSteps) - 1 ? 'disabled' : '' }}>
                                                <i data-lucide="chevron-down" class="w-4 h-4"></i>
                                            </button>
                                            <button type="button" 
                                                    class="btn btn-ghost btn-xs text-error btn-remove-step" 
                                                    title="Remove step"
                                                    {{ count($workflowSteps) <= 1 ? 'disabled' : '' }}>
                                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                                            </button>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>

                    {{-- Add New Step Section --}}
                    @if($canEditWorkflow)
                        <div class="mt-4 pt-4 border-t border-base-300">
                            <h4 class="text-sm font-medium text-gray-700 mb-3">Add New Step</h4>
                            <div class="flex items-end gap-2">
                                <div class="form-control flex-1">
                                    <label class="label py-1">
                                        <span class="label-text text-xs">Department</span>
                                    </label>
                                    <select id="add-step-department" class="select select-bordered select-sm w-full">
                                        <option value="">Select department...</option>
                                        @foreach($departments as $department)
                                            <option value="{{ $department->id }}" data-name="{{ $department->name }}">
                                                {{ $department->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-control w-20">
                                    <label class="label py-1">
                                        <span class="label-text text-xs">Time</span>
                                    </label>
                                    <input type="number" id="add-step-time-value" value="1" min="1" class="input input-bordered input-sm w-full">
                                </div>
                                <div class="form-control w-28">
                                    <label class="label py-1">
                                        <span class="label-text text-xs">Unit</span>
                                    </label>
                                    <select id="add-step-time-unit" class="select select-bordered select-sm w-full">
                                        <option value="minutes">Minutes</option>
                                        <option value="hours">Hours</option>
                                        <option value="days" selected>Days</option>
                                        <option value="weeks">Weeks</option>
                                    </select>
                                </div>
                                <button type="button" id="btn-add-step" class="btn btn-primary btn-sm gap-1">
                                    <i data-lucide="plus" class="w-4 h-4"></i>
                                    Add
                                </button>
                            </div>
                        </div>
                    @endif
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

                    {{-- Department --}}
                    <div class="form-control mb-4">
                        <label class="label">
                            <span class="label-text font-medium">Department</span>
                        </label>
                        <select name="department_id" class="select select-bordered w-full">
                            <option value="">Select department</option>
                            @foreach($departments as $department)
                                <option value="{{ $department->id }}" {{ old('department_id', $transaction->department_id) == $department->id ? 'selected' : '' }}>
                                    {{ $department->name }}
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

@if($canEditWorkflow)
<script>
(function() {
    const workflowStepsContainer = document.getElementById('workflow-steps');
    
    function reindexSteps() {
        const steps = workflowStepsContainer.querySelectorAll('.step-item');
        steps.forEach((step, index) => {
            step.dataset.index = index;
            
            // Update step number display
            const stepNumber = step.querySelector('.flex-shrink-0 span');
            if (stepNumber && !stepNumber.querySelector('i')) {
                stepNumber.textContent = index + 1;
            }
            
            // Update input names
            step.querySelectorAll('[name*="workflow_snapshot[steps]"]').forEach(input => {
                input.name = input.name.replace(/\[steps\]\[\d+\]/, `[steps][${index}]`);
            });
            
            // Update move buttons state
            const moveUpBtn = step.querySelector('.btn-move-up');
            const moveDownBtn = step.querySelector('.btn-move-down');
            const removeBtn = step.querySelector('.btn-remove-step');
            
            if (moveUpBtn) moveUpBtn.disabled = index === 0;
            if (moveDownBtn) moveDownBtn.disabled = index === steps.length - 1;
            if (removeBtn) removeBtn.disabled = steps.length <= 1;
        });
    }
    
    function moveStep(stepElement, direction) {
        const steps = Array.from(workflowStepsContainer.querySelectorAll('.step-item'));
        const currentIndex = steps.indexOf(stepElement);
        const targetIndex = currentIndex + direction;
        
        if (targetIndex < 0 || targetIndex >= steps.length) return;
        
        const targetElement = steps[targetIndex];
        
        if (direction === -1) {
            workflowStepsContainer.insertBefore(stepElement, targetElement);
        } else {
            workflowStepsContainer.insertBefore(targetElement, stepElement);
        }
        
        reindexSteps();
    }
    
    function removeStep(stepElement) {
        const steps = workflowStepsContainer.querySelectorAll('.step-item');
        if (steps.length <= 1) {
            alert('Workflow must have at least one step');
            return;
        }
        
        stepElement.remove();
        reindexSteps();
    }
    
    function addStep() {
        const deptSelect = document.getElementById('add-step-department');
        const timeValue = document.getElementById('add-step-time-value');
        const timeUnit = document.getElementById('add-step-time-unit');
        
        const deptId = deptSelect.value;
        const deptName = deptSelect.options[deptSelect.selectedIndex]?.dataset?.name;
        
        if (!deptId || !deptName) {
            alert('Please select a department');
            return;
        }
        
        const steps = workflowStepsContainer.querySelectorAll('.step-item');
        const newIndex = steps.length;
        
        const stepHtml = `
            <div class="step-item border border-base-300 rounded-lg p-4 bg-base-50" data-index="${newIndex}">
                <div class="flex items-start gap-4">
                    <div class="flex-shrink-0">
                        <span class="flex items-center justify-center w-8 h-8 rounded-full bg-base-200 text-gray-600">
                            ${newIndex + 1}
                        </span>
                    </div>
                    <div class="flex-1 grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="label">
                                <span class="label-text font-medium">Department</span>
                            </label>
                            <input type="text" value="${deptName}" class="input input-bordered w-full bg-base-200" readonly>
                            <input type="hidden" name="workflow_snapshot[steps][${newIndex}][department_id]" value="${deptId}" class="step-dept-id">
                            <input type="hidden" name="workflow_snapshot[steps][${newIndex}][department_name]" value="${deptName}" class="step-dept-name">
                        </div>
                        <div>
                            <label class="label">
                                <span class="label-text font-medium">Processing Time</span>
                            </label>
                            <div class="flex gap-2">
                                <input type="number" name="workflow_snapshot[steps][${newIndex}][process_time_value]" value="${timeValue.value}" min="1" class="input input-bordered w-20 step-time-value">
                                <select name="workflow_snapshot[steps][${newIndex}][process_time_unit]" class="select select-bordered flex-1 step-time-unit">
                                    <option value="minutes" ${timeUnit.value === 'minutes' ? 'selected' : ''}>Minutes</option>
                                    <option value="hours" ${timeUnit.value === 'hours' ? 'selected' : ''}>Hours</option>
                                    <option value="days" ${timeUnit.value === 'days' ? 'selected' : ''}>Days</option>
                                    <option value="weeks" ${timeUnit.value === 'weeks' ? 'selected' : ''}>Weeks</option>
                                </select>
                            </div>
                        </div>
                        <div class="md:col-span-2">
                            <label class="label">
                                <span class="label-text font-medium">Notes</span>
                            </label>
                            <input type="text" name="workflow_snapshot[steps][${newIndex}][notes]" value="" class="input input-bordered w-full step-notes" placeholder="Additional notes for this step">
                        </div>
                    </div>
                    <div class="flex flex-col gap-1">
                        <button type="button" class="btn btn-ghost btn-xs btn-move-up" title="Move up">
                            <i data-lucide="chevron-up" class="w-4 h-4"></i>
                        </button>
                        <button type="button" class="btn btn-ghost btn-xs btn-move-down" title="Move down" disabled>
                            <i data-lucide="chevron-down" class="w-4 h-4"></i>
                        </button>
                        <button type="button" class="btn btn-ghost btn-xs text-error btn-remove-step" title="Remove step">
                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                        </button>
                    </div>
                </div>
            </div>
        `;
        
        workflowStepsContainer.insertAdjacentHTML('beforeend', stepHtml);
        
        // Reset form
        deptSelect.value = '';
        timeValue.value = '1';
        timeUnit.value = 'days';
        
        reindexSteps();
        
        // Reinitialize lucide icons
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    }
    
    // Event delegation for step actions
    workflowStepsContainer.addEventListener('click', function(e) {
        const target = e.target.closest('button');
        if (!target) return;
        
        const stepItem = target.closest('.step-item');
        if (!stepItem) return;
        
        if (target.classList.contains('btn-move-up')) {
            moveStep(stepItem, -1);
        } else if (target.classList.contains('btn-move-down')) {
            moveStep(stepItem, 1);
        } else if (target.classList.contains('btn-remove-step')) {
            removeStep(stepItem);
        }
    });
    
    // Add step button
    const addStepBtn = document.getElementById('btn-add-step');
    if (addStepBtn) {
        addStepBtn.addEventListener('click', addStep);
    }
})();
</script>
@endif
