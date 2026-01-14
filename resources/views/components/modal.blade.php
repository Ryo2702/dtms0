@props([
    'id' => 'modal',
    'title' => '',
    'size' => 'md',
    'actions' => null
])

@php
    $sizeClasses = [
        'sm' => 'max-w-sm',
        'md' => 'max-w-md',
        'lg' => 'max-w-2xl',
        'xl' => 'max-w-4xl',
        'full' => 'max-w-7xl'
    ];
    
    $modalSize = $sizeClasses[$size] ?? $sizeClasses['md'];
@endphp

<div id="{{ $id }}" class="fixed inset-0 z-[9999] hidden overflow-y-auto" aria-labelledby="{{ $id }}-title" role="dialog" aria-modal="true">

    <div class="fixed inset-0 bg-black/5 transition-opacity backdrop-blur-sm" onclick="document.getElementById('{{ $id }}').classList.add('hidden')"></div>
    
    {{-- Modal container - centered --}}
    <div class="flex min-h-full items-center justify-center p-4">
        <div class="relative transform overflow-hidden rounded-lg bg-white shadow-xl transition-all w-full {{ $modalSize }}">
            <button type="button" 
                    class="absolute right-4 top-4 z-10 rounded-full p-1 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-gray-400"
                    onclick="document.getElementById('{{ $id }}').classList.add('hidden')">
                <svg class="h-6 w-6 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
            
            @if($title)
                <div class="border-b border-gray-200 px-6 py-4">
                    <h3 id="{{ $id }}-title" class="text-lg font-semibold text-gray-900">
                        {{ $title }}
                    </h3>
                </div>
            @endif

            <div class="px-6 py-4">
                {{ $slot }}
            </div>

            @if($actions)
                <div class="border-t border-gray-200 px-6 py-4 flex justify-end gap-3">
                    {{ $actions }}
                </div>
            @endif
        </div>
    </div>
</div>

<script>

    window['{{ $id }}'] = {
        showModal: function() {
            document.getElementById('{{ $id }}').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        },
        close: function() {
            document.getElementById('{{ $id }}').classList.add('hidden');
            document.body.style.overflow = '';
        }
    };

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const modal = document.getElementById('{{ $id }}');
            if (!modal.classList.contains('hidden')) {
                modal.classList.add('hidden');
                document.body.style.overflow = '';
            }
        }
    });
</script>