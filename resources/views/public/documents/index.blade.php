@extends('layouts.public')

@section('title', 'Dokumen Publik')

@push('styles')
    @vite(['resources/css/landing-animations.css'])
@endpush

@section('content')
    {{-- Background Image with Overlay --}}
    <div class="bg-image-overlay">
        <img src="{{ asset('images/rsup-ngoerah.jpg') }}" alt="" aria-hidden="true" onerror="this.style.display='none'">
    </div>

    <div class="relative min-h-screen overflow-hidden hero-gradient-bg">
        {{-- Background decorative elements --}}
        <div class="absolute inset-0 hero-grid pointer-events-none"></div>

        {{-- Animated blobs --}}
        <div class="blob blob-primary blob-animate absolute -top-32 -right-32 w-72 h-72 opacity-20" data-parallax="0.3">
        </div>
        <div class="blob blob-lime blob-animate-reverse absolute top-1/3 -left-32 w-96 h-96 opacity-15" data-parallax="0.2">
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
                    <a href="{{ route('landing') }}#features" class="nav-link">Fitur</a>
                    <a href="{{ route('landing') }}#stats" class="nav-link">Statistik</a>
                    <a href="{{ route('public.documents') }}" class="nav-link active">Dokumen</a>
                    <a href="{{ route('landing') }}#faq" class="nav-link">FAQ</a>
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
                    <button type="button" class="mobile-menu-btn md:hidden" aria-label="Menu">
                        <i class="bi bi-list text-lg"></i>
                    </button>
                </div>
            </nav>
        </div>

        {{-- Hero Mini Section --}}
        <section class="relative pt-28 pb-12">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                {{-- Breadcrumb --}}
                <div class="reveal">
                    <a href="{{ route('landing') }}"
                        class="inline-flex items-center gap-2 text-sm text-[var(--text-tertiary)] hover:text-[var(--color-primary)] transition-colors">
                        <i class="bi bi-arrow-left"></i>
                        Kembali ke beranda
                    </a>
                </div>

                {{-- Title --}}
                <h1 class="mt-4 text-3xl sm:text-4xl font-bold text-[var(--text-primary)] reveal delay-1">
                    Dokumen <span class="gradient-text">Publik</span>
                </h1>
                <p class="mt-3 text-[var(--text-secondary)] max-w-2xl reveal delay-2">
                    Daftar dokumen yang dapat diakses publik dan sudah dipublikasikan oleh RSUP Prof. Dr. I.G.N.G. Ngoerah.
                </p>

                {{-- Search & Filter --}}
                <form action="{{ route('public.documents') }}" method="GET"
                    class="mt-8 flex flex-col sm:flex-row gap-3 reveal delay-3">
                    <div class="relative flex-1 max-w-md">
                        <i class="bi bi-search absolute left-4 top-1/2 -translate-y-1/2 text-[var(--text-tertiary)]"></i>
                        <input type="text" name="search" value="{{ $search }}"
                            class="w-full pl-11 pr-4 py-3 bg-[var(--surface-glass)] border border-[var(--surface-glass-border)] rounded-xl text-sm text-[var(--text-primary)] placeholder-[var(--text-tertiary)] focus:outline-none focus:border-[var(--color-primary)] focus:ring-2 focus:ring-[var(--color-primary)]/20 transition-all"
                            placeholder="Cari judul atau nomor dokumen...">
                    </div>
                    <button type="submit" class="btn-primary-landing">
                        <i class="bi bi-search"></i>
                        Cari
                    </button>
                </form>

                {{-- Meta Info --}}
                <div class="mt-6 flex flex-wrap items-center justify-between gap-4 text-sm reveal delay-4">
                    <span class="text-[var(--text-tertiary)]">
                        <i class="bi bi-files mr-1"></i>
                        Menampilkan <strong class="text-[var(--text-primary)]">{{ $documents->total() }}</strong> dokumen
                    </span>
                    <a href="{{ route('login') }}"
                        class="inline-flex items-center gap-2 text-[var(--color-primary)] hover:opacity-80 transition-opacity">
                        <i class="bi bi-lock"></i>
                        Masuk untuk akses internal
                    </a>
                </div>
            </div>
        </section>

        {{-- Decorative gradient line --}}
        <div class="h-px bg-gradient-to-r from-transparent via-[var(--color-primary)]/30 to-transparent"></div>

        {{-- Documents Grid --}}
        <section class="py-12">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @forelse ($documents as $index => $document)
                        @php
                            $expiryInfo = app(\App\Services\DocumentStatusService::class)->getExpiryInfo($document);
                            $statusType = match ($expiryInfo['status']) {
                                'expired' => 'expired',
                                'critical' => 'critical',
                                'warning' => 'warning',
                                'attention' => 'attention',
                                default => 'success',
                            };
                            $delayClass = 'delay-' . (($index % 6) + 1);
                        @endphp

                        <div class="doc-card reveal {{ $delayClass }}">
                            {{-- Card Header --}}
                            <div class="flex items-start justify-between gap-3">
                                <div class="flex-1 min-w-0">
                                    <p class="text-xs font-medium text-[var(--color-primary)]">
                                        {{ $document->documentType?->name ?? 'Dokumen' }}
                                    </p>
                                    <h3 class="mt-1.5 text-base font-semibold text-[var(--text-primary)] line-clamp-2">
                                        {{ $document->title }}
                                    </h3>
                                </div>
                                <div class="flex-shrink-0">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium 
                                                        {{ $statusType === 'success' ? 'bg-green-500/10 text-green-600 dark:text-green-400' : '' }}
                                                        {{ $statusType === 'warning' ? 'bg-yellow-500/10 text-yellow-600 dark:text-yellow-400' : '' }}
                                                        {{ $statusType === 'expired' ? 'bg-red-500/10 text-red-600 dark:text-red-400' : '' }}
                                                        {{ $statusType === 'critical' ? 'bg-red-500/10 text-red-600 dark:text-red-400' : '' }}
                                                        {{ $statusType === 'attention' ? 'bg-orange-500/10 text-orange-600 dark:text-orange-400' : '' }}
                                                    ">
                                        {{ $expiryInfo['label'] }}
                                    </span>
                                </div>
                            </div>

                            {{-- Document Number --}}
                            <p class="mt-3 text-xs text-[var(--text-tertiary)] font-mono">
                                {{ $document->document_number }}
                            </p>

                            {{-- Card Footer --}}
                            <div
                                class="mt-4 pt-4 border-t border-[var(--surface-glass-border)] flex items-center justify-between text-xs text-[var(--text-tertiary)]">
                                <span class="flex items-center gap-1.5">
                                    <i class="bi bi-calendar-check"></i>
                                    {{ $document->published_at?->format('d M Y') ?? '-' }}
                                </span>
                                <span class="flex items-center gap-1.5">
                                    <i class="bi bi-clock"></i>
                                    {{ $expiryInfo['days_text'] }}
                                </span>
                            </div>

                            {{-- Hover gradient border effect --}}
                            <div class="absolute inset-0 rounded-2xl opacity-0 transition-opacity pointer-events-none
                                                        border border-transparent bg-gradient-to-br from-[var(--color-primary)]/20 to-[var(--color-lime)]/20
                                                        group-hover:opacity-100"></div>
                        </div>
                    @empty
                        {{-- Empty State --}}
                        <div class="md:col-span-2 lg:col-span-3 reveal">
                            <div
                                class="text-center py-16 px-8 bg-[var(--surface-glass)] border border-[var(--surface-glass-border)] rounded-2xl">
                                <div
                                    class="mx-auto w-16 h-16 rounded-2xl bg-[var(--color-primary)]/10 flex items-center justify-center text-[var(--color-primary)] text-2xl">
                                    <i class="bi bi-folder2-open"></i>
                                </div>
                                <h3 class="mt-6 text-lg font-semibold text-[var(--text-primary)]">
                                    Belum Ada Dokumen
                                </h3>
                                <p class="mt-2 text-sm text-[var(--text-secondary)] max-w-sm mx-auto">
                                    Belum ada dokumen publik yang tersedia saat ini. Silakan cek kembali nanti.
                                </p>
                                <a href="{{ route('landing') }}"
                                    class="mt-6 inline-flex items-center gap-2 text-sm font-medium text-[var(--color-primary)] hover:opacity-80 transition-opacity">
                                    <i class="bi bi-arrow-left"></i>
                                    Kembali ke beranda
                                </a>
                            </div>
                        </div>
                    @endforelse
                </div>

                {{-- Pagination --}}
                @if($documents->hasPages())
                    <div class="mt-12 reveal">
                        {{ $documents->links('pagination.glass') }}
                    </div>
                @endif
            </div>
        </section>

        {{-- Footer --}}
        <footer class="relative border-t border-[var(--surface-glass-border)] bg-[var(--surface-glass)]/60 mt-auto">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                    {{-- Brand --}}
                    <a href="{{ route('landing') }}" class="flex items-center gap-2">
                        <img src="{{ asset('images/logo kemenkes.png') }}" alt="Kemenkes" class="h-8 w-8">
                        <div>
                            <div class="text-sm font-bold text-[var(--text-primary)]">Hukum Ngoerah</div>
                            <div class="text-xs text-[var(--text-tertiary)]">Portal Dokumentasi</div>
                        </div>
                    </a>

                    {{-- Copyright --}}
                    <p class="text-xs text-[var(--text-tertiary)]">
                        © {{ date('Y') }} RSUP Prof. Dr. I.G.N.G. Ngoerah. Hak cipta dilindungi.
                    </p>
                </div>
            </div>
        </footer>
    </div>
@endsection

@push('scripts')
    @vite(['resources/js/landing.js'])
@endpush