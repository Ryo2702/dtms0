@props([
    'headers' => [],
    'emptyMessage' => 'no records found.',
    'colspan' => count($headers),
    'paginator' => null,
])

{{-- table --}}
<div class="bg-base-100 rounded-lg shadow-md overflow-x-auto">
    <table class="min-w-full divide-y bg-white-secondary">
        <thead>
            <tr>
                @foreach ($headers as $header)
                    <th class="px-4 py-3 text-left text-xs font-medium bg-stat-primary text-white uppercase">
                        {{ $header }}
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
