@extends('layouts.app')

@section('title', 'Workflow Management')

@section('content')
<div class="container mx-auto px-4 py-6">
    {{-- Header --}}
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Workflow Management</h1>
            <p class="text-gray-600 mt-1">Configure document routing workflows for each transaction type</p>
        </div>
        <a href="{{ route('admin.workflows.create') }}" class="btn btn-primary">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Create Workflow
        </a>
    </div>

    {{-- Transaction Types with Workflows --}}
    @foreach($transactionTypes as $transactionType)
    <div class="card bg-base-100 shadow-md mb-6">
        <div class="card-body">
            <div class="flex justify-between items-start mb-4">
                <div>
                    <h2 class="card-title text-lg">{{ $transactionType->document_name }}</h2>
                    <p class="text-sm text-gray-500">{{ $transactionType->description }}</p>
                </div>
                <a href="{{ route('admin.workflows.create', ['transaction_type_id' => $transactionType->id]) }}" 
                   class="btn btn-sm btn-outline btn-primary">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Add Workflow
                </a>
            </div>

            @if($transactionType->workflows->count() > 0)
                <div class="overflow-x-auto">
                    <table class="table table-zebra w-full">
                        <thead>
                            <tr>
                                <th>Workflow Name</th>
                                <th>Steps</th>
                                <th>Difficulty</th>
                                <th>Default</th>
                                <th>Status</th>
                                <th class="text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($transactionType->workflows as $workflow)
                            <tr>
                                <td>
                                    <div class="font-medium">{{ $workflow->name }}</div>
                                    @if($workflow->description)
                                        <div class="text-xs text-gray-500">{{ Str::limit($workflow->description, 50) }}</div>
                                    @endif
                                </td>
                                <td>
                                    <div class="flex flex-wrap gap-1">
                                        @foreach($workflow->getWorkflowSteps() as $index => $step)
                                            <span class="badge badge-ghost badge-sm">
                                                {{ $index + 1 }}. {{ $step['department_name'] ?? 'Unknown' }}
                                            </span>
                                        @endforeach
                                    </div>
                                </td>
                                <td>
                                    <span class="badge {{ $workflow->getDifficultBadgeClass() }}">
                                        {{ ucfirst(str_replace('_', ' ', $workflow->difficulty)) }}
                                    </span>
                                </td>
                                <td>
                                    @if($workflow->is_default)
                                        <span class="badge badge-primary">Default</span>
                                    @else
                                        <button onclick="setDefault({{ $workflow->id }})" 
                                                class="btn btn-xs btn-ghost text-gray-400 hover:text-primary">
                                            Set Default
                                        </button>
                                    @endif
                                </td>
                                <td>
                                    <label class="swap">
                                        <input type="checkbox" 
                                               {{ $workflow->status ? 'checked' : '' }}
                                               onchange="toggleStatus({{ $workflow->id }})">
                                        <span class="swap-on badge badge-success">Active</span>
                                        <span class="swap-off badge badge-ghost">Inactive</span>
                                    </label>
                                </td>
                                <td class="text-right">
                                    <div class="flex gap-1 justify-end">
                                        <a href="{{ route('admin.workflows.edit', $workflow) }}" 
                                           class="btn btn-xs btn-ghost" title="Edit">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                            </svg>
                                        </a>
                                        <button onclick="openDuplicateModal({{ $workflow->id }}, '{{ $workflow->name }}')" 
                                                class="btn btn-xs btn-ghost" title="Duplicate">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                            </svg>
                                        </button>
                                        <button onclick="confirmDelete({{ $workflow->id }}, '{{ $workflow->name }}')" 
                                                class="btn btn-xs btn-ghost text-error" title="Delete">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-8 text-gray-500">
                    <svg class="w-12 h-12 mx-auto mb-2 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <p>No workflows configured yet</p>
                    <a href="{{ route('admin.workflows.create', ['transaction_type_id' => $transactionType->id]) }}" 
                       class="btn btn-sm btn-primary mt-2">
                        Create First Workflow
                    </a>
                </div>
            @endif
        </div>
    </div>
    @endforeach

    @if($transactionTypes->isEmpty())
        <div class="card bg-base-100 shadow-md">
            <div class="card-body text-center py-12">
                <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <h3 class="text-lg font-medium text-gray-900 mb-2">No Transaction Types</h3>
                <p class="text-gray-500 mb-4">Create transaction types first before configuring workflows.</p>
                <a href="{{ route('admin.transaction-types.index') }}" class="btn btn-primary">
                    Manage Transaction Types
                </a>
            </div>
        </div>
    @endif
