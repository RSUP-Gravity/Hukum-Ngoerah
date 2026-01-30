@extends('public.panduan-layout')

@section('title', 'Panduan - Persiapan awal')

@section('panduan-content')
<section id="persiapan-awal" class="feature-card reveal">
    <div class="flex items-start gap-4">
        <div class="icon-container bg-[var(--color-primary)]/10 text-[var(--color-primary)]">
            <i class="bi bi-gear"></i>
        </div>
        <div>
            <h2 class="text-xl font-semibold text-[var(--text-primary)]">Persiapan awal</h2>
            <p class="mt-2 text-sm text-[var(--text-secondary)]">
                Pastikan perangkat siap sebelum mengakses portal agar pengalaman penggunaan tetap
                lancar.
            </p>
            <div class="mt-5 grid gap-4 md:grid-cols-2">
                <div
                    class="rounded-xl border border-[var(--surface-glass-border)] bg-[var(--surface-glass)] p-4">
                    <h3 class="text-sm font-semibold text-[var(--text-primary)]">Checklist perangkat
                    </h3>
                    <ul class="mt-3 space-y-2 text-sm text-[var(--text-secondary)]">
                        <li class="flex items-start gap-2">
                            <i class="bi bi-check-circle text-[var(--color-primary)]"></i>
                            Gunakan browser terbaru (Chrome, Edge, atau Firefox).
                        </li>
                        <li class="flex items-start gap-2">
                            <i class="bi bi-check-circle text-[var(--color-primary)]"></i>
                            Pastikan koneksi internet stabil untuk proses unduh dan unggah.
                        </li>
                        <li class="flex items-start gap-2">
                            <i class="bi bi-check-circle text-[var(--color-primary)]"></i>
                            Aktifkan penampil PDF agar dokumen cepat dibuka.
                        </li>
                        <li class="flex items-start gap-2">
                            <i class="bi bi-check-circle text-[var(--color-primary)]"></i>
                            Izinkan pop-up untuk unduhan dan pratinjau.
                        </li>
                        <li class="flex items-start gap-2">
                            <i class="bi bi-check-circle text-[var(--color-primary)]"></i>
                            Pastikan tanggal dan waktu perangkat akurat.
                        </li>
                    </ul>
                </div>
                <div
                    class="rounded-xl border border-[var(--surface-glass-border)] bg-[var(--surface-glass)] p-4">
                    <h3 class="text-sm font-semibold text-[var(--text-primary)]">Kesiapan akun pegawai
                    </h3>
                    <ul class="mt-3 space-y-2 text-sm text-[var(--text-secondary)]">
                        <li class="flex items-start gap-2">
                            <i class="bi bi-check-circle text-[var(--color-primary)]"></i>
                            Pastikan akun sudah aktif dan terdaftar oleh admin.
                        </li>
                        <li class="flex items-start gap-2">
                            <i class="bi bi-check-circle text-[var(--color-primary)]"></i>
                            Siapkan username dan password yang diberikan.
                        </li>
                        <li class="flex items-start gap-2">
                            <i class="bi bi-check-circle text-[var(--color-primary)]"></i>
                            Ganti password saat diminta pada login pertama.
                        </li>
                        <li class="flex items-start gap-2">
                            <i class="bi bi-check-circle text-[var(--color-primary)]"></i>
                            Simpan kontak admin untuk reset password.
                        </li>
                        <li class="flex items-start gap-2">
                            <i class="bi bi-check-circle text-[var(--color-primary)]"></i>
                            Logout setelah selesai untuk menjaga keamanan.
                        </li>
                    </ul>
                </div>
            </div>

            <div
                class="mt-4 rounded-xl border border-[var(--surface-glass-border)] bg-[var(--surface-glass)] p-4 text-sm text-[var(--text-secondary)]">
                <div class="flex items-start gap-2">
                    <i class="bi bi-info-circle text-[var(--color-primary)]"></i>
                    Gunakan perangkat kerja yang terpercaya dan hindari menyimpan password di
                    komputer umum.
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
