@extends('layouts.app')

@section('content')
    <div class="p-4 sm:p-6">
        {{-- Header --}}
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Documents</h1>
                <p class="text-gray-600 mt-2">Manage your documents</p>
            </div>
            <button type="button" class="btn btn-primary gap-2" onclick="tagModal.showModal()">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M5 12h14"/>
                    <path d="M12 5v14"/>
                </svg>
                Add Documents
            </button>
        </div>

        {{-- Filters --}}
        <div class="mb-4 flex gap-2">
            <select class="select select-bordered w-full max-w-xs" id="departmentFilter" onchange="filterByDepartment()">
                <option value="">All Departments</option>
                @foreach($departments ?? [] as $dept)
                    <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                @endforeach
            </select>
            
            <select class="select select-bordered w-full max-w-xs" id="statusFilter" onchange="filterByStatus()">
                <option value="">All Status</option>
                <option value="1">Active</option>
                <option value="0">Inactive</option>
            </select>
        </div>

        {{-- Data Table --}}
        <x-data-table :headers="['Tag Name', 'Slug', 'Description', 'Department', 'Status', 'Actions']" :paginator="$documentTags"
            emptyMessage="No document tags found.">
            @forelse($documentTags as $tag)
                <tr class="hover">
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-primary">
                                <path d="M12 2H2v10l9.29 9.29c.94.94 2.48.94 3.42 0l6.58-6.58c.94-.94.94-2.48 0-3.42L12 2Z"/>
                                <path d="M7 7h.01"/>
                            </svg>
                            <span class="font-medium text-gray-900">{{ $tag->name }}</span>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="badge badge-ghost">{{ $tag->slug }}</span>
                    </td>
                    <td class="px-6 py-4 text-gray-600 max-w-xs truncate">
                        {{ $tag->description ?? '-' }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        @if($tag->departments->count() > 0)
                            <div class="flex flex-wrap gap-1">
                                @foreach($tag->departments->take(3) as $dept)
                                    <span class="badge badge-outline badge-sm">{{ $dept->name }}</span>
                                @endforeach
                                @if($tag->departments->count() > 3)
                                    <span class="badge badge-ghost badge-sm">+{{ $tag->departments->count() - 3 }} more</span>
                                @endif
                            </div>
                        @else
                            <span class="text-gray-400">All Departments</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        @if($tag->status)
                            <span class="badge badge-success gap-1">
                                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                                    <path d="m9 11 3 3L22 4"/>
                                </svg>
                                Active
                            </span>
                        @else
                            <span class="badge badge-ghost gap-1">
                                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="12" cy="12" r="10"/>
                                    <path d="m15 9-6 6"/>
                                    <path d="m9 9 6 6"/>
                                </svg>
                                Inactive
                            </span>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                        <button type="button" onclick="editDocumentTag({{ $tag->id }})"
                            class="btn btn-ghost btn-sm gap-1">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/>
                                <path d="m15 5 4 4"/>
                            </svg>
                            Edit
                        </button>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="px-6 py-8 text-center">
                        <div class="flex flex-col items-center justify-center text-gray-500">
                            <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mb-2 text-gray-400">
                                <path d="M12 2H2v10l9.29 9.29c.94.94 2.48.94 3.42 0l6.58-6.58c.94-.94.94-2.48 0-3.42L12 2Z"/>
                                <path d="M7 7h.01"/>
                            </svg>
                            <p>No documents found.</p>
                        </div>
                    </td>
                </tr>
            @endforelse
        </x-data-table>
    </div>

    {{-- Create Tag Modal --}}
    <x-modal id="tagModal" title="Create Document Tag" size="lg">
        <form id="tag-form" action="{{ route('admin.document-tags.store') }}" method="POST" class="space-y-4">
            @csrf

            {{-- Tag Name Field --}}
            <div class="form-control">
                <label class="label">
                    <span class="label-text font-medium">Tag Name <span class="text-error">*</span></span>
                </label>
                <input type="text" id="name" name="name" required class="input input-bordered"
                    placeholder="e.g., Urgent, Confidential">
                @error('name')
                    <label class="label">
                        <span class="label-text-alt text-error">{{ $message }}</span>
                    </label>
                @enderror
            </div>

            {{-- Slug Field --}}
            <div class="form-control">
                <label class="label">
                    <span class="label-text font-medium">Slug</span>
                    <span class="label-text-alt text-gray-500">Auto-generated from name</span>
                </label>
                <input type="text" id="slug" name="slug" class="input input-bordered"
                    placeholder="auto-generated">
                <label class="label">
                    <span class="label-text-alt text-gray-500">Leave empty to auto-generate</span>
                </label>
                @error('slug')
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
                <textarea name="description" id="description" class="textarea textarea-bordered h-24"
                    placeholder="Enter tag description..."></textarea>
                @error('description')
                    <label class="label">
                        <span class="label-text-alt text-error">{{ $message }}</span>
                    </label>
                @enderror
            </div>

            {{-- Departments Field (Multiple) --}}
            <div class="form-control">
                <label class="label">
                    <span class="label-text font-medium">Departments</span>
                    <span class="label-text-alt text-gray-500">Leave empty for all departments</span>
                </label>
                <div class="grid grid-cols-2 gap-2 max-h-48 overflow-y-auto p-2 border rounded-lg">
                    @foreach($departments ?? [] as $dept)
                        <label class="cursor-pointer label justify-start gap-2">
                            <input type="checkbox" name="department_ids[]" value="{{ $dept->id }}" class="checkbox checkbox-sm checkbox-primary">
                            <span class="label-text">{{ $dept->name }}</span>
                        </label>
                    @endforeach
                </div>
                @error('department_ids')
                    <label class="label">
                        <span class="label-text-alt text-error">{{ $message }}</span>
                    </label>
                @enderror
            </div>

            {{-- Status Field --}}
            <div class="form-control">
                <label class="cursor-pointer label justify-start gap-2">
                    <input type="checkbox" id="status" name="status" value="1" checked class="checkbox checkbox-primary">
                    <span class="label-text">Active</span>
                </label>
            </div>
        </form>

        @slot('actions')
        <button type="button" class="btn btn-ghost" onclick="tagModal.close()">
            Cancel
        </button>
        <button type="submit" form="tag-form" class="btn btn-primary" id="submitBtn">
            Create Tag
        </button>
        @endslot
    </x-modal>

    {{-- Edit Tag Modal --}}
    <x-modal id="editTagModal" title="Edit Document Tag" size="lg">
        <form id="edit-tag-form" method="POST" class="space-y-4">
            @csrf
            @method('PUT')

            {{-- Tag Name Field --}}
            <div class="form-control">
                <label class="label">
                    <span class="label-text font-medium">Tag Name <span class="text-error">*</span></span>
                </label>
                <input type="text" id="edit_name" name="name" required class="input input-bordered"
                    placeholder="e.g., Urgent, Confidential">
                @error('name')
                    <label class="label">
                        <span class="label-text-alt text-error">{{ $message }}</span>
                    </label>
                @enderror
            </div>

            {{-- Slug Field --}}
            <div class="form-control">
                <label class="label">
                    <span class="label-text font-medium">Slug</span>
                </label>
                <input type="text" id="edit_slug" name="slug" class="input input-bordered"
                    placeholder="auto-generated">
                @error('slug')
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
                <textarea name="description" id="edit_description" class="textarea textarea-bordered h-24"
                    placeholder="Enter tag description..."></textarea>
                @error('description')
                    <label class="label">
                        <span class="label-text-alt text-error">{{ $message }}</span>
                    </label>
                @enderror
            </div>

            {{-- Departments Field (Multiple) --}}
            <div class="form-control">
                <label class="label">
                    <span class="label-text font-medium">Departments</span>
                    <span class="label-text-alt text-gray-500">Leave empty for all departments</span>
                </label>
                <div id="edit_departments_container" class="grid grid-cols-2 gap-2 max-h-48 overflow-y-auto p-2 border rounded-lg">
                    @foreach($departments ?? [] as $dept)
                        <label class="cursor-pointer label justify-start gap-2">
                            <input type="checkbox" name="department_ids[]" value="{{ $dept->id }}" class="checkbox checkbox-sm checkbox-primary edit-dept-checkbox">
                            <span class="label-text">{{ $dept->name }}</span>
                        </label>
                    @endforeach
                </div>
                @error('department_ids')
                    <label class="label">
                        <span class="label-text-alt text-error">{{ $message }}</span>
                    </label>
                @enderror
            </div>

            {{-- Status Field --}}
            <div class="form-control">
                <label class="cursor-pointer label justify-start gap-2">
                    <input type="checkbox" id="edit_status" name="status" value="1" class="checkbox checkbox-primary">
                    <span class="label-text">Active</span>
                </label>
            </div>
        </form>

        @slot('actions')
        <button type="button" class="btn btn-ghost" onclick="editTagModal.close()">
            Cancel
        </button>
        <button type="submit" form="edit-tag-form" class="btn btn-primary" id="editSubmitBtn">
            Update Tag
        </button>
        @endslot
    </x-modal>

    <script>
        // Create form handling
        const form = document.getElementById('tag-form');
        const submitBtn = document.getElementById('submitBtn');

        if (form) {
            form.addEventListener('submit', function (e) {
                submitBtn.disabled = true;
                submitBtn.textContent = 'Creating...';
            });
        }

        // Reset create modal on open
        const modalElement = document.getElementById('tagModal');
        if (modalElement) {
            const originalShowModal = tagModal.showModal;
            tagModal.showModal = function () {
                document.getElementById('tag-form').reset();
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Create Tag';
                }
                originalShowModal.call(this);
            }
        }

        // Auto-generate slug from name
        document.getElementById('name')?.addEventListener('input', function(e) {
            const slug = e.target.value
                .toLowerCase()
                .replace(/[^a-z0-9]+/g, '-')
                .replace(/^-+|-+$/g, '');
            document.getElementById('slug').value = slug;
        });

        document.getElementById('edit_name')?.addEventListener('input', function(e) {
            const slug = e.target.value
                .toLowerCase()
                .replace(/[^a-z0-9]+/g, '-')
                .replace(/^-+|-+$/g, '');
            document.getElementById('edit_slug').value = slug;
        });

        // Edit document tag
        function editDocumentTag(id) {
            fetch(`/admin/document-tags/${id}/edit`)
                .then(response => response.json())
                .then(data => {
                    document.getElementById('edit_name').value = data.name;
                    document.getElementById('edit_slug').value = data.slug || '';
                    document.getElementById('edit_description').value = data.description || '';
                    
                    // Reset all department checkboxes
                    document.querySelectorAll('.edit-dept-checkbox').forEach(cb => cb.checked = false);
                    
                    // Check the departments that are assigned
                    if (data.department_ids && data.department_ids.length > 0) {
                        data.department_ids.forEach(deptId => {
                            const checkbox = document.querySelector(`.edit-dept-checkbox[value="${deptId}"]`);
                            if (checkbox) checkbox.checked = true;
                        });
                    }
                    
                    document.getElementById('edit_status').checked = data.status == 1;

                    document.getElementById('edit-tag-form').action = `/admin/document-tags/${id}`;

                    const editSubmitBtn = document.getElementById('editSubmitBtn');
                    editSubmitBtn.disabled = false;
                    editSubmitBtn.textContent = 'Update Tag';

                    editTagModal.showModal();
                })
                .catch(error => {
                    console.error('Error', error);
                    alert('Failed to load document tag');
                });
        }

        // Handle edit form submission
        const editForm = document.getElementById('edit-tag-form');
        const editSubmitBtn = document.getElementById('editSubmitBtn');

        if (editForm) {
            editForm.addEventListener('submit', function (e) {
                editSubmitBtn.disabled = true;
                editSubmitBtn.textContent = 'Updating...';
            });
        }


        // Filter functions
        function filterByDepartment() {
            const departmentId = document.getElementById('departmentFilter').value;
            const statusId = document.getElementById('statusFilter').value;
            applyFilters(departmentId, statusId);
        }

        function filterByStatus() {
            const departmentId = document.getElementById('departmentFilter').value;
            const statusId = document.getElementById('statusFilter').value;
            applyFilters(departmentId, statusId);
        }

        function applyFilters(departmentId, statusId) {
            const params = new URLSearchParams(window.location.search);
            
            if (departmentId) {
                params.set('department', departmentId);
            } else {
                params.delete('department');
            }
            
            if (statusId !== '') {
                params.set('status', statusId);
            } else {
                params.delete('status');
            }
            
            window.location.search = params.toString();
        }
    </script>
@endsection