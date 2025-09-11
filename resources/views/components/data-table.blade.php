@props([
    'headers' => [],
    'emptyMessage' => 'no records found.',
    'colspan' => count($headers),
    'paginator' => null,
    'sortDirection' => null, // 'asc' or 'desc'
    'sortableFields' => [], // array of fields that can be sorted
])

@php
    $currentSort = request()->get('sort');
    $currentDirection = request()->get('direction', 'asc');
@endphp

{{-- table --}}
<div class="bg-base-100 rounded-lg shadow-md overflow-x-auto">
    <table class="min-w-full divide-y bg-white-secondary">
        <thead>
            <tr>
                @foreach ($headers as $index => $label)
                    @php
                        // Check if this header is sortable
                        $fieldName = is_string($index) ? $index : strtolower(str_replace(' ', '_', $label));
                        $isSortable = empty($sortableFields) || in_array($fieldName, $sortableFields);
                        $isCurrent = $currentSort === $fieldName;
                        $nextDirection = $isCurrent && $currentDirection === 'asc' ? 'desc' : 'asc';
                    @endphp
                    <th class="px-4 py-3 text-left text-xs font-medium bg-stat-primary text-white uppercase">
                        @if ($isSortable)
                            <a href="{{ request()->fullUrlWithQuery(['sort' => $fieldName, 'direction' => $nextDirection]) }}"
                                class="flex items-center space-x-1 hover:text-gray-200">
                                <span>{{ $label }}</span>
                                @if ($isCurrent)
                                    @if ($currentDirection === 'asc')
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M5 15l7-7 7 7" />
                                        </svg>
                                    @else
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 9l-7 7-7-7" />
                                        </svg>
                                    @endif
                                @else
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 opacity-30" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M8 9l4-4 4 4m0 6l-4 4-4-4" />
                                    </svg>
                                @endif
                            </a>
                        @else
                            <span>{{ $label }}</span>
                        @endif
                    </th>
                @endforeach
            </tr>
        </thead>
        <tbody class="divide-y">
            @if (trim($slot))
                {{ $slot }}
            @else
                <tr>
                    <td colspan="{{ $colspan }}" class="px-4 py-6 text-center text-gray-500">
                        {{ $emptyMessage }}
                    </td>
                </tr>
            @endif
        </tbody>
    </table>
</div>
{{-- paginator --}}
@if ($paginator)
    <div class="mt-6 flex justify-end">
        {{ $paginator->links() }}
    </div>
@endif
