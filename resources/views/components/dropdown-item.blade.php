@props([
    'href' => null,
    'active' => false,
    'type' => 'button',
])

@php
    $classes = 'dropdown-item' . ($active ? ' bg-[var(--surface-glass)] text-[var(--color-primary)]' : '');
@endphp

@if($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $classes . ' w-full text-left']) }}>
        {{ $slot }}
    </button>
@endif
