@props([
    'title',
    'value',
    'icon' => null,
    'color' => null, // text color for value
    'bgColor' => null, // background class
    'textColor' => null, // label color
])

<div class="card shadow-lg {{ $bgColor }}">
    <div class="card-body p-6 text-center">
        @if ($icon)
            <div class="mb-3">{!! $icon !!}</div>
        @endif

        <div class="stat-title text-sm mb-2"
            style="color: {{ $textColor ?: ($bgColor ? 'white' : 'inherit') }} !important;">
            {{ $title }}
        </div>

        <div class="stat-value text-3xl font-bold"
            style="color: {{ $color ?: ($bgColor ? 'white' : 'inherit') }} !important;">
            {{ $value }}
        </div>
    </div>
</div>