</div>

{{-- Duplicate Workflow Modal --}}
<dialog id="duplicateModal" class="modal">
    <div class="modal-box">
        <h3 class="font-bold text-lg">Duplicate Workflow</h3>
        <form id="duplicateForm">
            <input type="hidden" id="duplicateWorkflowId">
            <div class="form-control mt-4">
                <label class="label"><span class="label-text">New Workflow Name</span></label>
                <input type="text" id="duplicateName" class="input input-bordered" required>
            </div>
            <div class="form-control mt-4">
                <label class="label"><span class="label-text">Transaction Type (optional - leave same or copy to another)</span></label>
                <select id="duplicateTypeId" class="select select-bordered">
                    <option value="">Same Transaction Type</option>
                    @foreach($transactionTypes as $type)
                        <option value="{{ $type->id }}">{{ $type->document_name }}</option>
                    @endforeach
                </select>
            </div>
        </form>
        <div class="modal-action">
            <button class="btn btn-ghost" onclick="duplicateModal.close()">Cancel</button>
            <button class="btn btn-primary" onclick="submitDuplicate()">Duplicate</button>
        </div>
    </div>
</dialog>

{{-- Delete Confirmation Modal --}}
<dialog id="deleteModal" class="modal">
    <div class="modal-box">
        <h3 class="font-bold text-lg text-error">Delete Workflow</h3>
        <p class="py-4">Are you sure you want to delete "<span id="deleteWorkflowName"></span>"? This action cannot be undone.</p>
        <form id="deleteForm" method="POST">
            @csrf
            @method('DELETE')
        </form>
        <div class="modal-action">
            <button class="btn btn-ghost" onclick="deleteModal.close()">Cancel</button>
            <button class="btn btn-error" onclick="submitDelete()">Delete</button>
        </div>
    </div>
</dialog>

<script>
    const duplicateModal = document.getElementById('duplicateModal');
    const deleteModal = document.getElementById('deleteModal');

    function toggleStatus(workflowId) {
        fetch(`{{ url('admin/workflows') }}/${workflowId}/toggle-status`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        }).then(r => r.json()).then(data => {
            if (!data.success) alert(data.message || 'Error updating status');
        });
    }

    function setDefault(workflowId) {
        fetch(`{{ url('admin/workflows') }}/${workflowId}/set-default`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        }).then(r => r.json()).then(data => {
            if (data.success) window.location.reload();
            else alert(data.message || 'Error setting default');
        });
    }

    function openDuplicateModal(workflowId, workflowName) {
        document.getElementById('duplicateWorkflowId').value = workflowId;
        document.getElementById('duplicateName').value = workflowName + ' (Copy)';
        document.getElementById('duplicateTypeId').value = '';
        duplicateModal.showModal();
    }

    function submitDuplicate() {
        const workflowId = document.getElementById('duplicateWorkflowId').value;
        const name = document.getElementById('duplicateName').value;
        const typeId = document.getElementById('duplicateTypeId').value;

        fetch(`{{ url('admin/workflows') }}/${workflowId}/duplicate`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ 
                name: name,
                transaction_type_id: typeId || null
            })
        }).then(r => r.json()).then(data => {
            if (data.success) window.location.reload();
            else alert(data.error || 'Error duplicating workflow');
        });
    }

    function confirmDelete(workflowId, workflowName) {
        document.getElementById('deleteWorkflowName').textContent = workflowName;
        document.getElementById('deleteForm').action = `{{ url('admin/workflows') }}/${workflowId}`;
        deleteModal.showModal();
    }

    function submitDelete() {
        document.getElementById('deleteForm').submit();
    }
</script>
@endsection