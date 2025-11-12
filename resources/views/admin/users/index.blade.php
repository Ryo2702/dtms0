@extends('layouts.app')

@section('content')
    <div @class(['p-4', 'sm:p-6'])>
        <div @class(['flex', 'items-center', 'justify-between', 'mb-6'])>
            <h1 @class(['text-2xl', 'font-bold', 'text-gray-900'])>User Management</h1>
            <button type="button" @class([
                'inline-flex',
                'items-center',
                'gap-2',
                'px-4',
                'py-2',
                'bg-blue-600',
                'text-white',
                'rounded-lg',
                'hover:bg-blue-700',
                'focus:outline-none',
                'focus:ring-2',
                'focus:ring-blue-500',
            ]) onclick="userModal.showModal()">
                <svg @class(['w-5', 'h-5']) fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Add User
            </button>
        </div>

        {{-- Users Table --}}
        <x-data-table :headers="['Avatar', 'Employee ID', 'Name', 'Email', 'Department', 'Type', 'Online', 'Status', 'Actions']" :paginator="$users" :sortableFields="['name', 'email', 'type', 'status']" emptyMessage="No users found.">
            @foreach ($users as $user)
                <tr @class(['hover:bg-gray-50'])>
                    <td @class(['px-6', 'py-4', 'whitespace-nowrap'])>
                        <div @class(['relative', 'h-10', 'w-10'])>
                            @if ($user->avatar)
                                <img src="{{ Storage::url($user->avatar) }}" alt="{{ $user->name }}"
                                    @class(['h-10', 'w-10', 'rounded-full', 'object-cover'])>
                            @else
                                <div @class([
                                    'h-10',
                                    'w-10',
                                    'rounded-full',
                                    'bg-gradient-to-br',
                                    'from-blue-400',
                                    'to-blue-600',
                                    'flex',
                                    'items-center',
                                    'justify-center',
                                    'text-white',
                                    'font-bold',
                                ])>
                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                </div>
                            @endif
                            @if ($user->isOnline())
                                <span @class([
                                    'absolute',
                                    'bottom-0',
                                    'right-0',
                                    'h-3',
                                    'w-3',
                                    'bg-green-400',
                                    'rounded-full',
                                    'border-2',
                                    'border-white',
                                ])></span>
                            @endif
                        </div>
                    </td>
                    <td @class(['px-6', 'py-4', 'whitespace-nowrap'])>
                        <span @class([
                            'inline-flex',
                            'items-center',
                            'px-3',
                            'py-1',
                            'rounded-full',
                            'text-sm',
                            'font-mono',
                            'font-medium',
                            'bg-gray-100',
                            'text-gray-800',
                        ])>
                            {{ $user->employee_id }}
                        </span>
                    </td>
                    <td @class(['px-6', 'py-4', 'whitespace-nowrap'])>
                        <div @class(['text-sm', 'font-medium', 'text-gray-900'])>{{ $user->name }}</div>
                    </td>
                    <td @class(['px-6', 'py-4', 'whitespace-nowrap'])>
                        <div @class(['text-sm', 'text-gray-500'])>{{ $user->email }}</div>
                    </td>
                    <td @class(['px-6', 'py-4', 'whitespace-nowrap'])>
                        @if ($user->department)
                            <span @class([
                                'inline-flex',
                                'items-center',
                                'px-3',
                                'py-1',
                                'rounded-full',
                                'text-sm',
                                'font-medium',
                                'bg-purple-100',
                                'text-purple-800',
                            ])>
                                {{ $user->department->code }}
                            </span>
                        @else
                            <span @class([
                                'inline-flex',
                                'items-center',
                                'px-3',
                                'py-1',
                                'rounded-full',
                                'text-sm',
                                'font-medium',
                                'bg-gray-100',
                                'text-gray-500',
                            ])>
                                UKN
                            </span>
                        @endif
                    </td>
                    <td @class(['px-6', 'py-4', 'whitespace-nowrap'])>

                        <span @class([
                            'inline-flex',
                            'items-center',
                            'px-3',
                            'py-1',
                            'rounded-full',
                            'text-sm',
                            'font-medium',
                            'bg-blue-100',
                            'text-blue-800',
                        ])>
                            Head
                        </span>

                    </td>
                    <td class="px-4 py-3">
                        @if ($user->last_activity && \Carbon\Carbon::parse($user->last_activity)->gt(now()->subMinutes(5)))
                            <span class="badge badge-success">Online</span>
                        @else
                            <span class="badge badge-ghost">Offline</span>
                        @endif
                    </td>
                    <td @class(['px-6', 'py-4', 'whitespace-nowrap'])>
                        @if ($user->status)
                            <span @class([
                                'inline-flex',
                                'items-center',
                                'px-3',
                                'py-1',
                                'rounded-full',
                                'text-xs',
                                'font-medium',
                                'bg-green-100',
                                'text-green-800',
                            ])>
                                Active
                            </span>
                        @else
                            <span @class([
                                'inline-flex',
                                'items-center',
                                'px-3',
                                'py-1',
                                'rounded-full',
                                'text-xs',
                                'font-medium',
                                'bg-gray-100',
                                'text-gray-800',
                            ])>
                                Inactive
                            </span>
                        @endif
                    </td>
                    <td @class([
                        'px-6',
                        'py-4',
                        'whitespace-nowrap',
                        'text-sm',
                        'font-medium',
                        'space-x-3',
                    ])>
                        <button onclick="viewUserDetails({{ $user->id }})" @class(['text-blue-600', 'hover:text-blue-900'])>
                            Details
                        </button>
                        <button onclick="editUser({{ $user->id }})" @class(['text-indigo-600', 'hover:text-indigo-900'])>
                            Edit
                        </button>
                    </td>
                </tr>
            @endforeach
        </x-data-table>
    </div>

    {{-- Create User Modal --}}
    <x-modal id="userModal" title="Create User" size="lg">
        <form id="userForm" action="{{ route('admin.users.store') }}" method="POST" enctype="multipart/form-data"
            @class(['space-y-4'])>
            @csrf

            {{-- Name Field --}}
            <div>
                <label for="name" @class(['block', 'text-sm', 'font-medium', 'text-gray-700', 'mb-1'])>
                    Full Name
                </label>
                <input type="text" id="name" name="name" required @class([
                    'w-full',
                    'px-3',
                    'py-2',
                    'border',
                    'border-gray-300',
                    'rounded-lg',
                    'focus:outline-none',
                    'focus:ring-2',
                    'focus:ring-blue-500',
                ])
                    placeholder="e.g., John Doe">
                @error('name')
                    <p @class(['text-red-500', 'text-sm', 'mt-1'])>{{ $message }}</p>
                @enderror
            </div>

            {{-- Email Field --}}
            <div>
                <label for="email" @class(['block', 'text-sm', 'font-medium', 'text-gray-700', 'mb-1'])>
                    Email
                </label>
                <input type="email" id="email" name="email" required @class([
                    'w-full',
                    'px-3',
                    'py-2',
                    'border',
                    'border-gray-300',
                    'rounded-lg',
                    'focus:outline-none',
                    'focus:ring-2',
                    'focus:ring-blue-500',
                ])
                    placeholder="e.g., john@example.com">
                @error('email')
                    <p @class(['text-red-500', 'text-sm', 'mt-1'])>{{ $message }}</p>
                @enderror
            </div>

            {{-- Department Field --}}
            <div>
                <label for="department_id" @class(['block', 'text-sm', 'font-medium', 'text-gray-700', 'mb-1'])>
                    Department
                </label>
                <select id="department_id" name="department_id" @class([
                    'w-full',
                    'px-3',
                    'py-2',
                    'border',
                    'border-gray-300',
                    'rounded-lg',
                    'focus:outline-none',
                    'focus:ring-2',
                    'focus:ring-blue-500',
                ])>
                    <option value="">Select a department</option>
                    @foreach ($availableDepartments as $dept)
                        <option value="{{ $dept->id }}">{{ $dept->name }} ({{ $dept->code }})</option>
                    @endforeach
                </select>
                <p @class(['text-gray-500', 'text-xs', 'mt-1'])>Only departments without assigned users are shown</p>
                @error('department_id')
                    <p @class(['text-red-500', 'text-sm', 'mt-1'])>{{ $message }}</p>
                @enderror
            </div>

            {{-- Password Field --}}
            <div>
                <label for="password" @class(['block', 'text-sm', 'font-medium', 'text-gray-700', 'mb-1'])>
                    Password
                </label>
                <input type="password" id="password" name="password" required @class([
                    'w-full',
                    'px-3',
                    'py-2',
                    'border',
                    'border-gray-300',
                    'rounded-lg',
                    'focus:outline-none',
                    'focus:ring-2',
                    'focus:ring-blue-500',
                ])
                    placeholder="Minimum 8 characters">
                @error('password')
                    <p @class(['text-red-500', 'text-sm', 'mt-1'])>{{ $message }}</p>
                @enderror
            </div>

            {{-- Confirm Password Field --}}
            <div>
                <label for="password_confirmation" @class(['block', 'text-sm', 'font-medium', 'text-gray-700', 'mb-1'])>
                    Confirm Password
                </label>
                <input type="password" id="password_confirmation" name="password_confirmation" required
                    @class([
                        'w-full',
                        'px-3',
                        'py-2',
                        'border',
                        'border-gray-300',
                        'rounded-lg',
                        'focus:outline-none',
                        'focus:ring-2',
                        'focus:ring-blue-500',
                    ]) placeholder="Confirm password">
            </div>

            {{-- Avatar Field --}}
            <div>
                <label for="avatar" @class(['block', 'text-sm', 'font-medium', 'text-gray-700', 'mb-1'])>
                    Avatar
                </label>
                <input type="file" id="avatar" name="avatar" accept="image/*" @class([
                    'w-full',
                    'px-3',
                    'py-2',
                    'border',
                    'border-gray-300',
                    'rounded-lg',
                    'focus:outline-none',
                    'focus:ring-2',
                    'focus:ring-blue-500',
                ])>
                <p @class(['text-gray-500', 'text-xs', 'mt-1'])>Accepted formats: JPG, PNG, GIF (Max 2MB)</p>
                @error('avatar')
                    <p @class(['text-red-500', 'text-sm', 'mt-1'])>{{ $message }}</p>
                @enderror
            </div>

            {{-- Status Field --}}
            <div @class(['flex', 'items-center'])>
                <input type="checkbox" id="status" name="status" value="1" checked @class([
                    'h-4',
                    'w-4',
                    'text-blue-600',
                    'focus:ring-blue-500',
                    'border-gray-300',
                    'rounded',
                ])>
                <label for="status" @class(['ml-2', 'block', 'text-sm', 'text-gray-700'])>
                    Active
                </label>
            </div>
        </form>

        @slot('actions')
            <button type="button" @class([
                'px-4',
                'py-2',
                'text-gray-700',
                'border',
                'border-gray-300',
                'rounded-lg',
                'hover:bg-gray-50',
            ]) onclick="userModal.close()">
                Cancel
            </button>
            <button type="submit" form="userForm" @class([
                'px-4',
                'py-2',
                'bg-blue-600',
                'text-white',
                'rounded-lg',
                'hover:bg-blue-700',
            ]) id="submitBtn">
                Create User
            </button>
        @endslot
    </x-modal>

    {{-- Edit User Modal --}}
    <x-modal id="editUserModal" title="Edit User" size="lg">
        <form id="editUserForm" method="POST" enctype="multipart/form-data" @class(['space-y-4'])>
            @csrf
            @method('PUT')
            <input type="hidden" id="edit_user_id" name="user_id">

            {{-- Name Field --}}
            <div>
                <label for="edit_name" @class(['block', 'text-sm', 'font-medium', 'text-gray-700', 'mb-1'])>
                    Full Name
                </label>
                <input type="text" id="edit_name" name="name" required @class([
                    'w-full',
                    'px-3',
                    'py-2',
                    'border',
                    'border-gray-300',
                    'rounded-lg',
                    'focus:outline-none',
                    'focus:ring-2',
                    'focus:ring-blue-500',
                ])
                    placeholder="e.g., John Doe">
            </div>

            {{-- Email Field --}}
            <div>
                <label for="edit_email" @class(['block', 'text-sm', 'font-medium', 'text-gray-700', 'mb-1'])>
                    Email
                </label>
                <input type="email" id="edit_email" name="email" required @class([
                    'w-full',
                    'px-3',
                    'py-2',
                    'border',
                    'border-gray-300',
                    'rounded-lg',
                    'focus:outline-none',
                    'focus:ring-2',
                    'focus:ring-blue-500',
                ])
                    placeholder="e.g., john@example.com">
            </div>

            {{-- Department Field --}}
            <div>
                <label for="edit_department_id" @class(['block', 'text-sm', 'font-medium', 'text-gray-700', 'mb-1'])>
                    Department
                </label>
                <select id="edit_department_id" name="department_id" @class([
                    'w-full',
                    'px-3',
                    'py-2',
                    'border',
                    'border-gray-300',
                    'rounded-lg',
                    'focus:outline-none',
                    'focus:ring-2',
                    'focus:ring-blue-500',
                ])>
                    <option value="">Select a department</option>
                    {{-- Options will be populated dynamically via JavaScript --}}
                </select>
                <p @class(['text-gray-500', 'text-xs', 'mt-1'])>Only available departments are shown</p>
            </div>

            {{-- Password Field --}}
            <div>
                <label for="edit_password" @class(['block', 'text-sm', 'font-medium', 'text-gray-700', 'mb-1'])>
                    Password
                </label>
                <input type="password" id="edit_password" name="password" @class([
                    'w-full',
                    'px-3',
                    'py-2',
                    'border',
                    'border-gray-300',
                    'rounded-lg',
                    'focus:outline-none',
                    'focus:ring-2',
                    'focus:ring-blue-500',
                ])
                    placeholder="Leave empty to keep current password">
                <p @class(['text-gray-500', 'text-xs', 'mt-1'])>Leave empty to keep current password</p>
            </div>

            {{-- Confirm Password Field --}}
            <div>
                <label for="edit_password_confirmation" @class(['block', 'text-sm', 'font-medium', 'text-gray-700', 'mb-1'])>
                    Confirm Password
                </label>
                <input type="password" id="edit_password_confirmation" name="password_confirmation"
                    @class([
                        'w-full',
                        'px-3',
                        'py-2',
                        'border',
                        'border-gray-300',
                        'rounded-lg',
                        'focus:outline-none',
                        'focus:ring-2',
                        'focus:ring-blue-500',
                    ]) placeholder="Confirm password">
            </div>

            {{-- Avatar Field --}}
            <div>
                <label for="edit_avatar" @class(['block', 'text-sm', 'font-medium', 'text-gray-700', 'mb-1'])>
                    Avatar
                </label>
                <input type="file" id="edit_avatar" name="avatar" accept="image/*" @class([
                    'w-full',
                    'px-3',
                    'py-2',
                    'border',
                    'border-gray-300',
                    'rounded-lg',
                    'focus:outline-none',
                    'focus:ring-2',
                    'focus:ring-blue-500',
                ])>
                <p @class(['text-gray-500', 'text-xs', 'mt-1'])>Leave empty to keep current avatar. Accepted formats: JPG, PNG, GIF (Max
                    2MB)</p>
            </div>

            {{-- Status Field --}}
            <div @class(['flex', 'items-center'])>
                <input type="checkbox" id="edit_status" name="status" value="1" @class([
                    'h-4',
                    'w-4',
                    'text-blue-600',
                    'focus:ring-blue-500',
                    'border-gray-300',
                    'rounded',
                ])>
                <label for="edit_status" @class(['ml-2', 'block', 'text-sm', 'text-gray-700'])>
                    Active
                </label>
            </div>
        </form>

        @slot('actions')
            <button type="button" @class([
                'px-4',
                'py-2',
                'text-gray-700',
                'border',
                'border-gray-300',
                'rounded-lg',
                'hover:bg-gray-50',
            ]) onclick="editUserModal.close()">
                Cancel
            </button>
            <button type="submit" form="editUserForm" @class([
                'px-4',
                'py-2',
                'bg-blue-600',
                'text-white',
                'rounded-lg',
                'hover:bg-blue-700',
            ]) id="editSubmitBtn">
                Update User
            </button>
        @endslot
    </x-modal>

    {{-- Details User Modal --}}
    <x-modal id="detailsUserModal" title="User Details" size="lg">
        <div @class(['space-y-6'])>
            {{-- Avatar Section --}}
            <div @class(['flex', 'justify-center'])>
                <div id="details_avatar_container" @class([
                    'h-24',
                    'w-24',
                    'rounded-full',
                    'bg-gradient-to-br',
                    'from-blue-400',
                    'to-blue-600',
                    'flex',
                    'items-center',
                    'justify-center',
                    'overflow-hidden',
                    'text-white',
                    'font-bold',
                    'text-2xl',
                ])>
                    <img id="details_avatar" src="" alt="User Avatar" @class(['h-full', 'w-full', 'object-cover', 'hidden'])>
                    <span id="details_avatar_initial"></span>
                </div>
            </div>

            {{-- User Information --}}
            <div @class(['grid', 'grid-cols-1', 'md:grid-cols-2', 'gap-4'])>
                <div>
                    <label @class(['block', 'text-sm', 'font-medium', 'text-gray-500', 'mb-1'])>Full Name</label>
                    <p id="details_name" @class(['text-base', 'font-semibold', 'text-gray-900'])></p>
                </div>

                <div>
                    <label @class(['block', 'text-sm', 'font-medium', 'text-gray-500', 'mb-1'])>Employee ID</label>
                    <span id="details_employee_id" @class([
                        'inline-flex',
                        'items-center',
                        'px-3',
                        'py-1',
                        'rounded-full',
                        'text-sm',
                        'font-mono',
                        'font-medium',
                        'bg-gray-100',
                        'text-gray-800',
                    ])></span>
                </div>

                <div @class(['md:col-span-2'])>
                    <label @class(['block', 'text-sm', 'font-medium', 'text-gray-500', 'mb-1'])>Email</label>
                    <p id="details_email" @class(['text-sm', 'text-gray-700'])></p>
                </div>

                <div>
                    <label @class(['block', 'text-sm', 'font-medium', 'text-gray-500', 'mb-1'])>Department</label>
                    <span id="details_department"></span>
                </div>

                <div>
                    <label @class(['block', 'text-sm', 'font-medium', 'text-gray-500', 'mb-1'])>Type</label>
                    <span id="details_type"></span>
                </div>

                <div>
                    <label @class(['block', 'text-sm', 'font-medium', 'text-gray-500', 'mb-1'])>Status</label>
                    <span id="details_status"></span>
                </div>


                <div>
                    <label @class(['block', 'text-sm', 'font-medium', 'text-gray-500', 'mb-1'])>Last Activity</label>
                    <p id="details_last_activity" @class(['text-sm', 'text-gray-700'])></p>
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
            <button type="button" @class([
                'px-4',
                'py-2',
                'text-gray-700',
                'border',
                'border-gray-300',
                'rounded-lg',
                'hover:bg-gray-50',
            ]) onclick="detailsUserModal.close()">
                Close
            </button>
            <button type="button" id="details_edit_btn" @class([
                'px-4',
                'py-2',
                'bg-blue-600',
                'text-white',
                'rounded-lg',
                'hover:bg-blue-700',
            ])>
                Edit User
            </button>
        @endslot
    </x-modal>

    <script>
        // Handle form submission
        const form = document.getElementById('userForm');
        const submitBtn = document.getElementById('submitBtn');

        if (form) {
            form.addEventListener('submit', function(e) {
                submitBtn.disabled = true;
                submitBtn.textContent = 'Creating...';
            });
        }

        // Reset form when modal is closed
        const modalElement = document.getElementById('userModal');
        if (modalElement) {
            const originalShowModal = userModal.showModal;
            userModal.showModal = function() {
                document.getElementById('userForm').reset();
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Create User';
                }
                originalShowModal.call(this);
            };
        }

        function editUser(id) {
            fetch(`/admin/users/${id}/edit`)
                .then(response => response.json())
                .then(data => {
                    document.getElementById('edit_user_id').value = data.id;
                    document.getElementById('edit_name').value = data.name;
                    document.getElementById('edit_email').value = data.email;
                    document.getElementById('edit_status').checked = data.status == 1;

                    // Populate available departments
                    const deptSelect = document.getElementById('edit_department_id');
                    deptSelect.innerHTML = '<option value="">Select a department</option>';
                    
                    if (data.available_departments && data.available_departments.length > 0) {
                        data.available_departments.forEach(dept => {
                            const option = document.createElement('option');
                            option.value = dept.id;
                            option.textContent = `${dept.name} (${dept.code})`;
                            if (dept.id == data.department_id) {
                                option.selected = true;
                            }
                            deptSelect.appendChild(option);
                        });
                    }

                    // Update form action
                    document.getElementById('editUserForm').action = `/admin/users/${id}`;

                    editUserModal.showModal();
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Failed to load user data');
                });
        }

        function viewUserDetails(id) {
            fetch(`/admin/users/${id}/edit`)
                .then(response => response.json())
                .then(data => {
                    // Set avatar
                    const avatarImg = document.getElementById('details_avatar');
                    const avatarInitial = document.getElementById('details_avatar_initial');

                    if (data.avatar_url) {
                        avatarImg.src = data.avatar_url;
                        avatarImg.classList.remove('hidden');
                        avatarInitial.textContent = '';
                    } else {
                        avatarImg.classList.add('hidden');
                        avatarInitial.textContent = data.name.charAt(0).toUpperCase();
                    }

                    // Set basic information
                    document.getElementById('details_name').textContent = data.name;
                    document.getElementById('details_employee_id').textContent = data.employee_id;
                    document.getElementById('details_email').textContent = data.email;

                    // Set department
                    const deptElement = document.getElementById('details_department');
                    if (data.department_id && data.available_departments) {
                        const dept = data.available_departments.find(d => d.id == data.department_id);
                        if (dept) {
                            deptElement.innerHTML = `<span @class([
                                'inline-flex',
                                'items-center',
                                'px-3',
                                'py-1',
                                'rounded-full',
                                'text-sm',
                                'font-medium',
                                'bg-purple-100',
                                'text-purple-800',
                            ])>${dept.code}</span>`;
                        } else {
                            deptElement.innerHTML = `<span @class([
                                'inline-flex',
                                'items-center',
                                'px-3',
                                'py-1',
                                'rounded-full',
                                'text-sm',
                                'font-medium',
                                'bg-gray-100',
                                'text-gray-500',
                            ])>UKN</span>`;
                        }
                    } else {
                        deptElement.innerHTML = `<span @class([
                            'inline-flex',
                            'items-center',
                            'px-3',
                            'py-1',
                            'rounded-full',
                            'text-sm',
                            'font-medium',
                            'bg-gray-100',
                            'text-gray-500',
                        ])>UKN</span>`;
                    }

                    // Set type
                    const typeElement = document.getElementById('details_type');
                    if (data.type === 'Admin') {
                        typeElement.innerHTML = '<span @class([
                            'inline-flex',
                            'items-center',
                            'px-3',
                            'py-1',
                            'rounded-full',
                            'text-sm',
                            'font-medium',
                            'bg-red-100',
                            'text-red-800',
                        ])>Admin</span>';
                    } else {
                        typeElement.innerHTML = '<span @class([
                            'inline-flex',
                            'items-center',
                            'px-3',
                            'py-1',
                            'rounded-full',
                            'text-sm',
                            'font-medium',
                            'bg-blue-100',
                            'text-blue-800',
                        ])>Head</span>';
                    }

                    // Set status
                    const statusElement = document.getElementById('details_status');
                    if (data.status == 1) {
                        statusElement.innerHTML = '<span @class([
                            'inline-flex',
                            'items-center',
                            'px-3',
                            'py-1',
                            'rounded-full',
                            'text-xs',
                            'font-medium',
                            'bg-green-100',
                            'text-green-800',
                        ])>Active</span>';
                    } else {
                        statusElement.innerHTML = '<span @class([
                            'inline-flex',
                            'items-center',
                            'px-3',
                            'py-1',
                            'rounded-full',
                            'text-xs',
                            'font-medium',
                            'bg-gray-100',
                            'text-gray-800',
                        ])>Inactive</span>';
                    }

                    // Set dates
                    document.getElementById('details_last_activity').textContent = data.last_seen || 'Never';
                    document.getElementById('details_created_at').textContent = formatDate(data.created_at);
                    document.getElementById('details_updated_at').textContent = formatDate(data.updated_at);

                    // Set edit button action
                    document.getElementById('details_edit_btn').onclick = function() {
                        detailsUserModal.close();
                        editUser(id);
                    };

                    detailsUserModal.showModal();
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Failed to load user details');
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
    </script>
@endsection
