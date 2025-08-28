{{-- resources/views/components/user-type-badge.blade.php --}}
@props(['type'])

@php
    $classes = match ($type) {
        'Admin' => 'bg-red-100 text-red-800',
        'Head' => 'bg-yellow-100 text-yellow-800',
        'Staff' => 'bg-blue-100 text-blue-800',
        default => 'bg-gray-100 text-gray-800',
    };
@endphp

<span class="px-3 py-1 rounded-full text-xs font-medium {{ $classes }}">
    {{ $type }}
</span>
