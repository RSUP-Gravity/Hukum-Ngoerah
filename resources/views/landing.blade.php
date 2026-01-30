@extends('layouts.public')

@section('title', 'Portal Dokumentasi Hukum')

@push('styles')
    @vite(['resources/css/landing-animations.css', 'resources/css/connection-lines.css'])
@endpush

@section('content')
    {{-- Background Image with Overlay --}}
    <div class="bg-image-overlay">
        <img src="{{ asset('images/foto-rs.jpg') }}" alt="" aria-hidden="true" onerror="this.style.display='none'">
    </div>

    <div class="relative min-h-screen overflow-hidden hero-gradient-bg">
        {{-- Background decorative elements --}}
        <div class="absolute inset-0 hero-grid pointer-events-none"></div>

        {{-- Animated blobs --}}
        <div class="blob blob-primary blob-animate absolute -top-32 -right-32 w-96 h-96 opacity-30" data-parallax="0.3">
        </div>
        <div class="blob blob-lime blob-animate-reverse absolute top-1/2 -left-48 w-[500px] h-[500px] opacity-20"
            data-parallax="0.2"></div>
        <div class="blob blob-primary blob-animate absolute bottom-0 right-1/4 w-72 h-72 opacity-20" data-parallax="0.4">
        </div>

        {{-- Floating Navigation --}}
        <div class="floating-navbar-wrapper">
            <nav class="floating-navbar" data-navbar>
                {{-- Logo --}}
                <a href="{{ route('landing') }}" class="flex items-center gap-2 group">
                    <img src="{{ asset('images/logo kemenkes.png') }}" alt="Kemenkes"
                        class="h-8 w-8 transition-transform duration-300 group-hover:scale-105">
                    <div class="leading-tight hidden sm:block">
                        <div class="text-sm font-bold text-[var(--text-primary)] tracking-tight">Hukum Ngoerah</div>
                    </div>
                </a>

                {{-- Nav Links (Desktop) --}}
                <div class="nav-links hidden md:flex items-center gap-1">
                    <a href="#features" class="nav-link">Fitur</a>
                    <a href="#access" class="nav-link">Akses</a>
                    <a href="{{ route('panduan') }}" class="nav-link">Panduan</a>
                    <a href="{{ route('public.documents') }}" class="nav-link">Dokumen Publik</a>
                    <a href="#faq" class="nav-link">FAQ</a>
                </div>

                {{-- Actions --}}
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
                    {{-- Mobile menu button --}}
                    <button type="button" class="mobile-menu-btn md:hidden" aria-label="Menu" aria-expanded="false"
                        aria-controls="mobile-menu-panel" data-mobile-menu-toggle>
                        <i class="bi bi-list text-lg"></i>
                    </button>
                </div>
            </nav>

            {{-- Mobile Menu Panel --}}
            <div id="mobile-menu-panel" class="mobile-menu-panel md:hidden" data-mobile-menu hidden>
                <a href="#features" class="mobile-menu-link">Fitur</a>
                <a href="#access" class="mobile-menu-link">Akses</a>
                <a href="{{ route('panduan') }}" class="mobile-menu-link">Panduan</a>
                <a href="{{ route('public.documents') }}" class="mobile-menu-link">Dokumen Publik</a>
                <a href="#faq" class="mobile-menu-link">FAQ</a>
                <div class="mobile-menu-divider"></div>
                <a href="{{ route('login') }}" class="mobile-menu-link mobile-menu-cta">
                    <i class="bi bi-box-arrow-in-right"></i>
                    Masuk ke Portal
                </a>
            </div>
        </div>

        {{-- Hero Section --}}
        <section class="relative pt-28 pb-32 lg:pt-36 lg:pb-40" data-hero-interactive>
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="max-w-4xl mx-auto text-center">
                    {{-- Badge --}}
                    <div class="reveal">

                        <span
                            class="inline-flex items-center gap-2 rounded-full bg-[var(--color-primary)]/10 px-4 py-1.5 text-sm font-semibold text-[var(--color-primary)]">
                            <span class="relative flex h-2 w-2">
                                <span
                                    class="animate-ping absolute inline-flex h-full w-full rounded-full bg-[var(--color-primary)] opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-2 w-2 bg-[var(--color-primary)]"></span>
                            </span>
                            RSUP Prof. Dr. I.G.N.G. Ngoerah Denpasar
                        </span>
                    </div>

                    {{-- Headline --}}
                    <h1
                        class="mt-8 text-4xl sm:text-5xl lg:text-6xl font-bold tracking-tight text-[var(--text-primary)] reveal delay-1">
                        Dokumentasi Hukum
                        <br>
                        <span class="gradient-text-animated">Rumah Sakit Ngoerah</span>
                    </h1>

                    {{-- Subheadline --}}
                    <p
                        class="mt-6 text-lg sm:text-xl text-[var(--text-secondary)] max-w-2xl mx-auto leading-relaxed reveal delay-2">
                        Akses dokumen publik tanpa login, atau masuk untuk pengelolaan dokumen internal
                        dengan sistem yang aman, terstruktur, dan mudah digunakan.
                    </p>

                    {{-- CTA Buttons --}}
                    <div class="mt-10 flex flex-col sm:flex-row items-center justify-center gap-4 reveal delay-3">
                        <a href="{{ route('login') }}" class="btn-primary-landing w-full sm:w-auto">
                            <i class="bi bi-box-arrow-in-right"></i>
                            Masuk ke Portal
                        </a>
                        <a href="{{ route('public.documents') }}" class="btn-secondary-landing w-full sm:w-auto">
                            <i class="bi bi-files"></i>
                            Lihat Dokumen Publik
                        </a>
                    </div>

                    {{-- Trust Badges --}}
                    <div class="mt-12 flex flex-wrap items-center justify-center gap-4 reveal delay-4">
                        <span class="trust-badge">
                            <i class="bi bi-shield-check text-green-500"></i>
                            <span>Terverifikasi</span>
                        </span>
                        <span class="trust-badge">
                            <i class="bi bi-lock-fill text-[var(--color-primary)]"></i>
                            <span>Akses Terkontrol</span>
                        </span>
                        <span class="trust-badge">
                            <i class="bi bi-clock-history text-orange-500"></i>
                            <span>Riwayat Lengkap</span>
                        </span>
                    </div>
                </div>
            </div>

            {{-- Decorative gradient line --}}
            <div
                class="absolute bottom-0 left-0 right-0 h-px bg-gradient-to-r from-transparent via-[var(--color-primary)]/30 to-transparent">
            </div>
        </section>

        {{-- Access Section --}}
        <section id="access" class="relative py-24 bg-[var(--surface-glass)]/50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="max-w-2xl mx-auto text-center mb-16">
                    <h2 class="text-3xl sm:text-4xl font-bold text-[var(--text-primary)] reveal">
                        Jalur Akses
                    </h2>
                    <p class="mt-4 text-lg text-[var(--text-secondary)] reveal delay-1">
                        Pilih jalur sesuai kebutuhan Anda: publik tanpa login atau pegawai dengan akses internal.
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <a href="{{ route('public.documents') }}"
                        class="feature-card access-card access-card-link reveal flex flex-col no-underline text-[var(--text-primary)] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[var(--color-primary)]/50"
                        aria-label="Lihat dokumen publik">
                        <div class="icon-container bg-[var(--color-primary)]/10 text-[var(--color-primary)]">
                            <i class="bi bi-globe2"></i>
                        </div>
                        <h3 class="mt-5 text-xl font-semibold text-[var(--text-primary)]">Publik</h3>
                        <p class="mt-2 text-sm text-[var(--text-secondary)] leading-relaxed">
                            Akses dokumen yang telah dipublikasikan tanpa akun.
                        </p>
                        <ul class="access-list">
                            <li>
                                <i class="bi bi-check-circle text-[var(--color-primary)]"></i>
                                <span>Daftar dokumen publik terbaru</span>
                            </li>
                            <li>
                                <i class="bi bi-check-circle text-[var(--color-primary)]"></i>
                                <span>Pencarian dan filter cepat</span>
                            </li>
                            <li>
                                <i class="bi bi-check-circle text-[var(--color-primary)]"></i>
                                <span>Unduh dokumen publik</span>
                            </li>
                        </ul>
                        <span class="access-link mt-6">
                            Lihat Dokumen Publik
                            <i class="bi bi-arrow-right"></i>
                        </span>
                    </a>

                    <a href="{{ route('login') }}"
                        class="feature-card access-card access-card-link reveal delay-1 flex flex-col no-underline text-[var(--text-primary)] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[var(--color-primary)]/50"
                        aria-label="Masuk ke portal pegawai">
                        <div class="icon-container bg-lime-500/10 text-lime-600 dark:text-lime-400">
                            <i class="bi bi-shield-lock"></i>
                        </div>
                        <h3 class="mt-5 text-xl font-semibold text-[var(--text-primary)]">Pegawai</h3>
                        <p class="mt-2 text-sm text-[var(--text-secondary)] leading-relaxed">
                            Login untuk akses internal dan fitur lengkap.
                        </p>
                        <ul class="access-list">
                            <li>
                                <i class="bi bi-check-circle text-lime-600 dark:text-lime-400"></i>
                                <span>Dokumen internal dan arsip</span>
                            </li>
                            <li>
                                <i class="bi bi-check-circle text-lime-600 dark:text-lime-400"></i>
                                <span>Riwayat revisi dan jejak audit</span>
                            </li>
                            <li>
                                <i class="bi bi-check-circle text-lime-600 dark:text-lime-400"></i>
                                <span>Hak akses berbasis role</span>
                            </li>
                        </ul>
                        <span class="access-link access-link-primary mt-6">
                            Masuk ke Portal
                            <i class="bi bi-arrow-right"></i>
                        </span>
                    </a>
                </div>

                <div class="mt-10 flex flex-wrap items-center justify-center gap-4 reveal delay-2">
                    <span class="trust-badge">
                        <i class="bi bi-clock-history text-orange-500"></i>
                        <span>Akses 24/7</span>
                    </span>
                    <span class="trust-badge">
                        <i class="bi bi-shield-check text-[var(--color-primary)]"></i>
                        <span>Satu Portal Terpusat</span>
                    </span>
                </div>
            </div>
        </section>

        {{-- Features Section --}}
        <section id="features" class="relative py-24">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                {{-- Section Header --}}
                <div class="max-w-2xl mx-auto text-center mb-16">
                    <h2 class="text-3xl sm:text-4xl font-bold text-[var(--text-primary)] reveal">
                        Kelola Dokumen dengan
                        <span class="gradient-text">Mudah</span>
                    </h2>
                    <p class="mt-4 text-lg text-[var(--text-secondary)] reveal delay-1">
                        Sistem manajemen dokumen hukum yang dirancang untuk efisiensi dan kepatuhan.
                    </p>
                </div>

                {{-- Feature Cards --}}
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    {{-- Feature 1 --}}
                    <div class="feature-card reveal">
                        <div class="icon-container bg-[var(--color-primary)]/10 text-[var(--color-primary)]">
                            <i class="bi bi-search"></i>
                        </div>
                        <h3 class="mt-5 text-lg font-semibold text-[var(--text-primary)]">Pencarian Cepat</h3>
                        <p class="mt-2 text-sm text-[var(--text-secondary)] leading-relaxed">
                            Temukan dokumen dalam hitungan detik dengan fitur pencarian canggih dan filter multi-kriteria.
                        </p>
                    </div>

                    {{-- Feature 2 --}}
                    <div class="feature-card reveal delay-1">
                        <div class="icon-container bg-lime-500/10 text-lime-600 dark:text-lime-400">
                            <i class="bi bi-patch-check-fill"></i>
                        </div>
                        <h3 class="mt-5 text-lg font-semibold text-[var(--text-primary)]">Terverifikasi & Resmi</h3>
                        <p class="mt-2 text-sm text-[var(--text-secondary)] leading-relaxed">
                            Setiap dokumen melewati proses review dan approval untuk menjamin validitas hukum.
                        </p>
                    </div>

                    {{-- Feature 3 --}}
                    <div class="feature-card reveal delay-2">
                        <div class="icon-container bg-sky-500/10 text-sky-600 dark:text-sky-400">
                            <i class="bi bi-shield-lock-fill"></i>
                        </div>
                        <h3 class="mt-5 text-lg font-semibold text-[var(--text-primary)]">Kontrol Akses</h3>
                        <p class="mt-2 text-sm text-[var(--text-secondary)] leading-relaxed">
                            Sistem role-based access control memastikan dokumen hanya diakses pihak berwenang.
                        </p>
                    </div>

                    {{-- Feature 4 --}}
                    <div class="feature-card reveal">
                        <div class="icon-container bg-purple-500/10 text-purple-600 dark:text-purple-400">
                            <i class="bi bi-clock-history"></i>
                        </div>
                        <h3 class="mt-5 text-lg font-semibold text-[var(--text-primary)]">Riwayat & Versioning</h3>
                        <p class="mt-2 text-sm text-[var(--text-secondary)] leading-relaxed">
                            Lacak setiap perubahan dengan riwayat revisi lengkap dan kemampuan restore versi.
                        </p>
                    </div>

                    {{-- Feature 5 --}}
                    <div class="feature-card reveal delay-1">
                        <div class="icon-container bg-orange-500/10 text-orange-600 dark:text-orange-400">
                            <i class="bi bi-bell-fill"></i>
                        </div>
                        <h3 class="mt-5 text-lg font-semibold text-[var(--text-primary)]">Notifikasi Otomatis</h3>
                        <p class="mt-2 text-sm text-[var(--text-secondary)] leading-relaxed">
                            Peringatan dini sebelum dokumen kadaluarsa agar perpanjangan tepat waktu.
                        </p>
                    </div>

                    {{-- Feature 6 --}}
                    <div class="feature-card reveal delay-2">
                        <div class="icon-container bg-rose-500/10 text-rose-600 dark:text-rose-400">
                            <i class="bi bi-file-earmark-pdf-fill"></i>
                        </div>
                        <h3 class="mt-5 text-lg font-semibold text-[var(--text-primary)]">Download Aman</h3>
                        <p class="mt-2 text-sm text-[var(--text-secondary)] leading-relaxed">
                            Unduh dokumen dengan watermark untuk melindungi autentisitas dan mencegah penyalahgunaan.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        {{-- How It Works Section --}}
        <section id="how-it-works" class="relative py-24 bg-[var(--surface-glass)]/30">
            <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
                {{-- Section Header --}}
                <div class="text-center mb-16">
                    <h2 class="text-3xl sm:text-4xl font-bold text-[var(--text-primary)] reveal">
                        Bagaimana Cara Kerjanya?
                    </h2>
                    <p class="mt-4 text-lg text-[var(--text-secondary)] reveal delay-1">
                        Publik dapat melihat dokumen tanpa login, pegawai dapat masuk untuk akses internal.
                    </p>
                </div>

                {{-- Vertical Steps with Connectors --}}
                <div class="relative">
                    {{-- Step 1 --}}
                    <div class="flex items-start gap-6 reveal">
                        {{-- Number + Line --}}
                        <div class="flex flex-col items-center">
                            <div
                                class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-gradient-to-br from-[var(--color-primary)] to-[var(--color-lime)] text-white text-xl font-bold shadow-lg shadow-[var(--color-primary)]/30 step-badge">
                                1
                            </div>
                            {{-- Vertical connector with animated dot --}}
                            <div class="connection-line-wrapper relative w-0.5 h-20 mt-4">
                                <div
                                    class="absolute inset-0 bg-gradient-to-b from-[var(--color-lime)] to-[var(--color-primary)] rounded-full opacity-60">
                                </div>
                                <div class="flow-dot"></div>
                            </div>
                        </div>
                        {{-- Content --}}
                        <div class="flex-1 pt-2">
                            <h3 class="text-xl font-semibold text-[var(--text-primary)]">Lihat Dokumen Publik</h3>
                            <p class="mt-2 text-sm text-[var(--text-secondary)] leading-relaxed">
                                Akses daftar dokumen publik tanpa login langsung dari portal. Dokumen yang telah
                                dipublikasikan tersedia untuk umum.
                            </p>
                        </div>
                    </div>

                    {{-- Step 2 --}}
                    <div class="flex items-start gap-6 reveal delay-1">
                        {{-- Number + Line --}}
                        <div class="flex flex-col items-center">
                            <div
                                class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-gradient-to-br from-[var(--color-primary)] to-[var(--color-lime)] text-white text-xl font-bold shadow-lg shadow-[var(--color-primary)]/30 step-badge">
                                2
                            </div>
                            {{-- Vertical connector with animated dot --}}
                            <div class="connection-line-wrapper relative w-0.5 h-20 mt-4">
                                <div
                                    class="absolute inset-0 bg-gradient-to-b from-[var(--color-lime)] to-[var(--color-primary)] rounded-full opacity-60">
                                </div>
                                <div class="flow-dot"></div>
                            </div>
                        </div>
                        {{-- Content --}}
                        <div class="flex-1 pt-2">
                            <h3 class="text-xl font-semibold text-[var(--text-primary)]">Cari Dokumen</h3>
                            <p class="mt-2 text-sm text-[var(--text-secondary)] leading-relaxed">
                                Gunakan fitur pencarian atau filter untuk menemukan dokumen berdasarkan jenis, kategori,
                                atau kata kunci.
                            </p>
                        </div>
                    </div>

                    {{-- Step 3 --}}
                    <div class="flex items-start gap-6 reveal delay-2">
                        {{-- Number (no line after last) --}}
                        <div class="flex flex-col items-center">
                            <div
                                class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-gradient-to-br from-[var(--color-primary)] to-[var(--color-lime)] text-white text-xl font-bold shadow-lg shadow-[var(--color-primary)]/30 step-badge">
                                3
                            </div>
                        </div>
                        {{-- Content --}}
                        <div class="flex-1 pt-2">
                            <h3 class="text-xl font-semibold text-[var(--text-primary)]">Masuk untuk Akses Internal</h3>
                            <p class="mt-2 text-sm text-[var(--text-secondary)] leading-relaxed">
                                Pegawai RSUP Prof. Dr. I.G.N.G. Ngoerah dapat login untuk mengakses dokumen internal dan
                                fitur lengkap.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </section>


        {{-- Security Section --}}
        <section class="relative py-24">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="cta-gradient rounded-3xl p-8 lg:p-12 reveal-scale">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
                        <div>
                            <h2 class="text-3xl font-bold text-[var(--text-primary)]">
                                Keamanan & Kepatuhan
                            </h2>
                            <p class="mt-4 text-[var(--text-secondary)] leading-relaxed">
                                Setiap dokumen dipantau dan dilindungi dengan standar keamanan tinggi untuk menjaga
                                kredibilitas dan kepatuhan regulasi.
                            </p>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                            <div class="flex items-start gap-4">
                                <div
                                    class="flex-shrink-0 w-10 h-10 rounded-lg bg-[var(--color-primary)]/10 flex items-center justify-center">
                                    <i class="bi bi-shield-check text-[var(--color-primary)]"></i>
                                </div>
                                <div>
                                    <h4 class="font-semibold text-[var(--text-primary)]">Audit Trail</h4>
                                    <p class="mt-1 text-sm text-[var(--text-tertiary)]">Setiap aktivitas tercatat dengan
                                        detail.</p>
                                </div>
                            </div>
                            <div class="flex items-start gap-4">
                                <div
                                    class="flex-shrink-0 w-10 h-10 rounded-lg bg-lime-500/10 flex items-center justify-center">
                                    <i class="bi bi-lock text-lime-600 dark:text-lime-400"></i>
                                </div>
                                <div>
                                    <h4 class="font-semibold text-[var(--text-primary)]">Enkripsi Data</h4>
                                    <p class="mt-1 text-sm text-[var(--text-tertiary)]">Data terenkripsi saat transit dan
                                        penyimpanan.</p>
                                </div>
                            </div>
                            <div class="flex items-start gap-4">
                                <div
                                    class="flex-shrink-0 w-10 h-10 rounded-lg bg-sky-500/10 flex items-center justify-center">
                                    <i class="bi bi-cloud-check text-sky-500"></i>
                                </div>
                                <div>
                                    <h4 class="font-semibold text-[var(--text-primary)]">Backup Otomatis</h4>
                                    <p class="mt-1 text-sm text-[var(--text-tertiary)]">Data aman dengan backup berkala.</p>
                                </div>
                            </div>
                            <div class="flex items-start gap-4">
                                <div
                                    class="flex-shrink-0 w-10 h-10 rounded-lg bg-purple-500/10 flex items-center justify-center">
                                    <i class="bi bi-graph-up-arrow text-purple-500"></i>
                                </div>
                                <div>
                                    <h4 class="font-semibold text-[var(--text-primary)]">High Availability</h4>
                                    <p class="mt-1 text-sm text-[var(--text-tertiary)]">99.9% uptime untuk akses kapan saja.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- FAQ Section --}}
        <section id="faq" class="relative py-24 bg-[var(--surface-glass)]/30">
            <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-12">
                    <h2 class="text-3xl font-bold text-[var(--text-primary)] reveal">
                        Pertanyaan Umum
                    </h2>
                    <p class="mt-4 text-[var(--text-secondary)] reveal delay-1">
                        Jawaban untuk pertanyaan yang sering ditanyakan.
                    </p>
                </div>

                <div class="space-y-4">
                    <div class="faq-item reveal">
                        <button class="faq-trigger">
                            <span>Siapa yang dapat mengakses sistem ini?</span>
                            <i class="bi bi-plus-lg faq-icon text-[var(--color-primary)]"></i>
                        </button>
                        <div class="faq-content">
                            <p class="faq-answer">
                                Sistem ini dapat diakses oleh seluruh pegawai RSUP Prof. Dr. I.G.N.G. Ngoerah dengan akun
                                yang telah terdaftar.
                                Dokumen publik dapat diakses tanpa login, sedangkan dokumen internal memerlukan autentikasi.
                            </p>
                        </div>
                    </div>

                    <div class="faq-item reveal delay-1">
                        <button class="faq-trigger">
                            <span>Bagaimana cara mendapatkan akun?</span>
                            <i class="bi bi-plus-lg faq-icon text-[var(--color-primary)]"></i>
                        </button>
                        <div class="faq-content">
                            <p class="faq-answer">
                                Akun dibuat oleh administrator sistem (Unit Hukum dan Humas).
                                Silakan hubungi unit terkait untuk pengajuan akses ke sistem dokumentasi.
                            </p>
                        </div>
                    </div>

                    <div class="faq-item reveal delay-2">
                        <button class="faq-trigger">
                            <span>Apakah saya bisa mengunduh dokumen?</span>
                            <i class="bi bi-plus-lg faq-icon text-[var(--color-primary)]"></i>
                        </button>
                        <div class="faq-content">
                            <p class="faq-answer">
                                Ya, Anda dapat mengunduh dokumen sesuai dengan hak akses role Anda.
                                Dokumen yang diunduh akan memiliki watermark untuk menjaga autentisitas.
                            </p>
                        </div>
                    </div>

                    <div class="faq-item reveal delay-3">
                        <button class="faq-trigger">
                            <span>Bagaimana jika dokumen akan kadaluarsa?</span>
                            <i class="bi bi-plus-lg faq-icon text-[var(--color-primary)]"></i>
                        </button>
                        <div class="faq-content">
                            <p class="faq-answer">
                                Sistem akan mengirimkan notifikasi otomatis kepada admin 6 bulan, 3 bulan, dan 1 bulan
                                sebelum dokumen kadaluarsa agar dapat ditindaklanjuti tepat waktu.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- CTA Section --}}
        <section class="relative py-24">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="cta-gradient rounded-3xl p-8 lg:p-16 text-center reveal-scale">
                    <h2 class="text-3xl sm:text-4xl font-bold text-[var(--text-primary)]">
                        Siap Mengakses Portal?
                    </h2>
                    <p class="mt-4 text-lg text-[var(--text-secondary)] max-w-xl mx-auto">
                        Masuk ke sistem untuk mengelola dan mengakses dokumen hukum rumah sakit dengan mudah.
                    </p>
                    <div class="mt-8 flex flex-col sm:flex-row items-center justify-center gap-4">
                        <a href="{{ route('login') }}" class="btn-primary-landing text-base px-8 py-4">
                            <i class="bi bi-box-arrow-in-right text-lg"></i>
                            Masuk Sekarang
                        </a>
                        <a href="{{ route('public.documents') }}" class="btn-secondary-landing text-base px-8 py-4">
                            <i class="bi bi-files text-lg"></i>
                            Lihat Dokumen Publik
                        </a>
                    </div>
                </div>
            </div>
        </section>

        {{-- Footer --}}
        <footer class="relative border-t border-[var(--surface-glass-border)] bg-[var(--surface-glass)]/60">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                    {{-- Brand --}}
                    <div class="md:col-span-2">
                        <a href="{{ route('landing') }}" class="flex items-center gap-3">
                            <img src="{{ asset('images/logo kemenkes.png') }}" alt="Kemenkes" class="h-10 w-10">
                            <div>
                                <div class="text-base font-bold text-[var(--text-primary)]">Hukum Ngoerah</div>
                                <div class="text-xs text-[var(--text-tertiary)]">Portal Dokumentasi</div>
                            </div>
                        </a>
                        <p class="mt-4 text-sm text-[var(--text-tertiary)] max-w-sm">
                            Sistem Manajemen Dokumen Hukum Terpusat untuk mendukung transparansi,
                            akuntabilitas, dan layanan publik yang terpercaya.
                        </p>
                    </div>

                    {{-- Quick Links --}}
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
                                <a href="#features"
                                    class="text-sm text-[var(--text-tertiary)] hover:text-[var(--color-primary)] transition-colors">
                                    Fitur
                                </a>
                            </li>
                            <li>
                                <a href="#faq"
                                    class="text-sm text-[var(--text-tertiary)] hover:text-[var(--color-primary)] transition-colors">
                                    FAQ
                                </a>
                            </li>
                        </ul>
                    </div>

                    {{-- Contact --}}
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

                {{-- Copyright --}}
                <div
                    class="mt-12 pt-8 border-t border-[var(--surface-glass-border)] flex flex-col sm:flex-row items-center justify-between gap-4">
                    <p class="text-sm text-[var(--text-tertiary)]">
                        © {{ date('Y') }} RSUP Prof. Dr. I.G.N.G. Ngoerah. Hak cipta dilindungi.
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
