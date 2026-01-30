@extends('layouts.public')

@section('title', 'Panduan')

@push('styles')
    @vite(['resources/css/landing-animations.css'])
@endpush

@php
    $panduanTopics = [
        [
            'route' => 'panduan.persiapan-awal',
            'label' => 'Persiapan awal',
            'icon' => 'bi-gear',
        ],
        [
            'route' => 'panduan.jalur-akses',
            'label' => 'Jalur akses',
            'icon' => 'bi-diagram-3',
        ],
        [
            'route' => 'panduan.login-pegawai',
            'label' => 'Login pegawai',
            'icon' => 'bi-box-arrow-in-right',
        ],
        [
            'route' => 'panduan.pencarian-filter',
            'label' => 'Pencarian dan filter',
            'icon' => 'bi-search',
        ],
        [
            'route' => 'panduan.unduh-dokumen',
            'label' => 'Unduh dokumen',
            'icon' => 'bi-download',
        ],
        [
            'route' => 'panduan.bantuan',
            'label' => 'Bantuan',
            'icon' => 'bi-life-preserver',
        ],
    ];
@endphp

@section('content')
    <div class="bg-image-overlay">
        <img src="{{ asset('images/foto-rs.jpg') }}" alt="" aria-hidden="true" onerror="this.style.display='none'">
    </div>

    <div class="relative min-h-screen overflow-hidden hero-gradient-bg">
        <div class="absolute inset-0 hero-grid"></div>

        <div class="blob blob-primary blob-animate absolute -top-28 -right-28 w-80 h-80 opacity-25" data-parallax="0.3">
        </div>
        <div class="blob blob-lime blob-animate-reverse absolute top-1/2 -left-40 w-[420px] h-[420px] opacity-20"
            data-parallax="0.2"></div>

        <div class="floating-navbar-wrapper">
            <nav class="floating-navbar" data-navbar>
                <a href="{{ route('landing') }}" class="flex items-center gap-2 group">
                    <img src="{{ asset('images/logo kemenkes.png') }}" alt="Kemenkes"
                        class="h-8 w-8 transition-transform duration-300 group-hover:scale-105">
                    <div class="leading-tight hidden sm:block">
                        <div class="text-sm font-bold text-[var(--text-primary)] tracking-tight">Hukum Ngoerah</div>
                    </div>
                </a>

                <div class="nav-links hidden md:flex items-center gap-1">
                    <a href="{{ route('landing') }}#features" class="nav-link">Fitur</a>
                    <a href="{{ route('landing') }}#access" class="nav-link">Akses</a>
                    <a href="{{ route('panduan') }}"
                        class="nav-link {{ request()->routeIs('panduan*') ? 'active' : '' }}">Panduan</a>
                    <a href="{{ route('public.documents') }}" class="nav-link">Dokumen Publik</a>
                    <a href="{{ route('landing') }}#faq" class="nav-link">FAQ</a>
                </div>

                <div class="flex items-center gap-2">
                    <a href="{{ route('login') }}" class="nav-cta">
                        <i class="bi bi-box-arrow-in-right"></i>
                        <span>Masuk</span>
                    </a>
                    <button type="button" onclick="darkMode.toggle()" class="theme-toggle" aria-label="Toggle theme"
                        data-theme-toggle>
                        <i class="bi bi-sun-fill theme-toggle-icon sun-icon text-yellow-500"></i>
                        <i class="bi bi-moon-stars-fill theme-toggle-icon moon-icon text-[var(--text-secondary)]"></i>
                    </button>
                    <button type="button" class="mobile-menu-btn md:hidden" aria-label="Menu" aria-expanded="false"
                        aria-controls="mobile-menu-panel" data-mobile-menu-toggle>
                        <i class="bi bi-list text-lg"></i>
                    </button>
                </div>
            </nav>

            <div id="mobile-menu-panel" class="mobile-menu-panel md:hidden" data-mobile-menu hidden>
                <a href="{{ route('landing') }}#features" class="mobile-menu-link">Fitur</a>
                <a href="{{ route('landing') }}#access" class="mobile-menu-link">Akses</a>
                <a href="{{ route('panduan') }}" class="mobile-menu-link">Panduan</a>
                <a href="{{ route('public.documents') }}" class="mobile-menu-link">Dokumen Publik</a>
                <a href="{{ route('landing') }}#faq" class="mobile-menu-link">FAQ</a>
                <div class="mobile-menu-divider"></div>
                <a href="{{ route('login') }}" class="mobile-menu-link mobile-menu-cta">
                    <i class="bi bi-box-arrow-in-right"></i>
                    Masuk ke Portal
                </a>
            </div>
        </div>

        <section class="relative pt-28 pb-12">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="reveal">
                    <a href="{{ route('landing') }}"
                        class="inline-flex items-center gap-2 text-sm text-[var(--text-tertiary)] hover:text-[var(--color-primary)] transition-colors">
                        <i class="bi bi-arrow-left"></i>
                        Kembali ke beranda
                    </a>
                </div>

                <h1 class="mt-4 text-3xl sm:text-4xl font-bold text-[var(--text-primary)] reveal delay-1">
                    Panduan <span class="gradient-text">Penggunaan</span>
                </h1>
                <p class="mt-3 text-[var(--text-secondary)] max-w-2xl reveal delay-2">
                    Ringkasan langkah dan alur kerja untuk pengguna publik dan pegawai. Ikuti panduan ini agar akses
                    dokumen lebih cepat, aman, dan konsisten.
                </p>

                <div class="mt-6 flex flex-wrap items-center gap-3 reveal delay-3">
                    <span class="trust-badge">
                        <i class="bi bi-journal-text text-[var(--color-primary)]"></i>
                        <span>Panduan ringkas</span>
                    </span>
                    <span class="trust-badge">
                        <i class="bi bi-shield-check text-green-500"></i>
                        <span>Alur akses jelas</span>
                    </span>
                    <span class="trust-badge">
                        <i class="bi bi-clock-history text-orange-500"></i>
                        <span>Update berkala</span>
                    </span>
                </div>
            </div>
        </section>

        <div class="section-divider"></div>

        <section class="pt-6 pb-12">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 lg:grid-cols-[240px_minmax(0,1fr)_220px] gap-8 panduan-grid">
                    <aside class="feature-card reveal lg:-mt-6 lg:sticky lg:top-16 h-fit panduan-sidebar">
                        <div class="text-xs font-semibold uppercase tracking-widest text-[var(--text-tertiary)]">
                            Topik
                        </div>
                        <p class="mt-2 text-sm text-[var(--text-secondary)]">
                            Pilih topik untuk melihat detail panduan.
                        </p>

                        <nav class="mt-3 space-y-1 pointer-events-auto">
                            @foreach ($panduanTopics as $topic)
                                @php($isActive = request()->routeIs($topic['route']))
                                <a href="{{ route($topic['route']) }}"
                                    class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm transition-colors cursor-pointer focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[var(--color-primary)]/40 {{ $isActive ? 'text-[var(--color-primary)] bg-[var(--surface-glass-elevated)]' : 'text-[var(--text-secondary)] hover:text-[var(--text-primary)] hover:bg-[var(--surface-glass)]' }}"
                                    aria-current="{{ $isActive ? 'page' : 'false' }}">
                                    <i class="bi {{ $topic['icon'] }}"></i>
                                    {{ $topic['label'] }}
                                </a>
                            @endforeach
                        </nav>
                    </aside>

                    <article class="space-y-8 panduan-content">
                        @yield('panduan-content')
                    </article>

                    <aside class="feature-card reveal delay-1 lg:-mt-6 lg:sticky lg:top-16 h-fit panduan-sidebar">
                        <div class="text-xs font-semibold uppercase tracking-widest text-[var(--text-tertiary)]">
                            On this page
                        </div>
                        <ul class="mt-3 space-y-1 text-sm pointer-events-auto">
                            @foreach ($panduanTopics as $topic)
                                @php($isActive = request()->routeIs($topic['route']))
                                <li>
                                    <a href="{{ route($topic['route']) }}"
                                        class="flex items-center gap-2 px-3 py-2 rounded-lg transition-colors cursor-pointer focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[var(--color-primary)]/40 {{ $isActive ? 'text-[var(--color-primary)] bg-[var(--surface-glass-elevated)]' : 'text-[var(--text-secondary)] hover:text-[var(--color-primary)] hover:bg-[var(--surface-glass)]' }}"
                                        aria-current="{{ $isActive ? 'page' : 'false' }}">
                                        <i class="bi bi-pin-angle"></i>
                                        {{ $topic['label'] }}
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </aside>
                </div>
            </div>
        </section>

        <footer class="relative border-t border-[var(--surface-glass-border)] bg-[var(--surface-glass)]/60">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                    <div class="md:col-span-2">
                        <a href="{{ route('landing') }}" class="flex items-center gap-3">
                            <img src="{{ asset('images/logo kemenkes.png') }}" alt="Kemenkes" class="h-10 w-10">
                            <div>
                                <div class="text-base font-bold text-[var(--text-primary)]">Hukum Ngoerah</div>
                                <div class="text-xs text-[var(--text-tertiary)]">Portal Dokumentasi</div>
                            </div>
                        </a>
                        <p class="mt-4 text-sm text-[var(--text-tertiary)] max-w-sm">
                            Sistem manajemen dokumen hukum terpusat untuk mendukung transparansi, akuntabilitas, dan
                            layanan publik yang terpercaya.
                        </p>
                    </div>

                    <div>
                        <h4 class="text-sm font-semibold text-[var(--text-primary)] mb-4">Navigasi</h4>
                        <ul class="space-y-2">
                            <li>
                                <a href="{{ route('login') }}"
                                    class="text-sm text-[var(--text-tertiary)] hover:text-[var(--color-primary)] transition-colors">
                                    Masuk
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('public.documents') }}"
                                    class="text-sm text-[var(--text-tertiary)] hover:text-[var(--color-primary)] transition-colors">
                                    Dokumen Publik
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('panduan') }}"
                                    class="text-sm text-[var(--text-tertiary)] hover:text-[var(--color-primary)] transition-colors">
                                    Panduan
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('landing') }}#features"
                                    class="text-sm text-[var(--text-tertiary)] hover:text-[var(--color-primary)] transition-colors">
                                    Fitur
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('landing') }}#faq"
                                    class="text-sm text-[var(--text-tertiary)] hover:text-[var(--color-primary)] transition-colors">
                                    FAQ
                                </a>
                            </li>
                        </ul>
                    </div>

                    <div>
                        <h4 class="text-sm font-semibold text-[var(--text-primary)] mb-4">Kontak</h4>
                        <ul class="space-y-2 text-sm text-[var(--text-tertiary)]">
                            <li class="flex items-center gap-2">
                                <i class="bi bi-geo-alt"></i>
                                <span>Jl. Diponegoro, Denpasar</span>
                            </li>
                            <li class="flex items-center gap-2">
                                <i class="bi bi-telephone"></i>
                                <span>(0361) 227911</span>
                            </li>
                            <li class="flex items-center gap-2">
                                <i class="bi bi-envelope"></i>
                                <span>hukum@rsngoerah.go.id</span>
                            </li>
                        </ul>
                    </div>
                </div>

                <div
                    class="mt-12 pt-8 border-t border-[var(--surface-glass-border)] flex flex-col sm:flex-row items-center justify-between gap-4">
                    <p class="text-sm text-[var(--text-tertiary)]">
                        (c) {{ date('Y') }} RSUP Prof. Dr. I.G.N.G. Ngoerah. Hak cipta dilindungi.
                    </p>
                    <p class="text-xs text-[var(--text-tertiary)]">
                        Dikembangkan oleh Unit Hukum dan Humas
                    </p>
                </div>
            </div>
        </footer>
    </div>
@endsection

@push('scripts')
    @vite(['resources/js/landing.js'])
@endpush

