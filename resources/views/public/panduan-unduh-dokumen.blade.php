@extends('public.panduan-layout')

@section('title', 'Panduan - Unduh dokumen')

@section('panduan-content')
<section id="unduh-dokumen" class="feature-card reveal delay-4">
    <div class="flex items-start gap-4">
        <div class="icon-container bg-orange-500/10 text-orange-600 dark:text-orange-400">
            <i class="bi bi-download"></i>
        </div>
        <div>
            <h2 class="text-xl font-semibold text-[var(--text-primary)]">Unduh dokumen</h2>
            <p class="mt-2 text-sm text-[var(--text-secondary)]">
                Dokumen publik dapat diakses tanpa login. Dokumen internal mengikuti hak akses dan
                otomatis diberi watermark untuk keamanan.
            </p>
            <div class="mt-5 grid gap-4 md:grid-cols-2">
                <div
                    class="rounded-xl border border-[var(--surface-glass-border)] bg-[var(--surface-glass)] p-4">
                    <h3 class="text-sm font-semibold text-[var(--text-primary)]">Hak akses unduh</h3>
                    <ul class="mt-3 space-y-2 text-sm text-[var(--text-secondary)]">
                        <li class="flex items-start gap-2">
                            <i class="bi bi-check-circle text-[var(--color-primary)]"></i>
                            Admin dapat mengunduh file asli.
                        </li>
                        <li class="flex items-start gap-2">
                            <i class="bi bi-check-circle text-[var(--color-primary)]"></i>
                            Non-admin menerima file dengan watermark.
                        </li>
                        <li class="flex items-start gap-2">
                            <i class="bi bi-check-circle text-[var(--color-primary)]"></i>
                            Jika tombol unduh tidak tersedia, akses belum diberikan.
                        </li>
                    </ul>
                </div>
                <div
                    class="rounded-xl border border-[var(--surface-glass-border)] bg-[var(--surface-glass)] p-4">
                    <h3 class="text-sm font-semibold text-[var(--text-primary)]">Langkah unduh</h3>
                    <ol class="mt-3 space-y-2 text-sm text-[var(--text-secondary)] list-decimal list-inside">
                        <li>Buka detail dokumen di portal internal.</li>
                        <li>Klik tombol Unduh atau Download.</li>
                        <li>Simpan file pada folder kerja yang aman.</li>
                    </ol>
                </div>
            </div>

            <div
                class="mt-4 rounded-xl border border-[var(--surface-glass-border)] bg-[var(--surface-glass)] p-4 text-sm text-[var(--text-secondary)]">
                <div class="flex items-start gap-2">
                    <i class="bi bi-info-circle text-[var(--color-primary)]"></i>
                    Riwayat unduh dicatat oleh sistem. Simpan dokumen hanya pada perangkat yang aman
                    dan sesuai kebijakan.
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
