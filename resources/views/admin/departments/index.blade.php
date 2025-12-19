@extends('layouts.app')

@section('content')
    <div class="p-4 sm:p-6">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold text-gray-900">Department Management</h1>
            <button type="button" class="btn btn-primary gap-2" onclick="departmentModal.showModal()">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Add Department
            </button>
        </div>

        {{-- Departments Table --}}
        <x-data-table :headers="['Logo', 'Name', 'Code', 'Documents','Members', 'Status', 'Actions']" :paginator="$departments" :sortableFields="['name', 'code', 'status']" emptyMessage="No departments found.">
            @foreach ($departments as $department)
                <tr class="hover">
                    <td class="px-6 py-4 whitespace-nowrap">
                        @if ($department->getLogoUrl())
                            <img src="{{ $department->getLogoUrl() }}" alt="{{ $department->name }}"
                                class="h-10 w-10 rounded-full object-cover">
                        @else
                            <div class="h-10 w-10 rounded-full bg-gray-200 flex items-center justify-center">
                                <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z" />
                                </svg>
                            </div>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm font-medium text-gray-900">{{ $department->name }}</div>
                        <div class="text-sm text-gray-500">{{ Str::limit($department->description, 50) }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="badge badge-primary">{{ $department->code }}</span>
                    </td>
                     <td class="px-6 py-4">
                        <div class="flex flex-wrap gap-1 max-w-xs">
                            @forelse ($department->documentTags as $tag)
                                <span class="badge badge-neutral badge-outline badge-sm">{{ $tag->name }}</span>
                            @empty
                                <span class="text-gray-400 text-sm">-</span>
                            @endforelse
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ $department->getActiveUsersCount() }} / {{ $department->getTotalUsersCount() }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        @if ($department->status)
                            <span class="badge badge-success">Active</span>
                        @else
                            <span class="badge">Inactive</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-3">
                        <button onclick="viewDepartmentDetails({{ $department->id }})"
                            class="btn btn-ghost btn-sm btn-link">
                            Details
                        </button>
                        <button onclick="editDepartment({{ $department->id }})" class="btn btn-ghost btn-sm btn-link">
                            Edit
                        </button>
                        <button onclick="manageDepartmentUsers({{ $department->id }})"
                            class="btn btn-ghost btn-sm btn-link">
                            Manage Users
                        </button>
                    </td>
                </tr>
            @endforeach
        </x-data-table>
    </div>

    {{-- Create Department Modal --}}
    <x-modal id="departmentModal" title="Create Department" size="lg">
        <form id="departmentForm" action="{{ route('admin.departments.store') }}" method="POST"
            enctype="multipart/form-data" class="space-y-4">
            @csrf

            {{-- Logo Field --}}
            <div class="form-control">
                <label class="label">
                    <span class="label-text font-medium">Department Logo</span>
                </label>
                <input type="file" id="logo" name="logo" accept="image/*"
                    class="file-input file-input-bordered" />
                <label class="label">
                    <span class="label-text-alt">Accepted formats: JPG, PNG, GIF (Max 2MB)</span>
                </label>
                @error('logo')
                    <label class="label">
                        <span class="label-text-alt text-error">{{ $message }}</span>
                    </label>
                @enderror
            </div>

            {{-- Name Field --}}
            <div class="form-control">
                <label class="label">
                    <span class="label-text font-medium">Description</span>
                </label>
                <input id="name" name="title" placeholder="Enter department description"
                    class="input input-bordered"></input>
                @error('description')
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
                <textarea id="description" name="description" rows="3" placeholder="Enter department description"
                    class="textarea textarea-bordered"></textarea>
                @error('description')
                    <label class="label">
                        <span class="label-text-alt text-error">{{ $message }}</span>
                    </label>
                @enderror
            </div>


            {{-- Status Field --}}
            <div class="form-control">
                <label class="cursor-pointer label">
                    <input type="checkbox" id="status" name="status" value="1" checked
                        class="checkbox checkbox-primary" />
                    <span class="label-text ml-2">Active</span>
                </label>
            </div>

            {{-- Document Tags Field --}}
            @if (isset($availableDocumentTags) && $availableDocumentTags->count() > 0)
                <div class="form-control">
                    <label class="label">
                        <span class="label-text font-medium">Attach Document Tags</span>
                        <span class="label-text-alt text-gray-500">Optional</span>
                    </label>
                    <div class="border rounded-lg p-3 bg-gray-50 max-h-48 overflow-y-auto">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                            @foreach ($availableDocumentTags as $tag)
                                <label
                                    class="flex items-center gap-2 cursor-pointer p-2 bg-white rounded border border-gray-200 hover:border-primary transition-colors">
                                    <input type="checkbox" name="document_tags[]" value="{{ $tag->id }}"
                                        class="checkbox checkbox-sm checkbox-primary">
                                    <span class="text-sm">{{ $tag->name }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                    <label class="label">
                        <span class="label-text-alt">Select document tags to associate with this department</span>
                    </label>
                </div>
            @endif
        </form>

        @slot('actions')
            <button type="button" class="btn btn-ghost" onclick="departmentModal.close()">
                Cancel
            </button>
            <button type="submit" form="departmentForm" class="btn btn-primary" id="submitBtn">
                Create Department
            </button>
        @endslot
    </x-modal>

    {{-- Edit Department Modal --}}
    <x-modal id="editDepartmentModal" title="Edit Department" size="lg">
        <form id="editDepartmentForm" method="POST" enctype="multipart/form-data" class="space-y-4">
            @csrf
            @method('PUT')
            <input type="hidden" id="edit_department_id" name="department_id">

            {{-- Name Field --}}
            <div class="form-control">
                <label class="label">
                    <span class="label-text font-medium">Department Name</span>
                </label>
                <input type="text" id="edit_name" name="title" required placeholder="e.g., Human Resources"
                    class="input input-bordered" />
            </div>

            {{-- Description Field --}}
            <div class="form-control">
                <label class="label">
                    <span class="label-text font-medium">Description</span>
                </label>
                <textarea id="edit_description" name="description" rows="3" placeholder="Enter department description"
                    class="textarea textarea-bordered"></textarea>
            </div>

            {{-- Logo Field --}}
            <div class="form-control">
                <label class="label">
                    <span class="label-text font-medium">Department Logo</span>
                </label>
                <input type="file" id="edit_logo" name="logo" accept="image/*"
                    class="file-input file-input-bordered" />
                <label class="label">
                    <span class="label-text-alt">Leave empty to keep current logo. Accepted formats: JPG, PNG, GIF (Max
                        2MB)</span>
                </label>
            </div>

            {{-- Status Field --}}
            <div class="form-control">
                <label class="cursor-pointer label">
                    <input type="checkbox" id="edit_status" name="status" value="1"
                        class="checkbox checkbox-primary" />
                    <span class="label-text ml-2">Active</span>
                </label>
            </div>

            {{-- Document Tags Field --}}
            <div class="form-control">
                <label class="label">
                    <span class="label-text font-medium">Document Tags</span>
                    <span class="label-text-alt text-gray-500">Manage department tags</span>
                </label>
                <div id="edit_document_tags_container" class="border rounded-lg p-3 bg-gray-50 max-h-48 overflow-y-auto">
                    <p class="text-gray-500 text-sm text-center py-2">Loading tags...</p>
                </div>
                <label class="label">
                    <span class="label-text-alt">Select document tags to associate with this department</span>
                </label>
            </div>
        </form>

        @slot('actions')
            <button type="button" class="btn btn-ghost" onclick="editDepartmentModal.close()">
                Cancel
            </button>
            <button type="submit" form="editDepartmentForm" class="btn btn-primary" id="editSubmitBtn">
                Update Department
            </button>
        @endslot
    </x-modal>

    {{-- Details Department Modal --}}
    <x-modal id="detailsDepartmentModal" title="Department Details" size="lg">
        <div class="space-y-6">
            {{-- Logo Section --}}
            <div class="flex justify-center">
                <div id="details_logo_container"
                    class="h-24 w-24 rounded-full bg-gray-200 flex items-center justify-center overflow-hidden">
                    <img id="details_logo" src="" alt="Department Logo"
                        class="h-full w-full object-cover hidden">
                    <svg id="details_logo_placeholder" class="w-12 h-12 text-gray-400" fill="none"
                        stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z" />
                    </svg>
                </div>
            </div>

            {{-- Department Information --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Department Name</label>
                    <p id="details_name" class="text-base font-semibold text-gray-900"></p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Department Code</label>
                    <span id="details_code" class="badge badge-primary"></span>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-500 mb-1">Description</label>
                    <p id="details_description" class="text-sm text-gray-700"></p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Status</label>
                    <span id="details_status"></span>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Members</label>
                    <p id="details_members" class="text-sm text-gray-700"></p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Created At</label>
                    <p id="details_created_at" class="text-sm text-gray-700"></p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Last Updated</label>
                    <p id="details_updated_at" class="text-sm text-gray-700"></p>
                </div>
            </div>
        </div>

        @slot('actions')
            <button type="button" class="btn btn-ghost" onclick="detailsDepartmentModal.close()">
                Close
            </button>
            <button type="button" id="details_edit_btn" class="btn btn-primary">
                Edit Department
            </button>
        @endslot
    </x-modal>

    {{-- Manage Users Modal --}}
    <x-modal id="manageUsersModal" title="Manage Department Users" size="xl">
        <div class="space-y-4">
            {{-- Department Info --}}
            <div class="alert alert-info">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                    class="stroke-current shrink-0 w-6 h-6">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <span id="manage_dept_info"></span>
            </div>

            {{-- Assign User Section --}}
            <div class="divider"></div>

            <div>
                <h4 class="text-md font-medium text-gray-900 mb-3">Assign New User</h4>
                <form id="assignUserForm" method="POST" class="flex gap-2">
                    @csrf
                    <select id="assign_user_id" name="user_id" class="select select-bordered flex-1">
                        <option value="">Select a user...</option>
                    </select>
                    <button type="submit" class="btn btn-primary">
                        Assign User
                    </button>
                </form>
            </div>

            {{-- Current Users List --}}
            <div class="divider"></div>

            <div>
                <h4 class="text-md font-medium text-gray-900 mb-3">Current Members</h4>
                <div id="current_users_list" class="space-y-2 max-h-96 overflow-y-auto">
                    {{-- Users will be loaded here --}}
                </div>
            </div>
        </div>

        @slot('actions')
            <button type="button" class="btn btn-ghost" onclick="manageUsersModal.close()">
                Close
            </button>
        @endslot
    </x-modal>

    <script>
        // Handle form submission
        const form = document.getElementById('departmentForm');
        const submitBtn = document.getElementById('submitBtn');

        if (form) {
            form.addEventListener('submit', function(e) {
                submitBtn.disabled = true;
                submitBtn.textContent = 'Creating...';
            });
        }

        // Reset form when modal is closed
        const modalElement = document.getElementById('departmentModal');
        if (modalElement) {
            const originalShowModal = departmentModal.showModal;
            departmentModal.showModal = function() {
                document.getElementById('departmentForm').reset();
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Create Department';
                }
                originalShowModal.call(this);
            };
        }

        function editDepartment(id) {
            fetch(`/admin/departments/${id}/edit`)
                .then(response => response.json())
                .then(data => {
                    document.getElementById('edit_department_id').value = data.id;
                    document.getElementById('edit_name').value = data.name;
                    document.getElementById('edit_description').value = data.description || '';
                    document.getElementById('edit_status').checked = data.status == 1;

                    // Populate document tags
                    const tagsContainer = document.getElementById('edit_document_tags_container');
                    const allTags = [...(data.document_tags || []), ...(data.available_tags || [])];
                    const currentTagIds = (data.document_tags || []).map(t => t.id);

                    if (allTags.length > 0) {
                        let tagsHtml = '<div class="grid grid-cols-1 sm:grid-cols-2 gap-2">';
                        allTags.forEach(tag => {
                            const isChecked = currentTagIds.includes(tag.id) ? 'checked' : '';
                            tagsHtml += `
                                <label class="flex items-center gap-2 cursor-pointer p-2 bg-white rounded border border-gray-200 hover:border-primary transition-colors">
                                    <input type="checkbox" name="document_tags[]" value="${tag.id}" class="checkbox checkbox-sm checkbox-primary" ${isChecked}>
                                    <span class="text-sm">${tag.name}</span>
                                </label>
                            `;
                        });
                        tagsHtml += '</div>';
                        tagsContainer.innerHTML = tagsHtml;
                    } else {
                        tagsContainer.innerHTML =
                            '<p class="text-gray-500 text-sm text-center py-2">No document tags available</p>';
                    }

                    // Update form action
                    document.getElementById('editDepartmentForm').action = `/admin/departments/${id}`;

                    editDepartmentModal.showModal();
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Failed to load department data');
                });
        }

        function viewDepartmentDetails(id) {
            fetch(`/admin/departments/${id}/edit`)
                .then(response => response.json())
                .then(data => {
                    // Set logo
                    const logoImg = document.getElementById('details_logo');
                    const logoPlaceholder = document.getElementById('details_logo_placeholder');

                    if (data.logo_url) {
                        logoImg.src = data.logo_url;
                        logoImg.classList.remove('hidden');
                        logoPlaceholder.classList.add('hidden');
                    } else {
                        logoImg.classList.add('hidden');
                        logoPlaceholder.classList.remove('hidden');
                    }

                    // Set basic information
                    document.getElementById('details_name').textContent = data.name;
                    document.getElementById('details_code').textContent = data.code;
                    document.getElementById('details_description').textContent = data.description ||
                        'No description available';

                    // Set status
                    const statusElement = document.getElementById('details_status');
                    if (data.status == 1) {
                        statusElement.innerHTML = '<span class="badge badge-success">Active</span>';
                    } else {
                        statusElement.innerHTML = '<span class="badge">Inactive</span>';
                    }

                    // Set members count
                    const activeUsers = data.active_users_count || 0;
                    const totalUsers = data.total_users_count || 0;
                    document.getElementById('details_members').textContent =
                        `${activeUsers} active / ${totalUsers} total`;

                    // Set dates
                    document.getElementById('details_created_at').textContent = formatDate(data.created_at);
                    document.getElementById('details_updated_at').textContent = formatDate(data.updated_at);

                    // Set edit button action
                    document.getElementById('details_edit_btn').onclick = function() {
                        detailsDepartmentModal.close();
                        editDepartment(id);
                    };

                    detailsDepartmentModal.showModal();
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Failed to load department details');
                });
        }

        function formatDate(dateString) {
            const date = new Date(dateString);
            return date.toLocaleDateString('en-US', {
                year: 'numeric',
                month: 'long',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        }

        function manageDepartmentUsers(id) {
            fetch(`/admin/departments/${id}/users`)
                .then(response => response.json())
                .then(data => {
                    document.getElementById('manage_dept_info').textContent =
                        `${data.department.name} (${data.department.code})`;

                    // Populate available users dropdown
                    const userSelect = document.getElementById('assign_user_id');
                    userSelect.innerHTML = '<option value="">Select a user...</option>';
                    data.available_users.forEach(user => {
                        const option = document.createElement('option');
                        option.value = user.id;
                        option.textContent = `${user.name} (${user.email})`;
                        userSelect.appendChild(option);
                    });

                    // Set form action
                    document.getElementById('assignUserForm').action = `/admin/departments/${id}/assign-user`;

                    // Display current users
                    const usersList = document.getElementById('current_users_list');
                    usersList.innerHTML = '';

                    if (data.users.data.length === 0) {
                        usersList.innerHTML = '<p class="text-gray-500 text-center py-4">No users assigned yet.</p>';
                    } else {
                        data.users.data.forEach(user => {
                            const userDiv = document.createElement('div');
                            userDiv.className = 'flex items-center justify-between p-3 bg-base-200 rounded-lg';
                            userDiv.innerHTML = `
                                            <div class="flex items-center gap-3">
                                                <div class="avatar placeholder">
                                                    <div class="bg-primary text-primary-content rounded-full w-10">
                                                        <span>${user.name.charAt(0).toUpperCase()}</span>
                                                    </div>
                                                </div>
                                                <div>
                                                    <p class="font-medium text-gray-900">${user.name}</p>
                                                    <p class="text-sm text-gray-500">${user.email}</p>
                                                </div>
                                            </div>
                                            <form method="POST" action="/admin/departments/${id}/remove-user" class="inline">
                                                <input type="hidden" name="_token" value="${document.querySelector('meta[name="csrf-token"]').content}">
                                                <input type="hidden" name="_method" value="DELETE">
                                                <input type="hidden" name="user_id" value="${user.id}">
                                                <button type="submit" 
                                                        onclick="return confirm('Remove this user from the department?')"
                                                        class="btn btn-error btn-sm">
                                                    Remove
                                                </button>
                                            </form>
                                        `;
                            usersList.appendChild(userDiv);
                        });
                    }

                    manageUsersModal.showModal();
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Failed to load department users');
                });
        }

        // Handle assign user form submission
        const assignUserForm = document.getElementById('assignUserForm');
        if (assignUserForm) {
            assignUserForm.addEventListener('submit', function(e) {
                const userId = document.getElementById('assign_user_id').value;
                if (!userId) {
                    e.preventDefault();
                    alert('Please select a user to assign');
                }
            });
        }
    </script>

@endsection
