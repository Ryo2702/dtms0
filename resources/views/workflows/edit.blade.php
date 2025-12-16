@extends('layouts.app')

@section('content')
    <div class="p-4 sm:p-6">
        {{-- Header --}}
        <div class="flex items-center justify-between mb-6">
            <div>
                <nav class="text-sm breadcrumbs mb-2">
                    <ul>
                        <li><a href="{{ route('admin.workflows.index') }}" class="text-primary">Workflows</a></li>
                        <li>Configure</li>
                    </ul>
                </nav>
                <h1 class="text-2xl font-bold text-gray-900">Edit Workflow: {{ $workflow->name }}</h1>
                <p class="text-gray-600 mt-1">{{ $workflow->transactionType->document_name }}</p>
            </div>
        </div>

        {{-- Flash Messages --}}
        @if (session('success'))
            <div class="alert alert-success mb-4">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-error mb-4">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        @if ($errors->has('workflow'))
            <div class="alert alert-error mb-4">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
                <ul class="list-disc list-inside">
                    @foreach ($errors->get('workflow') as $error)
                        @if (is_array($error))
                            @foreach ($error as $e)
                                <li>{{ $e }}</li>
                            @endforeach
                        @else
                            <li>{{ $error }}</li>
                        @endif
                    @endforeach
                </ul>
            </div>
        @endif

        @if ($errors->any() && !$errors->has('workflow'))
            <div class="alert alert-error mb-4">
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            {{-- Step Builder --}}
            <div class="bg-base-100 rounded-lg shadow-md p-6">
                <form action="{{ route('admin.workflows.update', $workflow) }}" method="POST" id="workflowForm">
                    @csrf
                    @method('PUT')

                    {{-- Description --}}
                    <div class="form-control mb-6">
                        <label class="label">
                            <span class="label-text font-medium">Description</span>
                            <span class="label-text-alt text-gray-400">Optional</span>
                        </label>
                        <textarea name="description" class="textarea textarea-bordered" rows="2" placeholder="Describe this workflow...">{{ old('description', $workflow->description) }}</textarea>
                    </div>

                    {{-- Auto-calculated Difficulty Display --}}
                    <div class="mb-6 p-4 bg-gray-50 rounded-lg border border-gray-200">
                        <div class="flex items-center justify-between">
                            <div>
                                <h4 class="text-sm font-semibold text-gray-900">Workflow Complexity</h4>
                                <p class="text-xs text-gray-500 mt-1">Auto-calculated based on total processing time</p>
                            </div>
                            <div class="flex items-center gap-3">
                                <div id="stepCountBadge" class="badge badge-ghost">
                                    departments: <span id="stepCount">0</span>
                                </div>
                                <span id="difficultyBadge" class="badge badge-ghost">-</span>
                            </div>
                        </div>
                        {{-- Hidden input for difficulty (auto-set by JS) --}}
                        <input type="hidden" name="difficulty" id="difficultyInput"
                            value="{{ $currentConfig['difficulty'] ?? 'simple' }}">
                    </div>

                    {{-- Workflow Steps Section --}}
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Workflow Steps</h3>
                    <p class="text-sm text-gray-500 mb-4">Define the sequence of departments for document approval. The
                        order determines the routing flow.</p>

                    <div id="stepsContainer" class="space-y-4">
                        @if (empty($currentConfig['steps']))
                            <p class="text-gray-500 text-center py-8" id="noStepsMessage">
                                No steps configured. Add your first step below.
                            </p>
                        @endif
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
                            <span class="text-xs text-gray-600">Simple workflow with 1-7 days</span>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="badge badge-warning">Complex</span>
                            <span class="text-xs text-gray-600">Complex workflow with 2-4 weeks</span>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="badge badge-error">Highly Technical</span>
                            <span class="text-xs text-gray-600">Highly Technical workflow with 5-6+ weeks</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            const departments = @json($departments);
            const currentConfig = @json($currentConfig);
            let stepIndex = 0;

            function createStepHtml(index, departmentId = '', canReturnTo = [], processTimeValue = 3, processTimeUnit = 'days',
                notes = '') {
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
                                       required
                                       placeholder="e.g., 3">
                            </div>
                            <div class="form-control">
                                <label class="label">
                                    <span class="label-text font-medium">Time Unit</span>
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

            // Calculate total time in days from all steps
            function calculateTotalDays() {
                let totalDays = 0;
                document.querySelectorAll('.step-item').forEach(item => {
                    const value = parseFloat(item.querySelector('.process-time-value')?.value) || 0;
                    const unit = item.querySelector('.process-time-unit')?.value || 'days';

                    if (unit === 'hours') {
                        totalDays += value / 24;
                    } else if (unit === 'weeks') {
                        totalDays += value * 7;
                    } else {
                        totalDays += value;
                    }
                });
                return Math.round(totalDays * 10) / 10;
            }

            function calculateDifficulty(totalDays) {
                if (totalDays >= 35) return 'highly_technical';
                if (totalDays >= 14) return 'complex';
                return 'simple';
            }

            function getDifficultyLabel(difficulty) {
                switch (difficulty) {
                    case 'highly_technical':
                        return 'Highly Technical';
                    case 'complex':
                        return 'Complex';
                    default:
                        return 'Simple';
                }
            }

            function getDifficultyBadgeClass(difficulty) {
                switch (difficulty) {
                    case 'highly_technical':
                        return 'badge-error';
                    case 'complex':
                        return 'badge-warning';
                    default:
                        return 'badge-success';
                }
            }

            function updateStepCountAndDifficulty() {
                const count = document.querySelectorAll('.step-item').length;
                const totalDays = calculateTotalDays();

                document.getElementById('stepCount').textContent = count;

                const difficulty = calculateDifficulty(totalDays);
                document.getElementById('difficultyInput').value = difficulty;

                const stepBadge = document.getElementById('stepCountBadge');
                stepBadge.classList.remove('badge-success', 'badge-warning', 'badge-error', 'badge-ghost', 'badge-info');

                const diffBadge = document.getElementById('difficultyBadge');
                diffBadge.classList.remove('badge-success', 'badge-warning', 'badge-error', 'badge-ghost');

                if (count === 0) {
                    stepBadge.classList.add('badge-ghost');
                    diffBadge.classList.add('badge-ghost');
                    diffBadge.textContent = '-';
                } else {
                    stepBadge.classList.add('badge-info');
                    diffBadge.classList.add(getDifficultyBadgeClass(difficulty));
                    diffBadge.textContent = `${getDifficultyLabel(difficulty)} (${totalDays} days)`;
                }
            }

            // Update step count and suggest difficulty
            function updateStepCount() {
                const count = document.querySelectorAll('.step-item').length;
                document.getElementById('stepCount').textContent = count;

                // Update badge color based on count
                const badge = document.getElementById('stepCountBadge');
                badge.classList.remove('badge-success', 'badge-warning', 'badge-error', 'badge-ghost');

                if (count >= 5) {
                    badge.classList.add('badge-error');
                } else if (count >= 3) {
                    badge.classList.add('badge-warning');
                } else if (count >= 1) {
                    badge.classList.add('badge-success');
                } else {
                    badge.classList.add('badge-ghost');
                }

                // Update difficulty badge
                const difficultyBadge = document.getElementById('difficultyBadge');
                difficultyBadge.classList.remove('badge-success', 'badge-warning', 'badge-error', 'badge-ghost');

                if (count >= 5) {
                    difficultyBadge.classList.add('badge-error');
                    difficultyBadge.textContent = 'Highly Technical';
                } else if (count >= 3) {
                    difficultyBadge.classList.add('badge-warning');
                    difficultyBadge.textContent = 'Complex';
                } else if (count >= 1) {
                    difficultyBadge.classList.add('badge-success');
                    difficultyBadge.textContent = 'Simple';
                } else {
                    difficultyBadge.classList.add('badge-ghost');
                    difficultyBadge.textContent = '-';
                }

                // Update hidden difficulty input
                document.getElementById('difficultyInput').value = count >= 5 ? 'highly_technical' : count >= 3 ? 'complex' :
                    'simple';
            }

            // Initialize with existing config
            document.addEventListener('DOMContentLoaded', function() {
                const container = document.getElementById('stepsContainer');

                if (currentConfig.steps && currentConfig.steps.length > 0) {
                    const noStepsMsg = document.getElementById('noStepsMessage');
                    if (noStepsMsg) noStepsMsg.remove();

                    currentConfig.steps.forEach((step, index) => {
                        container.insertAdjacentHTML('beforeend', createStepHtml(
                            index,
                            step.department_id,
                            step.can_return_to || [],
                            step.process_time_value || 3,
                            step.process_time_unit || 'days',
                            step.notes || ''
                        ));
                        stepIndex = index + 1;
                    });

                    // Restore can_return_to checkboxes after all steps are added
                    setTimeout(() => {
                        updateReturnToOptions();
                        currentConfig.steps.forEach((step, index) => {
                            if (step.can_return_to && step.can_return_to.length > 0) {
                                const stepEl = container.querySelectorAll('.step-item')[index];
                                if (stepEl) {
                                    step.can_return_to.forEach(deptId => {
                                        const checkbox = stepEl.querySelector(
                                            `.return-to-checkbox[value="${deptId}"]`);
                                        if (checkbox) checkbox.checked = true;
                                    });
                                }
                            }
                        });
                        updatePreview();
                        updateStepCountAndDifficulty();
                    }, 100);
                }

                attachEventListeners();
                attachDifficultyListeners();
                updateStepCountAndDifficulty();
            });

            // Difficulty radio button listeners
            function attachDifficultyListeners() {
                document.querySelectorAll('input[name="difficulty"]').forEach(radio => {
                    radio.onchange = function() {
                        // Update visual styling
                        document.querySelectorAll('.difficulty-option').forEach(opt => {
                            opt.classList.remove('border-success', 'bg-success/10', 'border-warning',
                                'bg-warning/10', 'border-error', 'bg-error/10');
                            opt.classList.add('border-gray-200');
                        });

                        const selected = this.closest('.difficulty-option');
                        const value = this.value;

                        if (value === 'simple') {
                            selected.classList.add('border-success', 'bg-success/10');
                        } else if (value === 'complex') {
                            selected.classList.add('border-warning', 'bg-warning/10');
                        } else if (value === 'highly_technical') {
                            selected.classList.add('border-error', 'bg-error/10');
                        }
                        selected.classList.remove('border-gray-200');

                        updatePreview();
                        updateVisualFlow();
                    };
                });
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
                    input.onchange = function() {
                        updatePreview();
                        updateStepCountAndDifficulty();
                    };
                    input.oninput = debounce(function() {
                        updatePreview();
                        updateStepCountAndDifficulty();
                    }, 500);
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
                    let checkboxesHtml = '';

                    for (let i = 0; i < index; i++) {
                        const prevStep = steps[i];
                        const prevSelect = prevStep.querySelector('.department-select');
                        const prevDeptId = prevSelect?.value;
                        const prevDeptName = prevSelect?.options[prevSelect.selectedIndex]?.text;

                        if (prevDeptId && prevDeptName && prevDeptName !== 'Select Department') {
                            hasOptions = true;
                            checkboxesHtml += `
                                <label class="flex items-center gap-2 cursor-pointer hover:bg-gray-50 p-1 rounded">
                                    <input type="checkbox" 
                                           name="steps[${index}][can_return_to][]" 
                                           value="${prevDeptId}" 
                                           class="checkbox checkbox-sm checkbox-primary return-to-checkbox">
                                    <span class="label-text">Allow return to <strong>${prevDeptName}</strong></span>
                                </label>
                            `;
                        }
                    }

                    if (hasOptions) {
                        // Add "Select All" checkbox at the top
                        optionsDiv.innerHTML = `
                            <label class="flex items-center gap-2 cursor-pointer hover:bg-primary/10 p-1 rounded border-b border-gray-200 pb-2 mb-2">
                                <input type="checkbox" 
                                       class="checkbox checkbox-sm checkbox-primary select-all-checkbox">
                                <span class="label-text font-medium">Select All</span>
                            </label>
                            ${checkboxesHtml}
                        `;

                        // Attach select all functionality
                        const selectAllCb = optionsDiv.querySelector('.select-all-checkbox');
                        const returnToCbs = optionsDiv.querySelectorAll('.return-to-checkbox');

                        selectAllCb.onchange = function() {
                            returnToCbs.forEach(cb => {
                                cb.checked = this.checked;
                            });
                            updatePreview();
                        };

                        // Update "Select All" state when individual checkboxes change
                        returnToCbs.forEach(cb => {
                            cb.onchange = function() {
                                const allChecked = Array.from(returnToCbs).every(c => c.checked);
                                const someChecked = Array.from(returnToCbs).some(c => c.checked);
                                selectAllCb.checked = allChecked;
                                selectAllCb.indeterminate = someChecked && !allChecked;
                                updatePreview();
                            };
                        });
                    } else {
                        optionsDiv.innerHTML = '<span class="text-gray-400 text-sm">Select departments in previous steps first</span>';
                    }
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

                // Get difficulty from hidden input (not radio button)
                const difficulty = document.getElementById('difficultyInput').value || 'simple';

                return {
                    steps,
                    difficulty
                };
            }

            function updatePreview() {
                const data = getFormData();

                if (data.steps.length === 0) {
                    updateVisualFlow();
                    return;
                }

                fetch('{{ route('admin.workflows.preview') }}', {
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
                        updateVisualFlow();
                    })
                    .catch(err => {
                        console.error('Preview error:', err);
                    });
            }

            function getDifficultyBadgeClass() {
                const difficulty = document.getElementById('difficultyInput').value || 'simple';
                switch (difficulty) {
                    case 'complex':
                        return 'badge-warning';
                    case 'highly_technical':
                        return 'badge-error';
                    default:
                        return 'badge-success';
                }
            }

            function updateVisualFlow() {
                const container = document.getElementById('visualFlow');
                const steps = document.querySelectorAll('.step-item');
                const badgeClass = getDifficultyBadgeClass();

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

            // Form submission
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
        </script>
    @endpush
@endsection
