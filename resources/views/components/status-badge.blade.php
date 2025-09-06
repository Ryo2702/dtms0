@props([
    'status' => null,
    'labels' => ['active' => 'Active', 'inactive' => 'Inactive'],
    'variants' => ['active' => 'badge-success', 'inactive' => 'badge-error'],
])

@php
    $key = is_bool($status) ? ($status ? 'active' : 'inactive') : strtolower($status);
    $text = $labels[$key] ?? ucfirst($key);
    $class = $variants[$key] ?? 'badge-neutral';
@endphp

<div {{ $attributes->merge(['class' => "badge $class"]) }}>
    {{ $text }}
</div>
