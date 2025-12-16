@extends('layouts.app')

@section('content')
    <div class="p-4 sm:p-6">
        {{-- Header --}}
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Transaction Types</h1>
                <p class="text-gray-600 mt-2">Manage your transaction types</p>
            </div>
            <button type="button" class="btn btn-primary gap-2" onclick="transactionModal.showModal()">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Add Transaction Type
            </button>
        </div>

        {{-- Data Table --}}
        <x-data-table :headers="['Document Name', 'Description', 'Status', 'Actions']" :paginator="$transactionTypes"
            emptyMessage="No transaction types found.">
            @forelse($transactionTypes as $type)
                <tr class="hover">
                    <td class="px-6 py-4 whitespace-nowrap font-medium text-gray-900">
                        {{ $type->transaction_name }}
                    </td>
                    <td class="px-6 py-4 text-gray-600 max-w-xs truncate">
                        {{ $type->description ?? '-' }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        @if($type->status)
                            <span class="badge badge-success">Active</span>
                        @else
                            <span class="badge">Inactive</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                        <button type="button" onclick="editTransactionType({{ $type->id }})"
                            class="btn btn-ghost btn-sm btn-link">
                            Edit
                        </button>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="px-6 py-6 text-center text-gray-500">
                        No transaction types found.
                    </td>
                </tr>
            @endforelse
        </x-data-table>
    </div>

    {{-- Create Transaction Modal --}}
    <x-modal id="transactionModal" title="Create Transaction Type" size="lg">
        <form id="transaction-typeForm" action="{{ route('admin.transaction-types.store') }}" method="POST"
            enctype="multipart/form-data" class="space-y-4">
            @csrf

            {{-- Document Name Field --}}
            <div class="form-control">
                <label class="label">
                    <span class="label-text font-medium">Document Name</span>
                </label>
                <input type="text" id="document_name" name="document_name" required class="input input-bordered"
                    placeholder="e.g., Invoice">
                @error('document_name')
                    <label class="label">
                        <span class="label-text-alt text-error">{{ $message }}</span>
                    </label>
                @enderror
            </div>

            {{-- Description Field --}}
            <div class="form-control">
                <label class="label">
                    <span class="label-text font-medium">Description</span>
                </label>
                <input type="text" name="description" id="description" class="input input-bordered"
                    placeholder="Enter description...">
                @error('description')
                    <label class="label">
                        <span class="label-text-alt text-error">{{ $message }}</span>
                    </label>
                @enderror
            </div>

            {{-- Status Field --}}
            <div class="form-control">
                <label class="cursor-pointer label">
                    <input type="checkbox" id="status" name="status" value="1" checked class="checkbox checkbox-primary">
                    <span class="label-text ml-2">Active</span>
                </label>
            </div>
        </form>

        @slot('actions')
        <button type="button" class="btn btn-ghost" onclick="transactionModal.close()">
            Cancel
        </button>
        <button type="submit" form="transaction-typeForm" class="btn btn-primary" id="submitBtn">
            Create Transaction
        </button>
        @endslot
    </x-modal>

    {{-- Edit Transaction Modal --}}
    <x-modal id="editTransactionModal" title="Edit Transaction Type" size="lg">
        <form id="editTransaction-typeForm" method="POST" enctype="multipart/form-data" class="space-y-4">
            @csrf
            @method('PUT')

            {{-- Document Name Field --}}
            <div class="form-control">
                <label class="label">
                    <span class="label-text font-medium">Document Name</span>
                </label>
                <input type="text" id="edit_transaction_name" name="transaction_name" required class="input input-bordered"
                    placeholder="e.g., Invoice">
                @error('transaction_name')
                    <label class="label">
                        <span class="label-text-alt text-error">{{ $message }}</span>
                    </label>
                @enderror
            </div>

            {{-- Description Field --}}
            <div class="form-control">
                <label class="label">
                    <span class="label-text font-medium">Description</span>
                </label>
                <input type="text" name="description" id="edit_description" class="input input-bordered"
                    placeholder="Enter description...">
                @error('description')
                    <label class="label">
                        <span class="label-text-alt text-error">{{ $message }}</span>
                    </label>
                @enderror
            </div>

            {{-- Status Field --}}
            <div class="form-control">
                <label class="cursor-pointer label">
                    <input type="checkbox" id="edit_status" name="status" value="1" class="checkbox checkbox-primary">
                    <span class="label-text ml-2">Active</span>
                </label>
            </div>
        </form>

        @slot('actions')
        <button type="button" class="btn btn-ghost" onclick="editTransactionModal.close()">
            Cancel
        </button>
        <button type="submit" form="editTransaction-typeForm" class="btn btn-primary" id="editSubmitBtn">
            Update Transaction
        </button>
        @endslot
    </x-modal>

    <script>
        const form = document.getElementById('transaction-typeForm');
        const submitBtn = document.getElementById('submitBtn');

        if (form) {
            form.addEventListener('submit', function (e) {
                submitBtn.disabled = true;
                submitBtn.textContent = 'Creating...';
            });
        }

        const modalElement = document.getElementById('transactionModal');
        if (modalElement) {
            const originalShowModal = transactionModal.showModal;
            transactionModal.showModal = function () {
                document.getElementById('transaction-typeForm').reset();
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Create Transaction'
                }

                originalShowModal.call(this);
            }
        }

        function editTransactionType(id) {
            fetch(`/transaction-types/${id}/edit`)
                .then(response => response.json())
                .then(data => {
                    document.getElementById('edit_transaction_name').value = data.transaction_name;
                    document.getElementById('edit_description').value = data.description || '';
                    document.getElementById('edit_status').checked = data.status == 1;

                    document.getElementById('editTransaction-typeForm').action = `/transaction-types/${id}`;

                    const editSubmitBtn = document.getElementById('editSubmitBtn');
                    editSubmitBtn.disabled = false;
                    editSubmitBtn.textContent = 'Update Transaction';

                    editTransactionModal.showModal();
                })
                .catch(error => {
                    console.error('Error', error);
                    alert('Failed to load transaction type');
                });
        }

        // Handle edit form submission
        const editForm = document.getElementById('editTransaction-typeForm');
        const editSubmitBtn = document.getElementById('editSubmitBtn');

        if (editForm) {
            editForm.addEventListener('submit', function (e) {
                editSubmitBtn.disabled = true;
                editSubmitBtn.textContent = 'Updating...';
            });
        }

        // Collect all steps
        document.querySelectorAll('[id^="step-"]').forEach((step, index) => {
            const deptSelect = step.querySelector('select[name*="department_id"]');
            const seqInput = step.querySelector('input[name*="sequence_order"]');
            const originCheckbox = step.querySelector('input[name*="is_originating"]');

            if (deptSelect && deptSelect.value) {
                departments.push({
                    department_id: parseInt(deptSelect.value),
                    sequence_order: parseInt(seqInput.value),
                    is_originating: originCheckbox.checked ? 1 : 0
                });
            }
        });

        if (departments.length === 0) {
            alert('Please configure at least one workflow step');
            submitBtn.disabled = false;
            submitBtn.textContent = 'Configure Workflow Cycle';
            return;
        }
    </script>
@endsection