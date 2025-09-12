{{-- Reusable Modal Component --}}
@props([
    'id' => 'modal',
    'title' => 'Modal Title',
    'size' => 'md', // sm, md, lg, xl
    'footer' => true,
    'cancelText' => 'Cancel',
    'confirmText' => 'Confirm',
    'confirmClass' => 'btn-primary',
    'cancelClass' => 'btn-ghost',
    'closable' => true,
])

@php
    $sizeClasses = [
        'sm' => 'max-w-sm',
        'md' => 'max-w-md',
        'lg' => 'max-w-lg',
        'xl' => 'max-w-xl',
        '2xl' => 'max-w-2xl',
    ];
    $modalSize = $sizeClasses[$size] ?? $sizeClasses['md'];
@endphp

<div id="{{ $id }}" class="modal" tabindex="-1" role="dialog" style="display: none;">
    <div class="modal-box {{ $modalSize }}">
        {{-- Modal Header --}}
        <div class="flex items-center justify-between pb-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">
                {{ $title }}
            </h3>
            @if ($closable)
                <button type="button" class="btn btn-sm btn-circle btn-ghost"
                    onclick="closeModal('{{ $id }}')">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            @endif
        </div>

        {{-- Modal Body --}}
        <div class="py-4">
            {{ $slot }}
        </div>

        {{-- Modal Footer --}}
        @if ($footer)
            <div class="modal-action">
                @if (isset($actions))
                    {{ $actions }}
                @else
                    <button type="button" class="btn {{ $cancelClass }}" onclick="closeModal('{{ $id }}')">
                        {{ $cancelText }}
                    </button>
                    <button type="button" class="btn {{ $confirmClass }}"
                        onclick="confirmModal('{{ $id }}')">
                        {{ $confirmText }}
                    </button>
                @endif
            </div>
        @endif
    </div>
</div>

{{-- Modal Backdrop --}}
<div id="{{ $id }}-backdrop" class="modal-backdrop" style="display: none;"
    onclick="closeModal('{{ $id }}')"></div>

@once
    @push('scripts')
        <script>
            // Global modal functions
            function openModal(modalId) {
                const modal = document.getElementById(modalId);
                const backdrop = document.getElementById(modalId + '-backdrop');

                if (modal && backdrop) {
                    modal.style.display = 'flex';
                    backdrop.style.display = 'block';
                    document.body.style.overflow = 'hidden';

                    // Add modal classes for proper styling
                    modal.classList.add('modal-open');
                }
            }

            function closeModal(modalId) {
                const modal = document.getElementById(modalId);
                const backdrop = document.getElementById(modalId + '-backdrop');

                if (modal && backdrop) {
                    modal.style.display = 'none';
                    backdrop.style.display = 'none';
                    document.body.style.overflow = '';

                    // Remove modal classes
                    modal.classList.remove('modal-open');

                    // Reset form if exists
                    const form = modal.querySelector('form');
                    if (form) {
                        form.reset();
                    }
                }
            }

            function confirmModal(modalId) {
                // This function can be overridden for specific modal behavior
                const modal = document.getElementById(modalId);
                const form = modal.querySelector('form');

                if (form) {
                    form.submit();
                } else {
                    closeModal(modalId);
                }
            }

            // Close modal on Escape key
            document.addEventListener('keydown', function(event) {
                if (event.key === 'Escape') {
                    // Find and close any open modal
                    const openModals = document.querySelectorAll('.modal.modal-open');
                    openModals.forEach(modal => {
                        closeModal(modal.id);
                    });
                }
            });
        </script>

        <style>
            .modal {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                z-index: 1000;
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 1rem;
            }

            .modal-backdrop {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background-color: rgba(0, 0, 0, 0.5);
                z-index: 999;
            }

            .modal-box {
                position: relative;
                z-index: 1001;
                max-height: 90vh;
                overflow-y: auto;
            }
        </style>
    @endpush
@endonce
