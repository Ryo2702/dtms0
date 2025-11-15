@extends('layouts.app')

@section('content')
        <div @class(['p-4', 'sm:p-6'])>
        <div @class(['flex', 'items-center', 'justify-between', 'mb-6'])>
            <h1 @class(['text-2xl', 'font-bold', 'text-gray-900'])>Department Management</h1>
            <button type="button" 
                    @class(['inline-flex', 'items-center', 'gap-2', 'px-4', 'py-2', 'bg-blue-600', 'text-white', 'rounded-lg', 'hover:bg-blue-700', 'focus:outline-none', 'focus:ring-2', 'focus:ring-blue-500'])
                    onclick="departmentModal.showModal()">
                <svg @class(['w-5', 'h-5']) fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Add Department
            </button>
        </div>

        {{-- Success/Error Messages --}}
        @if ($message = Session::get('success'))
            <div @class(['mb-4', 'p-4', 'bg-green-50', 'border', 'border-green-200', 'rounded-lg', 'text-green-800'])>
                {{ $message }}
            </div>
        @endif

        @if ($message = Session::get('error'))
            <div @class(['mb-4', 'p-4', 'bg-red-50', 'border', 'border-red-200', 'rounded-lg', 'text-red-800'])>
                {{ $message }}
            </div>
        @endif

        {{-- Departments Table --}}
        <x-data-table 
            :headers="['Logo', 'Name', 'Code', 'Members', 'Status', 'Actions']"
            :paginator="$departments"
            :sortableFields="['name', 'code', 'status']"
            emptyMessage="No departments found.">
            @foreach($departments as $department)
                <tr @class(['hover:bg-gray-50'])>
                    <td @class(['px-6', 'py-4', 'whitespace-nowrap'])>
                        @if($department->getLogoUrl())
                            <img src="{{ $department->getLogoUrl() }}" alt="{{ $department->name }}" @class(['h-10', 'w-10', 'rounded-full', 'object-cover'])>
                        @else
                            <div @class(['h-10', 'w-10', 'rounded-full', 'bg-gray-200', 'flex', 'items-center', 'justify-center'])>
                                <svg @class(['w-6', 'h-6', 'text-gray-400']) fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z" />
                                </svg>
                            </div>
                        @endif
                    </td>
                    <td @class(['px-6', 'py-4', 'whitespace-nowrap'])>
                        <div @class(['text-sm', 'font-medium', 'text-gray-900'])>{{ $department->name }}</div>
                        <div @class(['text-sm', 'text-gray-500'])>{{ Str::limit($department->description, 50) }}</div>
                    </td>
                    <td @class(['px-6', 'py-4', 'whitespace-nowrap'])>
                        <span @class(['inline-flex', 'items-center', 'px-3', 'py-1', 'rounded-full', 'text-sm', 'font-medium', 'bg-blue-100', 'text-blue-800'])>
                            {{ $department->code }}
                        </span>
                    </td>
                    <td @class(['px-6', 'py-4', 'whitespace-nowrap', 'text-sm', 'text-gray-500'])>
                        {{ $department->getActiveUsersCount() }} / {{ $department->getTotalUsersCount() }}
                    </td>
                    <td @class(['px-6', 'py-4', 'whitespace-nowrap'])>
                        @if($department->status)
                            <span @class(['inline-flex', 'items-center', 'px-3', 'py-1', 'rounded-full', 'text-xs', 'font-medium', 'bg-green-100', 'text-green-800'])>
                                Active
                            </span>
                        @else
                            <span @class(['inline-flex', 'items-center', 'px-3', 'py-1', 'rounded-full', 'text-xs', 'font-medium', 'bg-gray-100', 'text-gray-800'])>
                                Inactive
                            </span>
                        @endif
                    </td>
                    <td @class(['px-6', 'py-4', 'whitespace-nowrap', 'text-sm', 'font-medium', 'space-x-3'])>
                        <button onclick="viewDepartmentDetails({{ $department->id }})" @class(['text-blue-600', 'hover:text-blue-900'])>
                            Details
                        </button>
                        <button onclick="editDepartment({{ $department->id }})" @class(['text-indigo-600', 'hover:text-indigo-900'])>
                            Edit
                        </button>
                        <button onclick="manageDepartmentUsers({{ $department->id}})" @class(['text-indigo-600', 'hover:text-indigo-900'])>
                            Manage Users
                        </button>
                    </td>
                </tr>
            @endforeach
        </x-data-table>
    </div>


    {{-- Create Department Modal --}}
    <x-modal id="departmentModal" title="Create Department" size="lg">
        <form id="departmentForm" action="{{ route('admin.departments.store') }}" method="POST" enctype="multipart/form-data" @class(['space-y-4'])>
            @csrf

            {{-- Name Field --}}
            <div>
                <label for="name" @class(['block', 'text-sm', 'font-medium', 'text-gray-700', 'mb-1'])>
                    Department Name
                </label>
                <input type="text" 
                       id="name" 
                       name="title" 
                       required
                       @class(['w-full', 'px-3', 'py-2', 'border', 'border-gray-300', 'rounded-lg', 'focus:outline-none', 'focus:ring-2', 'focus:ring-blue-500'])
                       placeholder="e.g., Human Resources">
                @error('title')
                    <p @class(['text-red-500', 'text-sm', 'mt-1'])>{{ $message }}</p>
                @enderror
            </div>
            
            {{-- Description Field --}}
            <div>
                <label for="description" @class(['block', 'text-sm', 'font-medium', 'text-gray-700', 'mb-1'])>
                    Description
                </label>
                <textarea id="description" 
                          name="description" 
                          rows="3"
                          @class(['w-full', 'px-3', 'py-2', 'border', 'border-gray-300', 'rounded-lg', 'focus:outline-none', 'focus:ring-2', 'focus:ring-blue-500'])
                          placeholder="Enter department description"></textarea>
                @error('description')
                    <p @class(['text-red-500', 'text-sm', 'mt-1'])>{{ $message }}</p>
                @enderror
            </div>

            {{-- Logo Field --}}
            <div>
                <label for="logo" @class(['block', 'text-sm', 'font-medium', 'text-gray-700', 'mb-1'])>
                    Department Logo
                </label>
                <input type="file" 
                       id="logo" 
                       name="logo" 
                       accept="image/*"
                       @class(['w-full', 'px-3', 'py-2', 'border', 'border-gray-300', 'rounded-lg', 'focus:outline-none', 'focus:ring-2', 'focus:ring-blue-500'])>
                <p @class(['text-gray-500', 'text-xs', 'mt-1'])>Accepted formats: JPG, PNG, GIF (Max 2MB)</p>
                @error('logo')
                    <p @class(['text-red-500', 'text-sm', 'mt-1'])>{{ $message }}</p>
                @enderror
            </div>

            {{-- Status Field --}}
            <div @class(['flex', 'items-center'])>
                <input type="checkbox" 
                       id="status" 
                       name="status" 
                       value="1"
                       checked
                       @class(['h-4', 'w-4', 'text-blue-600', 'focus:ring-blue-500', 'border-gray-300', 'rounded'])>
                <label for="status" @class(['ml-2', 'block', 'text-sm', 'text-gray-700'])>
                    Active
                </label>
            </div>
        </form>

        @slot('actions')
            <button type="button" 
                    @class(['px-4', 'py-2', 'text-gray-700', 'border', 'border-gray-300', 'rounded-lg', 'hover:bg-gray-50'])
                    onclick="departmentModal.close()">
                Cancel
            </button>
            <button type="submit" 
                    form="departmentForm"
                    @class(['px-4', 'py-2', 'bg-blue-600', 'text-white', 'rounded-lg', 'hover:bg-blue-700'])
                    id="submitBtn">
                Create Department
            </button>
        @endslot
    </x-modal>

    <x-modal id="editDepartmentModal" title="Edit Department" size="lg">
        <form id="editDepartmentForm" method="POST" enctype="multipart/form-data" @class(['space-y-4'])>
            @csrf
            @method('PUT')
            <input type="hidden" id="edit_department_id" name="department_id">

            {{-- Name Field --}}
            <div>
                <label for="edit_name" @class(['block', 'text-sm', 'font-medium', 'text-gray-700', 'mb-1'])>
                    Department Name
                </label>
                <input type="text" 
                       id="edit_name" 
                       name="title" 
                       required
                       @class(['w-full', 'px-3', 'py-2', 'border', 'border-gray-300', 'rounded-lg', 'focus:outline-none', 'focus:ring-2', 'focus:ring-blue-500'])
                       placeholder="e.g., Human Resources">
            </div>

            {{-- Description Field --}}
            <div>
                <label for="edit_description" @class(['block', 'text-sm', 'font-medium', 'text-gray-700', 'mb-1'])>
                    Description
                </label>
                <textarea id="edit_description" 
                          name="description" 
                          rows="3"
                          @class(['w-full', 'px-3', 'py-2', 'border', 'border-gray-300', 'rounded-lg', 'focus:outline-none', 'focus:ring-2', 'focus:ring-blue-500'])
                          placeholder="Enter department description"></textarea>
            </div>

            {{-- Logo Field --}}
            <div>
                <label for="edit_logo" @class(['block', 'text-sm', 'font-medium', 'text-gray-700', 'mb-1'])>
                    Department Logo
                </label>
                <input type="file" 
                       id="edit_logo" 
                       name="logo" 
                       accept="image/*"
                       @class(['w-full', 'px-3', 'py-2', 'border', 'border-gray-300', 'rounded-lg', 'focus:outline-none', 'focus:ring-2', 'focus:ring-blue-500'])>
                <p @class(['text-gray-500', 'text-xs', 'mt-1'])>Leave empty to keep current logo. Accepted formats: JPG, PNG, GIF (Max 2MB)</p>
            </div>

            {{-- Status Field --}}
            <div @class(['flex', 'items-center'])>
                <input type="checkbox" 
                       id="edit_status" 
                       name="status" 
                       value="1"
                       @class(['h-4', 'w-4', 'text-blue-600', 'focus:ring-blue-500', 'border-gray-300', 'rounded'])>
                <label for="edit_status" @class(['ml-2', 'block', 'text-sm', 'text-gray-700'])>
                    Active
                </label>
            </div>
        </form>

        @slot('actions')
            <button type="button" 
                    @class(['px-4', 'py-2', 'text-gray-700', 'border', 'border-gray-300', 'rounded-lg', 'hover:bg-gray-50'])
                    onclick="editDepartmentModal.close()">
                Cancel
            </button>
            <button type="submit" 
                    form="editDepartmentForm"
                    @class(['px-4', 'py-2', 'bg-blue-600', 'text-white', 'rounded-lg', 'hover:bg-blue-700'])
                    id="editSubmitBtn">
                Update Department
            </button>
        @endslot
    </x-modal>

      {{-- Details Department Modal --}}
    <x-modal id="detailsDepartmentModal" title="Department Details" size="lg">
        <div @class(['space-y-6'])>
            {{-- Logo Section --}}
            <div @class(['flex', 'justify-center'])>
                <div id="details_logo_container" @class(['h-24', 'w-24', 'rounded-full', 'bg-gray-200', 'flex', 'items-center', 'justify-center', 'overflow-hidden'])>
                    <img id="details_logo" src="" alt="Department Logo" @class(['h-full', 'w-full', 'object-cover', 'hidden'])>
                    <svg id="details_logo_placeholder" @class(['w-12', 'h-12', 'text-gray-400']) fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z" />
                    </svg>
                </div>
            </div>

            {{-- Department Information --}}
            <div @class(['grid', 'grid-cols-1', 'md:grid-cols-2', 'gap-4'])>
                <div>
                    <label @class(['block', 'text-sm', 'font-medium', 'text-gray-500', 'mb-1'])>Department Name</label>
                    <p id="details_name" @class(['text-base', 'font-semibold', 'text-gray-900'])></p>
                </div>

                <div>
                    <label @class(['block', 'text-sm', 'font-medium', 'text-gray-500', 'mb-1'])>Department Code</label>
                    <span id="details_code" @class(['inline-flex', 'items-center', 'px-3', 'py-1', 'rounded-full', 'text-sm', 'font-medium', 'bg-blue-100', 'text-blue-800'])></span>
                </div>

                <div @class(['md:col-span-2'])>
                    <label @class(['block', 'text-sm', 'font-medium', 'text-gray-500', 'mb-1'])>Description</label>
                    <p id="details_description" @class(['text-sm', 'text-gray-700'])></p>
                </div>

                <div>
                    <label @class(['block', 'text-sm', 'font-medium', 'text-gray-500', 'mb-1'])>Status</label>
                    <span id="details_status"></span>
                </div>

                <div>
                    <label @class(['block', 'text-sm', 'font-medium', 'text-gray-500', 'mb-1'])>Members</label>
                    <p id="details_members" @class(['text-sm', 'text-gray-700'])></p>
                </div>

                <div>
                    <label @class(['block', 'text-sm', 'font-medium', 'text-gray-500', 'mb-1'])>Created At</label>
                    <p id="details_created_at" @class(['text-sm', 'text-gray-700'])></p>
                </div>

                <div>
                    <label @class(['block', 'text-sm', 'font-medium', 'text-gray-500', 'mb-1'])>Last Updated</label>
                    <p id="details_updated_at" @class(['text-sm', 'text-gray-700'])></p>
                </div>
            </div>
        </div>

        @slot('actions')
            <button type="button" 
                    @class(['px-4', 'py-2', 'text-gray-700', 'border', 'border-gray-300', 'rounded-lg', 'hover:bg-gray-50'])
                    onclick="detailsDepartmentModal.close()">
                Close
            </button>
            <button type="button" 
                    id="details_edit_btn"
                    @class(['px-4', 'py-2', 'bg-blue-600', 'text-white', 'rounded-lg', 'hover:bg-blue-700'])>
                Edit Department
            </button>
        @endslot
    </x-modal>

     <x-modal id="manageUsersModal" title="Manage Department Users" size="xl">
        <div @class(['space-y-4'])>
            {{-- Department Info --}}
            <div @class(['bg-gray-50', 'p-4', 'rounded-lg'])>
                <h3 @class(['text-lg', 'font-semibold', 'text-gray-900'])>
                    <span id="manage_dept_name"></span>
                    <span id="manage_dept_code" @class(['ml-2', 'inline-flex', 'items-center', 'px-2', 'py-1', 'rounded-full', 'text-xs', 'font-medium', 'bg-blue-100', 'text-blue-800'])></span>
                </h3>
            </div>

            {{-- Assign User Section --}}
            <div @class(['border-b', 'pb-4'])>
                <h4 @class(['text-md', 'font-medium', 'text-gray-900', 'mb-3'])>Assign New User</h4>
                <form id="assignUserForm" method="POST" @class(['flex', 'gap-2'])>
                    @csrf
                    <select id="assign_user_id" name="user_id" @class(['flex-1', 'px-3', 'py-2', 'border', 'border-gray-300', 'rounded-lg', 'focus:outline-none', 'focus:ring-2', 'focus:ring-blue-500'])>
                        <option value="">Select a user...</option>
                    </select>
                    <button type="submit" @class(['px-4', 'py-2', 'bg-blue-600', 'text-white', 'rounded-lg', 'hover:bg-blue-700', 'whitespace-nowrap'])>
                        Assign User
                    </button>
                </form>
            </div>

            {{-- Current Users List --}}
            <div>
                <h4 @class(['text-md', 'font-medium', 'text-gray-900', 'mb-3'])>Current Members</h4>
                <div id="current_users_list" @class(['space-y-2', 'max-h-96', 'overflow-y-auto'])>
                    {{-- Users will be loaded here --}}
                </div>
            </div>
        </div>

        @slot('actions')
            <button type="button" 
                    @class(['px-4', 'py-2', 'text-gray-700', 'border', 'border-gray-300', 'rounded-lg', 'hover:bg-gray-50'])
                    onclick="manageUsersModal.close()">
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
                    document.getElementById('details_description').textContent = data.description || 'No description available';

                    // Set status
                    const statusElement = document.getElementById('details_status');
                    if (data.status == 1) {
                        statusElement.innerHTML = '<span @class(['inline-flex', 'items-center', 'px-3', 'py-1', 'rounded-full', 'text-xs', 'font-medium', 'bg-green-100', 'text-green-800'])>Active</span>';
                    } else {
                        statusElement.innerHTML = '<span @class(['inline-flex', 'items-center', 'px-3', 'py-1', 'rounded-full', 'text-xs', 'font-medium', 'bg-gray-100', 'text-gray-800'])>Inactive</span>';
                    }

                    // Set members count
                    const activeUsers = data.active_users_count || 0;
                    const totalUsers = data.total_users_count || 0;
                    document.getElementById('details_members').textContent = `${activeUsers} active / ${totalUsers} total`;

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
                    // Set department info
                    document.getElementById('manage_dept_name').textContent = data.department.name;
                    document.getElementById('manage_dept_code').textContent = data.department.code;

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
                        usersList.innerHTML = '<p @class(['text-gray-500', 'text-center', 'py-4'])>No users assigned yet.</p>';
                    } else {
                        data.users.data.forEach(user => {
                            const userDiv = document.createElement('div');
                            userDiv.className = 'flex items-center justify-between p-3 bg-gray-50 rounded-lg hover:bg-gray-100';
                            userDiv.innerHTML = `
                                <div class="flex items-center gap-3">
                                    <div class="h-10 w-10 rounded-full bg-gray-300 flex items-center justify-center text-gray-700 font-semibold">
                                        ${user.name.charAt(0).toUpperCase()}
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
                                            class="px-3 py-1 text-red-600 hover:text-red-800 hover:bg-red-50 rounded-lg transition-colors">
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
