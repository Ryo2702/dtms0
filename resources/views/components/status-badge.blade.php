{{-- resources/views/components/status-badge.blade.php --}}
@props(['active'])

@php
    $classes = $active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800';
    $text = $active ? 'Active' : 'Inactive';
@endphp

<span class="px-3 py-1 rounded-full text-xs font-medium {{ $classes }}">
    {{ $text }}
</span>
