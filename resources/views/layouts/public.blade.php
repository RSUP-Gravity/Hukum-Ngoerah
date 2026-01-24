<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" x-data x-bind:class="{
    'dark': localStorage.getItem('darkMode') === 'true' || (!localStorage.getItem('darkMode') && window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches),
    'light': localStorage.getItem('darkMode') === 'false' || (!localStorage.getItem('darkMode') && (!window.matchMedia || !window.matchMedia('(prefers-color-scheme: dark)').matches))
}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Beranda') - {{ config('app.name', 'Hukum RS Ngoerah') }}</title>

    <link rel="icon" type="image/png" href="{{ asset('images/logo kemenkes.png') }}">

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        html {
            background: #F8FAFC;
            color: #0B1220;
        }

        html.dark {
            background: #0F172A;
            color: #F1F5F9;
        }

        body {
            background: inherit;
            color: inherit;
        }
    </style>

    <script>
        (function () {
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

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @stack('styles')
</head>

<body class="min-h-screen bg-[var(--bg-primary)] text-[var(--text-primary)] antialiased">
    @yield('content')

    @stack('scripts')
</body>

</html>