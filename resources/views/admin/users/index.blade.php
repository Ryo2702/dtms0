@extends('layouts.app')

@section('content')
    <div class="p-4 sm:p-6">
        <x-page-header title="User Management" :canCreate="['ability' => 'create', 'model' => \App\Models\User::class]" :route="route('admin.users.create')" buttonLabel="Add User" icon="plus" />

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <x-stat-card bgColor="bg-stat-primary" title="Active Admins" :value="$activeAdminCount" />
            <x-stat-card bgColor="bg-stat-secondary" title="Active Heads" :value="$activeHeadCount" />
            <x-stat-card bgColor="bg-stat-accent" title="Active Staff" :value="$activeStaffCount" />
            <x-stat-card bgColor="bg-stat-danger" title="Inactive Users" :value="$inactiveUsersCount" />
        </div>

        {{-- Filters --}}
        <x-form.filter :action="route('admin.users.index')" searchPlaceholder="Search by name, email, or employee ID" :sortFields="['id' => 'ID', 'name' => 'Name', 'email' => 'Email', 'created_at' => 'Created At']"
            :statuses="['active' => 'Active', 'inactive' => 'Inactive']" containerId="filter-results" />

        <div id="filter-results">
            <x-data-table :headers="['ID', 'Employee ID', 'Name', 'Email', 'Type', 'Department', 'Status', 'Online', 'Actions']" :paginator="$users" emptyMessage="No users found.">

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
                            <x-actions :model="$user" resource="users" />
                        </td>
                    </tr>
                @endforeach
            </x-data-table>
        </div>

        <!-- Heads and Staff -->
        <div class="mt-12">
            <h2 class="text-xl font-semibold mb-4">Heads and Their Staff</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                @foreach ($heads as $head)
                    <div class="rounded-lg shadow p-4 bg-white-secondary">
                        <div class="font-bold mb-2">{{ $head->name }}
                            <span class="badge badge-info">{{ $head->department->name ?? 'No Department' }}</span>
                        </div>
                        <div class="mb-2 text-sm text-gray-500">Email: {{ $head->email }}</div>
                        <div class="mb-2 text-sm text-gray-500">Staff Members:</div>
                        <ul class="list-disc list-inside text-sm">
                            @forelse ($head->department->staff as $staff)
                                <li>{{ $staff->name }} ({{ $staff->email }})</li>
                            @empty
                                <li class="text-gray-400">No staff assigned.</li>
                            @endforelse
                        </ul>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@endsection
