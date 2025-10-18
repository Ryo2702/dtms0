@extends('layouts.app')
@section('content')
    <x-container>
        @if (session('success'))
            <x-toast type="success" :message="session('success')" />
        @endif

        @if(session('error'))
            <x-toast type="error" :message="session('error')" />
        @endif

        @if($errors->any())
            <x-toast type="error" title="Please correct the following errors:" :messages="$errors->all()" />
        @endif
        <div class="mb-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold">Document Types Management</h1>
                    <p class="text-gray-600 mt-1">Create and manage document types for your department</p>
                </div>

                <button type="button" class="btn btn-primary" onclick="openModal('addDoctypeModal')">
                    <i data-lucide="plus" class="w-4 h-4 mr-2"></i>
                    Add Document Type
                </button>
            </div>
            
            <div class="bg-white rounded-lg shadow-md">
                <x-data-table
                    :headers="['Title', 'Description', 'Status', 'Created', 'Actions']"
                    :sortableFields="['title', 'created_at']"
                    :paginator="$documentTypes">

                    @foreach ($documentTypes as $type)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 font-medium text-gray-900">
                                {{ $type->title }}
                            </td>
                            <td class="px-4 py-3 truncate text-gray-600">
                                {{ $type->description ?? 'No Description' }}
                            </td>
                            <td class="px-4 py-3">
                                @if ($type->is_active)
                                    <span>Active</span>
                                @else
                                    <span>Inactive</span>    
                                @endif
                            </td>
                            <td class="px-4 py-3 text-gray-600 text-sm">
                                {{ $type->created_at->format('M d, Y') }}
                            </td>
                            <td class="px-3 py-3">
                                <div class="flex gap-2">
                                    @if ($type->is_active)
                                    <button type="button" class="btn btn-sm btn-ghost text-blue-600 hover:text-blue-800 hover:bg-blue-50" onclick="editDocumentType(@json($type))" title="Edit">
                                        <i data-lucide="edit" class="w-4 h-4"></i>
                                    </button>
                                    @endif
                                </div>
                            </td>
`                        </tr>
                    @endforeach
                </x-data-table>
                  <x-modal id="addDoctypeModal" title="Add New Document Type" size="lg">
                <form action="{{ route('document-types.store') }}" method="POST">
                    @csrf

                    <x-form.input 
                        label="Document Title *"
                        name="title"
                        placeholder="e.g., Clearance, Travel Order"
                        :value="old('title')"
                        required
                    />

                <x-form.textarea 
                    label="Description" 
                    name="description" 
                    id="description"
                    rows="4"
                />

                <x-slot name="actions">
                    <button type="button" class="btn btn-ghost" onclick="closeModal('addDoctypeModal')">
                        Cancel
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i data-lucide="save" class="w-4 h-4 mr-2"></i>
                        Create
                    </button>
                </x-slot>
                </form>
            </x-modal>
            </div>
        </div>
    </x-container>

    <script>
        function openModal(modalId){
            const modal = document.getElementById(modalId);
            const backdrop = document.getElementById(modalId + '-backdrop')

            if (modal && backdrop) {
                modal.style.display = 'flex';
                backdrop.style.display = 'block';
                document.body.style.overflow = 'hidden';
                modal.classList.add('modal-open');

                if (typeof lucide !== 'undefined') {
                    lucide.createIcons();
                }
            }
        }

        function closeModal(modalId){
            const modal = document.getElementById(modalId);
             const backdrop = document.getElementById(modalId + '-backdrop')


            if (modal && backdrop) {
                modal.style.display = 'none';
                backdrop.style.display = 'none';
                document.body.style.overflow = '';
                modal.classList.remove('modal-open');

                const form = modal.querySelector('form');
                if (form) {
                    form.reset();
                }
            }
        }

         // Close modal on Escape key
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                const openModals = document.querySelectorAll('.modal[style*="display: flex"]');
                openModals.forEach(modal => {
                    closeModal(modal.id);
                });
            }
        });

         function editDocumentType(type) {
            document.getElementById('edit_title').value = type.title;
            document.getElementById('edit_description').value = type.description || '';
            document.getElementById('editDocTypeForm').action = `/document-types/${type.id}`;
            
            openModal('editDocTypeModal');
        }


        @if($errors->any() && old('_method') === 'PUT')
            document.addEventListener('DOMContentLoaded', function() {
                const oldId = '{{ old('id') }}';
                if (oldId) {
                    editDocumentType({
                        id: oldId,
                        title: '{{ old('title') }}',
                        description: '{{ old('description') }}'
                    });
                }
            });
        @elseif($errors->any())
            document.addEventListener('DOMContentLoaded', function() {
                openModal('addDocTypeModal');
            });
        @endif
    </script>
@endsection