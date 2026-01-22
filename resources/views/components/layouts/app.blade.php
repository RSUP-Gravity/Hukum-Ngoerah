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
    <!-- Page Transition Overlay -->
    <div id="page-transition-overlay" class="page-transition-overlay"></div>
    
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
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 page-content" x-data="{ pageLoaded: false }" x-init="setTimeout(() => pageLoaded = true, 50)" :class="{ 'page-enter-active': pageLoaded }">
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

        <!-- Mobile Bottom Navigation -->
        <nav class="mobile-bottom-nav lg:hidden" aria-label="Mobile navigation">
            <a href="{{ route('dashboard') }}" 
               class="mobile-nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}"
               aria-label="Dashboard">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                </svg>
                <span>Beranda</span>
            </a>
            <a href="{{ route('documents.index') }}" 
               class="mobile-nav-item {{ request()->routeIs('documents.*') ? 'active' : '' }}"
               aria-label="Documents">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <span>Dokumen</span>
            </a>
            <a href="{{ route('documents.create') }}" 
               class="mobile-nav-item mobile-nav-add"
               aria-label="Tambah Dokumen">
                <div class="mobile-nav-add-btn">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                </div>
            </a>
            <a href="{{ route('notifications.index') }}" 
               class="mobile-nav-item {{ request()->routeIs('notifications.*') ? 'active' : '' }}"
               aria-label="Notifications">
                <div class="relative">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                    </svg>
                    @if(auth()->user()->unreadNotifications()->count() > 0)
                        <span class="absolute -top-1 -right-1 w-4 h-4 bg-red-500 text-white text-[10px] font-bold rounded-full flex items-center justify-center">
                            {{ min(auth()->user()->unreadNotifications()->count(), 9) }}{{ auth()->user()->unreadNotifications()->count() > 9 ? '+' : '' }}
                        </span>
                    @endif
                </div>
                <span>Notifikasi</span>
            </a>
            <button @click="toggleMobile()" 
                    class="mobile-nav-item"
                    aria-label="Menu">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                </svg>
                <span>Menu</span>
            </button>
        </nav>
    </div>

    <!-- Page Transition Scripts -->
    <script>
        (function() {
            // Check if user prefers reduced motion
            const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
            
            if (!prefersReducedMotion) {
                const overlay = document.getElementById('page-transition-overlay');
                
                // Handle link clicks for page transitions
                document.addEventListener('click', function(e) {
                    const link = e.target.closest('a[href]');
                    
                    if (!link) return;
                    
                    // Skip external links, anchors, downloads, and special protocols
                    const href = link.getAttribute('href');
                    if (!href || 
                        href.startsWith('#') || 
                        href.startsWith('javascript:') ||
                        href.startsWith('mailto:') ||
                        href.startsWith('tel:') ||
                        link.hasAttribute('download') ||
                        link.getAttribute('target') === '_blank' ||
                        link.dataset.noTransition !== undefined ||
                        e.ctrlKey || e.metaKey || e.shiftKey) {
                        return;
                    }
                    
                    // Check if it's an internal link
                    const url = new URL(href, window.location.origin);
                    if (url.origin !== window.location.origin) {
                        return;
                    }
                    
                    // Prevent default and start transition
                    e.preventDefault();
                    
                    // Add leaving class to current page content
                    document.body.classList.add('page-leaving');
                    
                    // Show overlay
                    overlay.classList.add('active');
                    
                    // Navigate after transition
                    setTimeout(function() {
                        window.location.href = href;
                    }, 200);
                });
                
                // Handle back/forward navigation
                window.addEventListener('pageshow', function(e) {
                    if (e.persisted) {
                        // Page was loaded from cache (back/forward)
                        overlay.classList.remove('active');
                        overlay.classList.add('exit');
                        document.body.classList.remove('page-leaving');
                        
                        setTimeout(function() {
                            overlay.classList.remove('exit');
                        }, 400);
                    }
                });
                
                // On page load, ensure overlay exits smoothly
                window.addEventListener('load', function() {
                    overlay.classList.add('exit');
                    setTimeout(function() {
                        overlay.classList.remove('exit', 'active');
                    }, 400);
                });
            }
        })();
    </script>

    @stack('scripts')
</body>
</html>
