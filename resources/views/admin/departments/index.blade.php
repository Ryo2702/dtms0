@extends('layouts.app')

@section('content')
    <div class="p-4 sm:p-6">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
            <h1 class="text-2xl font-bold">Department Management</h1>
            @can('create', \App\Models\Department::class)
                <a href="{{ route('admin.departments.create') }}" class="btn btn-primary">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Add Account
                </a>
            @endcan
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <x-stat-card title="Total Departments" :value="$totalDepartments ?? 0" />
            <x-stat-card title="Active Departments" :value="$activeDepartments ?? 0" />
            <x-stat-card title="Inactive Departments" :value="$inactiveDepartments ?? 0" />
            <x-stat-card title="Departments With Heads" :value="$departmentsWithHeads ?? 0" />
        </div>

        <form id="filters-form" method="GET" action="{{ $action ?? url()->current() }}"
            class="mb-6 flex flex-wrap gap-4 items-end">
            <div>
                <label for="search" class="block text-sm font-medium">Search</label>
                <input type="text" name="search" id="search" value="{{ request('search') }}"
                    class="input input-bordered w-full max-w-xs"
                    placeholder="{{ $searchPlaceholder ?? 'Search by name, code, or description' }}">
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
        <div class="bg-base-100 rounded-lg shadow-md overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead>
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Logo</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Code</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Head</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Staff Count</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse ($departments as $department)
                        <tr>
                            <td class="px-4 py-3">{{ $department->id }}</td>
                            <td class="px-4 py-3">
                                @if ($department->logo)
                                    <img src="{{ Storage::url($department->logo) }}" alt="{{ $department->name }} Logo"
                                        class="w-12 h-12 object-cover rounded" />
                                @else
                                    <span>—</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 font-mono text-sm">{{ $department->code }}</td>
                            <td class="px-4 py-3 font-medium">{{ $department->name }}</td>
                            <td class="px-4 py-3">{{ $department->head?->name ?? '—' }}</td>
                            <td class="px-4 py-3">{{ $department->staff->count() }}</td>
                            <td class="px-4 py-3">
                                <div class="badge {{ $department->status ? 'badge-success' : 'badge-error' }}">
                                    {{ $department->status ? 'Active' : 'Inactive' }}
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <x-department-actions :department="$department" />
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-6 text-center text-gray-500">No departments found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="mt-6 flex justify-center">
            {{ $departments->links() }}
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('filters-form');
            if (!form) return;

            const searchInput = form.querySelector('input[name="search"]');
            const selects = form.querySelectorAll('select[name="department_id"], select[name="type"]');

            // Debounce helper
            let debounceTimer;

            function debounceSubmit(delay = 500) {
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(() => form.submit(), delay);
            }

            // Auto-submit on search input (debounced)
            if (searchInput) {
                searchInput.addEventListener('input', () => debounceSubmit(500));
                searchInput.addEventListener('keydown', (e) => {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        form.submit();
                    }
                });
            }

            selects.forEach(s => s.addEventListener('change', () => form.submit()));
        });
    </script>
@endsection
