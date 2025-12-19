@extends('layouts.app')

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
                <form action="{{ route('admin.workflows.store') }}" method="POST" id="workflowForm">
                    @csrf

                    {{-- Transaction Name --}}
                    <div class="form-control mb-6">
                        <label class="label">
                            <span class="label-text font-medium">Transaction Name</span>
                            <span class="label-text-alt text-error">*</span>
                        </label>
                        <input type="text" name="transaction_name" 
                               class="input input-bordered @error('transaction_name') input-error @enderror" 
                               placeholder="e.g., Business Permit Application"
                               value="{{ old('transaction_name') }}" 
                               required>
                        @error('transaction_name')
                            <label class="label">
                                <span class="label-text-alt text-error">{{ $message }}</span>
                            </label>
                        @enderror
                    </div>

                    {{-- Description --}}
                    <div class="form-control mb-6">
                        <label class="label">
                            <span class="label-text font-medium">Description</span>
                            <span class="label-text-alt text-gray-400">Optional</span>
                        </label>
                        <textarea name="description" class="textarea textarea-bordered" rows="2" placeholder="Describe this workflow...">{{ old('description') }}</textarea>
                    </div>

                    {{-- Origin Departments Section --}}
                    <div class="mb-6 p-4 bg-blue-50 rounded-lg border border-blue-200">
                        <div class="flex items-center justify-between mb-3">
                            <div>
                                <h4 class="text-sm font-semibold text-gray-900 flex items-center gap-2">
                                    <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                    </svg>
                                    Origin Departments
                                </h4>
                                <p class="text-xs text-gray-500 mt-1">Select departments where this transaction can originate. The workflow will be visible to all selected departments.</p>
                            </div>
                            <span id="originDeptCount" class="badge badge-info badge-sm">0 selected</span>
                        </div>
                        
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-2 max-h-48 overflow-y-auto">
                            @foreach($departments as $department)
                                <label class="flex items-center gap-2 p-2 bg-white rounded border border-gray-200 hover:border-blue-400 transition-colors cursor-pointer">
                                    <input type="checkbox" 
                                           name="origin_departments[]" 
                                           value="{{ $department->id }}" 
                                           class="checkbox checkbox-sm checkbox-info origin-dept-checkbox"
                                           {{ in_array($department->id, old('origin_departments', [])) ? 'checked' : '' }}>
                                    <span class="text-sm truncate" title="{{ $department->name }}">{{ $department->name }}</span>
                                </label>
                            @endforeach
                        </div>

                        {{-- Selected Origin Departments Summary --}}
                        <div id="originDeptSummary" class="mt-3 p-2 bg-white rounded border border-blue-200" style="display: none;">
                            <h5 class="text-xs font-medium text-gray-600 mb-1">Selected Origin Departments:</h5>
                            <div id="originDeptList" class="flex flex-wrap gap-1">
                                {{-- Populated by JS --}}
                            </div>
                        </div>
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

                    {{-- Document Tags Section --}}
                    <div class="mt-6 pt-6 border-t border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Document Tags</h3>
                        <p class="text-sm text-gray-500 mb-4">Select document tags required for this workflow.</p>
                        
                        <div id="documentTagsContainer" class="space-y-4">
                            @if($documentTags->count() > 0)
                                <div class="border rounded-lg p-4 bg-gray-50">
                                    <div class="flex items-center justify-between mb-3">
                                        <h4 class="font-medium text-gray-900 flex items-center gap-2">
                                            <svg class="w-4 h-4 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                                            </svg>
                                            Available Document Tags
                                        </h4>
                                        <span class="badge badge-ghost badge-sm">{{ $documentTags->count() }} tags</span>
                                    </div>
                                    
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                        @foreach($documentTags as $tag)
                                            <div class="flex items-center justify-between p-2 bg-white rounded border border-gray-200 hover:border-primary transition-colors">
                                                <label class="flex items-center gap-2 cursor-pointer flex-1">
                                                    <input type="checkbox" 
                                                           name="document_tags[{{ $loop->index }}][id]" 
                                                           value="{{ $tag->id }}" 
                                                           class="checkbox checkbox-sm checkbox-primary document-tag-checkbox"
                                                           data-tag-id="{{ $tag->id }}"
                                                           data-department-ids="{{ $tag->departments->pluck('id')->join(',') }}">
                                                    <div>
                                                        <span class="text-sm">{{ $tag->name }}</span>
                                                        @if($tag->departments->count() > 0)
                                                            <div class="flex flex-wrap gap-1 mt-1">
                                                                @foreach($tag->departments->take(2) as $dept)
                                                                    <span class="badge badge-ghost badge-xs">{{ $dept->name }}</span>
                                                                @endforeach
                                                                @if($tag->departments->count() > 2)
                                                                    <span class="badge badge-ghost badge-xs">+{{ $tag->departments->count() - 2 }}</span>
                                                                @endif
                                                            </div>
                                                        @endif
                                                    </div>
                                                </label>
                                                <label class="flex items-center gap-1 cursor-pointer" title="Mark as required">
                                                    <input type="checkbox" 
                                                           name="document_tags[{{ $loop->index }}][is_required]" 
                                                           value="1" 
                                                           class="checkbox checkbox-xs checkbox-warning required-checkbox"
                                                           data-tag-id="{{ $tag->id }}"
                                                           disabled>
                                                    <span class="text-xs text-gray-500">Required</span>
                                                </label>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @else
                                <div class="text-center py-6 text-gray-500">
                                    <svg class="w-12 h-12 mx-auto mb-2 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                                    </svg>
                                    <p>No document tags available.</p>
                                    <a href="{{ route('admin.document-tags.index') }}" class="btn btn-sm btn-primary mt-2">
                                        Create Document Tag
                                    </a>
                                </div>
                            @endif
                        </div>

                        {{-- Selected Tags Summary --}}
                        <div id="selectedTagsSummary" class="mt-4 p-3 bg-primary/5 rounded-lg border border-primary/20" style="display: none;">
                            <h5 class="text-sm font-medium text-gray-700 mb-2">Selected Tags:</h5>
                            <div id="selectedTagsList" class="flex flex-wrap gap-2">
                                {{-- Populated by JS --}}
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 pt-6 border-t border-gray-200 mt-6">
                        <a href="{{ route('admin.workflows.index') }}" class="btn btn-ghost">Cancel</a>
                        <button type="submit" class="btn btn-primary" id="saveBtn">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            Create Workflow
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
                            <span class="text-xs text-gray-600">1-7 days</span>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="badge badge-warning">Complex</span>
                            <span class="text-xs text-gray-600">2-4 weeks</span>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="badge badge-error">Highly Technical</span>
                            <span class="text-xs text-gray-600">5-6+ weeks</span>
                        </div>
                    </div>
                </div>

                {{-- Connected Departments via Tags --}}
                <div class="mt-6 p-4 bg-gray-50 rounded-lg">
                    <h4 class="text-sm font-medium text-gray-700 mb-3">Connected Departments</h4>
                    <p class="text-xs text-gray-500 mb-2">Departments linked through selected document tags</p>
                    <div id="connectedDepartments" class="space-y-2">
                        <span class="text-gray-400 text-sm">Select document tags to see connected departments</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            const departments = @json($departments);
            const documentTags = @json($documentTags);
            const currentConfig = @json($currentConfig);
            let stepIndex = 0;

            // Document Tags functionality
            function initDocumentTags() {
                document.querySelectorAll('.document-tag-checkbox').forEach(checkbox => {
                    checkbox.addEventListener('change', function() {
                        const tagId = this.dataset.tagId;
                        const requiredCheckbox = document.querySelector(`.required-checkbox[data-tag-id="${tagId}"]`);
                        
                        if (requiredCheckbox) {
                            requiredCheckbox.disabled = !this.checked;
                            if (!this.checked) {
                                requiredCheckbox.checked = false;
                            }
                        }
                        
                        updateSelectedTagsSummary();
                        updateConnectedDepartments();
                    });
                });

                // Initialize summary on page load
                updateSelectedTagsSummary();
                updateConnectedDepartments();
            }

            function updateSelectedTagsSummary() {
                const selectedCheckboxes = document.querySelectorAll('.document-tag-checkbox:checked');
                const summary = document.getElementById('selectedTagsSummary');
                const list = document.getElementById('selectedTagsList');

                if (selectedCheckboxes.length === 0) {
                    summary.style.display = 'none';
                    return;
                }

                summary.style.display = 'block';
                list.innerHTML = '';

                selectedCheckboxes.forEach(checkbox => {
                    const tagId = checkbox.dataset.tagId;
                    const tag = documentTags.find(t => t.id == tagId);
                    const isRequired = document.querySelector(`.required-checkbox[data-tag-id="${tagId}"]`)?.checked;
                    
                    if (tag) {
                        const badge = document.createElement('span');
                        badge.className = `badge ${isRequired ? 'badge-warning' : 'badge-primary'} gap-1`;
                        badge.innerHTML = `
                            ${tag.name}
                            ${isRequired ? '<svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>' : ''}
                            <button type="button" class="hover:text-error" onclick="removeTag(${tagId})">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        `;
                        list.appendChild(badge);
                    }
                });
            }

            function updateConnectedDepartments() {
                const selectedCheckboxes = document.querySelectorAll('.document-tag-checkbox:checked');
                const container = document.getElementById('connectedDepartments');

                if (selectedCheckboxes.length === 0) {
                    container.innerHTML = '<span class="text-gray-400 text-sm">Select document tags to see connected departments</span>';
                    return;
                }

                const departmentIds = new Set();
                selectedCheckboxes.forEach(checkbox => {
                    const tagId = checkbox.dataset.tagId;
                    const tag = documentTags.find(t => t.id == tagId);
                    // Handle multiple departments per tag
                    if (tag && tag.departments && tag.departments.length > 0) {
                        tag.departments.forEach(dept => departmentIds.add(dept.id));
                    }
                });

                let html = '';
                departmentIds.forEach(deptId => {
                    const dept = departments.find(d => d.id == deptId);
                    if (dept) {
                        // Count tags that belong to this department
                        const selectedTagsInDept = documentTags.filter(t => {
                            if (!t.departments) return false;
                            const belongsToDept = t.departments.some(d => d.id == deptId);
                            const isSelected = document.querySelector(`.document-tag-checkbox[data-tag-id="${t.id}"]:checked`);
                            return belongsToDept && isSelected;
                        });
                        
                        html += `
                            <div class="flex items-center justify-between p-2 bg-white rounded border border-gray-200">
                                <span class="text-sm font-medium">${dept.name}</span>
                                <span class="badge badge-sm badge-ghost">${selectedTagsInDept.length} tag${selectedTagsInDept.length !== 1 ? 's' : ''}</span>
                            </div>
                        `;
                    }
                });

                container.innerHTML = html || '<span class="text-gray-400 text-sm">No departments connected</span>';
            }

            function removeTag(tagId) {
                const checkbox = document.querySelector(`.document-tag-checkbox[data-tag-id="${tagId}"]`);
                if (checkbox) {
                    checkbox.checked = false;
                    checkbox.dispatchEvent(new Event('change'));
                }
            }

            function createStepHtml(index, departmentId = '', processTimeValue = 3, processTimeUnit = 'days',
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

                        <div class="form-control">
                            <label class="label">
                                <span class="label-text font-medium">Instructions/Notes</span>
                                <span class="label-text-alt text-gray-400">Optional</span>
                            </label>
                            <textarea name="steps[${index}][notes]" 
                                      class="textarea textarea-bordered textarea-sm step-notes" 
                                      rows="2" 
                                      placeholder="e.g., Review budget allocation and verify fund availability...">${notes}</textarea>
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

            // Initialize
            document.addEventListener('DOMContentLoaded', function() {
                initDocumentTags();
                initOriginDepartments();
                attachEventListeners();
                updateStepCountAndDifficulty();
            });

            // Origin Departments functionality
            function initOriginDepartments() {
                document.querySelectorAll('.origin-dept-checkbox').forEach(checkbox => {
                    checkbox.addEventListener('change', updateOriginDeptSummary);
                });
                updateOriginDeptSummary();
            }

            function updateOriginDeptSummary() {
                const selectedCheckboxes = document.querySelectorAll('.origin-dept-checkbox:checked');
                const countBadge = document.getElementById('originDeptCount');
                const summary = document.getElementById('originDeptSummary');
                const list = document.getElementById('originDeptList');

                countBadge.textContent = `${selectedCheckboxes.length} selected`;

                if (selectedCheckboxes.length === 0) {
                    summary.style.display = 'none';
                    return;
                }

                summary.style.display = 'block';
                list.innerHTML = '';

                selectedCheckboxes.forEach(checkbox => {
                    const deptId = checkbox.value;
                    const dept = departments.find(d => d.id == deptId);
                    
                    if (dept) {
                        const badge = document.createElement('span');
                        badge.className = 'badge badge-info gap-1';
                        badge.innerHTML = `
                            ${dept.name}
                            <button type="button" class="hover:text-error" onclick="removeOriginDept(${deptId})">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        `;
                        list.appendChild(badge);
                    }
                });
            }

            function removeOriginDept(deptId) {
                const checkbox = document.querySelector(`.origin-dept-checkbox[value="${deptId}"]`);
                if (checkbox) {
                    checkbox.checked = false;
                    updateOriginDeptSummary();
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
                            updatePreview();
                            updateVisualFlow();
                        }
                    };
                });

                // Department change
                document.querySelectorAll('.department-select').forEach(select => {
                    select.onchange = function() {
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
                });
            }

            function getFormData() {
                const steps = [];
                document.querySelectorAll('.step-item').forEach((item) => {
                    const deptId = item.querySelector('.department-select')?.value;
                    const processTimeValue = item.querySelector('.process-time-value')?.value || 3;
                    const processTimeUnit = item.querySelector('.process-time-unit')?.value || 'days';
                    const notes = item.querySelector('.step-notes')?.value || '';

                    if (deptId) {
                        steps.push({
                            department_id: parseInt(deptId),
                            process_time_value: parseInt(processTimeValue),
                            process_time_unit: processTimeUnit,
                            notes: notes
                        });
                    }
                });

                // Get difficulty from hidden input
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

            function getDifficultyBadgeClassForFlow() {
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
                const badgeClass = getDifficultyBadgeClassForFlow();

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
                saveBtn.innerHTML = '<span class="loading loading-spinner loading-sm"></span> Creating...';
            });
        </script>
    @endpush
@endsection