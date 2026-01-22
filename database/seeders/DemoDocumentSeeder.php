<?php

namespace Database\Seeders;

use App\Models\Document;
use App\Models\DocumentType;
use App\Models\DocumentCategory;
use App\Models\Directorate;
use App\Models\Unit;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DemoDocumentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Seeding demo documents...');

        $admin = User::where('username', 'superadmin')->first();
        
        if (!$admin) {
            $admin = User::first();
        }

        if (!$admin) {
            $this->command->error('No users found. Please run UserSeeder first.');
            return;
        }

        $documentTypes = DocumentType::where('is_active', true)->get();
        $documentCategories = DocumentCategory::where('is_active', true)->get();
        $directorates = Directorate::where('is_active', true)->get();
        $units = Unit::where('is_active', true)->get();

        if ($documentTypes->isEmpty() || $documentCategories->isEmpty()) {
            $this->command->error('No document types or categories found. Please run master data seeders first.');
            return;
        }

        // Sample partner names for document titles
        $partners = [
            'PT. Astra International Tbk',
            'PT. Bank Central Asia Tbk',
            'PT. Telkom Indonesia Tbk',
            'Universitas Udayana',
            'RSUP Dr. Sardjito',
            'RSUP Dr. Kariadi',
            'PT. Pertamina (Persero)',
            'Kementerian Kesehatan RI',
            'Dinas Kesehatan Provinsi Bali',
            'BPJS Kesehatan',
            'PT. Kimia Farma Tbk',
            'PT. Bio Farma (Persero)',
            'Fakultas Kedokteran Unud',
            'PT. Indofood Sukses Makmur',
            'PT. Garuda Indonesia',
            'PT. PLN (Persero)',
            'Rumah Sakit Sanglah',
            'RSUD Mangusada',
            'RS Prima Medika',
            'Klinik Pratama Sehat',
        ];

        // Document titles based on type
        $titleTemplates = [
            'Perjanjian Kerjasama' => [
                'Perjanjian Kerjasama Pelayanan Kesehatan dengan %s',
                'Perjanjian Kerjasama Rujukan Pasien dengan %s',
                'Perjanjian Kerjasama Pendidikan dan Penelitian dengan %s',
                'Perjanjian Kerjasama Pengadaan Alat Kesehatan dengan %s',
                'Perjanjian Kerjasama Pengelolaan Limbah Medis dengan %s',
            ],
            'MoU' => [
                'Memorandum of Understanding dengan %s',
                'Nota Kesepahaman Kerjasama dengan %s',
                'MoU Pengembangan SDM dengan %s',
                'Nota Kesepahaman Penelitian dengan %s',
            ],
            'Kontrak' => [
                'Kontrak Pengadaan Obat-obatan dengan %s',
                'Kontrak Pemeliharaan Peralatan Medis dengan %s',
                'Kontrak Jasa Kebersihan dengan %s',
                'Kontrak Jasa Keamanan dengan %s',
                'Kontrak Pengadaan ATK dengan %s',
            ],
            'SPK' => [
                'SPK Pengadaan Alat Laboratorium dari %s',
                'SPK Jasa Konsultasi IT dari %s',
                'SPK Renovasi Ruangan dengan %s',
                'SPK Pengadaan Furniture dari %s',
            ],
            'Addendum' => [
                'Addendum Perjanjian Kerjasama dengan %s',
                'Addendum Kontrak Pengadaan dengan %s',
                'Addendum MoU dengan %s',
            ],
            'Surat Keputusan' => [
                'SK Penetapan Tarif Pelayanan',
                'SK Pembentukan Tim Akreditasi',
                'SK Standar Pelayanan Minimal',
                'SK Struktur Organisasi',
            ],
            'SOP' => [
                'SOP Pelayanan Rawat Jalan',
                'SOP Pelayanan Rawat Inap',
                'SOP Penanganan Pasien COVID-19',
                'SOP Keselamatan Pasien',
                'SOP Manajemen Risiko',
            ],
        ];

        // Keywords for documents
        $keywordSets = [
            'kerjasama, pelayanan, kesehatan, rumah sakit',
            'rujukan, pasien, medis, pelayanan',
            'pendidikan, penelitian, akademik, mahasiswa',
            'pengadaan, alat, medis, peralatan',
            'limbah, medis, pengelolaan, lingkungan',
            'mou, kesepahaman, kerjasama, strategis',
            'kontrak, pengadaan, barang, jasa',
            'spk, pekerjaan, pelaksanaan, teknis',
            'sop, prosedur, standar, operasional',
            'sk, keputusan, direktur, penetapan',
        ];

        // Various statuses for demo
        $statuses = [
            'draft' => 5,
            'pending_review' => 5,
            'pending_approval' => 5,
            'approved' => 5,
            'published' => 25,
            'expired' => 3,
            'archived' => 2,
        ];

        // Confidentiality levels
        $confidentialities = ['public', 'internal', 'confidential', 'restricted'];

        $statusIndex = 0;
        $statusCounts = [];
        foreach ($statuses as $status => $count) {
            for ($j = 0; $j < $count; $j++) {
                $statusCounts[] = $status;
            }
        }
        shuffle($statusCounts);

        // Generate 50 sample documents with various statuses
        for ($i = 1; $i <= 50; $i++) {
            $documentType = $documentTypes->random();
            $documentCategory = $documentCategories->random();
            $directorate = $directorates->random();
            $unit = $units->where('directorate_id', $directorate->id)->first() ?? $units->random();
            $partner = $partners[array_rand($partners)];
            
            // Get title template based on document type
            $typeName = $documentType->name;
            $templates = $titleTemplates[$typeName] ?? $titleTemplates['Perjanjian Kerjasama'];
            $titleTemplate = $templates[array_rand($templates)];
            
            // Format title
            $title = str_contains($titleTemplate, '%s') 
                ? sprintf($titleTemplate, $partner) 
                : $titleTemplate;

            // Generate dates
            $createdDate = Carbon::now()->subDays(rand(30, 730));
            $effectiveDate = $createdDate->copy()->addDays(rand(0, 30));
            
            // Various expiry scenarios
            $expiryScenario = rand(1, 10);
            $expiryDate = match(true) {
                $expiryScenario <= 2 => null, // 20% perpetual (no expiry)
                $expiryScenario <= 3 => Carbon::now()->subDays(rand(1, 60)), // 10% expired
                $expiryScenario <= 4 => Carbon::now()->addDays(rand(1, 30)), // 10% critical (≤1 month)
                $expiryScenario <= 5 => Carbon::now()->addDays(rand(31, 90)), // 10% warning (≤3 months)
                $expiryScenario <= 6 => Carbon::now()->addDays(rand(91, 180)), // 10% attention (≤6 months)
                default => Carbon::now()->addDays(rand(181, 730)), // 40% active
            };

            // Generate review date (usually 6 months before expiry or random future date)
            $reviewDate = $expiryDate ? $expiryDate->copy()->subMonths(6) : Carbon::now()->addMonths(rand(6, 24));

            // Generate document number
            $year = $createdDate->format('Y');
            $month = $createdDate->format('m');
            $docNumber = sprintf(
                '%03d/%s/%s/%s/%s',
                $i,
                strtoupper(Str::slug($documentType->code ?? substr($documentType->name, 0, 3), '')),
                strtoupper($directorate->code ?? 'DIR'),
                $month,
                $year
            );

            // Get status
            $status = $statusCounts[$i - 1] ?? 'published';
            
            // Set approval and publish data for published documents
            $approvedBy = null;
            $approvedAt = null;
            $publishedBy = null;
            $publishedAt = null;
            
            if (in_array($status, ['approved', 'published', 'expired', 'archived'])) {
                $approvedBy = $admin->id;
                $approvedAt = $createdDate->copy()->addDays(rand(1, 7));
            }
            
            if (in_array($status, ['published', 'expired', 'archived'])) {
                $publishedBy = $admin->id;
                $publishedAt = $approvedAt ? $approvedAt->copy()->addDays(rand(0, 3)) : $createdDate->copy()->addDays(rand(1, 10));
            }

            Document::create([
                'document_type_id' => $documentType->id,
                'document_category_id' => $documentCategory->id,
                'directorate_id' => $directorate->id,
                'unit_id' => $unit->id,
                'document_number' => $docNumber,
                'title' => $title,
                'description' => $this->generateDescription($documentType->name, $partner),
                'effective_date' => $effectiveDate->format('Y-m-d'),
                'expiry_date' => $expiryDate?->format('Y-m-d'),
                'review_date' => $reviewDate->format('Y-m-d'),
                'retention_days' => rand(365, 3650), // 1-10 years retention
                'status' => $status,
                'rejection_reason' => null,
                'current_version' => 1,
                'is_locked' => in_array($status, ['pending_review', 'pending_approval']),
                'confidentiality' => $confidentialities[array_rand($confidentialities)],
                'keywords' => $keywordSets[array_rand($keywordSets)],
                'created_by' => $admin->id,
                'updated_by' => $admin->id,
                'approved_by' => $approvedBy,
                'approved_at' => $approvedAt,
                'published_by' => $publishedBy,
                'published_at' => $publishedAt,
                'created_at' => $createdDate,
                'updated_at' => Carbon::now(),
            ]);
        }

        $this->command->info('Successfully seeded 50 demo documents.');
        
        // Summary by status
        $this->displayStatusSummary();
    }

    /**
     * Generate description based on document type
     */
    private function generateDescription(string $typeName, string $partner): string
    {
        $descriptions = [
            'Perjanjian Kerjasama' => "Dokumen perjanjian kerjasama antara RSUP Prof. Dr. I.G.N.G. Ngoerah dengan {$partner} dalam rangka peningkatan kualitas pelayanan kesehatan.",
            'MoU' => "Memorandum of Understanding antara RSUP Prof. Dr. I.G.N.G. Ngoerah dengan {$partner} sebagai landasan kerjasama strategis di bidang kesehatan.",
            'Kontrak' => "Kontrak pengadaan barang/jasa antara RSUP Prof. Dr. I.G.N.G. Ngoerah dengan {$partner} sesuai ketentuan yang berlaku.",
            'SPK' => "Surat Perintah Kerja untuk pelaksanaan pekerjaan dengan {$partner}.",
            'Addendum' => "Addendum perubahan atas perjanjian yang telah disepakati sebelumnya dengan {$partner}.",
            'Surat Keputusan' => "Surat Keputusan Direktur RSUP Prof. Dr. I.G.N.G. Ngoerah.",
            'SOP' => "Standar Operasional Prosedur RSUP Prof. Dr. I.G.N.G. Ngoerah.",
        ];

        return $descriptions[$typeName] ?? "Dokumen resmi RSUP Prof. Dr. I.G.N.G. Ngoerah.";
    }

    /**
     * Display status summary
     */
    private function displayStatusSummary(): void
    {
        $now = Carbon::now();
        
        $total = Document::count();
        $perpetual = Document::whereNull('expiry_date')->count();
        $expired = Document::whereNotNull('expiry_date')
            ->where('expiry_date', '<', $now)
            ->count();
        $critical = Document::whereNotNull('expiry_date')
            ->whereBetween('expiry_date', [$now, $now->copy()->addDays(30)])
            ->count();
        $warning = Document::whereNotNull('expiry_date')
            ->whereBetween('expiry_date', [$now->copy()->addDays(31), $now->copy()->addDays(90)])
            ->count();
        $attention = Document::whereNotNull('expiry_date')
            ->whereBetween('expiry_date', [$now->copy()->addDays(91), $now->copy()->addDays(180)])
            ->count();
        $active = $total - $perpetual - $expired - $critical - $warning - $attention;

        $this->command->table(
            ['Status', 'Count'],
            [
                ['Total Documents', $total],
                ['Perpetual (No Expiry)', $perpetual],
                ['Expired', $expired],
                ['Critical (≤1 month)', $critical],
                ['Warning (≤3 months)', $warning],
                ['Attention (≤6 months)', $attention],
                ['Active (>6 months)', $active],
            ]
        );
    }
}
