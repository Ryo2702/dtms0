{{-- filepath: /home/ryo/project/dtms0/resources/views/workflows/create.blade.php --}}
@extends('layouts.app')

@section('title', 'Create Workflow')

@section('content')
<div class="p-4 sm:p-6">
    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <nav class="text-sm breadcrumbs mb-2">
                <ul>
                    <li><a href="{{ route('admin.workflows.index') }}" class="text-primary">Workflows</a></li>
                    <li>Create</li>
                </ul>
            </nav>
            <h1 class="text-2xl font-bold text-gray-900">Create New Workflow</h1>
            <p class="text-gray-600 mt-1">Define the document routing steps</p>
        </div>
    </div>

    {{-- Flash Messages --}}
    @if(session('error'))
        <div class="alert alert-error mb-4">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-error mb-4">
            <ul class="list-disc list-inside">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Step Builder --}}
        <div class="bg-base-100 rounded-lg shadow-md p-6">
            <form action="{{ route('admin.workflows.store') }}" method="POST" id="workflowForm">
                @csrf

                {{-- Transaction Type Selection --}}
                <div class="form-control mb-4">
                    <label class="label">
                        <span class="label-text font-medium">Transaction Type *</span>
                    </label>
                    <select name="transaction_type_id" class="select select-bordered" required>
                        <option value="">Select Transaction Type...</option>
                        @forelse($transactionTypes ?? [] as $type)
                            <option value="{{ $type->id }}" 
                                {{ (($transactionType->id ?? null) == $type->id || old('transaction_type_id') == $type->id) ? 'selected' : '' }}>
                                {{ $type->document_name }}
                            </option>
                        @empty
                            <option value="" disabled>No transaction types available</option>
                        @endforelse
                    </select>
                </div>

                {{-- Workflow Name --}}
                <div class="form-control mb-4">
                    <label class="label">
                        <span class="label-text font-medium">Workflow Name *</span>
                    </label>
                    <input type="text" name="name" class="input input-bordered"
                           placeholder="e.g., Travel Order - Local" value="{{ old('name') }}" required>
                </div>

                {{-- Description --}}
                <div class="form-control mb-4">
                    <label class="label">
                        <span class="label-text font-medium">Description</span>
                        <span class="label-text-alt text-gray-400">Optional</span>
                    </label>
                    <textarea name="description" class="textarea textarea-bordered" rows="2"
                              placeholder="Brief description of this workflow variant">{{ old('description') }}</textarea>
                </div>

                {{-- Default Workflow Checkbox --}}
                <div class="form-control mb-4">
                    <label class="label cursor-pointer justify-start gap-4">
                        <input type="checkbox" name="is_default" value="1" class="checkbox checkbox-primary"
                               {{ old('is_default') ? 'checked' : '' }}>
                        <div>
                            <span class="label-text font-medium">Set as Default Workflow</span>
                            <p class="text-xs text-gray-500">This workflow will be used by default for new transactions</p>
                        </div>
                    </label>
                </div>

                {{-- Auto-calculated Difficulty Display --}}
                <div class="mb-6 p-4 bg-gray-50 rounded-lg border border-gray-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <h4 class="text-sm font-semibold text-gray-900">Workflow Complexity</h4>
                            <p class="text-xs text-gray-500 mt-1">Auto-calculated based on number of departments</p>
                        </div>
                        <div class="flex items-center gap-3">
                            <div id="stepCountBadge" class="badge badge-ghost">
                                <span id="stepCount">0</span> departments
                            </div>
                            <span id="difficultyBadge" class="badge badge-ghost">-</span>
                        </div>
                    </div>
                    {{-- Hidden input for difficulty (auto-set by JS) --}}
                    <input type="hidden" name="difficulty" id="difficultyInput" value="simple">
                </div>

                {{-- Workflow Steps Section --}}
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Workflow Steps</h3>
                <p class="text-sm text-gray-500 mb-4">Define the sequence of departments for document approval. The order determines the routing flow.</p>

                <div id="stepsContainer" class="space-y-4">
                    <p class="text-gray-500 text-center py-8" id="noStepsMessage">
                        No steps configured. Add your first step below.
                    </p>
                </div>

                <button type="button" id="addStepBtn" class="btn btn-outline btn-primary w-full mt-4">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Add Step
                </button>

                <div class="flex justify-end gap-3 pt-6 border-t border-gray-200 mt-6">
                    <a href="{{ route('admin.workflows.index') }}" class="btn btn-ghost">Cancel</a>
                    <button type="submit" class="btn btn-primary" id="saveBtn">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        Save Workflow
                    </button>
                </div>
            </form>
        </div>

        {{-- Transition Map Preview --}}
        <div class="bg-base-100 rounded-lg shadow-md p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-2">Transition Map Preview</h3>
            <p class="text-sm text-gray-500 mb-4">Auto-generated state transitions based on your configuration</p>

            <div id="transitionPreview" class="bg-gray-900 rounded-lg p-4 overflow-auto max-h-[500px]">
                <pre class="text-green-400 text-sm"><code id="transitionJson">{
  "steps": [],
  "transitions": {}
}</code></pre>
            </div>

            <button type="button" id="refreshPreviewBtn" class="btn btn-outline btn-sm mt-4">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
                Refresh Preview
            </button>

            {{-- Visual Flow --}}
            <div class="mt-6">
                <h4 class="text-sm font-medium text-gray-700 mb-3">Visual Flow</h4>
                <div id="visualFlow" class="flex flex-wrap items-center gap-2">
                    <span class="text-gray-400 text-sm">Add steps to see the flow</span>
                </div>
            </div>

            {{-- Difficulty Legend --}}
            <div class="mt-6 p-4 bg-gray-50 rounded-lg">
                <h4 class="text-sm font-medium text-gray-700 mb-3">Complexity Level Guide</h4>
                <div class="space-y-2">
                    <div class="flex items-center gap-3">
                        <span class="badge badge-success">Simple</span>
                        <span class="text-xs text-gray-600">Simple workflow with 1-2 departments</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="badge badge-warning">Moderate</span>
                        <span class="text-xs text-gray-600">Moderate workflow with 3-4 departments</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="badge badge-error">Complex</span>
                        <span class="text-xs text-gray-600">Complex workflow with 5+ departments</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    const departments = @json($departments);
    let stepIndex = 0;

    function createStepHtml(index, departmentId = '', canReturnTo = [], processTimeValue = 3, processTimeUnit = 'days', notes = '') {
        const deptOptions = departments.map(d =>
            `<option value="${d.id}" ${departmentId == d.id ? 'selected' : ''}>${d.name}</option>`
        ).join('');

        return `
            <div class="step-item border rounded-lg p-4 bg-gray-50" data-index="${index}">
                <div class="flex items-center justify-between mb-3">
                    <span class="badge badge-primary step-number">Step ${index + 1}</span>
                    <div class="flex gap-1">
                        <button type="button" class="btn btn-xs btn-ghost move-up" title="Move Up">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/>
                            </svg>
                        </button>
                        <button type="button" class="btn btn-xs btn-ghost move-down" title="Move Down">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>
                        <button type="button" class="btn btn-xs btn-error remove-step" title="Remove">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="form-control mb-3">
                    <label class="label">
                        <span class="label-text font-medium">Department</span>
                    </label>
                    <select name="steps[${index}][department_id]" class="select select-bordered department-select" required>
                        <option value="">Select Department</option>
                        ${deptOptions}
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-3 mb-3">
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-medium">Process Time</span>
                        </label>
                        <input type="number" 
                               name="steps[${index}][process_time_value]" 
                               class="input input-bordered input-sm process-time-value" 
                               value="${processTimeValue}" 
                               min="1" 
                               required>
                    </div>
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-medium">Unit</span>
                        </label>
                        <select name="steps[${index}][process_time_unit]" class="select select-bordered select-sm process-time-unit" required>
                            <option value="hours" ${processTimeUnit === 'hours' ? 'selected' : ''}>Hours</option>
                            <option value="days" ${processTimeUnit === 'days' ? 'selected' : ''}>Days</option>
                            <option value="weeks" ${processTimeUnit === 'weeks' ? 'selected' : ''}>Weeks</option>
                        </select>
                    </div>
                </div>

                <div class="form-control mb-3">
                    <label class="label">
                        <span class="label-text font-medium">Instructions/Notes</span>
                        <span class="label-text-alt text-gray-400">Optional</span>
                    </label>
                    <textarea name="steps[${index}][notes]" 
                              class="textarea textarea-bordered textarea-sm step-notes" 
                              rows="2" 
                              placeholder="e.g., Review budget allocation and verify fund availability...">${notes}</textarea>
                </div>

                <div class="form-control return-to-container" style="display: none;">
                    <label class="label">
                        <span class="label-text font-medium">Can Return To</span>
                        <span class="label-text-alt text-gray-400">Enable loop-back routing</span>
                    </label>
                    <div class="return-to-options space-y-2 p-3 bg-white rounded border border-gray-200">
                        <span class="text-gray-400 text-sm">No previous steps available</span>
                    </div>
                </div>
            </div>
        `;
    }

    // Auto-calculate difficulty based on step count
    function calculateDifficulty(count) {
        if (count >= 5) return 'complex';
        if (count >= 3) return 'moderate';
        return 'simple';
    }

    function getDifficultyLabel(difficulty) {
        switch(difficulty) {
            case 'complex': return 'Complex';
            case 'moderate': return 'Moderate';
            default: return 'Simple';
        }
    }

    function getDifficultyBadgeClass(difficulty) {
        switch(difficulty) {
            case 'complex': return 'badge-error';
            case 'moderate': return 'badge-warning';
            default: return 'badge-success';
        }
    }

    // Update step count and auto-set difficulty
    function updateStepCountAndDifficulty() {
        const count = document.querySelectorAll('.step-item').length;
        document.getElementById('stepCount').textContent = count;

        // Auto-calculate difficulty
        const difficulty = calculateDifficulty(count);
        document.getElementById('difficultyInput').value = difficulty;

        // Update step count badge color
        const stepBadge = document.getElementById('stepCountBadge');
        stepBadge.classList.remove('badge-success', 'badge-warning', 'badge-error', 'badge-ghost');
        
        // Update difficulty badge
        const diffBadge = document.getElementById('difficultyBadge');
        diffBadge.classList.remove('badge-success', 'badge-warning', 'badge-error', 'badge-ghost');
        
        if (count === 0) {
            stepBadge.classList.add('badge-ghost');
            diffBadge.classList.add('badge-ghost');
            diffBadge.textContent = '-';
        } else {
            stepBadge.classList.add(getDifficultyBadgeClass(difficulty));
            diffBadge.classList.add(getDifficultyBadgeClass(difficulty));
            diffBadge.textContent = getDifficultyLabel(difficulty);
        }
    }

    // Add Step button
    document.getElementById('addStepBtn').addEventListener('click', function() {
        const container = document.getElementById('stepsContainer');
        const noStepsMsg = document.getElementById('noStepsMessage');
        if (noStepsMsg) noStepsMsg.remove();

        container.insertAdjacentHTML('beforeend', createStepHtml(stepIndex));
        stepIndex++;

        updateStepNumbers();
        updateReturnToOptions();
        attachEventListeners();
        updatePreview();
        updateVisualFlow();
        updateStepCountAndDifficulty();
    });

    function attachEventListeners() {
        // Remove step
        document.querySelectorAll('.remove-step').forEach(btn => {
            btn.onclick = function() {
                this.closest('.step-item').remove();
                updateStepNumbers();
                updateReturnToOptions();
                updatePreview();
                updateVisualFlow();
                updateStepCountAndDifficulty();
                
                // Show empty message if no steps
                if (document.querySelectorAll('.step-item').length === 0) {
                    const container = document.getElementById('stepsContainer');
                    container.innerHTML = '<p class="text-gray-500 text-center py-8" id="noStepsMessage">No steps configured. Add your first step below.</p>';
                }
            };
        });

        // Move up
        document.querySelectorAll('.move-up').forEach(btn => {
            btn.onclick = function() {
                const item = this.closest('.step-item');
                const prev = item.previousElementSibling;
                if (prev && prev.classList.contains('step-item')) {
                    item.parentNode.insertBefore(item, prev);
                    updateStepNumbers();
                    updateReturnToOptions();
                    updatePreview();
                    updateVisualFlow();
                }
            };
        });

        // Move down
        document.querySelectorAll('.move-down').forEach(btn => {
            btn.onclick = function() {
                const item = this.closest('.step-item');
                const next = item.nextElementSibling;
                if (next && next.classList.contains('step-item')) {
                    item.parentNode.insertBefore(next, item);
                    updateStepNumbers();
                    updateReturnToOptions();
                    updatePreview();
                    updateVisualFlow();
                }
            };
        });

        // Department change
        document.querySelectorAll('.department-select').forEach(select => {
            select.onchange = function() {
                updateReturnToOptions();
                updatePreview();
                updateVisualFlow();
            };
        });

        // Process time and notes change
        document.querySelectorAll('.process-time-value, .process-time-unit, .step-notes').forEach(input => {
            input.onchange = updatePreview;
            input.oninput = debounce(updatePreview, 500);
        });
    }

    function debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }

    function updateStepNumbers() {
        document.querySelectorAll('.step-item').forEach((item, index) => {
            item.querySelector('.step-number').textContent = `Step ${index + 1}`;
            item.dataset.index = index;

            const select = item.querySelector('.department-select');
            if (select) select.name = `steps[${index}][department_id]`;

            const timeValue = item.querySelector('.process-time-value');
            if (timeValue) timeValue.name = `steps[${index}][process_time_value]`;

            const timeUnit = item.querySelector('.process-time-unit');
            if (timeUnit) timeUnit.name = `steps[${index}][process_time_unit]`;

            const notes = item.querySelector('.step-notes');
            if (notes) notes.name = `steps[${index}][notes]`;

            item.querySelectorAll('.return-to-checkbox').forEach(cb => {
                cb.name = `steps[${index}][can_return_to][]`;
            });
        });
    }

    function updateReturnToOptions() {
        const steps = document.querySelectorAll('.step-item');

        steps.forEach((step, index) => {
            const container = step.querySelector('.return-to-container');
            const optionsDiv = step.querySelector('.return-to-options');

            if (index === 0) {
                container.style.display = 'none';
                return;
            }

            container.style.display = 'block';
            optionsDiv.innerHTML = '';

            let hasOptions = false;

            for (let i = 0; i < index; i++) {
                const prevStep = steps[i];
                const prevSelect = prevStep.querySelector('.department-select');
                const prevDeptId = prevSelect?.value;
                const prevDeptName = prevSelect?.options[prevSelect.selectedIndex]?.text;

                if (prevDeptId && prevDeptName && prevDeptName !== 'Select Department') {
                    hasOptions = true;
                    const checkboxHtml = `
                        <label class="flex items-center gap-2 cursor-pointer hover:bg-gray-50 p-1 rounded">
                            <input type="checkbox" 
                                   class="checkbox checkbox-sm return-to-checkbox" 
                                   name="steps[${index}][can_return_to][]" 
                                   value="${prevDeptId}">
                            <span class="text-sm">Step ${i + 1}: ${prevDeptName}</span>
                        </label>
                    `;
                    optionsDiv.insertAdjacentHTML('beforeend', checkboxHtml);
                }
            }

            if (!hasOptions) {
                optionsDiv.innerHTML = '<span class="text-gray-400 text-sm">Select departments in previous steps first</span>';
            }

            optionsDiv.querySelectorAll('.return-to-checkbox').forEach(cb => {
                cb.onchange = updatePreview;
            });
        });
    }

    function getFormData() {
        const steps = [];
        document.querySelectorAll('.step-item').forEach((item) => {
            const deptId = item.querySelector('.department-select')?.value;
            const processTimeValue = item.querySelector('.process-time-value')?.value || 3;
            const processTimeUnit = item.querySelector('.process-time-unit')?.value || 'days';
            const notes = item.querySelector('.step-notes')?.value || '';
            const canReturnTo = [];

            item.querySelectorAll('.return-to-checkbox:checked').forEach(cb => {
                canReturnTo.push(parseInt(cb.value));
            });

            if (deptId) {
                steps.push({
                    department_id: parseInt(deptId),
                    process_time_value: parseInt(processTimeValue),
                    process_time_unit: processTimeUnit,
                    notes: notes,
                    can_return_to: canReturnTo
                });
            }
        });

        // Get auto-calculated difficulty
        const difficulty = document.getElementById('difficultyInput').value;

        return { steps, difficulty };
    }

    function updatePreview() {
        const data = getFormData();

        if (data.steps.length === 0) {
            document.getElementById('transitionJson').textContent = JSON.stringify({
                difficulty: data.difficulty,
                steps: [],
                transitions: {}
            }, null, 2);
            updateVisualFlow();
            return;
        }

        fetch('{{ route("admin.workflows.preview") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(config => {
            document.getElementById('transitionJson').textContent = JSON.stringify(config, null, 2);
            updateVisualFlow();
        })
        .catch(err => {
            console.error('Preview error:', err);
        });
    }

    function updateVisualFlow() {
        const container = document.getElementById('visualFlow');
        const steps = document.querySelectorAll('.step-item');
        const difficulty = document.getElementById('difficultyInput').value;
        const badgeClass = getDifficultyBadgeClass(difficulty);

        if (steps.length === 0) {
            container.innerHTML = '<span class="text-gray-400 text-sm">Add steps to see the flow</span>';
            return;
        }

        let html = '';
        steps.forEach((step, index) => {
            const select = step.querySelector('.department-select');
            const deptName = select?.options[select.selectedIndex]?.text || 'Not selected';
            const timeValue = step.querySelector('.process-time-value')?.value || '';
            const timeUnit = step.querySelector('.process-time-unit')?.value || '';

            if (deptName !== 'Select Department') {
                if (index > 0) {
                    html += `
                        <svg class="w-6 h-6 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                        </svg>
                    `;
                }
                html += `
                    <div class="flex flex-col items-center">
                        <span class="badge ${badgeClass}">${deptName}</span>
                        <span class="text-xs text-gray-500 mt-1">${timeValue} ${timeUnit}</span>
                    </div>
                `;
            }
        });

        html += `
            <svg class="w-6 h-6 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
            </svg>
            <span class="badge badge-neutral">Completed</span>
        `;

        container.innerHTML = html || '<span class="text-gray-400 text-sm">Add steps to see the flow</span>';
    }

    document.getElementById('refreshPreviewBtn').addEventListener('click', updatePreview);

    // Form submission validation
    document.getElementById('workflowForm').addEventListener('submit', function(e) {
        const steps = document.querySelectorAll('.step-item');
        if (steps.length === 0) {
            e.preventDefault();
            alert('Please add at least one workflow step.');
            return;
        }

        let valid = true;
        steps.forEach((step, index) => {
            const dept = step.querySelector('.department-select')?.value;
            const timeValue = step.querySelector('.process-time-value')?.value;
            if (!dept) {
                alert(`Please select a department for Step ${index + 1}`);
                valid = false;
            }
            if (!timeValue || timeValue < 1) {
                alert(`Please enter a valid process time for Step ${index + 1}`);
                valid = false;
            }
        });

        if (!valid) {
            e.preventDefault();
            return;
        }

        const saveBtn = document.getElementById('saveBtn');
        saveBtn.disabled = true;
        saveBtn.innerHTML = '<span class="loading loading-spinner loading-sm"></span> Saving...';
    });

    // Initialize
    updateStepCountAndDifficulty();
</script>
@endpush
@endsection