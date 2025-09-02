@extends('layouts.app')

@section('content')
    <div class="p-4 sm:p-6">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
            <h1 class="text-2xl font-bold">Staff List</h1>
            <a href="{{ route('head.staff.create') }}" class="btn btn-primary">Add Staff</a>
        </div>

        {{-- Stats --}}
        @php
            $collection =
                $staff instanceof \Illuminate\Pagination\LengthAwarePaginator ? $staff->getCollection() : $staff;
            $totalStaff =
                $totalStaff ??
                ($staff instanceof \Illuminate\Pagination\LengthAwarePaginator
                    ? $staff->total()
                    : $collection->count());
            $activeStaff = $activeStaffCount ?? $collection->where('status', 1)->count();
            $inactiveStaff = $inactiveStaffCount ?? $collection->where('status', 0)->count();
            $onlineStaff =
                $onlineStaffCount ??
                $collection
                    ->filter(
                        fn($u) => $u->last_activity &&
                            \Carbon\Carbon::parse($u->last_activity)->gt(now()->subMinutes(5)),
                    )
                    ->count();
        @endphp

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <x-stat-card title="Total Staff" :value="$totalStaff" />
            <x-stat-card title="Active Staff" :value="$activeStaff" />
            <x-stat-card title="Inactive Staff" :value="$inactiveStaff" />
            <x-stat-card title="Online Staff" :value="$onlineStaff" />
        </div>

        {{-- Filters --}}
        <form id="filters-form" method="GET" action="{{ url()->current() }}" class="mb-6 flex flex-wrap gap-4 items-end">
            <div>
                <label for="search" class="block text-sm font-medium">Search</label>
                <input type="text" name="search" id="search" value="{{ request('search') }}"
                    class="input input-bordered w-full max-w-xs" placeholder="Search by name, email, or employee ID">
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

        {{-- Toast for success --}}
        <x-toast :message="session('success')" type="success" title="Success" :timeout="4000" position="top-right" />

        {{-- Table --}}
        <div class="bg-base-100 rounded-lg shadow-md overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead>
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Employee ID</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Last Seen</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse($staff as $user)
                        <tr>
                            <td class="px-4 py-3">
                                {{ $loop->iteration }}
                            </td>
                            <td class="px-4 py-3 font-mono text-xs">{{ $user->employee_id }}</td>
                            <td class="px-4 py-3 font-medium">{{ $user->name }}</td>
                            <td class="px-4 py-3">{{ $user->email }}</td>
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
                                <a href="{{ route('head.staff.show', $user->id) }}" class="btn btn-xs btn-info">View</a>
                                <a href="{{ route('head.staff.edit', $user->id) }}" class="btn btn-xs btn-ghost">Edit</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-6 text-center text-gray-500">No staff found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div class="mt-6 flex justify-center">
            {{ $staff->links() }}
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('filters-form');
            if (!form) return;

            const searchInput = form.querySelector('input[name="search"]');

            // Debounce helper
            let debounceTimer;

            function debounceSubmit(delay = 500) {
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(() => form.submit(), delay);
            }

            if (searchInput) {
                searchInput.addEventListener('input', () => debounceSubmit(500));
                searchInput.addEventListener('keydown', (e) => {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        form.submit();
                    }
                });
            }
        });
    </script>
@endsection
