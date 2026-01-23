<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" x-data x-bind:class="{
    'dark': localStorage.getItem('darkMode') === 'true' || (!localStorage.getItem('darkMode') && window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches),
    'light': localStorage.getItem('darkMode') === 'false' || (!localStorage.getItem('darkMode') && (!window.matchMedia || !window.matchMedia('(prefers-color-scheme: dark)').matches))
}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'Login' }} - {{ config('app.name', 'Hukum RS Ngoerah') }}</title>

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('images/logo kemenkes.png') }}">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        html { background: #F8FAFC; color: #0B1220; }
        html.dark { background: #0F172A; color: #F1F5F9; }
        body { background: inherit; color: inherit; }

        /* Page loader overlay - removed to eliminate transition flash */
        #page-loader {
            display: none !important;
        }
    </style>

    <script>
        (function() {
            const stored = localStorage.getItem('darkMode');
            const prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
            const isDark = stored === 'true' || (stored === null && prefersDark);

            if (isDark) {
                document.documentElement.classList.add('dark');
                document.documentElement.classList.remove('light');
                document.documentElement.style.backgroundColor = '#0F172A';
                document.documentElement.style.color = '#F1F5F9';
            } else {
                document.documentElement.classList.add('light');
                document.documentElement.classList.remove('dark');
                document.documentElement.style.backgroundColor = '#F8FAFC';
                document.documentElement.style.color = '#0B1220';
            }
        })();
    </script>

    <!-- Scripts & Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-[var(--bg-primary)] text-[var(--text-primary)] antialiased loading">
    <div class="min-h-screen flex">
        <!-- Left Side - Branding -->
        <div class="hidden lg:flex lg:w-1/2 relative bg-gradient-to-br from-primary-500 to-lime-500 p-12 flex-col justify-between">
            <!-- Pattern Overlay -->
            <div class="absolute inset-0 opacity-10">
                <svg class="w-full h-full" xmlns="http://www.w3.org/2000/svg">
                    <defs>
                        <pattern id="grid" width="40" height="40" patternUnits="userSpaceOnUse">
                            <path d="M 40 0 L 0 0 0 40" fill="none" stroke="white" stroke-width="1"/>
                        </pattern>
                    </defs>
                    <rect width="100%" height="100%" fill="url(#grid)" />
                </svg>
            </div>

            <!-- Logo & Title -->
            <div class="relative z-10">
                <div class="flex items-center gap-4 mb-8">
                    <img
                        src="{{ asset('images/logo-white.png') }}"
                        alt="Logo RS Ngoerah"
                        class="h-16 w-16 object-contain"
                        onerror="this.style.display='none'"
                    >
                    <div>
                        <h1 class="text-2xl font-bold text-white">RS Ngoerah</h1>
                        <p class="text-white/80">Kementerian Kesehatan RI</p>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="relative z-10 space-y-6">
                <h2 class="text-4xl font-bold text-white leading-tight">
                    Sistem Manajemen<br>Dokumen Hukum
                </h2>
                <p class="text-lg text-white/80 max-w-md">
                    Platform terpusat untuk mengelola dokumen hukum, perjanjian kerjasama, MoU, dan kontrak RS Ngoerah.
                </p>

                <!-- Features -->
                <div class="space-y-4 pt-4">
                    <div class="flex items-center gap-3 text-white/90">
                        <div class="w-10 h-10 rounded-lg bg-white/20 flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                            </svg>
                        </div>
                        <span>Keamanan dokumen terjamin</span>
                    </div>
                    <div class="flex items-center gap-3 text-white/90">
                        <div class="w-10 h-10 rounded-lg bg-white/20 flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <span>Notifikasi dokumen kadaluarsa</span>
                    </div>
                    <div class="flex items-center gap-3 text-white/90">
                        <div class="w-10 h-10 rounded-lg bg-white/20 flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"></path>
                            </svg>
                        </div>
                        <span>Versioning & audit trail</span>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="relative z-10 text-white/60 text-sm">
                © {{ date('Y') }} RS Ngoerah - Kementerian Kesehatan RI
            </div>
        </div>

        <!-- Right Side - Form -->
        <div class="w-full lg:w-1/2 flex items-center justify-center p-8">
            <div class="w-full max-w-md">
                <!-- Mobile Logo -->
                <div class="lg:hidden text-center mb-8">
                    <img
                        src="{{ asset('images/Logo-RS-New.png') }}"
                        alt="Logo RS Ngoerah"
                        class="h-20 w-20 mx-auto object-contain mb-4"
                        onerror="this.style.display='none'"
                    >
                    <h1 class="text-xl font-bold text-gradient">Hukum RS Ngoerah</h1>
                </div>

                <!-- Dark Mode Toggle -->
                <div class="absolute top-4 right-4">
                    <button
                        @click="darkMode.toggle()"
                        class="p-2 rounded-lg text-[var(--text-secondary)] hover:text-[var(--text-primary)] hover:bg-[var(--surface-glass)] transition-colors"
                    >
                        <svg class="w-5 h-5 hidden dark:block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path>
                        </svg>
                        <svg class="w-5 h-5 block dark:hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path>
                        </svg>
                    </button>
                </div>

                {{ $slot }}
            </div>
        </div>
    </div>

    <script>
        // Enable transitions after page load to prevent flash
        window.addEventListener('DOMContentLoaded', function() {
            // Small delay to ensure styles are applied
            setTimeout(() => {
                document.body.classList.remove('loading');
            }, 50);
        });

        // Fallback for already loaded pages
        if (document.readyState === 'complete' || document.readyState === 'interactive') {
            setTimeout(() => {
                document.body.classList.remove('loading');
            }, 50);
        }
    </script>
</body>
</html>
