@props([
    'action' => url()->current(),
    'searchPlaceholder' => 'Search...',
    'sortFields' => ['id' => 'ID', 'name' => 'Name', 'created_at' => 'Created At'],
    'statuses' => ['active' => 'Active', 'inactive' => 'Inactive'],
    'defaultSort' => 'id',
    'defaultDirection' => 'asc',
    'containerId' => 'filter-results',
])

<form action="{{ $action }}" method="GET" class="mb-6 space-y-4">
    <div class="flex flex-wrap gap-4 items-end">
        <!-- Search Input -->
        <div class="flex-1 min-w-64 ">
            <label for="search" class="block text-sm font-medium mb-1">Search</label>
            <input type="text" name="search" id="search" value="{{ request('search') }}"
                class="input input-bordered w-full " placeholder="{{ $searchPlaceholder }}">
        </div>

        <!-- Status Filter -->
        <div>
            <label for="status" class="block text-sm font-medium mb-1">Status</label>
            <select name="status" id="status" class="select select-bordered">
                <option value="">All</option>
                @foreach ($statuses as $key => $label)
                    <option value="{{ $key }}" {{ request('status') == $key ? 'selected' : '' }}>
                        {{ $label }}
                    </option>
                @endforeach
            </select>
        </div>

        <!-- Sort Field -->
        <div>
            <label for="sort" class="block text-sm font-medium mb-1">Sort By</label>
            <select name="sort" id="sort" class="select select-bordered">
                @foreach ($sortFields as $field => $label)
                    <option value="{{ $field }}" {{ request('sort', $defaultSort) == $field ? 'selected' : '' }}>
                        {{ $label }}
                    </option>
                @endforeach
            </select>
        </div>

        <!-- Sort Direction -->
        <div>
            <label for="direction" class="block text-sm font-medium mb-1">Order</label>
            <select name="direction" id="direction" class="select select-bordered">
                <option value="asc" {{ request('direction', $defaultDirection) == 'asc' ? 'selected' : '' }}>
                    Ascending
                </option>
                <option value="desc" {{ request('direction') == 'desc' ? 'selected' : '' }}>
                    Descending
                </option>
            </select>
        </div>

        <!-- Submit Button -->
        <div>
            <button type="submit" class="btn btn-primary">
                <i data-lucide="search" class="w-4 h-4 mr-2"></i>
                Filter
            </button>
        </div>
    </div>

    <!-- Clear Filters -->
    @if (request()->hasAny(['search', 'status', 'sort', 'direction']))
        <div class="flex justify-end">
            <a href="{{ $action }}" class="btn btn-ghost btn-sm">
                <i data-lucide="x" class="w-4 h-4 mr-1"></i>
                Clear Filters
            </a>
        </div>
    @endif
</form>
