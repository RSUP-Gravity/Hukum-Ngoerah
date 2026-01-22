<x-layouts.app :title="$__env->yieldContent('title', 'Dashboard')">
    <x-slot name="header">
        @yield('header')
    </x-slot>
    
    @yield('content')
</x-layouts.app>
