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
            <x-data-table :headers="['ID', 'Employee ID', 'Name', 'Email', 'Type', 'Department', 'Status', 'Online', 'Actions']" :paginator="$users" :sortableFields="['id', 'employee_id', 'name', 'email', 'type']" emptyMessage="No users found.">

                @foreach ($users as $user)
                    <tr>
                        <td class="px-4 py-3">{{ $user->id }}</td>
                        <td class="px-4 py-3 font-mono text-xs">{{ $user->employee_id }}</td>
                        <td class="px-4 py-3 font-medium">{{ $user->name }}</td>
                        <td class="px-4 py-3">{{ $user->email }}</td>
                        <td class="px-4 py-3">{{ $user->type }}</td>
                        <td class="px-4 py-3">{{ $user->department?->name ?? '—' }}</td>
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
                            <div class="flex items-center space-x-2">
                                <button type="button" 
                                        class="text-blue-600 hover:text-blue-900 text-sm font-medium"
                                        onclick="showUserDetails(@json($user->load('department')))">
                                    <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                    </svg>
                                    View
                                </button>
                                <x-actions :model="$user" resource="users" />
                            </div>
                        </td>
                    </tr>
                @endforeach
            </x-data-table>

            <x-modal id="userDetailsModal" title="User Details" size="lg">
                <div class="space-y-4" id="userDetailsContent">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <x-label text="Name" />
                            <p class="mt-1 text-sm text-gray-900" id="userName"></p>
                        </div>
                        <div>
                            <x-label text="Email" />
                            <p class="mt-1 text-sm text-gray-900" id="userEmail"></p>
                        </div>
                        <div>
                            <x-label text="Employee ID" />
                            <p class="mt-1 text-sm text-gray-900" id="userEmployeeId"></p>
                        </div>
                        <div>
                            <x-label text="Department" />
                            <p class="mt-1 text-sm text-gray-900" id="userDepartment"></p>
                        </div>
                        <div>
                            <x-label text="Type" />
                            <p class="mt-1 text-sm text-gray-900">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800" id="userType"></span>
                            </p>
                        </div>
                        <div>
                            <x-label text="Status" />
                            <p class="mt-1 text-sm text-gray-900">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium" id="userStatus"></span>
                            </p>
                        </div>
                    </div>
                </div>
                
                <x-slot name="actions">
                    <button type="button" class="btn-error" onclick="userDetailsModal.close()">
                        Close
                    </button>
                </x-slot>
            </x-modal>
        </div>
    </div>

    <script>
        function showUserDetails(user) {
            document.getElementById('userName').textContent = user.name;
            document.getElementById('userEmail').textContent = user.email;
            document.getElementById('userType').textContent = user.type;
            document.getElementById('userEmployeeId').textContent = user.employee_id;
            document.getElementById('userDepartment').textContent = user.department?.name || 'N/A';

            const statusElement = document.getElementById('userStatus');
            statusElement.textContent = user.status ? 'Active' : 'Inactive';
            statusElement.className = `inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${user.status ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}`;

            userDetailsModal.showModal();
        }
    </script>
@endsection
