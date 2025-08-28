@props([
    'title',
    'value',
    'icon' => null,
    'color' => 'text-dtms-text',
    'bgColor' => 'bg-white',
    'textColor' => 'text-gray-500',
])

<div class="card {{ $bgColor }} shadow-lg">
    <div class="card-body p-6 text-center">
        @if ($icon)
            <div class="mb-3">
                {!! $icon !!}
            </div>
        @endif

        <div class="stat-title text-sm {{ $textColor }} mb-2">{{ $title }}</div>
        <div class="stat-value text-3xl font-bold {{ $color }}">{{ $value }}</div>
    </div>
</div>
