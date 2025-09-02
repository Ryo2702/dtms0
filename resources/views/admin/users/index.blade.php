@extends('layouts.app')

@section('content')
    <div class="p-4 sm:p-6">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
            <h1 class="text-2xl font-bold">Admins</h1>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <x-stat-card title="Active Admins" :value="$activeAdminCount" />
            <x-stat-card title="Active Heads" :value="$activeHeadCount" />
            <x-stat-card title="Active Staff" :value="$activeStaffCount" />
            <x-stat-card title="Inactive Users" :value="$inactiveUsersCount" />
        </div>

        <form method="GET" action="{{ url()->current() }}" class="mb-6 flex flex-wrap gap-4 items-end">
            <div>
                <label for="search" class="block text-sm font-medium">Search</label>
                <input type="text" name="search" id="search" value="{{ request('search') }}"
                    class="input input-bordered w-full max-w-xs" placeholder="Search by name, email, or municipal ID">
            </div>
            <div>
                <label for="department_id" class="block text-sm font-medium">Department</label>
                <select name="department_id" id="department_id" class="select select-bordered">
                    <option value="">All</option>
                    @foreach ($departments as $department)
                        <option value="{{ $department->id }}" @selected(request('department_id') == $department->id)>
                            {{ $department->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="type" class="block text-sm font-medium">Type</label>
                <select name="type" id="type" class="select select-bordered">
                    <option value="">All</option>
                    <option value="Admin" @selected(request('type') == 'Admin')>Admin</option>
                    <option value="Head" @selected(request('type') == 'Head')>Head</option>
                    <option value="Staff" @selected(request('type') == 'Staff')>Staff</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Status</label>
                <div class="flex gap-2">
                    <button type="submit" name="status" value=""
                        class="btn {{ request('status') == '' ? 'btn-primary' : 'btn-outline' }}">All</button>
                    <button type="submit" name="status" value="active"
                        class="btn {{ request('status') == 'active' ? 'btn-primary' : 'btn-outline' }}">Active</button>
                    <button type="submit" name="status" value="inactive"
                        class="btn {{ request('status') == 'inactive' ? 'btn-primary' : 'btn-outline' }}">Inactive</button>
                </div>
            </div>
        </form>

        <!-- Users Table -->
        <div class="bg-base-100 rounded-lg shadow-md overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead>
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Employee ID</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Department</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Online Status</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y ">
                    @forelse ($users as $user)
                        <tr>
                            <td class="px-4 py-3">{{ $user->id }}</td>
                            <td class="px-4 py-3 font-mono text-xs">{{ $user->employee_id }}</td>
                            <td class="px-4 py-3 font-medium">{{ $user->name }}</td>
                            <td class="px-4 py-3">{{ $user->email }}</td>
                            <td class="px-4 py-3">{{ $user->type }}</td>
                            <td class="px-4 py-3">{{ $user->department?->name ?? '—' }}</td>
                            <td class="px-4 py-3">
                                <span class="badge {{ $user->status ? 'badge-success' : 'badge-error' }}">
                                    {{ $user->status ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                @if ($user->last_activity && \Carbon\Carbon::parse($user->last_activity)->gt(now()->subMinutes(5)))
                                    <span class="badge badge-success">Online</span>
                                @else
                                    <span class="badge badge-ghost">Offline</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <a href="{{ route('admin.users.show', $user) }}" class="btn btn-xs btn-info">View</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-6 text-center text-gray-500">No users found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="mt-6 flex justify-center">
            {{ $users->links() }}
        </div>

        <!-- Heads and their Staff (for system admin) -->
        <div class="mt-12">
            <h2 class="text-xl font-semibold mb-4">Heads and Their Staff</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                @foreach ($heads as $head)
                    <div class="bg-base-100 rounded-lg shadow p-4">
                        <div class="font-bold mb-2">{{ $head->name }} <span
                                class="badge badge-info">{{ $head->department->name ?? 'No Department' }}</span></div>
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
