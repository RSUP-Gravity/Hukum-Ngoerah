<?php

namespace Database\Seeders;

use App\Models\SystemSetting;
use Illuminate\Database\Seeder;

class SystemSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            // General
            [
                'key' => 'app.name',
                'value' => 'Sistem Manajemen Dokumen Hukum',
                'type' => 'string',
                'group' => 'general',
                'label' => 'Nama Aplikasi',
                'description' => 'Nama aplikasi yang ditampilkan',
                'is_public' => true,
            ],
            [
                'key' => 'app.hospital_name',
                'value' => 'RSUP Prof. Dr. I.G.N.G. Ngoerah',
                'type' => 'string',
                'group' => 'general',
                'label' => 'Nama Rumah Sakit',
                'is_public' => true,
            ],
            [
                'key' => 'app.hospital_address',
                'value' => 'Jl. Diponegoro, Dauh Puri Klod, Denpasar Barat, Kota Denpasar, Bali 80113',
                'type' => 'string',
                'group' => 'general',
                'label' => 'Alamat Rumah Sakit',
                'is_public' => true,
            ],

            // Security
            [
                'key' => 'security.session_idle_timeout',
                'value' => '120',
                'type' => 'integer',
                'group' => 'security',
                'label' => 'Session Idle Timeout (menit)',
                'description' => 'Waktu idle sebelum sesi berakhir',
            ],
            [
                'key' => 'security.session_max_lifetime',
                'value' => '480',
                'type' => 'integer',
                'group' => 'security',
                'label' => 'Session Max Lifetime (menit)',
                'description' => 'Waktu maksimal sesi aktif',
            ],
            [
                'key' => 'security.password_min_length',
                'value' => '8',
                'type' => 'integer',
                'group' => 'security',
                'label' => 'Panjang Minimum Password',
            ],
            [
                'key' => 'security.password_expiry_days',
                'value' => '90',
                'type' => 'integer',
                'group' => 'security',
                'label' => 'Masa Berlaku Password (hari)',
            ],
            [
                'key' => 'security.max_login_attempts',
                'value' => '5',
                'type' => 'integer',
                'group' => 'security',
                'label' => 'Maksimal Percobaan Login',
            ],
            [
                'key' => 'security.lockout_duration',
                'value' => '15',
                'type' => 'integer',
                'group' => 'security',
                'label' => 'Durasi Lockout (menit)',
            ],

            // Documents
            [
                'key' => 'documents.max_file_size',
                'value' => '10240',
                'type' => 'integer',
                'group' => 'documents',
                'label' => 'Ukuran File Maksimal (KB)',
                'description' => 'Ukuran maksimal file yang dapat diupload',
            ],
            [
                'key' => 'documents.allowed_extensions',
                'value' => '["pdf","doc","docx","xls","xlsx","ppt","pptx","jpg","jpeg","png"]',
                'type' => 'json',
                'group' => 'documents',
                'label' => 'Ekstensi File yang Diizinkan',
            ],
            [
                'key' => 'documents.expiry_reminder_days',
                'value' => '30',
                'type' => 'integer',
                'group' => 'documents',
                'label' => 'Pengingat Sebelum Kadaluarsa (hari)',
            ],
            [
                'key' => 'documents.approval_deadline_days',
                'value' => '7',
                'type' => 'integer',
                'group' => 'documents',
                'label' => 'Batas Waktu Approval (hari)',
            ],
            [
                'key' => 'documents.enable_versioning',
                'value' => 'true',
                'type' => 'boolean',
                'group' => 'documents',
                'label' => 'Aktifkan Versioning',
            ],
            [
                'key' => 'documents.require_change_summary',
                'value' => 'true',
                'type' => 'boolean',
                'group' => 'documents',
                'label' => 'Wajib Ringkasan Perubahan',
            ],

            // Appearance
            [
                'key' => 'appearance.primary_color',
                'value' => '#00A0B0',
                'type' => 'string',
                'group' => 'appearance',
                'label' => 'Warna Utama',
                'is_public' => true,
            ],
            [
                'key' => 'appearance.secondary_color',
                'value' => '#A4C639',
                'type' => 'string',
                'group' => 'appearance',
                'label' => 'Warna Sekunder',
                'is_public' => true,
            ],
            [
                'key' => 'appearance.default_theme',
                'value' => 'light',
                'type' => 'string',
                'group' => 'appearance',
                'label' => 'Tema Default',
                'is_public' => true,
            ],

            // Email
            [
                'key' => 'email.enabled',
                'value' => 'false',
                'type' => 'boolean',
                'group' => 'email',
                'label' => 'Notifikasi Email Aktif',
            ],
            [
                'key' => 'email.from_address',
                'value' => 'noreply@rsngoerah.go.id',
                'type' => 'string',
                'group' => 'email',
                'label' => 'Email Pengirim',
            ],
            [
                'key' => 'email.from_name',
                'value' => 'SIMHUKUM RSUP Ngoerah',
                'type' => 'string',
                'group' => 'email',
                'label' => 'Nama Pengirim',
            ],
        ];

        foreach ($settings as $setting) {
            SystemSetting::updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }
    }
}
