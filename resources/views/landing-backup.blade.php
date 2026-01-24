@extends('layouts.public')

@section('title', 'Beranda')

@section('content')
<div class="relative overflow-hidden">
    <div class="pointer-events-none absolute -top-40 right-[-6rem] h-72 w-72 rounded-full bg-[var(--color-primary)]/20 blur-3xl"></div>
    <div class="pointer-events-none absolute top-1/3 left-[-8rem] h-80 w-80 rounded-full bg-[var(--color-lime)]/20 blur-3xl"></div>

    @include('public.partials.nav')

    <main class="relative z-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 sm:py-14">
            <section class="relative overflow-hidden rounded-3xl border border-[var(--surface-glass-border)] shadow-[0_24px_80px_rgba(15,23,42,0.25)]">
                <div class="absolute inset-0 bg-cover bg-center" style="background-image: url('{{ asset('images/foto-rs.jpg') }}');"></div>
                <div class="absolute inset-0 bg-gradient-to-br from-slate-950/80 via-slate-900/60 to-slate-950/90"></div>
                <div class="relative z-10 p-8 sm:p-12 lg:p-16">
                    <div class="max-w-3xl landing-reveal">
                        <span class="inline-flex items-center gap-2 rounded-full bg-white/10 px-4 py-1 text-xs font-semibold uppercase tracking-[0.2em] text-white">
                            Sistem Dokumen Publik
                        </span>
                        <h1 class="mt-4 text-3xl sm:text-4xl lg:text-5xl font-semibold tracking-tight text-white">
                            Akses dokumen hukum RS Ngoerah dengan cepat, aman, dan terpercaya.
                        </h1>
                        <p class="mt-4 text-base sm:text-lg text-slate-200 leading-relaxed">
                            Jelajahi dokumen publik yang sudah diverifikasi, lengkap dengan status, masa berlaku, dan riwayat pembaruan.
                        </p>

                        <form class="mt-6 flex flex-col sm:flex-row gap-3 landing-reveal landing-reveal-delay-1" action="{{ route('public.documents') }}" method="GET">
                            <div class="flex-1">
                                <input type="text" name="search" value="" class="glass-input w-full" placeholder="Cari judul atau nomor dokumen publik">
                            </div>
                            <x-button type="submit">
                                Lihat Dokumen Publik
                            </x-button>
                        </form>

                        <div class="mt-6 flex flex-wrap items-center gap-4 text-xs text-slate-200 landing-reveal landing-reveal-delay-2">
                            <div class="inline-flex items-center gap-2 rounded-full bg-white/10 px-3 py-1">
                                <i class="bi bi-shield-check"></i>
                                Dokumen terverifikasi
                            </div>
                            <div class="inline-flex items-center gap-2 rounded-full bg-white/10 px-3 py-1">
                                <i class="bi bi-arrow-repeat"></i>
                                Update berkala
                            </div>
                            <div class="inline-flex items-center gap-2 rounded-full bg-white/10 px-3 py-1">
                                <i class="bi bi-clock-history"></i>
                                Riwayat revisi
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section class="mt-12 grid grid-cols-1 md:grid-cols-3 gap-6">
                <x-glass-card :hover="false" class="p-6 landing-reveal">
                    <div class="flex items-start gap-4">
                        <div class="h-12 w-12 rounded-xl bg-primary-500/10 text-primary-500 flex items-center justify-center">
                            <i class="bi bi-search"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-[var(--text-primary)]">Pencarian Cepat</h3>
                            <p class="mt-2 text-sm text-[var(--text-secondary)]">
                                Temukan dokumen publik berdasarkan judul, nomor, atau kata kunci.
                            </p>
                        </div>
                    </div>
                </x-glass-card>
                <x-glass-card :hover="false" class="p-6 landing-reveal landing-reveal-delay-1">
                    <div class="flex items-start gap-4">
                        <div class="h-12 w-12 rounded-xl bg-lime-500/10 text-lime-600 dark:text-lime-400 flex items-center justify-center">
                            <i class="bi bi-patch-check"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-[var(--text-primary)]">Terverifikasi</h3>
                            <p class="mt-2 text-sm text-[var(--text-secondary)]">
                                Setiap dokumen sudah melewati proses review dan publikasi resmi.
                            </p>
                        </div>
                    </div>
                </x-glass-card>
                <x-glass-card :hover="false" class="p-6 landing-reveal landing-reveal-delay-2">
                    <div class="flex items-start gap-4">
                        <div class="h-12 w-12 rounded-xl bg-sky-500/10 text-sky-500 flex items-center justify-center">
                            <i class="bi bi-shield-lock"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-[var(--text-primary)]">Kontrol Akses</h3>
                            <p class="mt-2 text-sm text-[var(--text-secondary)]">
                                Dokumen internal tetap terlindungi, hanya yang publik ditampilkan di sini.
                            </p>
                        </div>
                    </div>
                </x-glass-card>
            </section>

            <section class="mt-14">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                    <div class="landing-reveal">
                        <h2 class="text-2xl font-semibold text-[var(--text-primary)]">Bagaimana Cara Kerjanya</h2>
                        <p class="text-sm text-[var(--text-secondary)]">Alur sederhana untuk menemukan dokumen publik.</p>
                    </div>
                </div>
                <div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-6">
                    <x-glass-card :hover="false" class="p-6 landing-reveal">
                        <div class="flex items-center gap-3">
                            <span class="h-10 w-10 rounded-full bg-primary-500/10 text-primary-500 flex items-center justify-center font-semibold">1</span>
                            <h3 class="text-base font-semibold text-[var(--text-primary)]">Cari Dokumen</h3>
                        </div>
                        <p class="mt-3 text-sm text-[var(--text-secondary)]">
                            Gunakan pencarian untuk menemukan dokumen hukum yang Anda perlukan.
                        </p>
                    </x-glass-card>
                    <x-glass-card :hover="false" class="p-6 landing-reveal landing-reveal-delay-1">
                        <div class="flex items-center gap-3">
                            <span class="h-10 w-10 rounded-full bg-primary-500/10 text-primary-500 flex items-center justify-center font-semibold">2</span>
                            <h3 class="text-base font-semibold text-[var(--text-primary)]">Baca Ringkasan</h3>
                        </div>
                        <p class="mt-3 text-sm text-[var(--text-secondary)]">
                            Lihat status, masa berlaku, dan informasi penting dalam sekali lihat.
                        </p>
                    </x-glass-card>
                    <x-glass-card :hover="false" class="p-6 landing-reveal landing-reveal-delay-2">
                        <div class="flex items-center gap-3">
                            <span class="h-10 w-10 rounded-full bg-primary-500/10 text-primary-500 flex items-center justify-center font-semibold">3</span>
                            <h3 class="text-base font-semibold text-[var(--text-primary)]">Akses Dokumen</h3>
                        </div>
                        <p class="mt-3 text-sm text-[var(--text-secondary)]">
                            Akses dokumen publik atau login untuk dokumen internal sesuai peran Anda.
                        </p>
                    </x-glass-card>
                </div>
            </section>

            <section class="mt-14">
                <x-glass-card :hover="false" class="p-8 landing-reveal">
                    <div class="flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
                        <div>
                            <h2 class="text-2xl font-semibold text-[var(--text-primary)]">Keamanan dan Kepatuhan</h2>
                            <p class="mt-2 text-sm text-[var(--text-secondary)]">
                                Setiap dokumen dipantau dan dilindungi untuk menjaga kredibilitas informasi publik.
                            </p>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div class="flex items-start gap-3">
                                <i class="bi bi-shield-check text-primary-500"></i>
                                <div>
                                    <p class="text-sm font-semibold text-[var(--text-primary)]">Audit trail</p>
                                    <p class="text-xs text-[var(--text-tertiary)]">Riwayat perubahan tercatat.</p>
                                </div>
                            </div>
                            <div class="flex items-start gap-3">
                                <i class="bi bi-lock text-primary-500"></i>
                                <div>
                                    <p class="text-sm font-semibold text-[var(--text-primary)]">Kontrol akses</p>
                                    <p class="text-xs text-[var(--text-tertiary)]">Hanya dokumen publik tampil.</p>
                                </div>
                            </div>
                            <div class="flex items-start gap-3">
                                <i class="bi bi-cloud-check text-primary-500"></i>
                                <div>
                                    <p class="text-sm font-semibold text-[var(--text-primary)]">Backup & arsip</p>
                                    <p class="text-xs text-[var(--text-tertiary)]">Data aman dan terarsip.</p>
                                </div>
                            </div>
                            <div class="flex items-start gap-3">
                                <i class="bi bi-graph-up-arrow text-primary-500"></i>
                                <div>
                                    <p class="text-sm font-semibold text-[var(--text-primary)]">Siap skala besar</p>
                                    <p class="text-xs text-[var(--text-tertiary)]">Dirancang untuk ribuan pengguna.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </x-glass-card>
            </section>

            <section class="mt-14">
                <div class="flex items-center justify-between">
                    <div class="landing-reveal">
                        <h2 class="text-2xl font-semibold text-[var(--text-primary)]">Preview Dokumen Publik</h2>
                        <p class="text-sm text-[var(--text-secondary)]">Contoh tampilan dokumen yang tersedia untuk publik.</p>
                    </div>
                    <a href="{{ route('public.documents') }}" class="text-sm text-primary-500 hover:text-primary-600">
                        Lihat semua
                    </a>
                </div>
                <div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-6">
                    <x-glass-card :hover="false" class="p-6 landing-reveal">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <p class="text-xs text-[var(--text-tertiary)]">Perjanjian Kerjasama</p>
                                <h3 class="mt-1 text-base font-semibold text-[var(--text-primary)]">Kemitraan Pelayanan Kesehatan</h3>
                                <p class="mt-2 text-xs text-[var(--text-tertiary)]">No. 014/RSN/PKS/2024</p>
                            </div>
                            <x-badge type="success" size="sm">Publik</x-badge>
                        </div>
                        <div class="mt-4 flex items-center justify-between text-xs text-[var(--text-tertiary)]">
                            <span>Berlaku sampai 30 Des 2026</span>
                            <span>Aktif</span>
                        </div>
                    </x-glass-card>
                    <x-glass-card :hover="false" class="p-6 landing-reveal landing-reveal-delay-1">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <p class="text-xs text-[var(--text-tertiary)]">Surat Keputusan</p>
                                <h3 class="mt-1 text-base font-semibold text-[var(--text-primary)]">SK Standar Layanan Publik</h3>
                                <p class="mt-2 text-xs text-[var(--text-tertiary)]">No. 221/RSN/SK/2025</p>
                            </div>
                            <x-badge type="info" size="sm">Publik</x-badge>
                        </div>
                        <div class="mt-4 flex items-center justify-between text-xs text-[var(--text-tertiary)]">
                            <span>Berlaku tanpa batas</span>
                            <span>Terbaru</span>
                        </div>
                    </x-glass-card>
                    <x-glass-card :hover="false" class="p-6 landing-reveal landing-reveal-delay-2">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <p class="text-xs text-[var(--text-tertiary)]">SOP</p>
                                <h3 class="mt-1 text-base font-semibold text-[var(--text-primary)]">SOP Pelayanan Rawat Jalan</h3>
                                <p class="mt-2 text-xs text-[var(--text-tertiary)]">No. 089/RSN/SOP/2024</p>
                            </div>
                            <x-badge type="success" size="sm">Publik</x-badge>
                        </div>
                        <div class="mt-4 flex items-center justify-between text-xs text-[var(--text-tertiary)]">
                            <span>Review 01 Apr 2026</span>
                            <span>Revisi 2</span>
                        </div>
                    </x-glass-card>
                </div>
            </section>

            <section class="mt-14">
                <div class="landing-reveal">
                    <h2 class="text-2xl font-semibold text-[var(--text-primary)]">Pertanyaan Umum</h2>
                    <p class="text-sm text-[var(--text-secondary)]">Jawaban singkat untuk pertanyaan yang sering muncul.</p>
                </div>
                <div class="mt-6 grid grid-cols-1 lg:grid-cols-2 gap-4">
                    <details class="glass-card-static p-5 landing-reveal">
                        <summary class="cursor-pointer text-sm font-semibold text-[var(--text-primary)]">Apa yang dimaksud dokumen publik?</summary>
                        <p class="mt-2 text-sm text-[var(--text-secondary)]">
                            Dokumen publik adalah dokumen hukum yang sudah disetujui dan dapat diakses masyarakat luas.
                        </p>
                    </details>
                    <details class="glass-card-static p-5 landing-reveal landing-reveal-delay-1">
                        <summary class="cursor-pointer text-sm font-semibold text-[var(--text-primary)]">Apakah saya bisa mengunduh dokumen?</summary>
                        <p class="mt-2 text-sm text-[var(--text-secondary)]">
                            Dokumen publik dapat diakses langsung, sementara dokumen internal memerlukan login.
                        </p>
                    </details>
                    <details class="glass-card-static p-5 landing-reveal">
                        <summary class="cursor-pointer text-sm font-semibold text-[var(--text-primary)]">Bagaimana meminta akses dokumen internal?</summary>
                        <p class="mt-2 text-sm text-[var(--text-secondary)]">
                            Silakan masuk menggunakan akun pegawai dan ajukan akses melalui unit terkait.
                        </p>
                    </details>
                    <details class="glass-card-static p-5 landing-reveal landing-reveal-delay-1">
                        <summary class="cursor-pointer text-sm font-semibold text-[var(--text-primary)]">Seberapa sering dokumen diperbarui?</summary>
                        <p class="mt-2 text-sm text-[var(--text-secondary)]">
                            Dokumen dipantau secara berkala dan diperbarui sesuai jadwal revisi.
                        </p>
                    </details>
                </div>
            </section>

            <section class="mt-16">
                <x-glass-card :hover="false" class="p-8 landing-reveal">
                    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
                        <div>
                            <h2 class="text-2xl font-semibold text-[var(--text-primary)]">Siap menjelajahi dokumen publik?</h2>
                            <p class="mt-2 text-sm text-[var(--text-secondary)]">
                                Mulai dengan melihat dokumen publik atau masuk untuk akses penuh sesuai peran Anda.
                            </p>
                        </div>
                        <div class="flex flex-col sm:flex-row gap-3">
                            <x-button href="{{ route('public.documents') }}">Lihat Dokumen Publik</x-button>
                            <x-button href="{{ route('login') }}" variant="secondary">Masuk</x-button>
                        </div>
                    </div>
                </x-glass-card>
            </section>
        </div>
    </main>

    <footer class="border-t border-[var(--surface-glass-border)] bg-[var(--surface-glass)]/60">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 text-xs text-[var(--text-tertiary)] flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
            <span>RS Ngoerah Legal Document Center</span>
            <span>Transparansi, akuntabilitas, dan layanan publik yang terpercaya.</span>
        </div>
    </footer>
</div>
@endsection
