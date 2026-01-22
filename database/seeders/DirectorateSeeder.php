<?php

namespace Database\Seeders;

use App\Models\Directorate;
use App\Models\Unit;
use Illuminate\Database\Seeder;

class DirectorateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $directorates = [
            [
                'code' => 'DIRUT',
                'name' => 'Direktorat Utama',
                'description' => 'Direktorat Utama RSUP Prof. Ngoerah',
                'units' => [
                    ['code' => 'SPI', 'name' => 'Satuan Pengawasan Internal'],
                    ['code' => 'HUKUM', 'name' => 'Bagian Hukum, Organisasi & Humas'],
                    ['code' => 'PERENCANAAN', 'name' => 'Bagian Perencanaan'],
                ],
            ],
            [
                'code' => 'DIRMED',
                'name' => 'Direktorat Medik & Keperawatan',
                'description' => 'Direktorat Pelayanan Medik dan Keperawatan',
                'units' => [
                    ['code' => 'YANMED', 'name' => 'Bidang Pelayanan Medik'],
                    ['code' => 'YANPRA', 'name' => 'Bidang Keperawatan'],
                    ['code' => 'PENMED', 'name' => 'Bidang Penunjang Medik'],
                    ['code' => 'IGD', 'name' => 'Instalasi Gawat Darurat'],
                    ['code' => 'RAWATINAP', 'name' => 'Instalasi Rawat Inap'],
                    ['code' => 'RAWATJALAN', 'name' => 'Instalasi Rawat Jalan'],
                    ['code' => 'BEDAHSENTRAL', 'name' => 'Instalasi Bedah Sentral'],
                    ['code' => 'ICU', 'name' => 'Instalasi Perawatan Intensif'],
                    ['code' => 'LAB', 'name' => 'Instalasi Laboratorium'],
                    ['code' => 'RADIOLOGI', 'name' => 'Instalasi Radiologi'],
                    ['code' => 'FARMASI', 'name' => 'Instalasi Farmasi'],
                ],
            ],
            [
                'code' => 'DIRKEU',
                'name' => 'Direktorat Keuangan',
                'description' => 'Direktorat Keuangan dan Akuntansi',
                'units' => [
                    ['code' => 'AKUNTANSI', 'name' => 'Bagian Akuntansi'],
                    ['code' => 'PERBENDAHARAAN', 'name' => 'Bagian Perbendaharaan'],
                    ['code' => 'VERIFIKASI', 'name' => 'Bagian Verifikasi'],
                ],
            ],
            [
                'code' => 'DIRUMUM',
                'name' => 'Direktorat Umum & SDM',
                'description' => 'Direktorat Umum dan Sumber Daya Manusia',
                'units' => [
                    ['code' => 'SDM', 'name' => 'Bagian SDM'],
                    ['code' => 'UMUM', 'name' => 'Bagian Umum'],
                    ['code' => 'RT', 'name' => 'Bagian Rumah Tangga'],
                    ['code' => 'PENGADAAN', 'name' => 'Bagian Pengadaan'],
                    ['code' => 'TI', 'name' => 'Instalasi Teknologi Informasi'],
                    ['code' => 'IPSRS', 'name' => 'Instalasi Pemeliharaan Sarana'],
                ],
            ],
            [
                'code' => 'DIRPENDIK',
                'name' => 'Direktorat Pendidikan & Penelitian',
                'description' => 'Direktorat Pendidikan, Penelitian dan Pengembangan',
                'units' => [
                    ['code' => 'DIKLAT', 'name' => 'Bagian Pendidikan & Pelatihan'],
                    ['code' => 'LITBANG', 'name' => 'Bagian Penelitian & Pengembangan'],
                ],
            ],
        ];

        foreach ($directorates as $index => $dirData) {
            $units = $dirData['units'] ?? [];
            unset($dirData['units']);

            $directorate = Directorate::updateOrCreate(
                ['code' => $dirData['code']],
                array_merge($dirData, ['sort_order' => $index + 1])
            );

            foreach ($units as $unitIndex => $unit) {
                Unit::updateOrCreate(
                    ['code' => $unit['code']],
                    [
                        'directorate_id' => $directorate->id,
                        'name' => $unit['name'],
                        'sort_order' => $unitIndex + 1,
                    ]
                );
            }
        }
    }
}
