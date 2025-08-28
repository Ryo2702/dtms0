@props([
    'title' => '',
    'backRoute' => null,
    'backText' => 'Back',
    'actionRoute' => null,
    'actionText' => 'Add',
    'actionIcon' => null,
])

<div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
    <h1 class="text-3xl font-bold text-dtms-text">{{ $title }}</h1>

    <div class="flex gap-2">
        @if ($backRoute)
            <a href="{{ $backRoute }}" class="btn btn-ghost text-dtms-primary hover:bg-dtms-primary hover:text-white">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                {{ $backText }}
            </a>
        @endif

        @if ($actionRoute)
            @can('create', \App\Models\User::class)
                <a href="{{ $actionRoute }}"
                    class="btn btn-primary bg-dtms-primary hover:bg-dtms-secondary text-white border-none">
                    @if ($actionIcon)
                        {!! $actionIcon !!}
                    @else
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                    @endif
                    {{ $actionText }}
                </a>
            @endcan
        @endif

        {{ $slot }}
    </div>
</div>
