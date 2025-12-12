@extends('layouts.app')

@section('content')
<div class="p-4 sm:p-6">
    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Workflow Configuration</h1>
            <p class="text-gray-600 mt-2">Configure document routing workflows for each transaction type</p>
        </div>
        <button type="button" class="btn btn-primary gap-2" onclick="createWorkflowModal.showModal()">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Create Workflow
        </button>
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

    {{-- Difficulty Legend --}}
    <div class="bg-base-100 rounded-lg shadow-sm p-4 mb-6">
        <div class="flex flex-wrap items-center gap-6">
            <span class="text-sm font-medium text-gray-700">Difficulty Levels:</span>
            <div class="flex items-center gap-2">
                <span class="badge badge-success badge-sm">Normal</span>
                <span class="text-xs text-gray-500">Standard processing</span>
            </div>
            <div class="flex items-center gap-2">
                <span class="badge badge-warning badge-sm">Urgent</span>
                <span class="text-xs text-gray-500">Priority handling</span>
            </div>
            <div class="flex items-center gap-2">
                <span class="badge badge-error badge-sm">Highly Urgent</span>
                <span class="text-xs text-gray-500">Immediate attention</span>
            </div>
        </div>
    </div>

    {{-- Transaction Types Grid --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($transactionTypes as $type)
            <div class="bg-base-100 rounded-lg shadow-md overflow-hidden">
                {{-- Card Header --}}
                <div class="p-4 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <h3 class="font-semibold text-gray-900">{{ $type->document_name }}</h3>
                        @if($type->status)
                            <span class="badge badge-success badge-sm">Active</span>
                        @else
                            <span class="badge badge-ghost badge-sm">Inactive</span>
                        @endif
                    </div>
                    @if($type->description)
                        <p class="text-sm text-gray-500 mt-1 line-clamp-2">{{ $type->description }}</p>
                    @endif
                </div>

                {{-- Workflow Status --}}
                <div class="p-4">
                    @if($type->hasWorkflowConfigured())
                        @php
                            $steps = $type->getWorkflowSteps();
                        @endphp
                        <div class="mb-3">
                            <span class="text-xs font-medium text-gray-500 uppercase">Workflow Steps</span>
                            <div class="mt-2 space-y-2">
                                @foreach($steps as $index => $step)
                                    @php
                                        $difficulty = $step['difficulty'] ?? 'normal';
                                        $badgeClass = match($difficulty) {
                                            'urgent' => 'badge-warning',
                                            'highly_urgent' => 'badge-error',
                                            default => 'badge-success'
                                        };
                                    @endphp
                                    <div class="flex items-start gap-2">
                                        {{-- Step Number with Difficulty Color --}}
                                        <span class="badge {{ $badgeClass }} badge-sm flex-shrink-0">{{ $index + 1 }}</span>
                                        
                                        <div class="flex-1 min-w-0">
                                            {{-- Department Name --}}
                                            <div class="flex items-center gap-2 flex-wrap">
                                                <span class="font-medium text-sm text-gray-900">{{ $step['department_name'] }}</span>
                                                {{-- Process Time Badge --}}
                                                <span class="badge badge-ghost badge-xs">
                                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                    </svg>
                                                    {{ $step['process_time_value'] ?? 3 }} {{ $step['process_time_unit'] ?? 'days' }}
                                                </span>
                                                {{-- Difficulty Badge --}}
                                                <span class="badge {{ $badgeClass }} badge-xs">{{ ucfirst(str_replace('_', ' ', $difficulty)) }}</span>
                                            </div>
                                            
                                            {{-- Notes (if exists) --}}
                                            @if(!empty($step['notes']))
                                                <p class="text-xs text-gray-500 mt-1 line-clamp-1" title="{{ $step['notes'] }}">
                                                    {{ $step['notes'] }}
                                                </p>
                                            @endif

                                            {{-- Return To Info --}}
                                            @if(!empty($step['can_return_to']))
                                                <div class="flex items-center gap-1 mt-1">
                                                    <svg class="w-3 h-3 text-warning" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6" />
                                                    </svg>
                                                    <span class="text-xs text-warning">Can return to previous</span>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        {{-- Summary --}}
                        <div class="flex items-center justify-between text-xs text-gray-500 pt-2 border-t border-gray-100">
                            <span>
                                <span class="font-medium">{{ count($steps) }}</span> steps
                            </span>
                            @php
                                // Calculate total estimated time
                                $totalHours = 0;
                                foreach ($steps as $step) {
                                    $value = $step['process_time_value'] ?? 3;
                                    $unit = $step['process_time_unit'] ?? 'days';
                                    $totalHours += match($unit) {
                                        'hours' => $value,
                                        'days' => $value * 24,
                                        'weeks' => $value * 24 * 7,
                                        default => $value * 24,
                                    };
                                }
                                // Convert back to readable format
                                if ($totalHours >= 168) {
                                    $totalDisplay = round($totalHours / 168, 1) . ' weeks';
                                } elseif ($totalHours >= 24) {
                                    $totalDisplay = round($totalHours / 24, 1) . ' days';
                                } else {
                                    $totalDisplay = $totalHours . ' hours';
                                }
                            @endphp
                            <span class="flex items-center gap-1">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Est. {{ $totalDisplay }}
                            </span>
                        </div>
                    @else
                        <div class="flex items-center gap-2 text-warning">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                            <span class="text-sm">No workflow configured</span>
                        </div>
                    @endif
                </div>

                {{-- Card Footer --}}
                <div class="p-4 bg-gray-50 border-t border-gray-200 flex gap-2">
                    <a href="{{ route('admin.workflows.edit', $type) }}" class="btn btn-primary btn-sm flex-1">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                        {{ $type->hasWorkflowConfigured() ? 'Edit' : 'Configure' }}
                    </a>
                    @if($type->hasWorkflowConfigured())
                        <button type="button" class="btn btn-outline btn-sm" onclick="duplicateWorkflow({{ $type->id }}, '{{ $type->document_name }}')" title="Duplicate Workflow">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                            </svg>
                        </button>
                    @endif
                </div>
            </div>
        @empty
            <div class="col-span-full">
                <div class="bg-base-100 rounded-lg shadow-md p-8 text-center">
                    <svg class="w-12 h-12 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">No Transaction Types</h3>
                    <p class="text-gray-500 mb-4">Create transaction types first before configuring workflows.</p>
                    <a href="{{ route('admin.transaction-types.index') }}" class="btn btn-primary">
                        Manage Transaction Types
                    </a>
                </div>
            </div>
        @endforelse
    </div>
</div>

{{-- Create Workflow Modal --}}
<x-modal id="createWorkflowModal" title="Create New Workflow" size="md">
    <form id="createWorkflowForm" action="" method="GET">
        <div class="form-control">
            <label class="label">
                <span class="label-text font-medium">Select Transaction Type</span>
            </label>
            <select name="transaction_type_id" id="selectTransactionType" class="select select-bordered" required>
                <option value="">Choose a transaction type...</option>
                @foreach($transactionTypes as $type)
                    <option value="{{ $type->id }}" data-has-workflow="{{ $type->hasWorkflowConfigured() ? '1' : '0' }}">
                        {{ $type->document_name }}
                        @if($type->hasWorkflowConfigured())
                            (Has Workflow)
                        @endif
                    </option>
                @endforeach
            </select>
            <label class="label">
                <span class="label-text-alt text-gray-500">Select a transaction type to configure its workflow</span>
            </label>
        </div>

        <div id="workflowWarning" class="alert alert-warning mt-4" style="display: none;">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
            <span>This transaction type already has a workflow. Editing will modify the existing configuration.</span>
        </div>
    </form>

    @slot('actions')
        <button type="button" class="btn btn-ghost" onclick="createWorkflowModal.close()">Cancel</button>
        <button type="button" class="btn btn-primary" id="goToWorkflowBtn" onclick="goToWorkflowEdit()">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
            </svg>
            Configure Workflow
        </button>
    @endslot
</x-modal>

{{-- Duplicate Workflow Modal --}}
<x-modal id="duplicateWorkflowModal" title="Duplicate Workflow" size="md">
    <form id="duplicateWorkflowForm">
        <input type="hidden" id="sourceTypeId" name="source_type_id">
        
        <div class="alert alert-info mb-4">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span>Copying workflow from: <strong id="sourceTypeName"></strong></span>
        </div>

        <div class="form-control">
            <label class="label">
                <span class="label-text font-medium">Copy To Transaction Type</span>
            </label>
            <select name="target_type_id" id="targetTransactionType" class="select select-bordered" required>
                <option value="">Choose destination...</option>
                @foreach($transactionTypes as $type)
                    <option value="{{ $type->id }}" data-has-workflow="{{ $type->hasWorkflowConfigured() ? '1' : '0' }}">
                        {{ $type->document_name }}
                        @if($type->hasWorkflowConfigured())
                            (Will Overwrite)
                        @endif
                    </option>
                @endforeach
            </select>
        </div>

        <div id="overwriteWarning" class="alert alert-warning mt-4" style="display: none;">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
            <span>Warning: This will overwrite the existing workflow configuration!</span>
        </div>
    </form>

    @slot('actions')
        <button type="button" class="btn btn-ghost" onclick="duplicateWorkflowModal.close()">Cancel</button>
        <button type="button" class="btn btn-primary" id="duplicateBtn" onclick="submitDuplicate()">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
            </svg>
            Duplicate Workflow
        </button>
    @endslot
</x-modal>

<script>
    // Modal helpers
    const createWorkflowModal = {
        showModal: function() {
            document.getElementById('selectTransactionType').value = '';
            document.getElementById('workflowWarning').style.display = 'none';
            document.getElementById('createWorkflowModal').classList.remove('hidden');
        },
        close: function() {
            document.getElementById('createWorkflowModal').classList.add('hidden');
        }
    };

    const duplicateWorkflowModal = {
        showModal: function() {
            document.getElementById('duplicateWorkflowModal').classList.remove('hidden');
        },
        close: function() {
            document.getElementById('duplicateWorkflowModal').classList.add('hidden');
        }
    };

    // Show warning if transaction type already has workflow
    document.getElementById('selectTransactionType').addEventListener('change', function() {
        const selected = this.options[this.selectedIndex];
        const hasWorkflow = selected.dataset.hasWorkflow === '1';
        document.getElementById('workflowWarning').style.display = hasWorkflow ? 'flex' : 'none';
    });

    // Show warning for overwrite in duplicate modal
    document.getElementById('targetTransactionType').addEventListener('change', function() {
        const selected = this.options[this.selectedIndex];
        const hasWorkflow = selected.dataset.hasWorkflow === '1';
        document.getElementById('overwriteWarning').style.display = hasWorkflow ? 'flex' : 'none';
    });

    function goToWorkflowEdit() {
        const typeId = document.getElementById('selectTransactionType').value;
        if (!typeId) {
            alert('Please select a transaction type');
            return;
        }
        window.location.href = `{{ url('admin/workflows') }}/${typeId}/edit`;
    }

    function duplicateWorkflow(sourceId, sourceName) {
        document.getElementById('sourceTypeId').value = sourceId;
        document.getElementById('sourceTypeName').textContent = sourceName;
        document.getElementById('targetTransactionType').value = '';
        document.getElementById('overwriteWarning').style.display = 'none';
        
        // Remove source from target options temporarily
        const targetSelect = document.getElementById('targetTransactionType');
        Array.from(targetSelect.options).forEach(opt => {
            if (opt.value === String(sourceId)) {
                opt.disabled = true;
                opt.text = opt.text + ' (Source)';
            } else {
                opt.disabled = false;
                opt.text = opt.text.replace(' (Source)', '');
            }
        });
        
        duplicateWorkflowModal.showModal();
    }

    function submitDuplicate() {
        const sourceId = document.getElementById('sourceTypeId').value;
        const targetId = document.getElementById('targetTransactionType').value;
        
        if (!targetId) {
            alert('Please select a destination transaction type');
            return;
        }

        const btn = document.getElementById('duplicateBtn');
        btn.disabled = true;
        btn.innerHTML = '<span class="loading loading-spinner loading-sm"></span> Duplicating...';

        fetch(`{{ url('admin/workflows') }}/${sourceId}/duplicate`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ target_type_id: targetId })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.reload();
            } else {
                alert(data.error || 'Failed to duplicate workflow');
                btn.disabled = false;
                btn.innerHTML = '<svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" /></svg> Duplicate Workflow';
            }
        })
        .catch(err => {
            console.error('Duplicate error:', err);
            alert('Failed to duplicate workflow');
            btn.disabled = false;
            btn.innerHTML = '<svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" /></svg> Duplicate Workflow';
        });
    }
</script>
@endsection