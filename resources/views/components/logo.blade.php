@props([
    'logo' => null,
    'name' => '',
    'size' => 'w-12 h-12',
])

@if ($logo)
    <img src="{{ Storage::url($logo) }}" alt="{{ $name }}"
        {{ $attributes->merge(['class' => $size . ' object-cover rounded']) }}>
@else
    <div {{ $attributes->merge(['class' => $size . ' bg-gray-200 rounded flex items-center justify-center']) }}>
        <span class="text-gray-400 text-xs">No logo</span>
    </div>
@endif
