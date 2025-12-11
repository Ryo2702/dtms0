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
            <h1 class="text-2xl font-bold text-gray-900">Configure Workflow</h1>
            <p class="text-gray-600 mt-2">{{ $transactionType->document_name }}</p>
        </div>
    </div>

    {{-- Flash Messages --}}
    @if(session('success'))
        <div class="alert alert-success mb-4">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-error mb-4">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    @if($errors->has('workflow'))
        <div class="alert alert-error mb-4">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
            <ul class="list-disc list-inside">
                @foreach($errors->get('workflow') as $error)
                    @if(is_array($error))
                        @foreach($error as $e)
                            <li>{{ $e }}</li>
                        @endforeach
                    @else
                        <li>{{ $error }}</li>
                    @endif
                @endforeach
            </ul>
        </div>
    @endif

    @if($errors->any() && !$errors->has('workflow'))
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
            <h3 class="text-lg font-semibold text-gray-900 mb-2">Workflow Steps</h3>
            <p class="text-sm text-gray-500 mb-4">Define the sequence of departments for document approval. The order determines the routing flow.</p>

            <form action="{{ route('admin.workflows.update', $transactionType) }}" method="POST" id="workflowForm">
                @csrf
                @method('PUT')

                <div id="stepsContainer" class="space-y-4">
                    @if(empty($currentConfig['steps']))
                        <p class="text-gray-500 text-center py-8" id="noStepsMessage">
                            No steps configured. Add your first step below.
                        </p>
                    @endif
                </div>

                <button type="button" id="addStepBtn" class="btn btn-outline btn-primary w-full mt-4">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Add Step
                </button>

                <div class="flex justify-end gap-3 pt-6 border-t border-gray-200 mt-6">
                    <a href="{{ route('admin.workflows.index') }}" class="btn btn-ghost">Cancel</a>
                    <button type="submit" class="btn btn-primary" id="saveBtn">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
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
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
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
        </div>
    </div>
</div>

@push('scripts')
<script>
    const departments = @json($departments);
    const currentConfig = @json($currentConfig);
    let stepIndex = 0;

    // Step template
    function createStepHtml(index, departmentId = '', canReturnTo = []) {
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

    // Initialize with existing config
    document.addEventListener('DOMContentLoaded', function() {
        const container = document.getElementById('stepsContainer');
        
        if (currentConfig.steps && currentConfig.steps.length > 0) {
            const noStepsMsg = document.getElementById('noStepsMessage');
            if (noStepsMsg) noStepsMsg.remove();

            currentConfig.steps.forEach((step, index) => {
                container.insertAdjacentHTML('beforeend', createStepHtml(index, step.department_id, step.can_return_to || []));
                stepIndex = index + 1;
            });

            // Restore can_return_to checkboxes after all steps are added
            setTimeout(() => {
                updateReturnToOptions();
                // Now check the boxes based on saved config
                currentConfig.steps.forEach((step, index) => {
                    if (step.can_return_to && step.can_return_to.length > 0) {
                        const stepEl = container.querySelectorAll('.step-item')[index];
                        if (stepEl) {
                            step.can_return_to.forEach(deptId => {
                                const checkbox = stepEl.querySelector(`.return-to-checkbox[value="${deptId}"]`);
                                if (checkbox) checkbox.checked = true;
                            });
                        }
                    }
                });
                updatePreview();
            }, 100);
        }

        attachEventListeners();
    });

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
    }

    function updateStepNumbers() {
        document.querySelectorAll('.step-item').forEach((item, index) => {
            item.querySelector('.step-number').textContent = `Step ${index + 1}`;
            item.dataset.index = index;
            
            // Update input names
            const select = item.querySelector('.department-select');
            if (select) select.name = `steps[${index}][department_id]`;
            
            // Update checkbox names
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
            
            // First step can't return to anything
            if (index === 0) {
                container.style.display = 'none';
                return;
            }

            container.style.display = 'block';
            optionsDiv.innerHTML = '';

            let hasOptions = false;

            // Add checkboxes for all previous steps
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
                                   name="steps[${index}][can_return_to][]" 
                                   value="${prevDeptId}" 
                                   class="checkbox checkbox-sm checkbox-primary return-to-checkbox">
                            <span class="label-text">Allow return to <strong>${prevDeptName}</strong></span>
                        </label>
                    `;
                    optionsDiv.insertAdjacentHTML('beforeend', checkboxHtml);
                }
            }

            if (!hasOptions) {
                optionsDiv.innerHTML = '<span class="text-gray-400 text-sm">Select departments in previous steps first</span>';
            }

            // Attach change listeners to checkboxes
            optionsDiv.querySelectorAll('.return-to-checkbox').forEach(cb => {
                cb.onchange = updatePreview;
            });
        });
    }

    function getFormData() {
        const steps = [];
        document.querySelectorAll('.step-item').forEach((item) => {
            const deptId = item.querySelector('.department-select')?.value;
            const canReturnTo = [];
            
            item.querySelectorAll('.return-to-checkbox:checked').forEach(cb => {
                canReturnTo.push(parseInt(cb.value));
            });

            if (deptId) {
                steps.push({
                    department_id: parseInt(deptId),
                    can_return_to: canReturnTo
                });
            }
        });
        return { steps };
    }

    function updatePreview() {
        const data = getFormData();
        
        if (data.steps.length === 0) {
            document.getElementById('transitionJson').textContent = JSON.stringify({
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
        
        if (steps.length === 0) {
            container.innerHTML = '<span class="text-gray-400 text-sm">Add steps to see the flow</span>';
            return;
        }

        let html = '';
        steps.forEach((step, index) => {
            const select = step.querySelector('.department-select');
            const deptName = select?.options[select.selectedIndex]?.text || 'Not selected';
            
            if (deptName !== 'Select Department') {
                if (index > 0) {
                    html += `
                        <svg class="w-6 h-6 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                        </svg>
                    `;
                }
                html += `<span class="badge badge-primary">${deptName}</span>`;
            }
        });

        html += `
            <svg class="w-6 h-6 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
            </svg>
            <span class="badge badge-success">Completed</span>
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

        const saveBtn = document.getElementById('saveBtn');
        saveBtn.disabled = true;
        saveBtn.innerHTML = '<span class="loading loading-spinner loading-sm"></span> Saving...';
    });
</script>
@endpush
@endsection