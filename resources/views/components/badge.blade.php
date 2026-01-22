@props([
    'type' => 'active',
    'size' => 'md',
])

@php
    $types = [
        'active' => 'badge-active',
        'attention' => 'badge-attention',
        'warning' => 'badge-warning',
        'critical' => 'badge-critical',
        'expired' => 'badge-expired',
        'info' => 'badge-info',
        'success' => 'badge-success',
        'default' => 'badge-default',
    ];

    $sizes = [
        'sm' => 'px-2 py-0.5 text-xs',
        'md' => 'px-2.5 py-1 text-xs',
        'lg' => 'px-3 py-1.5 text-sm',
    ];

    $classes = 'inline-flex items-center font-medium rounded-md ' . ($types[$type] ?? $types['default']) . ' ' . ($sizes[$size] ?? $sizes['md']);
@endphp

<span {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</span>
