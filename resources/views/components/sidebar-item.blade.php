@props(['route', 'active' => false, 'icon' => null, 'badge' => null])

<li class="mb-2">
    <a href="{{ $route }}"
        class="flex items-center justify-between p-2 lg:p-1 rounded-lg transition-colors duration-200 
        {{ $active ? 'bg-black/30 text-white border-l-4 border-white font-semibold' : 'hover:bg-dtms-secondary/50' }}">

        <div class="flex items-center">
            @if ($icon)
                <x-dynamic-component :component="'lucide-' . $icon" class="w-4 h-4 lg:h-5 lg:w-5" />
            @endif

            <span class="ml-2 lg:ml-3">{{ $slot }}</span>
        </div>

        @if ($badge && $badge['count'] > 0)
            <div class="flex items-center gap-1">
                <div class="inline-flex items-center justify-center rounded text-xs lg:text-sm px-2 py-1 font-semibold {{ $badge['class'] }}" title="Total completed documents">
                    {{ $badge['count'] }}
                </div>
                @if (isset($badge['overdue_count']) && $badge['overdue_count'] > 0)
                    <div class="inline-flex items-center justify-center rounded text-xs lg:text-sm px-2 py-1 font-semibold bg-red-600 text-white" title="Completed documents that were overdue">
                        {{ $badge['overdue_count'] }}
                    </div>
                @endif
            </div>
        @endif
    </a>
</li>
