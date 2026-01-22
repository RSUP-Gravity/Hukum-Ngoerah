<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" x-data x-bind:class="{ 'dark': localStorage.getItem('darkMode') === 'true' }">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'Dashboard' }} - {{ config('app.name', 'Hukum RS Ngoerah') }}</title>

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('images/favicon.png') }}">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">

    <!-- Scripts & Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Additional Head Content -->
    @stack('styles')
</head>
<body class="min-h-screen bg-[var(--bg-primary)] text-[var(--text-primary)] antialiased">
    <div x-data="sidebar()" class="min-h-screen">
        <!-- Sidebar -->
        @include('layouts.partials.sidebar')

        <!-- Mobile Sidebar Overlay -->
        <div 
            x-show="mobileOpen"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            @click="toggleMobile()"
            class="fixed inset-0 z-30 bg-black/50 lg:hidden"
            x-cloak
        ></div>

        <!-- Main Content Wrapper -->
        <div 
            class="transition-all duration-300"
            :class="expanded ? 'lg:ml-[280px]' : 'lg:ml-[80px]'"
        >
            <!-- Navbar -->
            @include('layouts.partials.navbar')

            <!-- Page Content -->
            <main class="min-h-[calc(100vh-64px)] pt-16 lg:pt-[64px]">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                    <!-- Page Header -->
                    @if(isset($header))
                        <div class="mb-8">
                            {{ $header }}
                        </div>
                    @endif

                    <!-- Flash Messages -->
                    @if(session('success'))
                        <x-alert type="success" class="mb-6">
                            {{ session('success') }}
                        </x-alert>
                    @endif

                    @if(session('error'))
                        <x-alert type="error" class="mb-6">
                            {{ session('error') }}
                        </x-alert>
                    @endif

                    @if(session('warning'))
                        <x-alert type="warning" class="mb-6">
                            {{ session('warning') }}
                        </x-alert>
                    @endif

                    <!-- Main Content -->
                    {{ $slot }}
                </div>
            </main>
        </div>

        <!-- Command Palette (Cmd+K) -->
        @include('layouts.partials.command-palette')

        <!-- Toast Notifications -->
        @include('layouts.partials.toast')
    </div>

    @stack('scripts')
</body>
</html>
