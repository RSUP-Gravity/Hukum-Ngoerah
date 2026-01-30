@extends('public.panduan-layout')

@section('title', 'Panduan - Login pegawai')

@section('panduan-content')
<section id="login-pegawai" class="feature-card reveal delay-2">
    <div class="flex items-start gap-4">
        <div class="icon-container bg-sky-500/10 text-sky-600 dark:text-sky-400">
            <i class="bi bi-person-badge"></i>
        </div>
        <div>
            <h2 class="text-xl font-semibold text-[var(--text-primary)]">Login pegawai</h2>
            <p class="mt-2 text-sm text-[var(--text-secondary)]">
                Pegawai menggunakan akun terdaftar untuk masuk ke portal internal.
            </p>
            <ol class="mt-4 space-y-2 text-sm text-[var(--text-secondary)] list-decimal list-inside">
                <li>Buka halaman login dan masukkan username serta password.</li>
                <li>Jika diminta, lakukan perubahan password pertama.</li>
                <li>Masuk ke dashboard untuk melihat ringkasan dokumen.</li>
            </ol>

            <div class="mt-5 grid gap-4 md:grid-cols-2">
                <div
                    class="rounded-xl border border-[var(--surface-glass-border)] bg-[var(--surface-glass)] p-4">
                    <h3 class="text-sm font-semibold text-[var(--text-primary)]">Keamanan akun</h3>
                    <ul class="mt-3 space-y-2 text-sm text-[var(--text-secondary)]">
                        <li class="flex items-start gap-2">
                            <i class="bi bi-check-circle text-[var(--color-primary)]"></i>
                            Jangan bagikan password kepada pihak lain.
                        </li>
                        <li class="flex items-start gap-2">
                            <i class="bi bi-check-circle text-[var(--color-primary)]"></i>
                            Ganti password secara berkala dan gunakan kombinasi yang kuat.
                        </li>
                        <li class="flex items-start gap-2">
                            <i class="bi bi-check-circle text-[var(--color-primary)]"></i>
                            Hindari login dari perangkat umum tanpa pengawasan.
                        </li>
                    </ul>
                </div>
                <div
                    class="rounded-xl border border-[var(--surface-glass-border)] bg-[var(--surface-glass)] p-4">
                    <h3 class="text-sm font-semibold text-[var(--text-primary)]">Masalah umum</h3>
                    <ul class="mt-3 space-y-2 text-sm text-[var(--text-secondary)]">
                        <li class="flex items-start gap-2">
                            <i class="bi bi-check-circle text-[var(--color-primary)]"></i>
                            Password salah: periksa huruf besar kecil dan coba ulang.
                        </li>
                        <li class="flex items-start gap-2">
                            <i class="bi bi-check-circle text-[var(--color-primary)]"></i>
                            Akun belum aktif: hubungi admin untuk aktivasi.
                        </li>
                        <li class="flex items-start gap-2">
                            <i class="bi bi-check-circle text-[var(--color-primary)]"></i>
                            Lupa password: minta reset melalui admin.
                        </li>
                    </ul>
                </div>
            </div>

            <div
                class="mt-4 rounded-xl border border-[var(--surface-glass-border)] bg-[var(--surface-glass)] p-4 text-sm text-[var(--text-secondary)]">
                <div class="flex items-start gap-2">
                    <i class="bi bi-info-circle text-[var(--color-primary)]"></i>
                    Gunakan menu Keluar setelah selesai. Anda akan kembali ke landing page.
                </div>
            </div>

            <div
                class="mt-4 rounded-xl border border-[var(--surface-glass-border)] bg-[var(--surface-glass)] p-4">
                <h3 class="text-sm font-semibold text-[var(--text-primary)]">Navigasi utama setelah login
                </h3>
                <div class="mt-3 grid gap-2 text-sm text-[var(--text-secondary)]">
                    <div class="flex items-start gap-2">
                        <i class="bi bi-check-circle text-[var(--color-primary)]"></i>
                        Dashboard: ringkasan dokumen dan aktivitas terbaru.
                    </div>
                    <div class="flex items-start gap-2">
                        <i class="bi bi-check-circle text-[var(--color-primary)]"></i>
                        Dokumen: daftar, tambah, revisi, dan tindakan sesuai akses.
                    </div>
                    <div class="flex items-start gap-2">
                        <i class="bi bi-check-circle text-[var(--color-primary)]"></i>
                        Master Data (Admin): direktorat, unit, jenis, dan kategori.
                    </div>
                    <div class="flex items-start gap-2">
                        <i class="bi bi-check-circle text-[var(--color-primary)]"></i>
                        Administrasi (Admin): pengguna, audit log, dan pengaturan sistem.
                    </div>
                    <div class="flex items-start gap-2">
                        <i class="bi bi-check-circle text-[var(--color-primary)]"></i>
                        Profil: pembaruan data diri dan password.
                    </div>
                </div>
            </div>

            <div
                class="mt-6 rounded-2xl border border-dashed border-[var(--surface-glass-border)] bg-[var(--surface-glass)] p-4">
                <div
                    class="aspect-video rounded-xl bg-[var(--surface-glass-elevated)] border border-[var(--surface-glass-border)] flex flex-col items-center justify-center text-[var(--text-tertiary)]">
                    <i class="bi bi-image text-2xl"></i>
                    <span class="mt-2 text-xs">Contoh tampilan navigasi panduan</span>
                </div>
                <p class="mt-3 text-xs text-[var(--text-tertiary)]">
                    Contoh alur menu sesuai panduan pada dokumen internal.
                </p>
            </div>
        </div>
    </div>
</section>
@endsection
