@extends('public.panduan-layout')

@section('title', 'Panduan - Bantuan')

@section('panduan-content')
<section id="bantuan" class="feature-card reveal delay-5">
    <div class="flex items-start gap-4">
        <div class="icon-container bg-rose-500/10 text-rose-600 dark:text-rose-400">
            <i class="bi bi-life-preserver"></i>
        </div>
        <div>
            <h2 class="text-xl font-semibold text-[var(--text-primary)]">Bantuan</h2>
            <p class="mt-2 text-sm text-[var(--text-secondary)]">
                Jika membutuhkan bantuan, hubungi Unit Hukum dan Humas untuk dukungan akses
                maupun troubleshooting.
            </p>
            <div class="mt-5 grid gap-4 md:grid-cols-2">
                <div
                    class="rounded-xl border border-[var(--surface-glass-border)] bg-[var(--surface-glass)] p-4">
                    <h3 class="text-sm font-semibold text-[var(--text-primary)]">Hubungi tim</h3>
                    <div class="mt-3 space-y-2 text-sm text-[var(--text-secondary)]">
                        <div class="flex items-center gap-2">
                            <i class="bi bi-envelope text-[var(--color-primary)]"></i>
                            <span>hukum@rsngoerah.go.id</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <i class="bi bi-telephone text-[var(--color-primary)]"></i>
                            <span>(0361) 227911</span>
                        </div>
                    </div>
                </div>
                <div
                    class="rounded-xl border border-[var(--surface-glass-border)] bg-[var(--surface-glass)] p-4">
                    <h3 class="text-sm font-semibold text-[var(--text-primary)]">Siapkan informasi</h3>
                    <ul class="mt-3 space-y-2 text-sm text-[var(--text-secondary)]">
                        <li class="flex items-start gap-2">
                            <i class="bi bi-check-circle text-[var(--color-primary)]"></i>
                            Nama, unit kerja, dan username.
                        </li>
                        <li class="flex items-start gap-2">
                            <i class="bi bi-check-circle text-[var(--color-primary)]"></i>
                            Waktu kejadian dan pesan error yang muncul.
                        </li>
                        <li class="flex items-start gap-2">
                            <i class="bi bi-check-circle text-[var(--color-primary)]"></i>
                            Nomor dokumen terkait atau tangkapan layar.
                        </li>
                    </ul>
                </div>
            </div>

            <div
                class="mt-4 rounded-xl border border-[var(--surface-glass-border)] bg-[var(--surface-glass)] p-4 text-sm text-[var(--text-secondary)]">
                <div class="flex items-start gap-2">
                    <i class="bi bi-info-circle text-[var(--color-primary)]"></i>
                    Coba login ulang, periksa koneksi, dan muat ulang halaman sebelum melapor.
                </div>
            </div>
            <div class="mt-6 flex flex-wrap gap-3">
                <a href="{{ route('login') }}" class="btn-primary-landing text-sm">
                    <i class="bi bi-box-arrow-in-right"></i>
                    Masuk Portal
                </a>
                <a href="{{ route('public.documents') }}" class="btn-secondary-landing text-sm">
                    <i class="bi bi-files"></i>
                    Dokumen Publik
                </a>
            </div>
        </div>
    </div>
</section>
@endsection
