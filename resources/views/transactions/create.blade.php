@extends('layouts.app')

@section('title', 'Create Transaction')

@section('content')
    <x-container>
        <div class="mb-6">
            <div class="flex items-center gap-2 text-sm text-gray-500 mb-2">
                <a href="{{ route('transactions.index') }}" class="hover:text-primary">Transactions</a>
                <i data-lucide="chevron-right" class="w-4 h-4"></i>
                <span>Create</span>
            </div>
            <h1 class="text-3xl font-bold">Create Transaction</h1>
            <p class="text-gray-600 mt-1">{{ $workflow->transaction_name }}</p>
        </div>

        <form action="{{ route('transactions.store') }}" method="POST" enctype="multipart/form-data"
            id="createTransactionForm">
            @csrf
            <input type="hidden" name="workflow_id" value="{{ $workflow->id }}">
            <input type="hidden" name="update_workflow_default" id="updateWorkflowDefault" value="0">

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                {{-- Main Form --}}
                <div class="lg:col-span-2 space-y-6">
                    {{-- Transaction Details --}}
                    <x-card title="Transaction Details">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Level of Urgency <span class="text-red-500">*</span>
                                </label>
                                <select name="level_of_urgency"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    required>
                                    <option value="normal" {{ old('level_of_urgency') == 'normal' ? 'selected' : '' }}>
                                        Normal</option>
                                    <option value="urgent" {{ old('level_of_urgency') == 'urgent' ? 'selected' : '' }}>
                                        Urgent</option>
                                    <option value="highly_urgent"
                                        {{ old('level_of_urgency') == 'highly_urgent' ? 'selected' : '' }}>Highly Urgent
                                    </option>
                                </select>
                                @error('level_of_urgency')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Assign Staff
                                </label>
                                <select name="assign_staff_id"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    <option value="">-- Select Staff --</option>
                                    @foreach ($assignStaff as $staff)
                                        <option value="{{ $staff->id }}"
                                            {{ old('assign_staff_id') == $staff->id ? 'selected' : '' }}>
                                            {{ $staff->full_name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('assign_staff_id')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="mt-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                <i data-lucide="tag" class="w-4 h-4 inline"></i>
                                Attach Document Tags (Multi-select)
                            </label>
                            <div class="border border-gray-300 rounded-lg overflow-hidden">
                                <div class="p-3 max-h-32 overflow-y-auto" id="documentTagsContainer">
                                    @if ($documentTags->count() > 0)
                                        <div class="space-y-2">
                                            @foreach ($documentTags as $tag)
                                                @if (!isset($tag->created_by_admin) || !$tag->created_by_admin)
                                                    <label
                                                        class="flex items-center gap-2 cursor-pointer hover:bg-gray-50 p-2 rounded">
                                                        <input type="checkbox" name="document_tag_ids[]"
                                                            value="{{ $tag->id }}"
                                                            class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                                                            onchange="updateSelectedDocumentTags()"
                                                            {{ in_array($tag->id, old('document_tag_ids', [])) ? 'checked' : '' }}>
                                                        <span class="text-sm">{{ $tag->name }}</span>
                                                    </label>
                                                @endif
                                            @endforeach
                                        </div>
                                    @else
                                        <p class="text-gray-400 text-sm">No document tags available</p>
                                    @endif
                                </div>
                                <div class="border-t border-gray-200 p-2">
                                    <button type="button" onclick="openCreateTagModal()"
                                        class="w-full px-3 py-2 text-sm bg-blue-900 text-white hover:bg-blue-800 rounded-lg font-medium flex items-center justify-center gap-2">
                                        <i data-lucide="plus-circle" class="w-4 h-4 text-white"></i>
                                    </button>
                                </div>
                            </div>
                            @error('document_tag_ids')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </x-card>

                    {{-- Workflow Steps Preview --}}
                    <x-card>
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <h3 class="text-lg font-semibold">Workflow Route</h3>
                                <p class="text-sm text-gray-500" id="stepCount">{{ count($workflowSteps) }} steps</p>
                            </div>
                            <div class="flex gap-2">
                                {{-- Preview Mode --}}
                                <div id="previewModeButtons">
                                    <div class="relative">
                                        <button type="button" class="p-2 hover:bg-gray-100 rounded-lg" id="dropdownEditBtn"
                                            onclick="toggleDropdown()">
                                            <i data-lucide="ellipsis-vertical" class="w-5 h-5"></i>
                                        </button>
                                        <div id="workflowDropdown"
                                            class="hidden absolute right-0 mt-2 w-52 bg-white rounded-lg shadow-lg border border-gray-200 z-10">
                                            <div class="py-1">
                                                <a id="editWorkflowBtn" href="#"
                                                    class="flex items-center gap-2 px-4 py-2 text-sm hover:bg-gray-100">
                                                    <i data-lucide="edit-2" class="w-4 h-4"></i> Edit Workflow
                                                </a>
                                                <a id="resetWorkflowBtn" href="#"
                                                    class="flex items-center gap-2 px-4 py-2 text-sm hover:bg-gray-100">
                                                    <i data-lucide="rotate-ccw" class="w-4 h-4"></i> Reset to Default
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Edit Mode Buttons --}}
                                <div id="editModeButtons" style="display: none;" class="flex gap-2">
                                    <button type="button" id="saveWorkflowBtn"
                                        class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700">
                                        <i data-lucide="check" class="w-4 h-4 mr-1 inline"></i>
                                        Save
                                    </button>
                                    <button type="button" id="cancelEditBtn"
                                        class="px-4 py-2 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-100">
                                        <i data-lucide="x" class="w-4 h-4 mr-1 inline"></i>
                                        Cancel
                                    </button>
                                </div>
                            </div>
                        </div>

                        {{-- Preview Mode --}}
                        <div id="workflowPreview" class="space-y-3 max-h-96 overflow-y-auto pr-2">
                            @foreach ($workflowSteps as $index => $step)
                                <div class="flex items-start gap-3 p-3 bg-gray-50 rounded-lg">
                                    <div
                                        class="flex-shrink-0 w-8 h-8 rounded-full bg-primary text-white flex items-center justify-center text-sm font-bold">
                                        {{ $index + 1 }}
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="font-medium truncate">
                                            {{ $step['department_name'] ?? 'Unknown Department' }}</div>
                                        @if (isset($step['department_head']))
                                            <div class="text-xs text-gray-600">
                                                <i data-lucide="user" class="w-3 h-3 inline"></i>
                                                {{ $step['department_head'] }}
                                            </div>
                                        @endif
                                        <div class="text-sm text-gray-500">
                                            Process time: {{ $step['process_time_value'] ?? 0 }}
                                            {{ $step['process_time_unit'] ?? 'days' }}
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        {{-- Edit Mode --}}
                        <div id="workflowEdit" class="hidden">
                            <div class="space-y-3 max-h-96 overflow-y-auto pr-2" id="workflowStepsList">
                                {{-- Steps will be rendered dynamically --}}
                            </div>
                            <button type="button" id="addStepBtn"
                                class="w-full px-4 py-2 mt-3 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 flex items-center justify-center gap-2">
                                <i data-lucide="plus" class="w-4 h-4"></i>
                                Add Step
                            </button>
                        </div>

                        {{-- Hidden input to store workflow snapshot --}}
                        <input type="hidden" name="workflow_snapshot" id="workflowSnapshotInput">
                    </x-card>
                </div>

                {{-- Sidebar --}}
                <div class="space-y-6">
                    {{-- Workflow Info --}}
                    <x-card title="Workflow Information">
                        <dl class="space-y-3">
                            <div>
                                <dt class="text-sm text-gray-500">Workflow</dt>
                                <dd class="font-medium">{{ $workflow->transaction_name }}</dd>
                            </div>
                            @if ($workflow->description)
                                <div>
                                    <dt class="text-sm text-gray-500">Description</dt>
                                    <dd class="text-sm">{{ $workflow->description }}</dd>
                                </div>
                            @endif
                            <div>
                                <dt class="text-sm text-gray-500">Total Steps</dt>
                                <dd class="font-medium" id="sidebarStepCount">{{ count($workflowSteps) }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm text-gray-500">Estimated Time</dt>
                                <dd class="font-medium" id="estimatedTime">-</dd>
                            </div>
                            <div id="workflowStatus">
                                <dt class="text-sm text-gray-500">Status</dt>
                                <dd><span
                                        class="inline-block px-2 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-700"
                                        id="workflowStatusBadge">Default Route</span></dd>
                            </div>
                            @if ($workflow->documentTags->count() > 0)
                                <div>
                                    <dt class="text-sm text-gray-500 mb-2">Default Document Attachment</dt>
                                    <dd class="flex flex-wrap gap-1">
                                        @foreach ($workflow->documentTags as $tag)
                                            <span
                                                class="inline-block px-2 py-1 text-xs font-medium rounded-full bg-green-500 text-white">{{ $tag->name }}</span>
                                        @endforeach
                                    </dd>
                                </div>
                            @endif
                            <div id="selectedDocumentTagsContainer">
                                <dt class="text-sm text-gray-500 mb-2">
                                    <i data-lucide="tag" class="w-4 h-4 inline"></i>
                                    Selected Document Attachment
                                </dt>
                                <dd id="selectedDocumentTagsList" class="flex flex-wrap gap-1">
                                    <span class="text-gray-400 text-xs">None selected</span>
                                </dd>
                            </div>
                            <div id="departmentHeadsContainer">
                                <dt class="text-sm text-gray-500 mb-2">Department Heads</dt>
                                <dd id="departmentHeadsList" class="text-sm space-y-1">
                                    <span class="text-gray-400">Loading...</span>
                                </dd>
                            </div>
                            @if ($workflow->difficulty)
                                <div>
                                    <dt class="text-sm text-gray-500">Difficulty</dt>
                                    <dd>
                                        <x-status-badge :status="$workflow->difficulty" :labels="['easy' => 'Easy', 'medium' => 'Medium', 'hard' => 'Hard']" :variants="[
                                            'easy' => 'badge-success',
                                            'medium' => 'badge-warning',
                                            'hard' => 'badge-error',
                                        ]" />
                                    </dd>
                                </div>
                            @endif
                        </dl>
                    </x-card>

                    {{-- Actions --}}
                    <x-card>
                        <div class="space-y-3">
                            @can('update', $workflow)
                                <div id="makeDefaultContainer" class="hidden">
                                    <label
                                        class="flex items-center gap-2 cursor-pointer p-3 border rounded-lg hover:bg-gray-50">
                                        <input type="checkbox" id="makeDefaultRoute"
                                            class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                        <span class="text-sm">Update workflow template with this custom route</span>
                                    </label>
                                </div>
                            @endcan
                            <button type="submit"
                                class="w-full px-4 py-2 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 flex items-center justify-center gap-2">
                                <i data-lucide="send" class="w-4 h-4"></i>
                                Submit Transaction
                            </button>
                            <a href="{{ route('transactions.index') }}"
                                class="block w-full px-4 py-2 text-gray-700 font-medium rounded-lg hover:bg-gray-100 text-center">
                                Cancel
                            </a>
                        </div>
                    </x-card>
                </div>
            </div>
        </form>
    </x-container>

    {{-- Modal for Creating Custom Document Tag --}}
    <x-modal id="createTagModal" title="Create Custom Document Tag" size="md">
        <form id="createTagForm">
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Tag Name <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="tagName" name="tag_name"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        placeholder="Enter tag name" required>
                    <p id="tagNameError" class="text-red-500 text-sm mt-1 hidden"></p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Description (Optional)
                    </label>
                    <textarea id="tagDescription" name="tag_description" rows="3"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        placeholder="Enter tag description"></textarea>
                </div>
            </div>
        </form>
        <x-slot name="actions">
            <button type="button" onclick="createTagModal.close()"
                class="px-4 py-2 text-gray-700 font-medium rounded-lg hover:bg-gray-100">
                Cancel
            </button>
            <button type="button" onclick="saveCustomTag()" id="saveTagBtn"
                class="px-4 py-2 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700">
                <span id="saveTagBtnText">Create Tag</span>
            </button>
        </x-slot>
    </x-modal>

    @push('scripts')
        <script>
            // Default workflow configuration from the template
            const defaultWorkflowConfig = @json($workflowConfig);
            const departments = @json($departments);
            const documentTags = @json($documentTags);
            const workflowId = '{{ $workflow->id }}';
            const storageKey = `workflow_custom_${workflowId}`;
            const canUpdateWorkflow = {{ auth()->user()->can('update', $workflow) ? 'true' : 'false' }};

            // Dropdown toggle function
            function toggleDropdown() {
                const dropdown = document.getElementById('workflowDropdown');
                dropdown.classList.toggle('hidden');
            }

            // Close dropdown when clicking outside
            document.addEventListener('click', function(e) {
                const dropdownBtn = document.getElementById('dropdownEditBtn');
                const dropdown = document.getElementById('workflowDropdown');
                if (dropdown && !dropdown.classList.contains('hidden') &&
                    !dropdownBtn.contains(e.target) && !dropdown.contains(e.target)) {
                    dropdown.classList.add('hidden');
                }
            });

            // Update selected document tags display
            window.updateSelectedDocumentTags = function() {
                const selectedTagsList = document.getElementById('selectedDocumentTagsList');
                const checkboxes = document.querySelectorAll('input[name="document_tag_ids[]"]:checked');

                if (checkboxes.length === 0) {
                    selectedTagsList.innerHTML = '<span class="text-gray-400 text-xs">None selected</span>';
                } else {
                    const selectedTags = Array.from(checkboxes).map(cb => {
                        const tagId = parseInt(cb.value);
                        const tag = documentTags.find(t => t.id === tagId);
                        return tag ? tag.name : '';
                    }).filter(name => name);

                    selectedTagsList.innerHTML = selectedTags.map(name =>
                        `<span class="inline-block px-2 py-1 text-xs font-medium rounded-full bg-blue-500 text-white">${name}</span>`
                    ).join('');
                }
            };

            // Load saved workflow from localStorage or use default
            let currentWorkflowConfig;
            try {
                const savedConfig = localStorage.getItem(storageKey);
                if (savedConfig) {
                    currentWorkflowConfig = JSON.parse(savedConfig);
                    console.log('Loaded custom workflow from localStorage');
                } else {
                    currentWorkflowConfig = JSON.parse(JSON.stringify(defaultWorkflowConfig));
                }
            } catch (e) {
                console.error('Error loading saved workflow:', e);
                currentWorkflowConfig = JSON.parse(JSON.stringify(defaultWorkflowConfig));
            }

            // Edit mode state
            let isEditMode = false;

            // DOM elements
            const dropdownEditBtn = document.getElementById('dropdownEditBtn');
            const editBtn = document.getElementById('editWorkflowBtn');
            const saveBtn = document.getElementById('saveWorkflowBtn');
            const cancelBtn = document.getElementById('cancelEditBtn');
            const resetBtn = document.getElementById('resetWorkflowBtn');
            const previewModeButtons = document.getElementById('previewModeButtons');
            const editModeButtons = document.getElementById('editModeButtons');
            const previewSection = document.getElementById('workflowPreview');
            const editSection = document.getElementById('workflowEdit');
            const stepsList = document.getElementById('workflowStepsList');
            const addStepBtn = document.getElementById('addStepBtn');
            const stepCountEl = document.getElementById('stepCount');
            const workflowSnapshotInput = document.getElementById('workflowSnapshotInput');
            const makeDefaultCheckbox = document.getElementById('makeDefaultRoute');
            const updateWorkflowDefaultInput = document.getElementById('updateWorkflowDefault');

            // Initialize: Update preview if custom workflow exists, otherwise use default
            const hasCustomWorkflow = JSON.stringify(currentWorkflowConfig) !== JSON.stringify(defaultWorkflowConfig);
            if (hasCustomWorkflow) {
                // Custom workflow exists in localStorage - update preview to show it
                updatePreview();
                console.log('Using custom workflow from localStorage');
            } else {
                // Use default workflow - just update info
                updateWorkflowInfo();
            }

            // Initialize document tags display
            updateSelectedDocumentTags();

            // Modal functions for custom tag creation
            function openCreateTagModal() {
                document.getElementById('tagName').value = '';
                document.getElementById('tagDescription').value = '';
                document.getElementById('tagNameError').classList.add('hidden');
                createTagModal.showModal();
            }

            async function saveCustomTag() {
                const tagName = document.getElementById('tagName').value.trim();
                const tagDescription = document.getElementById('tagDescription').value.trim();
                const errorEl = document.getElementById('tagNameError');
                const saveBtn = document.getElementById('saveTagBtn');
                const saveBtnText = document.getElementById('saveTagBtnText');

                if (!tagName) {
                    errorEl.textContent = 'Tag name is required';
                    errorEl.classList.remove('hidden');
                    return;
                }

                // Show loading state
                saveBtn.disabled = true;
                saveBtnText.textContent = 'Creating...';

                try {
                    const response = await fetch('{{ route('admin.document-tags.store') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: JSON.stringify({
                            name: tagName,
                            description: tagDescription,
                            status: true,
                            created_by_head: true
                        })
                    });

                    const data = await response.json();

                    if (response.ok) {
                        // Add the new tag to the documentTags array
                        documentTags.push(data.tag);

                        // Add the new tag to the UI
                        const container = document.getElementById('documentTagsContainer');
                        const noTagsMsg = container.querySelector('p.text-gray-400');
                        if (noTagsMsg) {
                            noTagsMsg.remove();
                            const newDiv = document.createElement('div');
                            newDiv.className = 'space-y-2';
                            container.appendChild(newDiv);
                        }

                        const tagsDiv = container.querySelector('div.space-y-2');
                        const newTagLabel = document.createElement('label');
                        newTagLabel.className = 'flex items-center gap-2 cursor-pointer hover:bg-gray-50 p-2 rounded';
                        newTagLabel.innerHTML = `
                        <input type="checkbox" 
                               name="document_tag_ids[]" 
                               value="${data.tag.id}"
                               class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                               onchange="updateSelectedDocumentTags()"
                               checked>
                        <span class="text-sm">${data.tag.name}</span>
                    `;
                        tagsDiv.appendChild(newTagLabel);

                        // Update selected tags display
                        updateSelectedDocumentTags();

                        // Reinitialize Lucide icons in the new tag
                        if (typeof lucide !== 'undefined') {
                            lucide.createIcons();
                        }

                        // Close modal
                        createTagModal.close();

                        // Show success message
                        alert('Custom tag created successfully!');
                    } else {
                        errorEl.textContent = data.message || 'Failed to create tag';
                        errorEl.classList.remove('hidden');
                    }
                } catch (error) {
                    console.error('Error creating tag:', error);
                    errorEl.textContent = 'An error occurred. Please try again.';
                    errorEl.classList.remove('hidden');
                } finally {
                    // Reset button state
                    saveBtn.disabled = false;
                    saveBtnText.textContent = 'Create Tag';
                }
            }

            // Toggle edit mode
            editBtn.addEventListener('click', (e) => {
                e.preventDefault();
                // Close dropdown
                const dropdown = document.getElementById('workflowDropdown');
                if (dropdown) {
                    dropdown.classList.add('hidden');
                }
                isEditMode = true;
                enterEditMode();
            });

            // Save workflow changes
            saveBtn.addEventListener('click', () => {
                // Validate that all steps have departments selected
                const invalidSteps = currentWorkflowConfig.steps.filter(step => !step.department_id);
                if (invalidSteps.length > 0) {
                    alert('Please select a department for all workflow steps.');
                    return;
                }

                // Save to localStorage - this becomes the active workflow for this transaction
                try {
                    localStorage.setItem(storageKey, JSON.stringify(currentWorkflowConfig));
                    console.log('Workflow saved to localStorage');
                } catch (e) {
                    console.error('Error saving workflow to localStorage:', e);
                }

                isEditMode = false;
                exitEditMode();
            });

            // Cancel edit mode
            cancelBtn.addEventListener('click', () => {
                if (confirm('Are you sure you want to cancel? Any unsaved changes will be lost.')) {
                    // Reload from localStorage or revert to default
                    try {
                        const savedConfig = localStorage.getItem(storageKey);
                        if (savedConfig) {
                            currentWorkflowConfig = JSON.parse(savedConfig);
                        } else {
                            currentWorkflowConfig = JSON.parse(JSON.stringify(defaultWorkflowConfig));
                        }
                    } catch (e) {
                        currentWorkflowConfig = JSON.parse(JSON.stringify(defaultWorkflowConfig));
                    }
                    isEditMode = false;
                    exitEditMode();
                }
            });

            // Reset to default workflow
            resetBtn.addEventListener('click', (e) => {
                e.preventDefault();
                // Close dropdown
                const dropdown = document.getElementById('workflowDropdown');
                if (dropdown) {
                    dropdown.classList.add('hidden');
                }
                if (confirm('Are you sure you want to reset to the default workflow? All changes will be lost.')) {
                    currentWorkflowConfig = JSON.parse(JSON.stringify(defaultWorkflowConfig));
                    // Clear localStorage
                    try {
                        localStorage.removeItem(storageKey);
                        console.log('Custom workflow cleared from localStorage');
                    } catch (e) {
                        console.error('Error clearing localStorage:', e);
                    }
                    updatePreview();
                    updateWorkflowInfo();
                }
            });

            // Add new step
            addStepBtn.addEventListener('click', () => {
                const newStep = {
                    department_id: '',
                    department_name: '',
                    department_head: '',
                    process_time_value: 1,
                    process_time_unit: 'days'
                };
                currentWorkflowConfig.steps.push(newStep);
                renderEditableSteps();
                updateStepCount();
            });

            // Enter edit mode
            function enterEditMode() {
                previewSection.classList.add('hidden');
                editSection.classList.remove('hidden');

                // Toggle button visibility
                previewModeButtons.style.display = 'none';
                editModeButtons.style.display = 'flex';

                renderEditableSteps();
            }

            // Exit edit mode
            function exitEditMode() {
                previewSection.classList.remove('hidden');
                editSection.classList.add('hidden');

                // Toggle button visibility
                previewModeButtons.style.display = 'block';
                editModeButtons.style.display = 'none';

                updatePreview();
                updateWorkflowInfo();
            }

            // Render editable steps
            function renderEditableSteps() {
                stepsList.innerHTML = '';

                if (!currentWorkflowConfig.steps || currentWorkflowConfig.steps.length === 0) {
                    stepsList.innerHTML =
                        '<p class="text-gray-500 text-center py-4">No steps added yet. Click "Add Step" to begin.</p>';
                    updateWorkflowInfo();
                    return;
                }

                currentWorkflowConfig.steps.forEach((step, index) => {
                    const stepElement = createEditableStepElement(step, index);
                    stepsList.appendChild(stepElement);
                });

                // Reinitialize Lucide icons
                if (typeof lucide !== 'undefined') {
                    lucide.createIcons();
                }

                // Update workflow info after rendering
                updateWorkflowInfo();
            }

            // Create editable step element
            function createEditableStepElement(step, index) {
                const div = document.createElement('div');
                div.className = 'border rounded-lg p-4 bg-white';
                div.innerHTML = `
                <div class="flex items-start gap-3">
                    <div class="flex-shrink-0 w-8 h-8 rounded-full bg-primary text-white flex items-center justify-center text-sm font-bold">
                        ${index + 1}
                    </div>
                    <div class="flex-1 space-y-3 min-w-0">
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">
                                Department
                            </label>
                            <select class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" onchange="updateStepDepartment(${index}, this.value)">
                                <option value="">-- Select Department --</option>
                                ${departments.map(dept => `
                                            <option value="${dept.id}" data-name="${dept.name}" data-head="${dept.head_name || ''}" ${step.department_id == dept.id ? 'selected' : ''}>
                                                ${dept.name}
                                            </option>
                                        `).join('')}
                            </select>
                            ${step.department_head ? `<p class="text-xs text-gray-600 mt-1"><i data-lucide="user" class="w-3 h-3 inline"></i> ${step.department_head}</p>` : ''}
                        </div>
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">
                                    Process Time
                                </label>
                                <input type="number" min="1" value="${step.process_time_value || 1}" 
                                    class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                                    onchange="updateStepProcessTime(${index}, this.value, '${step.process_time_unit}')">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">
                                    Unit
                                </label>
                                <select class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                                    onchange="updateStepProcessTime(${index}, ${step.process_time_value}, this.value)">
                                    <option value="minutes" ${step.process_time_unit === 'minutes' ? 'selected' : ''}>Minutes</option>
                                    <option value="hours" ${step.process_time_unit === 'hours' ? 'selected' : ''}>Hours</option>
                                    <option value="days" ${step.process_time_unit === 'days' ? 'selected' : ''}>Days</option>
                                    <option value="weeks" ${step.process_time_unit === 'weeks' ? 'selected' : ''}>Weeks</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="flex flex-col gap-2">
                        ${index > 0 ? `
                                    <button type="button" class="p-1 hover:bg-gray-100 rounded" onclick="moveStepUp(${index})" title="Move Up">
                                        <i data-lucide="chevron-up" class="w-4 h-4"></i>
                                    </button>
                                ` : '<div class="w-8 h-6"></div>'}
                        ${index < currentWorkflowConfig.steps.length - 1 ? `
                                    <button type="button" class="p-1 hover:bg-gray-100 rounded" onclick="moveStepDown(${index})" title="Move Down">
                                        <i data-lucide="chevron-down" class="w-4 h-4"></i>
                                    </button>
                                ` : '<div class="w-8 h-6"></div>'}
                        <button type="button" class="p-1 text-red-600 hover:bg-red-50 rounded" onclick="removeStep(${index})" title="Remove">
                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                        </button>
                    </div>
                </div>
            `;
                return div;
            }

            // Update step department
            window.updateStepDepartment = function(index, deptId) {
                const dept = departments.find(d => d.id == deptId);
                currentWorkflowConfig.steps[index].department_id = deptId ? parseInt(deptId) : '';
                currentWorkflowConfig.steps[index].department_name = dept ? dept.name : '';
                currentWorkflowConfig.steps[index].department_head = dept && dept.head_name ? dept.head_name : '';
                renderEditableSteps();
                updateStepCount();
            };

            // Update step process time
            window.updateStepProcessTime = function(index, value, unit) {
                currentWorkflowConfig.steps[index].process_time_value = parseInt(value);
                currentWorkflowConfig.steps[index].process_time_unit = unit;
                updateStepCount();
            };

            // Remove step
            window.removeStep = function(index) {
                if (confirm('Are you sure you want to remove this step?')) {
                    currentWorkflowConfig.steps.splice(index, 1);
                    renderEditableSteps();
                    updateStepCount();
                }
            };

            // Move step up
            window.moveStepUp = function(index) {
                if (index > 0) {
                    const temp = currentWorkflowConfig.steps[index];
                    currentWorkflowConfig.steps[index] = currentWorkflowConfig.steps[index - 1];
                    currentWorkflowConfig.steps[index - 1] = temp;
                    renderEditableSteps();
                }
            };

            // Move step down
            window.moveStepDown = function(index) {
                if (index < currentWorkflowConfig.steps.length - 1) {
                    const temp = currentWorkflowConfig.steps[index];
                    currentWorkflowConfig.steps[index] = currentWorkflowConfig.steps[index + 1];
                    currentWorkflowConfig.steps[index + 1] = temp;
                    renderEditableSteps();
                }
            };

            // Update preview after edit
            function updatePreview() {
                previewSection.innerHTML = '';

                if (!currentWorkflowConfig.steps || currentWorkflowConfig.steps.length === 0) {
                    previewSection.innerHTML = '<p class="text-gray-500 text-center py-4">No workflow steps configured.</p>';
                    return;
                }

                currentWorkflowConfig.steps.forEach((step, index) => {
                    const stepDiv = document.createElement('div');
                    stepDiv.className = 'flex items-start gap-3 p-3 bg-gray-50 rounded-lg';
                    const departmentHead = step.department_head ? `
                    <div class="text-xs text-gray-600">
                        <i data-lucide="user" class="w-3 h-3 inline"></i> ${step.department_head}
                    </div>
                ` : '';
                    stepDiv.innerHTML = `
                    <div class="flex-shrink-0 w-8 h-8 rounded-full bg-primary text-white flex items-center justify-center text-sm font-bold">
                        ${index + 1}
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="font-medium truncate">${step.department_name || 'Unknown Department'}</div>
                        ${departmentHead}
                        <div class="text-sm text-gray-500">
                            Process time: ${step.process_time_value || 0} ${step.process_time_unit || 'days'}
                        </div>
                    </div>
                `;
                    previewSection.appendChild(stepDiv);
                });

                // Reinitialize Lucide icons
                if (typeof lucide !== 'undefined') {
                    lucide.createIcons();
                }

                updateStepCount();
            }

            // Calculate estimated time in hours
            function calculateEstimatedTime() {
                if (!currentWorkflowConfig.steps || currentWorkflowConfig.steps.length === 0) {
                    return 0;
                }

                let totalHours = 0;
                currentWorkflowConfig.steps.forEach(step => {
                    const value = parseInt(step.process_time_value) || 0;
                    const unit = step.process_time_unit || 'days';

                    switch (unit) {
                        case 'minutes':
                            totalHours += value / 60;
                            break;
                        case 'hours':
                            totalHours += value;
                            break;
                        case 'days':
                            totalHours += value * 24;
                            break;
                        case 'weeks':
                            totalHours += value * 24 * 7;
                            break;
                    }
                });

                return totalHours;
            }

            // Format estimated time for display
            function formatEstimatedTime(hours) {
                if (hours === 0) return '0 minutes';

                const weeks = Math.floor(hours / (24 * 7));
                const days = Math.floor((hours % (24 * 7)) / 24);
                const remainingHours = Math.floor(hours % 24);
                const minutes = Math.round((hours % 1) * 60);

                const parts = [];
                if (weeks > 0) parts.push(`${weeks} week${weeks !== 1 ? 's' : ''}`);
                if (days > 0) parts.push(`${days} day${days !== 1 ? 's' : ''}`);
                if (remainingHours > 0) parts.push(`${remainingHours} hour${remainingHours !== 1 ? 's' : ''}`);
                if (minutes > 0) parts.push(`${minutes} minute${minutes !== 1 ? 's' : ''}`);

                return parts.join(', ') || '0 minutes';
            }

            // Update step count
            function updateStepCount() {
                const count = currentWorkflowConfig.steps ? currentWorkflowConfig.steps.length : 0;
                stepCountEl.textContent = `${count} step${count !== 1 ? 's' : ''}`;
                updateWorkflowInfo();
            }

            // Update workflow information sidebar
            function updateWorkflowInfo() {
                const count = currentWorkflowConfig.steps ? currentWorkflowConfig.steps.length : 0;
                const sidebarCount = document.getElementById('sidebarStepCount');
                const statusBadge = document.getElementById('workflowStatusBadge');
                const estimatedTimeEl = document.getElementById('estimatedTime');
                const makeDefaultContainer = document.getElementById('makeDefaultContainer');
                const departmentHeadsList = document.getElementById('departmentHeadsList');

                if (sidebarCount) {
                    sidebarCount.textContent = count;
                }

                if (estimatedTimeEl) {
                    const totalHours = calculateEstimatedTime();
                    estimatedTimeEl.textContent = formatEstimatedTime(totalHours);
                }

                const isCustomRoute = JSON.stringify(currentWorkflowConfig) !== JSON.stringify(defaultWorkflowConfig);

                if (statusBadge) {
                    if (isCustomRoute) {
                        statusBadge.textContent = 'Custom Route';
                        statusBadge.className =
                            'inline-block px-2 py-1 text-xs font-medium rounded-full bg-yellow-100 text-yellow-800';
                    } else {
                        statusBadge.textContent = 'Default Route';
                        statusBadge.className =
                            'inline-block px-2 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-700';
                    }
                }

                // Show/hide "Make default" checkbox based on whether route is custom
                if (makeDefaultContainer && canUpdateWorkflow) {
                    if (isCustomRoute) {
                        makeDefaultContainer.classList.remove('hidden');
                    } else {
                        makeDefaultContainer.classList.add('hidden');
                    }
                }

                // Update department heads list
                if (departmentHeadsList) {
                    const uniqueDepartments = new Map();
                    currentWorkflowConfig.steps.forEach(step => {
                        if (step.department_id && step.department_name) {
                            uniqueDepartments.set(step.department_id, {
                                name: step.department_name,
                                head: step.department_head || 'Not assigned'
                            });
                        }
                    });

                    if (uniqueDepartments.size === 0) {
                        departmentHeadsList.innerHTML = '<span class="text-gray-400">No departments yet</span>';
                    } else {
                        departmentHeadsList.innerHTML = Array.from(uniqueDepartments.values())
                            .map(dept => `
                            <div class="flex items-start gap-2">
                                <i data-lucide="user" class="w-4 h-4 mt-0.5 flex-shrink-0"></i>
                                <div class="flex-1 min-w-0">
                                    <div class="font-medium text-gray-700">${dept.name}</div>
                                    <div class="text-xs text-gray-500">${dept.head}</div>
                                </div>
                            </div>
                        `).join('');

                        // Reinitialize Lucide icons
                        if (typeof lucide !== 'undefined') {
                            lucide.createIcons();
                        }
                    }
                }
            }

            // Before form submission, set the workflow snapshot
            document.getElementById('createTransactionForm').addEventListener('submit', function(e) {
                // Always use currentWorkflowConfig (which is either custom from localStorage or default)
                // Check if it's different from the workflow template
                const isCustomRoute = JSON.stringify(currentWorkflowConfig) !== JSON.stringify(defaultWorkflowConfig);

                // Validate all steps have departments
                const invalidSteps = currentWorkflowConfig.steps.filter(step => !step.department_id);
                if (invalidSteps.length > 0) {
                    e.preventDefault();
                    alert('Please ensure all workflow steps have a department selected.');
                    return false;
                }

                // Prepare workflow snapshot
                const workflowSnapshot = {
                    workflow_id: workflowId,
                    is_custom: isCustomRoute,
                    steps: currentWorkflowConfig.steps.map((step, index) => ({
                        step_order: index + 1,
                        department_id: step.department_id,
                        department_name: step.department_name,
                        department_head: step.department_head || '',
                        process_time_value: step.process_time_value || 1,
                        process_time_unit: step.process_time_unit || 'days'
                    }))
                };

                workflowSnapshotInput.value = JSON.stringify(workflowSnapshot);

                // Check if user wants to update workflow template default
                if (canUpdateWorkflow && makeDefaultCheckbox && makeDefaultCheckbox.checked) {
                    updateWorkflowDefaultInput.value = '1';
                    // If updating workflow template, clear the custom route from localStorage
                    // so the new default will be used on next load
                    try {
                        localStorage.removeItem(storageKey);
                        console.log('Cleared custom workflow from localStorage (updating template default)');
                    } catch (e) {
                        console.error('Error clearing localStorage:', e);
                    }
                }
                // Note: Custom workflows persist in localStorage for reuse
                // Users can explicitly reset using the "Reset" button if needed

                console.log(isCustomRoute ? 'Submitting custom workflow' : 'Submitting default workflow',
                    workflowSnapshot);
            });
        </script>
    @endpush
@endsection
