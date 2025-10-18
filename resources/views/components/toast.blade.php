@props([
    'type' => 'info', // success | error | warning | info
    'title' => null,
    'message' => null,
    'messages' => [],
    'timeout' => 5000, // ms
    'position' => 'top-right', 
])

@php
    $positions = [
        'top-right' => 'fixed right-4 top-4',
        'top-left' => 'fixed left-4 top-4',
        'bottom-right' => 'fixed right-4 bottom-4',
        'bottom-left' => 'fixed left-4 bottom-4',
    ];
    $bg = match ($type) {
        'success' => 'bg-green-500 text-white',
        'error' => 'bg-red-500 text-white',
        'warning' => 'bg-yellow-500 text-white',
        default => 'bg-blue-500 text-white',
    };
    $hasMessages = $message || (is_array($messages) && count($messages));
@endphp

@if ($hasMessages)
    <div class="{{ $positions[$position] ?? $positions['top-right'] }} z-50"
        id="blade-toast-{{ \Illuminate\Support\Str::random(6) }}" data-timeout="{{ $timeout }}">
        <div class="space-y-2">
            <div class="max-w-sm w-full {{ $bg }} rounded-lg shadow-lg p-4 flex items-start gap-3"
                role="alert">
                <div class="flex-1">
                    @if ($title)
                        <div class="font-semibold">{{ $title }}</div>
                    @endif

                    @if ($message)
                        <div class="text-sm mt-1">{{ $message }}</div>
                    @endif

                    @if (is_array($messages) && count($messages))
                        <div class="text-sm mt-1">
                            <ul class="list-disc list-inside space-y-1">
                                @foreach ($messages as $m)
                                    <li>{{ $m }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </div>

                <button type="button" class="text-white opacity-90 hover:opacity-100 text-xl leading-none px-2"
                    data-blade-toast-close>&times;</button>
            </div>
        </div>
    </div>

    <script>
        (function() {
            const container = document.getElementById('blade-toast-{{ \Illuminate\Support\Str::random(6) }}') ||
                document.querySelector('[id^="blade-toast-"]');
            if (!container) return;
            const timeout = parseInt(container.getAttribute('data-timeout') || {{ $timeout }});
            setTimeout(() => container.remove(), timeout);
            container.addEventListener('click', function(e) {
                if (e.target.matches('[data-blade-toast-close]')) container.remove();
            });
        })();
    </script>
@endif
