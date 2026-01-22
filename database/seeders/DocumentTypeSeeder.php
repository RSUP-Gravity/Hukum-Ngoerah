<?php

namespace Database\Seeders;

use App\Models\DocumentType;
use App\Models\DocumentCategory;
use Illuminate\Database\Seeder;

class DocumentTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $types = [
            [
                'code' => 'PERJANJIAN',
                'name' => 'Perjanjian',
                'description' => 'Dokumen perjanjian kerja sama dan kontrak',
                'prefix' => 'PRJ',
                'requires_approval' => true,
                'has_expiry' => true,
                'default_retention_days' => 1825, // 5 years
                'categories' => [
                    ['code' => 'MOU', 'name' => 'Memorandum of Understanding'],
                    ['code' => 'MOA', 'name' => 'Memorandum of Agreement'],
                    ['code' => 'PKS', 'name' => 'Perjanjian Kerja Sama'],
                    ['code' => 'KONTRAK', 'name' => 'Kontrak Kerja'],
                    ['code' => 'ADDENDUM', 'name' => 'Addendum Perjanjian'],
                ],
            ],
            [
                'code' => 'PERATURAN',
                'name' => 'Peraturan',
                'description' => 'Peraturan internal rumah sakit',
                'prefix' => 'PER',
                'requires_approval' => true,
                'has_expiry' => false,
                'default_retention_days' => null,
                'categories' => [
                    ['code' => 'PERDIR', 'name' => 'Peraturan Direktur'],
                    ['code' => 'PERDIRUT', 'name' => 'Peraturan Direktur Utama'],
                    ['code' => 'PEDOMAN', 'name' => 'Pedoman'],
                    ['code' => 'SPO', 'name' => 'Standar Prosedur Operasional'],
                ],
            ],
            [
                'code' => 'SK',
                'name' => 'Surat Keputusan',
                'description' => 'Surat keputusan internal',
                'prefix' => 'SK',
                'requires_approval' => true,
                'has_expiry' => true,
                'default_retention_days' => 365, // 1 year
                'categories' => [
                    ['code' => 'SK-PENGANGKATAN', 'name' => 'SK Pengangkatan'],
                    ['code' => 'SK-MUTASI', 'name' => 'SK Mutasi'],
                    ['code' => 'SK-PEMBERHENTIAN', 'name' => 'SK Pemberhentian'],
                    ['code' => 'SK-TIM', 'name' => 'SK Pembentukan Tim'],
                    ['code' => 'SK-LAINNYA', 'name' => 'SK Lainnya'],
                ],
            ],
            [
                'code' => 'LEGAL',
                'name' => 'Dokumen Legal',
                'description' => 'Dokumen perizinan dan legal lainnya',
                'prefix' => 'LGL',
                'requires_approval' => true,
                'has_expiry' => true,
                'default_retention_days' => 1825, // 5 years
                'categories' => [
                    ['code' => 'IZIN', 'name' => 'Perizinan'],
                    ['code' => 'SERTIFIKAT', 'name' => 'Sertifikat'],
                    ['code' => 'AKREDITASI', 'name' => 'Akreditasi'],
                    ['code' => 'LISENSI', 'name' => 'Lisensi'],
                ],
            ],
            [
                'code' => 'SURAT',
                'name' => 'Surat',
                'description' => 'Surat-surat resmi',
                'prefix' => 'SRT',
                'requires_approval' => false,
                'has_expiry' => false,
                'default_retention_days' => 730, // 2 years
                'categories' => [
                    ['code' => 'SURAT-MASUK', 'name' => 'Surat Masuk'],
                    ['code' => 'SURAT-KELUAR', 'name' => 'Surat Keluar'],
                    ['code' => 'UNDANGAN', 'name' => 'Undangan'],
                    ['code' => 'KETERANGAN', 'name' => 'Surat Keterangan'],
                ],
            ],
        ];

        foreach ($types as $typeData) {
            $categories = $typeData['categories'] ?? [];
            unset($typeData['categories']);

            $type = DocumentType::updateOrCreate(
                ['code' => $typeData['code']],
                $typeData
            );

            foreach ($categories as $index => $category) {
                DocumentCategory::updateOrCreate(
                    ['code' => $category['code']],
                    [
                        'document_type_id' => $type->id,
                        'name' => $category['name'],
                        'sort_order' => $index + 1,
                    ]
                );
            }
        }
    }
}
