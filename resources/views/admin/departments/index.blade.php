@extends('layouts.app')

@section('content')
    <div class="p-4 sm:p-6">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
            <h1 class="text-2xl font-bold">Department Management</h1>
            @can('create', \App\Models\Department::class)
                <a href="{{ route('admin.departments.create') }}" class="btn btn-primary">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Add Department
                </a>
            @endcan
        </div>
        <!-- Stats Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <x-stat-card title="Total Departments " :value="$totalDepartments" />

            <x-stat-card title="Active Departments" :value="$activeDepartments" />

            <x-stat-card title="Inactive Departments" :value="$inactiveDepartments" />

            <x-stat-card title="Department With Heads" :value="$departmentsWithHeads" />
        </div>

        <form method="GET" action="{{ $action ?? url()->current() }}" class="mb-6 flex flex-wrap gap-4 items-end">
            <div>
                <label for="search" class="block text-sm font-medium">Search</label>
                <input type="text" name="search" id="search" value="{{ request('search') }}"
                    class="input input-bordered w-full max-w-xs" placeholder="{{ $searchPlaceholder ?? 'Search...' }}">
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
                    @foreach ($sortFields ?? ['id' => 'ID', 'name' => 'Name', 'created_at' => 'Created At'] as $field => $label)
                        <button type="submit" name="sort" value="{{ $field }}"
                            class="btn {{ request('sort', 'id') == $field ? 'btn-primary' : 'btn-outline' }}">{{ $label }}</button>
                    @endforeach
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

        <!-- Departments Table -->
        <div class="bg-base-100 rounded-lg shadow-md overflow-hidden">
            <x-responsive-table :headers="['ID', 'Logo', 'Code', 'Name', 'Head', 'Staff Count', 'Status', 'Actions']">
                @foreach ($departments as $department)
                    <!-- Desktop Table Row -->
                    <tr class="hidden lg:table-row hover:bg-base-50">
                        <td class="px-4 xl:px-6 py-4 font-medium">{{ $department->id }}</td>
                        <td class="px-4 xl:px-6 py-4">
                            @if ($department->logo)
                                <img src="{{ Storage::url($department->logo) }}" alt="{{ $department->name }} Logo"
                                    class="w-12 h-12 object-cover rounded" />
                            @else
                                <span>—</span>
                            @endif
                        </td>
                        <td class="px-4 xl:px-6 py-4 font-mono text-sm">{{ $department->code }}</td>
                        <td class="px-4 xl:px-6 py-4 font-medium">{{ $department->name }}</td>
                        <td class="px-4 xl:px-6 py-4 text-sm">{{ $department->head?->name ?? '—' }}</td>
                        <td class="px-4 xl:px-6 py-4">{{ $department->staff->count() }}</td>
                        <td class="px-4 xl:px-6 py-4">
                            <div class="badge {{ $department->status ? 'badge-success' : 'badge-error' }}">
                                {{ $department->status ? 'Active' : 'Inactive' }}
                            </div>
                        </td>
                        <td class="px-4 xl:px-6 py-4">
                            <x-department-actions :department="$department" />
                        </td>
                    </tr>
                @endforeach

                @slot('mobileSlot')
                    @foreach ($departments as $department)
                        <!-- Mobile Card Layout -->
                        <div class="lg:hidden p-4 hover:bg-base-50 transition-colors">
                            <div class="flex items-start gap-4 mb-3">
                                <div class="flex-shrink-0">
                                    @if ($department->logo)
                                        <img src="{{ Storage::url($department->logo) }}" alt="{{ $department->name }} Logo"
                                            class="w-16 h-16 object-cover rounded-lg" />
                                    @else
                                        <div class="w-16 h-16 bg-gray-200 rounded-lg flex items-center justify-center">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-gray-400" fill="none"
                                                viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                            </svg>
                                        </div>
                                    @endif
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center justify-between mb-1">
                                        <h3 class="font-semibold text-dtms-text truncate">{{ $department->name }}</h3>
                                        <div class="badge {{ $department->status ? 'badge-success' : 'badge-error' }} ml-2">
                                            {{ $department->status ? 'Active' : 'Inactive' }}
                                        </div>
                                    </div>
                                    <p class="text-sm text-gray-600 font-mono">{{ $department->code }}</p>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm">
                                <div>
                                    <span class="text-gray-500">Department Head:</span>
                                    <div class="font-medium">{{ $department->head?->name ?? 'No Head Assigned' }}</div>
                                </div>
                                <div>
                                    <span class="text-gray-500">Staff Count:</span>
                                    <div class="font-medium">{{ $department->staff->count() }}
                                        {{ Str::plural('member', $department->staff->count()) }}</div>
                                </div>
                            </div>

                            @if ($department->description)
                                <div class="mt-2 text-sm text-gray-600">
                                    <span class="text-gray-500">Description:</span>
                                    <p class="mt-1">{{ Str::limit($department->description, 100) }}</p>
                                </div>
                            @endif

                            <div class="mt-3 pt-3 border-t border-gray-200">
                                <x-department-actions :department="$department" />
                            </div>
                        </div>
                    @endforeach
                @endslot
            </x-responsive-table>
        </div>

        <!-- Pagination -->
        <div class="mt-6 flex justify-center">
            {{ $departments->links() }}
        </div>
    </div>
@endsection
