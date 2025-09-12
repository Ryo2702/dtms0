@props([
    'title' => null,
    'subtitle' => null,
    'bgColor' => 'bg-white-secondary',
    'compact' => false,
])

<div class="card {{ $bgColor }} {{ $compact ? 'mb-4' : 'mb-6' }}">
    <div class="card-body">
        @if ($title)
            <h3 class="card-title {{ $compact ? 'text-base' : 'text-lg' }} mb-4">{{ $title }}
                @if ($subtitle)
                    <div class="badge badge-secondary badge-outline">
                        {{ $subtitle }}
                    </div>
                @endif
            </h3>
        @endif

        {{ $slot }}
    </div>
</div>
