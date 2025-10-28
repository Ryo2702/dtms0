@extends('layouts.app')

@section('content')
    <div class="p-4 sm:p-6">
        <x-page-header title="Heads Account" :canCreate="['ability' => 'create', 'model' => \App\Models\User::class]" :route="route('admin.departments.create')" buttonLabel="Add Heads Account" icon="plus" />

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 gap-4 mb-6 sm:grid-cols-2 lg:grid-cols-4">
            <x-stat-card bgColor="bg-stat-secondary" title="Active Admins" :value="$activeHeadCount" />
            <x-stat-card bgColor="bg-stat-danger" title="Inactive Users" :value="$inactiveUsersCount" />
        </div>

        {{-- Filters --}}
        <x-form.filter :action="route('admin.users.index')" searchPlaceholder="Search by name, email, or employee ID" :sortFields="['id' => 'ID', 'name' => 'Name', 'email' => 'Email', 'created_at' => 'Created At']"
            :statuses="['active' => 'Active', 'inactive' => 'Inactive']" containerId="filter-results" />

        <div id="filter-results">
            <x-data-table :headers="['ID', 'Employee ID', 'Name', 'Department','Type','Email', 'Status', 'Online', 'Actions']" :paginator="$users" :sortableFields="['id', 'employee_id', 'name', 'email', 'type']" emptyMessage="No users found.">

                @foreach ($users as $user)
                    <tr>
                        <td class="px-4 py-3">{{ $users->firstItem() + $loop->index }}</td>
                        <td class="px-4 py-3 font-mono text-xs">{{ $user->employee_id }}</td>
                        <td class="px-4 py-3 font-medium">{{ $user->name }}</td>
                          <td class="px-4 py-3">{{ $user->department?->name ?? '—' }}</td>
                           <td class="px-4 py-3">{{ $user->type }}</td>
                        <td class="px-4 py-3">{{ $user->email }}</td>
                       
                      
                        <td class="px-4 py-3">
                            <x-status-badge :status="$user->status ? 'active' : 'inactive'" />
                        </td>
                        <td class="px-4 py-3">
                            @if ($user->last_activity && \Carbon\Carbon::parse($user->last_activity)->gt(now()->subMinutes(5)))
                                <span class="badge badge-success">Online</span>
                            @else
                                <span class="badge badge-ghost">Offline</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex gap-2">
                                <button onclick="showUserDetails({{ $user->toJson() }})" 
                                        class="btn btn-sm btn-outline" 
                                        title="View">
                                    <i data-lucide="eye" class="w-4 h-4 mr-1"></i>
                                    View
                                </button>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </x-data-table>
        </div>
    </div>

    {{-- User Details Modal --}}
    <x-modal id="user-details-modal" title="User Details" size="lg">
        <div class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <x-label text="Name" />
                    <p class="mt-1 text-sm text-gray-900 font-medium" id="modal-user-name"></p>
                </div>
                <div>
                    <x-label text="Email" />
                    <p class="mt-1 text-sm text-gray-900" id="modal-user-email"></p>
                </div>
                <div>
                    <x-label text="Type" />
                    <p class="mt-1 text-sm text-gray-900 capitalize" id="modal-user-type"></p>
                </div>
                <div>
                    <x-label text="Status" />
                    <div class="mt-1" id="modal-user-status">
                        <span class="badge badge-success" id="status-active" style="display: none;">Active</span>
                        <span class="badge badge-ghost" id="status-inactive" style="display: none;">Inactive</span>
                    </div>
                </div>
                <div>
                    <x-label text="Department" />
                    <p class="mt-1 text-sm text-gray-900" id="modal-user-department"></p>
                </div>
                <div>
                    <x-label text="Municipal ID" />
                    <p class="mt-1 text-sm text-gray-900 font-mono bg-gray-50 px-2 py-1 rounded" id="modal-user-employee-id"></p>
                </div>
            </div>
            
            <div class="border-t pt-4">
                <x-label text="Account Information" />
                <div class="mt-2 grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-gray-600">
                    <div>
                        <span class="font-medium">Created:</span>
                        <span id="modal-user-created"></span>
                    </div>
                    <div>
                        <span class="font-medium">Last Activity:</span>
                        <span id="modal-user-last-activity"></span>
                    </div>
                </div>
            </div>
        </div>

        <x-slot name="actions">
            <button type="button" 
                    onclick="editUser()" 
                    class="btn btn-primary" 
                    id="edit-user-btn">
                <i data-lucide="edit" class="w-4 h-4 mr-2"></i>
                Edit User
            </button>
            <button type="button" 
                    onclick="window['user-details-modal'].close()" 
                    class="btn btn-secondary">
                Close
            </button>
        </x-slot>
    </x-modal>

    <script>
    let currentUser = null;

    function showUserDetails(user) {
        currentUser = user;
        
        // Populate modal with user data
        document.getElementById('modal-user-name').textContent = user.name || '';
        document.getElementById('modal-user-email').textContent = user.email || '';
        document.getElementById('modal-user-type').textContent = user.type || '';
        document.getElementById('modal-user-department').textContent = user.department?.name || 'N/A';
        document.getElementById('modal-user-employee-id').textContent = user.employee_id || '';
        
        // Format dates
        const createdAt = user.created_at ? new Date(user.created_at).toLocaleDateString() : 'N/A';
        const lastActivity = user.last_activity ? new Date(user.last_activity).toLocaleString() : 'Never';
        
        document.getElementById('modal-user-created').textContent = createdAt;
        document.getElementById('modal-user-last-activity').textContent = lastActivity;
        
        // Handle status display
        const activeStatus = document.getElementById('status-active');
        const inactiveStatus = document.getElementById('status-inactive');
        
        if (user.is_active || user.status) {
            activeStatus.style.display = 'inline-block';
            inactiveStatus.style.display = 'none';
        } else {
            activeStatus.style.display = 'none';
            inactiveStatus.style.display = 'inline-block';
        }
        
        // Show modal
        window['user-details-modal'].showModal();
    }

    function editUser() {
        if (currentUser) {
            window.location.href = `/admin/departments/${currentUser.id}/edit`;
        }
    }
    </script>
@endsection
