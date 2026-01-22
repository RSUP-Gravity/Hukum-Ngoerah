@props([
    'align' => 'right',
    'width' => '48',
    'contentClasses' => '',
])

@php
    $alignmentClasses = [
        'left' => 'origin-top-left left-0',
        'right' => 'origin-top-right right-0',
        'center' => 'origin-top left-1/2 -translate-x-1/2',
    ][$align] ?? 'origin-top-right right-0';

    $widthClasses = [
        '48' => 'w-48',
        '56' => 'w-56',
        '64' => 'w-64',
        '72' => 'w-72',
    ][$width] ?? 'w-48';
@endphp

<div x-data="dropdown()" class="relative inline-block text-left">
    <!-- Trigger -->
    <div @click="toggle()">
        {{ $trigger }}
    </div>

    <!-- Dropdown Content -->
    <div
        x-show="open"
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-100"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        @click.outside="close()"
        class="glass-dropdown {{ $alignmentClasses }} {{ $widthClasses }} {{ $contentClasses }}"
        x-cloak
    >
        {{ $slot }}
    </div>
</div>
