@extends('public.panduan-layout')

@section('title', 'Panduan - Jalur akses')

@section('panduan-content')
<section id="jalur-akses" class="feature-card reveal delay-1">
    <div class="flex items-start gap-4">
        <div class="icon-container bg-lime-500/10 text-lime-600 dark:text-lime-400">
            <i class="bi bi-diagram-3"></i>
        </div>
        <div>
            <h2 class="text-xl font-semibold text-[var(--text-primary)]">Jalur akses</h2>
            <p class="mt-2 text-sm text-[var(--text-secondary)]">
                Tersedia dua jalur akses sesuai kebutuhan Anda: publik tanpa login atau pegawai
                dengan akses internal.
            </p>

            <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-4">
                <a href="{{ route('public.documents') }}"
                    class="rounded-xl border border-[var(--surface-glass-border)] bg-[var(--surface-glass)] p-4 hover:border-[var(--color-primary)] transition-colors">
                    <div class="flex items-center gap-3">
                        <div
                            class="w-10 h-10 rounded-lg bg-[var(--color-primary)]/10 flex items-center justify-center text-[var(--color-primary)]">
                            <i class="bi bi-globe2"></i>
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-[var(--text-primary)]">Publik</p>
                            <p class="text-xs text-[var(--text-tertiary)]">Tanpa login</p>
                        </div>
                    </div>
                    <p class="mt-3 text-xs text-[var(--text-secondary)]">
                        Lihat daftar dokumen yang sudah dipublikasikan dan dapat diakses.
                    </p>
                    <ul class="mt-3 space-y-2 text-xs text-[var(--text-secondary)]">
                        <li class="flex items-start gap-2">
                            <i class="bi bi-check-circle text-[var(--color-primary)]"></i>
                            Akses daftar dokumen yang berstatus publik.
                        </li>
                        <li class="flex items-start gap-2">
                            <i class="bi bi-check-circle text-[var(--color-primary)]"></i>
                            Gunakan pencarian berdasarkan judul atau nomor dokumen.
                        </li>
                        <li class="flex items-start gap-2">
                            <i class="bi bi-check-circle text-[var(--color-primary)]"></i>
                            Unduhan mengikuti kebijakan akses yang berlaku.
                        </li>
                    </ul>
                    <div
                        class="mt-4 rounded-lg border border-[var(--surface-glass-border)] bg-[var(--surface-glass-elevated)] p-3 text-xs text-[var(--text-secondary)]">
                        <div class="text-xs font-semibold text-[var(--text-primary)]">Langkah cepat</div>
                        <ol class="mt-2 space-y-1 list-decimal list-inside">
                            <li>Masuk ke halaman Dokumen Publik.</li>
                            <li>Gunakan pencarian untuk mempersempit hasil.</li>
                            <li>Catat informasi dokumen yang dibutuhkan.</li>
                        </ol>
                    </div>
                </a>

                <a href="{{ route('login') }}"
                    class="rounded-xl border border-[var(--surface-glass-border)] bg-[var(--surface-glass)] p-4 hover:border-[var(--color-primary)] transition-colors">
                    <div class="flex items-center gap-3">
                        <div
                            class="w-10 h-10 rounded-lg bg-lime-500/10 flex items-center justify-center text-lime-600 dark:text-lime-400">
                            <i class="bi bi-shield-lock"></i>
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-[var(--text-primary)]">Pegawai</p>
                            <p class="text-xs text-[var(--text-tertiary)]">Login internal</p>
                        </div>
                    </div>
                    <p class="mt-3 text-xs text-[var(--text-secondary)]">
                        Akses arsip internal, revisi, dan fitur pengelolaan dokumen.
                    </p>
                    <ul class="mt-3 space-y-2 text-xs text-[var(--text-secondary)]">
                        <li class="flex items-start gap-2">
                            <i class="bi bi-check-circle text-lime-600 dark:text-lime-400"></i>
                            Login diperlukan untuk membuka menu internal.
                        </li>
                        <li class="flex items-start gap-2">
                            <i class="bi bi-check-circle text-lime-600 dark:text-lime-400"></i>
                            Menu yang tampil mengikuti role dan izin Anda.
                        </li>
                        <li class="flex items-start gap-2">
                            <i class="bi bi-check-circle text-lime-600 dark:text-lime-400"></i>
                            Aksi lihat, unduh, atau revisi mengikuti akses.
                        </li>
                    </ul>
                    <div
                        class="mt-4 rounded-lg border border-[var(--surface-glass-border)] bg-[var(--surface-glass-elevated)] p-3 text-xs text-[var(--text-secondary)]">
                        <div class="text-xs font-semibold text-[var(--text-primary)]">Langkah cepat</div>
                        <ol class="mt-2 space-y-1 list-decimal list-inside">
                            <li>Login menggunakan akun pegawai.</li>
                            <li>Buka menu Dokumen dari navigasi utama.</li>
                            <li>Gunakan filter atau aksi sesuai kebutuhan.</li>
                        </ol>
                    </div>
                </a>
            </div>

            <div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-4">
                <div
                    class="rounded-xl border border-[var(--surface-glass-border)] bg-[var(--surface-glass)] p-4">
                    <div class="text-xs font-semibold text-[var(--text-tertiary)]">Role</div>
                    <p class="mt-1 text-sm font-semibold text-[var(--text-primary)]">Admin (Hukmas)</p>
                    <p class="mt-2 text-xs text-[var(--text-secondary)]">
                        Kelola dokumen penuh, termasuk unduh file asli.
                    </p>
                </div>
                <div
                    class="rounded-xl border border-[var(--surface-glass-border)] bg-[var(--surface-glass)] p-4">
                    <div class="text-xs font-semibold text-[var(--text-tertiary)]">Role</div>
                    <p class="mt-1 text-sm font-semibold text-[var(--text-primary)]">Eksekutif</p>
                    <p class="mt-2 text-xs text-[var(--text-secondary)]">
                        Akses baca dan unduh dengan watermark.
                    </p>
                </div>
                <div
                    class="rounded-xl border border-[var(--surface-glass-border)] bg-[var(--surface-glass)] p-4">
                    <div class="text-xs font-semibold text-[var(--text-tertiary)]">Role</div>
                    <p class="mt-1 text-sm font-semibold text-[var(--text-primary)]">Umum</p>
                    <p class="mt-2 text-xs text-[var(--text-secondary)]">
                        Lihat metadata sesuai kebijakan dan izin.
                    </p>
                </div>
            </div>

            <div
                class="mt-4 rounded-xl border border-[var(--surface-glass-border)] bg-[var(--surface-glass)] p-4 text-sm text-[var(--text-secondary)]">
                <div class="flex items-start gap-2">
                    <i class="bi bi-info-circle text-[var(--color-primary)]"></i>
                    Dokumen publik yang tampil adalah yang sudah dipublikasikan. Jika menu atau
                    tombol aksi tidak terlihat, akses Anda belum tersedia. Hubungi admin untuk
                    penyesuaian hak.
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
