@props([
    'name' => 'modal',
    'maxWidth' => 'lg',
    'closeable' => true,
])

@php
    $maxWidthClass = [
        'sm' => 'max-w-sm',
        'md' => 'max-w-md',
        'lg' => 'max-w-lg',
        'xl' => 'max-w-xl',
        '2xl' => 'max-w-2xl',
        '3xl' => 'max-w-3xl',
        '4xl' => 'max-w-4xl',
        'full' => 'max-w-full',
    ][$maxWidth] ?? 'max-w-lg';
@endphp

<div
    x-data="modal()"
    x-on:open-modal.window="if ($event.detail === '{{ $name }}') show()"
    x-on:close-modal.window="if ($event.detail === '{{ $name }}') hide()"
    x-on:keydown.escape.window="@if($closeable) hide() @endif"
    x-show="open"
    x-cloak
    class="fixed inset-0 z-50 overflow-y-auto"
    role="dialog"
    aria-modal="true"
    aria-labelledby="{{ $name }}-title"
>
    <!-- Backdrop -->
    <div
        x-show="open"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="modal-backdrop"
        @if($closeable) @click="hide()" @endif
        aria-hidden="true"
    >
        <!-- Modal Content -->
        <div
            x-show="open"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            class="modal-content {{ $maxWidthClass }}"
            @click.stop
            x-trap.noscroll="open"
        >
            <!-- Close Button -->
            @if($closeable)
                <button
                    @click="hide()"
                    class="absolute top-4 right-4 p-2 rounded-lg text-[var(--text-tertiary)] hover:text-[var(--text-primary)] hover:bg-[var(--surface-glass)] transition-colors"
                    aria-label="Close modal"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            @endif

            <!-- Modal Header -->
            @if(isset($header))
                <div class="mb-6" id="{{ $name }}-title">
                    {{ $header }}
                </div>
            @endif

            <!-- Modal Body -->
            <div>
                {{ $slot }}
            </div>

            <!-- Modal Footer -->
            @if(isset($footer))
                <div class="mt-6 pt-6 border-t border-[var(--surface-glass-border)]">
                    {{ $footer }}
                </div>
            @endif
        </div>
    </div>
</div>
