@extends('public.panduan-layout')

@section('title', 'Panduan')

@section('panduan-content')
    <section class="feature-card reveal">
        <div class="flex items-start gap-4">
            <div class="icon-container bg-[var(--color-primary)]/10 text-[var(--color-primary)]">
                <i class="bi bi-journal-text"></i>
            </div>
            <div>
                <h2 class="text-xl font-semibold text-[var(--text-primary)]">Ringkasan panduan</h2>
                <p class="mt-2 text-sm text-[var(--text-secondary)]">
                    Pilih topik di samping untuk detail langkah. Setiap topik berada di halaman terpisah agar panduan
                    tetap ringkas dan mudah diikuti.
                </p>
                <div class="mt-4 flex flex-wrap gap-3">
                    <a href="{{ route('panduan.jalur-akses') }}" class="btn-primary-landing text-sm">
                        <i class="bi bi-diagram-3"></i>
                        Mulai dari jalur akses
                    </a>
                    <a href="{{ route('public.documents') }}" class="btn-secondary-landing text-sm">
                        <i class="bi bi-files"></i>
                        Lihat dokumen publik
                    </a>
                </div>
            </div>
        </div>
    </section>

    <section class="feature-card reveal delay-1">
        <div class="flex items-start gap-4">
            <div class="icon-container bg-lime-500/10 text-lime-600 dark:text-lime-400">
                <i class="bi bi-collection"></i>
            </div>
            <div class="flex-1">
                <h2 class="text-xl font-semibold text-[var(--text-primary)]">Topik panduan</h2>
                <p class="mt-2 text-sm text-[var(--text-secondary)]">
                    Buka topik sesuai kebutuhan Anda. Klik kartu untuk menuju halaman panduan lengkap.
                </p>
                <div class="mt-5 grid gap-4 md:grid-cols-2">
                    <a href="{{ route('panduan.persiapan-awal') }}"
                        class="rounded-xl border border-[var(--surface-glass-border)] bg-[var(--surface-glass)] p-4 hover:border-[var(--color-primary)] transition-colors">
                        <div class="flex items-center gap-3">
                            <div
                                class="w-10 h-10 rounded-lg bg-[var(--color-primary)]/10 flex items-center justify-center text-[var(--color-primary)]">
                                <i class="bi bi-gear"></i>
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-[var(--text-primary)]">Persiapan awal</p>
                                <p class="text-xs text-[var(--text-tertiary)]">Perangkat & akun</p>
                            </div>
                        </div>
                        <p class="mt-3 text-xs text-[var(--text-secondary)]">
                            Cek perangkat, koneksi, dan kesiapan akun sebelum mulai.
                        </p>
                    </a>
                    <a href="{{ route('panduan.jalur-akses') }}"
                        class="rounded-xl border border-[var(--surface-glass-border)] bg-[var(--surface-glass)] p-4 hover:border-[var(--color-primary)] transition-colors">
                        <div class="flex items-center gap-3">
                            <div
                                class="w-10 h-10 rounded-lg bg-lime-500/10 flex items-center justify-center text-lime-600 dark:text-lime-400">
                                <i class="bi bi-diagram-3"></i>
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-[var(--text-primary)]">Jalur akses</p>
                                <p class="text-xs text-[var(--text-tertiary)]">Publik & pegawai</p>
                            </div>
                        </div>
                        <p class="mt-3 text-xs text-[var(--text-secondary)]">
                            Tentukan jalur akses sesuai kebutuhan dokumen Anda.
                        </p>
                    </a>
                    <a href="{{ route('panduan.login-pegawai') }}"
                        class="rounded-xl border border-[var(--surface-glass-border)] bg-[var(--surface-glass)] p-4 hover:border-[var(--color-primary)] transition-colors">
                        <div class="flex items-center gap-3">
                            <div
                                class="w-10 h-10 rounded-lg bg-sky-500/10 flex items-center justify-center text-sky-600 dark:text-sky-400">
                                <i class="bi bi-person-badge"></i>
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-[var(--text-primary)]">Login pegawai</p>
                                <p class="text-xs text-[var(--text-tertiary)]">Akses internal</p>
                            </div>
                        </div>
                        <p class="mt-3 text-xs text-[var(--text-secondary)]">
                            Ikuti langkah login dan tips keamanan akun pegawai.
                        </p>
                    </a>
                    <a href="{{ route('panduan.pencarian-filter') }}"
                        class="rounded-xl border border-[var(--surface-glass-border)] bg-[var(--surface-glass)] p-4 hover:border-[var(--color-primary)] transition-colors">
                        <div class="flex items-center gap-3">
                            <div
                                class="w-10 h-10 rounded-lg bg-purple-500/10 flex items-center justify-center text-purple-600 dark:text-purple-400">
                                <i class="bi bi-search"></i>
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-[var(--text-primary)]">Pencarian dan filter</p>
                                <p class="text-xs text-[var(--text-tertiary)]">Hasil cepat</p>
                            </div>
                        </div>
                        <p class="mt-3 text-xs text-[var(--text-secondary)]">
                            Gunakan kata kunci, filter, dan sortir untuk hasil tepat.
                        </p>
                    </a>
                    <a href="{{ route('panduan.unduh-dokumen') }}"
                        class="rounded-xl border border-[var(--surface-glass-border)] bg-[var(--surface-glass)] p-4 hover:border-[var(--color-primary)] transition-colors">
                        <div class="flex items-center gap-3">
                            <div
                                class="w-10 h-10 rounded-lg bg-orange-500/10 flex items-center justify-center text-orange-600 dark:text-orange-400">
                                <i class="bi bi-download"></i>
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-[var(--text-primary)]">Unduh dokumen</p>
                                <p class="text-xs text-[var(--text-tertiary)]">Hak akses</p>
                            </div>
                        </div>
                        <p class="mt-3 text-xs text-[var(--text-secondary)]">
                            Pahami aturan unduh, watermark, dan keamanan dokumen.
                        </p>
                    </a>
                    <a href="{{ route('panduan.bantuan') }}"
                        class="rounded-xl border border-[var(--surface-glass-border)] bg-[var(--surface-glass)] p-4 hover:border-[var(--color-primary)] transition-colors">
                        <div class="flex items-center gap-3">
                            <div
                                class="w-10 h-10 rounded-lg bg-rose-500/10 flex items-center justify-center text-rose-600 dark:text-rose-400">
                                <i class="bi bi-life-preserver"></i>
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-[var(--text-primary)]">Bantuan</p>
                                <p class="text-xs text-[var(--text-tertiary)]">Kontak dukungan</p>
                            </div>
                        </div>
                        <p class="mt-3 text-xs text-[var(--text-secondary)]">
                            Hubungi tim terkait jika mengalami kendala akses.
                        </p>
                    </a>
                </div>
            </div>
        </div>
    </section>
@endsection
