@extends('layouts.app')
@section('content')
    <x-container>
        <div class="mb-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold">Document Types Management</h1>
                    <p class="text-gray-600 mt-1">Create and manage document types for your department</p>
                </div>

                <button type="button" class="btn btn-primary" onclick="addDoctypeModal.showModal()">
                    <i data-lucide="plus" class="w-4 h-4 mr-2"></i>
                    Add Document Type
                </button>
            </div>


            <x-data-table :headers="['ID','Document Types', 'Description', 'Status', 'Created', 'Actions']"
                :sortableFields="['title', 'created_at']" :paginator="$documentTypes">

                @foreach ($documentTypes as $type)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-medium text-gray-900">
                            {{ $documentTypes->firstItem() + $loop->index }}
                        </td>
                        <td class="px-4 py-3 font-medium text-gray-900">
                            {{ $type->title }}
                        </td>
                        <td class="px-4 py-3 truncate text-gray-600">
                            {{ $type->description ?? 'No Description' }}
                        </td>
                        <td class="px-4 py-3">
                            @if ($type->is_active)
                                <span class="badge badge-success">Active</span>
                            @else
                                <span class="badge badge-ghost">Inactive</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-gray-600 text-sm">
                            {{ $type->created_at->format('M d, Y') }}
                        </td>
                        <td class="px-3 py-3">
                            <div class="flex gap-2">
                                @if ($type->is_active)
                                    <button type="button" class="btn btn-sm btn-ghost"
                                        onclick="openEditModal({{ $type->id }}, '{{ addslashes($type->title) }}', '{{ addslashes($type->description ?? '') }}')"
                                        title="Edit">
                                        <i data-lucide="edit" class="w-4 h-4"></i>
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforeach
            </x-data-table>
        </div>

        {{-- Add Modal --}}
        <x-modal id="addDoctypeModal" title="Add New Document Type" size="xl">
            <form action="{{ route('document-types.store') }}" method="POST" id="addDocTypeForm">
                @csrf

                <div class="mb-4">
                    <x-form.input name="title" label="Document Title" placeholder="e.g., Clearance, Travel Order" required
                        class="w-full" />
                </div>

                <div class="mb-4">
                    <x-form.textarea name="description" label="Description" placeholder="Enter description..." rows="4"
                        class="w-full" />
                </div>
            </form>

            <x-slot name="actions">
                <button type="button" class="btn btn-ghost" onclick="addDoctypeModal.close()">
                    Cancel
                </button>
                <button type="submit" form="addDocTypeForm" class="btn btn-primary">
                    <i data-lucide="save" class="w-4 h-4 mr-2"></i>
                    Create
                </button>
            </x-slot>
        </x-modal>

        {{-- Edit Modal --}}
        <x-modal id="editDoctypeModal" title="Edit Document Type" size="xl">
            <form method="POST" id="editDocTypeForm">
                @csrf
                @method('PUT')

                <div class="mb-4">
                    <x-form.input name="title" id="edit_title" label="Document Title"
                        placeholder="e.g., Clearance, Travel Order" required class="w-full" />
                </div>

                <div class="mb-4">
                    <x-form.textarea name="description" id="edit_description" label="Description"
                        placeholder="Enter description..." rows="4" class="w-full" />
                </div>
            </form>

            <x-slot name="actions">
                <button type="button" class="btn btn-ghost" onclick="editDoctypeModal.close()">
                    Cancel
                </button>
                <button type="submit" form="editDocTypeForm" class="btn btn-primary">
                    <i data-lucide="save" class="w-4 h-4 mr-2"></i>
                    Update
                </button>
            </x-slot>
        </x-modal>
    </x-container>
@endsection


@push('scripts')
    <script>
        function openEditModal(id, title, description) {
            document.getElementById('edit_title').value = title;
            document.getElementById('edit_description').value = description;
            document.getElementById('editDocTypeForm').action = `/document-types/${id}`;

            editDoctypeModal.showModal();

            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
        }

        // Auto-open modals on validation errors
        document.addEventListener('DOMContentLoaded', function () {
            @if($errors->any())
                @if(old('_method') === 'PUT')
                    const oldId = "{{ old('id', '') }}";
                    if (oldId) {
                        openEditModal(oldId, "{{ addslashes(old('title', '')) }}", "{{ addslashes(old('description', '')) }}");
                    }
                @else
                    addDoctypeModal.showModal();
                @endif
            @endif
            });
    </script>
@endpush