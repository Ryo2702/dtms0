@props([
    'title',
    'value',
    'icon' => null,
    'iconName' => null, // For dynamic component icons (e.g., 'users', 'building')
    'color' => 'text-white', // text color for value
    'bgColor' => 'bg-white-secondary', // background class - using DTMS colors
    'textColor' => 'text-white', // label color
    'iconColor' => 'text-white', // icon color
    'subtitle' => null, // optional subtitle
    'trend' => null, // trend data (array with percentage and direction)
    'href' => null, // optional link
])

@php
    $cardClasses = "card shadow-lg hover:shadow-xl transition-all duration-200 {$bgColor}";
    if ($href) {
        $cardClasses .= " cursor-pointer hover:scale-105";
    }
    
    // Handle icon background based on card background
    $iconBgClass = str_contains($bgColor, 'bg-stat-') || str_contains($bgColor, 'bg-primary') || str_contains($bgColor, 'bg-secondary') 
        ? 'bg-white/20' 
        : 'bg-primary/10';
@endphp

<div class="{{ $cardClasses }}" @if($href) onclick="window.location.href='{{ $href }}'" @endif>
    <div class="card-body p-6">
        <div class="flex items-start justify-between">
            <div class="flex-1">
                <div class="flex items-center gap-3 mb-3">
                    @if ($iconName)
                        <div class="p-2 rounded-lg {{ $iconBgClass }}">
                            <x-dynamic-component :component="'lucide-' . $iconName" class="w-6 h-6 {{ $iconColor }}" />
                        </div>
                    @elseif ($icon)
                        <div class="p-2 rounded-lg {{ $iconBgClass }}">
                            <div class="{{ $iconColor }}">
                                {!! $icon !!}
                            </div>
                        </div>
                    @endif
                    <div>
                        <div class="stat-title text-sm {{ $textColor }}">
                            {{ $title }}
                        </div>
                        @if ($subtitle)
                            <div class="text-xs opacity-60">
                                {{ $subtitle }}
                            </div>
                        @endif
                    </div>
                </div>

                <div class="stat-value text-3xl font-bold {{ $color }} mb-2">
                    {{ $value }}
                </div>

                @if ($trend)
                    <div class="flex items-center gap-1 text-sm">
                        @if ($trend['direction'] === 'up')
                            <x-dynamic-component component="lucide-trending-up" class="w-4 h-4 text-success" />
                            <span class="text-success">+{{ $trend['percentage'] }}%</span>
                        @elseif ($trend['direction'] === 'down')
                            <x-dynamic-component component="lucide-trending-down" class="w-4 h-4 text-error" />
                            <span class="text-error">-{{ $trend['percentage'] }}%</span>
                        @else
                            <x-dynamic-component component="lucide-minus" class="w-4 h-4 text-warning" />
                            <span class="text-warning">{{ $trend['percentage'] }}%</span>
                        @endif
                        <span class="{{ str_contains($bgColor, 'bg-stat-') ? 'text-white/60' : 'text-base-content/60' }}">from last month</span>
                    </div>
                @endif
            </div>

            @if ($href)
                <div class="flex-shrink-0">
                    <x-dynamic-component component="lucide-arrow-right" class="w-5 h-5 {{ str_contains($bgColor, 'bg-stat-') ? 'text-white/40' : 'text-base-content/40' }}" />
                </div>
            @endif
        </div>
    </div>
</div>
