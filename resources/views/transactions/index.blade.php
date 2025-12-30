@extends('layouts.app')

@section('title', 'Transactions')

@section('content')
    <div class="container mx-auto px-4 py-6">
        {{-- Header --}}
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-900">Transactions</h1>
            <p class="text-gray-600 mt-1">Select a workflow to create a new transaction</p>
        </div>

        {{-- Available Workflows --}}
        @if($workflows->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($workflows as $workflow)
                    @php
                        $steps = $workflow->getWorkflowSteps();
                        $totalTime = 0;
                        foreach ($steps as $step) {
                            $value = $step['process_time_value'] ?? 0;
                            $unit = $step['process_time_unit'] ?? 'days';
                            if ($unit === 'hours') {
                                $totalTime += $value / 24;
                            } elseif ($unit === 'weeks') {
                                $totalTime += $value * 7;
                            } else {
                                $totalTime += $value;
                            }
                        }
                        $totalTime = round($totalTime, 1);
                        $documentTags = $workflow->documentTags()->where('status', true)->get();
                    @endphp
                    
                    <div class="card bg-base-100 shadow-md hover:shadow-lg transition-shadow">
                        <div class="card-body">
                            {{-- Workflow Header --}}
                            <div class="flex items-start justify-between mb-3">
                                <div>
                                    <span class="badge badge-primary badge-sm font-mono mb-2">{{ $workflow->id }}</span>
                                    <h2 class="card-title text-lg">{{ $workflow->transaction_name }}</h2>
                                </div>
                                <span class="badge {{ $workflow->getDifficultBadgeClass() }}">
                                    {{ ucfirst(str_replace('_', ' ', $workflow->difficulty)) }}
                                </span>
                            </div>

                            @if($workflow->description)
                                <p class="text-sm text-gray-600 mb-3">{{ Str::limit($workflow->description, 100) }}</p>
                            @endif

                            {{-- Workflow Stats --}}
                            <div class="flex items-center gap-4 text-sm text-gray-500 mb-4">
                                <div class="flex items-center gap-1">
                                    <i data-lucide="git-branch" class="w-4 h-4"></i>
                                    <span>{{ count($steps) }} steps</span>
                                </div>
                                <div class="flex items-center gap-1">
                                    <i data-lucide="clock" class="w-4 h-4"></i>
                                    <span>{{ $totalTime }} {{ $totalTime == 1 ? 'day' : 'days' }}</span>
                                </div>
                            </div>

                            {{-- Workflow Route Preview --}}
                            @if(count($steps) > 0)
                                <div class="mb-4">
                                    <p class="text-xs font-semibold text-gray-500 uppercase mb-2">Route</p>
                                    <div class="flex flex-wrap items-center gap-1">
                                        @foreach($steps as $index => $step)
                                            <span class="badge badge-sm badge-outline">
                                                {{ $step['department_name'] ?? 'Dept ' . ($index + 1) }}
                                            </span>
                                            @if($index < count($steps) - 1)
                                                <i data-lucide="arrow-right" class="w-3 h-3 text-gray-400"></i>
                                            @endif
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            {{-- Required Documents --}}
                            @if($documentTags->count() > 0)
                                <div class="mb-4">
                                    <p class="text-xs font-semibold text-gray-500 uppercase mb-2">Required Documents</p>
                                    <div class="flex flex-wrap gap-1">
                                        @foreach($documentTags->take(4) as $tag)
                                            <span class="badge badge-sm badge-ghost" title="{{ $tag->description }}">
                                                <i data-lucide="file-text" class="w-3 h-3 mr-1"></i>
                                                {{ Str::limit($tag->name, 15) }}
                                            </span>
                                        @endforeach
                                        @if($documentTags->count() > 4)
                                            <span class="badge badge-sm badge-ghost">+{{ $documentTags->count() - 4 }} more</span>
                                        @endif
                                    </div>
                                </div>
                            @endif

                            {{-- Action Buttons --}}
                            <div class="card-actions justify-end mt-auto gap-2">
                                <button type="button" 
                                        onclick="openViewModal('{{ $workflow->id }}')"
                                        class="btn btn-ghost btn-sm">
                                    <i data-lucide="eye" class="w-4 h-4 mr-1"></i>
                                    View
                                </button>
                                <button type="button" 
                                        onclick="openCreateModal('{{ $workflow->id }}')"
                                        class="btn btn-primary btn-sm">
                                    <i data-lucide="plus" class="w-4 h-4 mr-1"></i>
                                    Create Transaction
                                </button>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="card bg-base-100 shadow-md">
                <div class="card-body text-center py-12">
                    <i data-lucide="inbox" class="w-16 h-16 mx-auto text-gray-300 mb-4"></i>
                    <h3 class="text-lg font-medium text-gray-600">No Workflows Available</h3>
                    <p class="text-gray-500 mt-2">There are no active workflows available for your department.</p>
                </div>
            </div>
        @endif
    </div>

    {{-- View Workflow Modal --}}
    <x-modal id="view-workflow-modal" title="Workflow Details" size="lg">
        <div id="view-workflow-content">
            <div class="flex justify-center py-8">
                <span class="loading loading-spinner loading-lg"></span>
            </div>
        </div>
    </x-modal>

    {{-- Create Transaction Modal --}}
    <x-modal id="create-transaction-modal" title="Create Transaction" size="xl">
        <div id="create-transaction-content">
            <div class="flex justify-center py-8">
                <span class="loading loading-spinner loading-lg"></span>
            </div>
        </div>
    </x-modal>

    {{-- View Transaction Modal --}}
    <x-modal id="view-transaction-modal" title="Transaction Details" size="xl">
        <div id="view-transaction-content">
            <div class="flex justify-center py-8">
                <span class="loading loading-spinner loading-lg"></span>
            </div>
        </div>
    </x-modal>

    {{-- Edit Transaction Modal --}}
    <x-modal id="edit-transaction-modal" title="Edit Transaction" size="xl">
        <div id="edit-transaction-content">
            <div class="flex justify-center py-8">
                <span class="loading loading-spinner loading-lg"></span>
            </div>
        </div>
    </x-modal>

    {{-- Store workflow data for JavaScript --}}
    <script>
        const workflowsData = @json($workflows->keyBy('id'));
        const assignStaffData = @json(\App\Models\AssignStaff::active()->get());
        const departmentsData = @json($departments);
        const canEditRoute = @json($canEditRoute);

        function openViewModal(workflowId) {
            const workflow = workflowsData[workflowId];
            if (!workflow) return;

            const steps = workflow.workflow_config?.steps || [];
            const documentTags = workflow.document_tags || [];
            
            let stepsHtml = '';
            steps.forEach((step, index) => {
                stepsHtml += `
                    <div class="flex items-start gap-4 ${index < steps.length - 1 ? 'pb-4 border-l-2 border-base-300 ml-4' : ''}">
                        <div class="flex-shrink-0 ${index < steps.length - 1 ? '-ml-4' : ''}">
                            <span class="flex items-center justify-center w-8 h-8 rounded-full bg-primary text-primary-content text-sm font-bold">
                                ${index + 1}
                            </span>
                        </div>
                        <div class="flex-1 ${index < steps.length - 1 ? 'pl-4' : ''}">
                            <h4 class="font-medium">${step.department_name || 'Department ' + (index + 1)}</h4>
                            <p class="text-sm text-gray-500">Processing time: ${step.process_time_value || 1} ${step.process_time_unit || 'days'}</p>
                            ${step.notes ? `<p class="text-sm text-gray-400 italic">${step.notes}</p>` : ''}
                        </div>
                    </div>
                `;
            });

            let docsHtml = '';
            if (documentTags.length > 0) {
                docsHtml = '<div class="mt-6"><h4 class="font-semibold text-gray-700 mb-3">Required Documents</h4><div class="space-y-2">';
                documentTags.forEach(tag => {
                    docsHtml += `
                        <div class="flex items-center justify-between p-3 border border-base-300 rounded-lg">
                            <div class="flex items-center gap-3">
                                <div class="flex items-center justify-center w-10 h-10 bg-base-200 rounded-lg">
                                    <i data-lucide="file" class="w-5 h-5 text-gray-600"></i>
                                </div>
                                <div>
                                    <p class="font-medium">${tag.name}</p>
                                    ${tag.description ? `<p class="text-sm text-gray-500">${tag.description}</p>` : ''}
                                </div>
                            </div>
                        </div>
                    `;
                });
                docsHtml += '</div></div>';
            }

            const difficultyClass = {
                'simple': 'badge-success',
                'moderate': 'badge-warning',
                'complex': 'badge-error',
                'highly_complex': 'badge-error'
            }[workflow.difficulty] || 'badge-ghost';

            const content = `
                <div class="space-y-6">
                    <div class="flex items-start justify-between">
                        <div>
                            <span class="badge badge-primary badge-sm font-mono mb-2">${workflow.id}</span>
                            <h3 class="text-xl font-bold">${workflow.transaction_name}</h3>
                            ${workflow.description ? `<p class="text-gray-600 mt-2">${workflow.description}</p>` : ''}
                        </div>
                        <span class="badge ${difficultyClass}">
                            ${workflow.difficulty.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase())}
                        </span>
                    </div>

                    <div>
                        <h4 class="font-semibold text-gray-700 mb-3">Workflow Steps</h4>
                        <div class="space-y-4">
                            ${stepsHtml}
                        </div>
                    </div>

                    ${docsHtml}

                    <div class="flex justify-end gap-3 pt-4 border-t">
                        <button type="button" onclick="window['view-workflow-modal'].close()" class="btn btn-ghost">
                            Close
                        </button>
                        <button type="button" onclick="window['view-workflow-modal'].close(); openCreateModal('${workflowId}');" class="btn btn-primary">
                            <i data-lucide="plus" class="w-4 h-4 mr-1"></i>
                            Create Transaction
                        </button>
                    </div>
                </div>
            `;

            document.getElementById('view-workflow-content').innerHTML = content;
            window['view-workflow-modal'].showModal();
            
            // Reinitialize lucide icons
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
        }

        function openCreateModal(workflowId) {
            const workflow = workflowsData[workflowId];
            if (!workflow) return;

            // Clone steps so we can modify them
            window.currentWorkflowSteps = JSON.parse(JSON.stringify(workflow.workflow_config?.steps || []));
            window.currentWorkflowId = workflowId;
            
            renderCreateModal(workflow);
        }

        function renderCreateModal(workflow) {
            const steps = window.currentWorkflowSteps;
            const documentTags = workflow.document_tags || [];

            let stepsHtml = '';
            steps.forEach((step, index) => {
                stepsHtml += `
                    <div class="step-item border border-base-300 rounded-lg p-3 bg-base-50" data-index="${index}">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <span class="flex items-center justify-center w-7 h-7 rounded-full bg-primary text-primary-content text-sm font-bold">
                                    ${index + 1}
                                </span>
                                <div>
                                    <h4 class="font-medium text-sm">${step.department_name || 'Unknown Department'}</h4>
                                    <p class="text-xs text-gray-500">${step.process_time_value || 1} ${step.process_time_unit || 'days'}</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-1">
                                ${canEditRoute ? `
                                    <button type="button" class="btn btn-ghost btn-xs" onclick="moveStep(${index}, -1)" ${index === 0 ? 'disabled' : ''}>
                                        <i data-lucide="chevron-up" class="w-4 h-4"></i>
                                    </button>
                                    <button type="button" class="btn btn-ghost btn-xs" onclick="moveStep(${index}, 1)" ${index === steps.length - 1 ? 'disabled' : ''}>
                                        <i data-lucide="chevron-down" class="w-4 h-4"></i>
                                    </button>
                                ` : ''}
                                <button type="button" class="btn btn-ghost btn-xs" onclick="toggleStepEdit(${index})">
                                    <i data-lucide="edit-2" class="w-3 h-3"></i>
                                </button>
                                ${canEditRoute && steps.length > 1 ? `
                                    <button type="button" class="btn btn-ghost btn-xs text-error" onclick="removeStep(${index})">
                                        <i data-lucide="trash-2" class="w-3 h-3"></i>
                                    </button>
                                ` : ''}
                            </div>
                        </div>
                        
                        <input type="hidden" name="workflow_snapshot[steps][${index}][department_id]" value="${step.department_id}">
                        <input type="hidden" name="workflow_snapshot[steps][${index}][department_name]" value="${step.department_name || ''}">
                        
                        <div id="step-edit-${index}" class="hidden mt-3 pt-3 border-t border-base-300 space-y-2">
                            <div class="flex items-center gap-2">
                                <label class="label-text text-xs font-medium w-24">Processing</label>
                                <input type="number" name="workflow_snapshot[steps][${index}][process_time_value]" value="${step.process_time_value || 1}" min="1" class="input input-bordered input-xs w-16">
                                <select name="workflow_snapshot[steps][${index}][process_time_unit]" class="select select-bordered select-xs flex-1">
                                    <option value="minutes" ${step.process_time_unit === 'minutes' ? 'selected' : ''}>Minutes</option>
                                    <option value="hours" ${step.process_time_unit === 'hours' ? 'selected' : ''}>Hours</option>
                                    <option value="days" ${step.process_time_unit === 'days' || !step.process_time_unit ? 'selected' : ''}>Days</option>
                                    <option value="weeks" ${step.process_time_unit === 'weeks' ? 'selected' : ''}>Weeks</option>
                                </select>
                            </div>
                            <div class="flex items-center gap-2">
                                <label class="label-text text-xs font-medium w-24">Notes</label>
                                <input type="text" name="workflow_snapshot[steps][${index}][notes]" value="${step.notes || ''}" class="input input-bordered input-xs flex-1" placeholder="Optional">
                            </div>
                        </div>
                    </div>
                `;
            });

            // Department options for adding new step
            let deptOptionsHtml = '<option value="">Select department...</option>';
            departmentsData.forEach(dept => {
                deptOptionsHtml += `<option value="${dept.id}" data-name="${dept.name}">${dept.name}</option>`;
            });

            let docsHtml = '';
            if (documentTags.length > 0) {
                docsHtml = `
                    <div class="mt-4">
                        <h4 class="text-xs font-semibold text-gray-500 uppercase mb-2 flex items-center gap-1">
                            <i data-lucide="file-text" class="w-3 h-3"></i>
                            Required Documents
                        </h4>
                        <div class="flex flex-wrap gap-1">
                `;
                documentTags.forEach(tag => {
                    docsHtml += `
                        <span class="badge badge-sm badge-outline gap-1 ${tag.pivot?.is_required ? 'badge-error' : ''}">
                            ${tag.name}${tag.pivot?.is_required ? '*' : ''}
                        </span>
                        <input type="hidden" name="document_tags_id" value="${tag.id}">
                    `;
                });
                docsHtml += '</div></div>';
            }

            let staffOptionsHtml = '<option value="">Select staff member</option>';
            assignStaffData.forEach(staff => {
                staffOptionsHtml += `<option value="${staff.id}">${staff.full_name} - ${staff.position}</option>`;
            });

            const difficultyClass = {
                'simple': 'badge-success',
                'moderate': 'badge-warning',
                'complex': 'badge-error',
                'highly_complex': 'badge-error'
            }[workflow.difficulty] || 'badge-ghost';

            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
            
            const content = `
                <form action="{{ route('transactions.store') }}" method="POST" id="create-transaction-form">
                    <input type="hidden" name="_token" value="${csrfToken}">
                    <input type="hidden" name="workflow_id" value="${window.currentWorkflowId}">
                    
                    <div class="space-y-4">
                        {{-- Workflow Header --}}
                        <div class="flex items-start justify-between pb-3 border-b border-base-300">
                            <div>
                                <div class="flex items-center gap-2 mb-1">
                                    <span class="badge badge-primary badge-xs font-mono">${workflow.id}</span>
                                    <span class="badge ${difficultyClass} badge-xs">${workflow.difficulty.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase())}</span>
                                </div>
                                <h3 class="font-bold">${workflow.transaction_name}</h3>
                            </div>
                        </div>

                        {{-- Transaction Settings --}}
                        <div class="flex flex-wrap gap-3">
                            <div class="form-control flex-1 min-w-[180px]">
                                <label class="label py-1"><span class="label-text text-xs font-medium">Assign Staff <span class="text-error">*</span></span></label>
                                <select name="assign_staff_id" class="select select-bordered select-sm w-full" required>
                                    ${staffOptionsHtml}
                                </select>
                            </div>
                            <div class="form-control flex-1 min-w-[180px]">
                                <label class="label py-1"><span class="label-text text-xs font-medium">Urgency</span></label>
                                <select name="level_of_urgency" class="select select-bordered select-sm w-full">
                                    <option value="normal" selected>Normal</option>
                                    <option value="urgent">Urgent</option>
                                    <option value="highly_urgent">Highly Urgent</option>
                                </select>
                            </div>
                        </div>

                        {{-- Workflow Route --}}
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <h4 class="text-xs font-semibold text-gray-500 uppercase flex items-center gap-1">
                                    <i data-lucide="git-branch" class="w-3 h-3"></i>
                                    Workflow Route (${steps.length} steps)
                                </h4>
                                <div class="flex items-center gap-1">
                                    <button type="button" class="btn btn-ghost btn-xs" onclick="toggleAllStepEdits()">
                                        <i data-lucide="settings" class="w-3 h-3"></i>
                                    </button>
                                </div>
                            </div>
                            
                            <div id="steps-container" class="space-y-2 max-h-[300px] overflow-y-auto pr-1">
                                ${stepsHtml}
                            </div>
                            
                            ${canEditRoute ? `
                                <div class="mt-3 pt-3 border-t border-base-300">
                                    <div class="flex items-center gap-2">
                                        <select id="add-department-select" class="select select-bordered select-sm flex-1">
                                            ${deptOptionsHtml}
                                        </select>
                                        <button type="button" class="btn btn-outline btn-sm" onclick="addStep()">
                                            <i data-lucide="plus" class="w-4 h-4"></i>
                                            Add Step
                                        </button>
                                    </div>
                                </div>
                            ` : ''}
                        </div>

                        ${docsHtml}

                        {{-- Actions --}}
                        <div class="flex justify-end gap-2 pt-3 border-t border-base-300">
                            <button type="button" onclick="window['create-transaction-modal'].close()" class="btn btn-ghost btn-sm">
                                Cancel
                            </button>
                            <button type="submit" class="btn btn-primary btn-sm">
                                <i data-lucide="send" class="w-4 h-4 mr-1"></i>
                                Create
                            </button>
                        </div>
                    </div>
                </form>
            `;

            document.getElementById('create-transaction-content').innerHTML = content;
            
            // Only show modal if not already open (initial call)
            if (!document.getElementById('create-transaction-modal').classList.contains('modal-open')) {
                window['create-transaction-modal'].showModal();
            }
            
            // Reinitialize lucide icons
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
        }

        function addStep() {
            const select = document.getElementById('add-department-select');
            const deptId = select.value;
            const deptName = select.options[select.selectedIndex]?.dataset?.name;
            
            if (!deptId || !deptName) {
                alert('Please select a department');
                return;
            }
            
            window.currentWorkflowSteps.push({
                department_id: parseInt(deptId),
                department_name: deptName,
                process_time_value: 1,
                process_time_unit: 'days',
                notes: ''
            });
            
            const workflow = workflowsData[window.currentWorkflowId];
            renderCreateModal(workflow);
        }

        function removeStep(index) {
            if (window.currentWorkflowSteps.length <= 1) {
                alert('Workflow must have at least one step');
                return;
            }
            
            window.currentWorkflowSteps.splice(index, 1);
            const workflow = workflowsData[window.currentWorkflowId];
            renderCreateModal(workflow);
        }

        function moveStep(index, direction) {
            const newIndex = index + direction;
            if (newIndex < 0 || newIndex >= window.currentWorkflowSteps.length) return;
            
            const steps = window.currentWorkflowSteps;
            [steps[index], steps[newIndex]] = [steps[newIndex], steps[index]];
            
            const workflow = workflowsData[window.currentWorkflowId];
            renderCreateModal(workflow);
        }

        function toggleStepEdit(index) {
            const editDiv = document.getElementById(`step-edit-${index}`);
            if (editDiv) {
                editDiv.classList.toggle('hidden');
            }
        }

        function toggleAllStepEdits() {
            const editDivs = document.querySelectorAll('[id^="step-edit-"]');
            const allHidden = Array.from(editDivs).every(div => div.classList.contains('hidden'));
            editDivs.forEach(div => {
                if (allHidden) {
                    div.classList.remove('hidden');
                } else {
                    div.classList.add('hidden');
                }
            });
        }

        function openEditModal(transactionId) {
            // Show loading state
            document.getElementById('edit-transaction-content').innerHTML = '<div class="flex justify-center py-8"><span class="loading loading-spinner loading-lg"></span></div>';
            window['edit-transaction-modal'].showModal();

            // Fetch transaction data via AJAX
            fetch(`/transactions/${transactionId}/edit`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.html) {
                    document.getElementById('edit-transaction-content').innerHTML = data.html;
                    if (typeof lucide !== 'undefined') {
                        lucide.createIcons();
                    }
                }
            })
            .catch(error => {
                console.error('Error loading edit form:', error);
                document.getElementById('edit-transaction-content').innerHTML = '<div class="alert alert-error">Failed to load edit form. Please try again.</div>';
            });
        }

        function openViewTransactionModal(transactionId) {
            // Show loading state
            document.getElementById('view-transaction-content').innerHTML = '<div class="flex justify-center py-8"><span class="loading loading-spinner loading-lg"></span></div>';
            window['view-transaction-modal'].showModal();

            // Fetch transaction data via AJAX
            fetch(`/transactions/${transactionId}`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.html) {
                    document.getElementById('view-transaction-content').innerHTML = data.html;
                    if (typeof lucide !== 'undefined') {
                        lucide.createIcons();
                    }
                }
            })
            .catch(error => {
                console.error('Error loading transaction details:', error);
                document.getElementById('view-transaction-content').innerHTML = '<div class="alert alert-error">Failed to load transaction details. Please try again.</div>';
            });
        }

        // Initialize icons on page load
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
        });
    </script>
@endsection
