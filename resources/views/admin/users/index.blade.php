@extends('layouts.app')

@section('content')
    <div class="p-4 sm:p-6">
        <!-- Header -->
        <x-page-header title="User Management" :actionRoute="route('admin.users.create')" actionText="Add User" />

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <x-stat-card title="Active Admins" :value="$activeAdminCount" />

            <x-stat-card title="Active Heads" :value="$activeHeadCount" />

            <x-stat-card title="Active Staff" :value="$activeStaffCount" />

            <x-stat-card title="Inactive Users" :value="$inactiveUsersCount" />
        </div>
        <!-- Filter & Sort Form -->
        <form method="GET" action="{{ route('admin.users.index') }}" class="mb-6 flex flex-wrap gap-4 items-end">
            <div>
                <label for="search" class="block text-sm font-medium">Search</label>
                <input type="text" name="search" id="search" value="{{ request('search') }}"
                    class="input input-bordered w-full max-w-xs" placeholder="Name, Email, ID...">
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
            <div>
                <label class="block text-sm font-medium mb-1">Sort By</label>
                <div class="flex gap-2">
                    <button type="submit" name="sort" value="id"
                        class="btn {{ request('sort', 'id') == 'id' ? 'btn-primary' : 'btn-outline' }}">ID</button>
                    <button type="submit" name="sort" value="name"
                        class="btn {{ request('sort') == 'name' ? 'btn-primary' : 'btn-outline' }}">Name</button>
                    <button type="submit" name="sort" value="email"
                        class="btn {{ request('sort') == 'email' ? 'btn-primary' : 'btn-outline' }}">Email</button>
                    <button type="submit" name="sort" value="created_at"
                        class="btn {{ request('sort') == 'created_at' ? 'btn-primary' : 'btn-outline' }}">Created
                        At</button>
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Order</label>
                <div class="flex gap-2">
                    <button type="submit" name="direction" value="asc"
                        class="btn {{ request('direction', 'asc') == 'asc' ? 'btn-primary' : 'btn-outline' }}">Ascending</button>
                    <button type="submit" name="direction" value="desc"
                        class="btn {{ request('direction') == 'desc' ? 'btn-primary' : 'btn-outline' }}">Descending</button>
                </div>
            </div>
        </form>

        <!-- Users Table -->
        <div class="bg-base-100 rounded-lg shadow-md overflow-hidden">
            <x-responsive-table :headers="[
                '',
                'ID',
                'Municipal ID',
                'Name',
                'Email',
                'Department',
                'User Type',
                'Status',
                'Last Activity',
                'Actions',
            ]" :mobileCards="true" :bulkActions="true" emptyMessage="No users found"
                emptySubtitle="Try adjusting your search or filter criteria">
                {{-- Desktop Table Rows --}}
                @forelse ($users as $user)
                    <x-user-table-row :user="$user" :activeAdminCount="$activeAdminCount" />

                @empty
                    {{-- Empty state handled by component --}}
                @endforelse

                {{-- Mobile Card View --}}
                <x-slot name="mobileSlot">
                    @forelse ($users as $user)
                        <x-user-mobile-card :user="$user" :activeAdminCount="$activeAdminCount" />

                    @empty
                        {{-- Empty state handled by component --}}
                    @endforelse
                </x-slot>

            </x-responsive-table>

            <!-- Pagination -->
            <div class="mt-6 flex justify-center">
                {{ $users->links() }}
            </div>
        </div>
    @endsection
