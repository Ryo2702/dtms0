@props([
    'title' => '',
    'route' => null, // e.g. route('admin.users.create')
    'buttonLabel' => 'Add',
    'icon' => 'plus', // lucide icon name
    'canCreate' => null, // ['ability' => 'create', 'model' => \App\Models\User::class]
])

@php
    $ability = is_array($canCreate) ? $canCreate['ability'] ?? null : null;
    $policyModel = is_array($canCreate) ? $canCreate['model'] ?? null : null;
@endphp

<div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
    <h1 class="text-2xl font-bold">{{ $title }}</h1>

    @if ($route)
        @if ($ability && $policyModel)
            @can($ability, $policyModel)
                <a href="{{ $route }}" class="btn btn-primary">
                    @if ($icon)
                        <i data-lucide="{{ $icon }}" class="h-5 w-5 mr-2"></i>
                    @endif
                    {{ $buttonLabel }}
                </a>
            @endcan
        @else
            {{-- If no policy is provided, just show the button --}}
            <a href="{{ $route }}" class="btn btn-primary">
                @if ($icon)
                    <i data-lucide="{{ $icon }}" class="h-5 w-5 mr-2"></i>
                @endif
                {{ $buttonLabel }}
            </a>
        @endif
    @endif
</div>
