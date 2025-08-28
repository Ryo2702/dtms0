@props([
    'headers' => [],
    'data' => [],
    'actions' => null,
    'mobileCards' => true,
    'sortable' => true, // Add this prop if you want to enable/disable sorting
])

@php
    $currentSort = request('sort');
    $currentDirection = request('direction', 'asc');
@endphp

<div class="card bg-white shadow-lg overflow-hidden">
    <!-- Desktop Table View -->
    <div class="hidden lg:block overflow-x-auto">
        <table class="table w-full">
            <thead class="bg-gray-50">
                <tr>
                    @foreach ($headers as $key => $header)
                        <th
                            class="text-sm font-semibold text-dtms-text px-4 xl:px-6 py-4 border-b border-gray-200 whitespace-nowrap">
                            @if ($sortable && is_array($headers) && is_string($key) && $key !== '')
                                @php
                                    $isSorted = $currentSort === $key;
                                    $nextDirection = $isSorted && $currentDirection === 'asc' ? 'desc' : 'asc';
                                    $query = array_merge(request()->except(['page']), [
                                        'sort' => $key,
                                        'direction' => $nextDirection,
                                    ]);
                                @endphp
                                <a href="{{ url()->current() . '?' . http_build_query($query) }}"
                                    class="flex items-center gap-1 {{ $isSorted ? 'text-blue-600' : '' }}">
                                    {{ $header }}
                                    @if ($isSorted)
                                        @if ($currentDirection === 'asc')
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M5 15l7-7 7 7" />
                                            </svg>
                                        @else
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 9l-7 7-7-7" />
                                            </svg>
                                        @endif
                                    @endif
                                </a>
                            @else
                                {{ $header }}
                            @endif
                        </th>
                    @endforeach
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                {{ $slot }}
            </tbody>
        </table>
    </div>

    <!-- Mobile/Tablet Card View -->
    @if ($mobileCards)
        <div class="lg:hidden divide-y divide-gray-200">
            {{ $mobileSlot ?? $slot }}
        </div>
    @else
        <!-- Fallback: Horizontal scroll for mobile -->
        <div class="lg:hidden overflow-x-auto">
            <table class="table w-full min-w-max">
                <thead class="bg-gray-50">
                    <tr>
                        @foreach ($headers as $key => $header)
                            <th
                                class="text-xs font-semibold text-dtms-text px-3 py-3 border-b border-gray-200 whitespace-nowrap">
                                @if ($sortable && is_array($headers) && is_string($key) && $key !== '')
                                    @php
                                        $isSorted = $currentSort === $key;
                                        $nextDirection = $isSorted && $currentDirection === 'asc' ? 'desc' : 'asc';
                                        $query = array_merge(request()->except(['page']), [
                                            'sort' => $key,
                                            'direction' => $nextDirection,
                                        ]);
                                    @endphp
                                    <a href="{{ url()->current() . '?' . http_build_query($query) }}"
                                        class="flex items-center gap-1 {{ $isSorted ? 'text-blue-600' : '' }}">
                                        {{ $header }}
                                        @if ($isSorted)
                                            @if ($currentDirection === 'asc')
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2" d="M5 15l7-7 7 7" />
                                                </svg>
                                            @else
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2" d="M19 9l-7 7-7-7" />
                                                </svg>
                                            @endif
                                        @endif
                                    </a>
                                @else
                                    {{ $header }}
                                @endif
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    {{ $slot }}
                </tbody>
            </table>
        </div>
    @endif
</div>
