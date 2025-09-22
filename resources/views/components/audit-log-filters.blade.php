@props([
    'users' => collect(),
    'actions' => ['login', 'logout', 'create', 'update', 'delete', 'approve', 'reject', 'forward', 'download'],
])

<x-card title="Filter Audit Logs" compact="true">
    <form method="GET" class="space-y-4">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- User Filter -->
            <div>
                <label for="user_id" class="block text-sm font-medium text-gray-700 mb-1">User</label>
                <select name="user_id" id="user_id" class="select select-bordered w-full select-sm">
                    <option value="">All Users</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                            {{ $user->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Action Filter -->
            <div>
                <label for="action" class="block text-sm font-medium text-gray-700 mb-1">Action</label>
                <select name="action" id="action" class="select select-bordered w-full select-sm">
                    <option value="">All Actions</option>
                    @foreach($actions as $action)
                        <option value="{{ $action }}" {{ request('action') == $action ? 'selected' : '' }}>
                            {{ ucfirst($action) }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Date From -->
            <div>
                <label for="date_from" class="block text-sm font-medium text-gray-700 mb-1">Date From</label>
                <input 
                    type="date" 
                    name="date_from" 
                    id="date_from" 
                    value="{{ request('date_from') }}"
                    class="input input-bordered w-full input-sm"
                >
            </div>

            <!-- Date To -->
            <div>
                <label for="date_to" class="block text-sm font-medium text-gray-700 mb-1">Date To</label>
                <input 
                    type="date" 
                    name="date_to" 
                    id="date_to" 
                    value="{{ request('date_to') }}"
                    class="input input-bordered w-full input-sm"
                >
            </div>
        </div>

        <!-- Search Term -->
        <div>
            <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Search Description</label>
            <div class="flex space-x-2">
                <input 
                    type="text" 
                    name="search" 
                    id="search" 
                    value="{{ request('search') }}"
                    placeholder="Search in descriptions..."
                    class="input input-bordered flex-1 input-sm"
                >
                <button type="submit" class="btn btn-primary btn-sm">
                    <i data-lucide="search" class="w-4 h-4 mr-1"></i>
                    Filter
                </button>
                <a href="{{ url()->current() }}" class="btn btn-outline btn-sm">
                    <i data-lucide="x" class="w-4 h-4 mr-1"></i>
                    Clear
                </a>
            </div>
        </div>

        <!-- Quick Date Filters -->
        <div class="flex flex-wrap gap-2">
            <span class="text-sm font-medium text-gray-700">Quick filters:</span>
            <a href="{{ request()->fullUrlWithQuery(['date_from' => now()->format('Y-m-d'), 'date_to' => now()->format('Y-m-d')]) }}" 
               class="text-xs text-blue-600 hover:text-blue-800">Today</a>
            <a href="{{ request()->fullUrlWithQuery(['date_from' => now()->subDays(7)->format('Y-m-d'), 'date_to' => now()->format('Y-m-d')]) }}" 
               class="text-xs text-blue-600 hover:text-blue-800">Last 7 days</a>
            <a href="{{ request()->fullUrlWithQuery(['date_from' => now()->subDays(30)->format('Y-m-d'), 'date_to' => now()->format('Y-m-d')]) }}" 
               class="text-xs text-blue-600 hover:text-blue-800">Last 30 days</a>
        </div>
    </form>
</x-card>