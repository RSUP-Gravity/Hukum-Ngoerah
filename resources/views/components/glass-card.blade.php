@props([
    'hover' => true,
    'padding' => 'p-5 sm:p-6'
])

<div {{ $attributes->merge([
    'class' => ($hover ? 'glass-card' : 'glass-card-static') . ' ' . $padding
]) }}>
    {{ $slot }}
</div>
