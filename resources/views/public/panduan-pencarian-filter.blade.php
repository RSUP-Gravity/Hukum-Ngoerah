@extends('public.panduan-layout')

@section('title', 'Panduan - Pencarian dan filter')

@section('panduan-content')
<section id="pencarian-filter" class="feature-card reveal delay-3">
    <div class="flex items-start gap-4">
        <div class="icon-container bg-purple-500/10 text-purple-600 dark:text-purple-400">
            <i class="bi bi-search"></i>
        </div>
        <div>
            <h2 class="text-xl font-semibold text-[var(--text-primary)]">Pencarian dan filter</h2>
            <p class="mt-2 text-sm text-[var(--text-secondary)]">
                Gunakan kolom pencarian, filter status, dan kategori untuk mempercepat akses
                dokumen yang dibutuhkan.
            </p>
            <div class="mt-5 grid gap-4 md:grid-cols-2">
                <div
                    class="rounded-xl border border-[var(--surface-glass-border)] bg-[var(--surface-glass)] p-4">
                    <h3 class="text-sm font-semibold text-[var(--text-primary)]">Pencarian cepat</h3>
                    <ul class="mt-3 space-y-2 text-sm text-[var(--text-secondary)]">
                        <li class="flex items-start gap-2">
                            <i class="bi bi-check-circle text-[var(--color-primary)]"></i>
                            Gunakan kata kunci nomor dokumen atau judul.
                        </li>
                        <li class="flex items-start gap-2">
                            <i class="bi bi-check-circle text-[var(--color-primary)]"></i>
                            Gunakan 2-3 kata kunci spesifik untuk hasil lebih akurat.
                        </li>
                        <li class="flex items-start gap-2">
                            <i class="bi bi-check-circle text-[var(--color-primary)]"></i>
                            Jika hasil kosong, hapus kata kunci dan coba ulang.
                        </li>
                    </ul>
                </div>
                <div
                    class="rounded-xl border border-[var(--surface-glass-border)] bg-[var(--surface-glass)] p-4">
                    <h3 class="text-sm font-semibold text-[var(--text-primary)]">Filter lanjutan (internal)</h3>
                    <ul class="mt-3 space-y-2 text-sm text-[var(--text-secondary)]">
                        <li class="flex items-start gap-2">
                            <i class="bi bi-check-circle text-[var(--color-primary)]"></i>
                            Pilih jenis dan kategori dokumen sesuai kebutuhan.
                        </li>
                        <li class="flex items-start gap-2">
                            <i class="bi bi-check-circle text-[var(--color-primary)]"></i>
                            Gunakan filter direktorat, unit, dan status.
                        </li>
                        <li class="flex items-start gap-2">
                            <i class="bi bi-check-circle text-[var(--color-primary)]"></i>
                            Simpan preset filter untuk akses berulang.
                        </li>
                    </ul>
                </div>
            </div>

            <div
                class="mt-4 rounded-xl border border-[var(--surface-glass-border)] bg-[var(--surface-glass)] p-4">
                <h3 class="text-sm font-semibold text-[var(--text-primary)]">Sortir dan navigasi hasil</h3>
                <div class="mt-3 grid gap-2 text-sm text-[var(--text-secondary)]">
                    <div class="flex items-start gap-2">
                        <i class="bi bi-check-circle text-[var(--color-primary)]"></i>
                        Klik judul kolom untuk mengurutkan data (portal internal).
                    </div>
                    <div class="flex items-start gap-2">
                        <i class="bi bi-check-circle text-[var(--color-primary)]"></i>
                        Gunakan pagination untuk melihat halaman berikutnya.
                    </div>
                    <div class="flex items-start gap-2">
                        <i class="bi bi-check-circle text-[var(--color-primary)]"></i>
                        Bagikan URL hasil pencarian jika perlu kolaborasi.
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
