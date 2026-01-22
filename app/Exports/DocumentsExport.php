<?php

namespace App\Exports;

use App\Models\Document;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class DocumentsExport implements FromQuery, WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithTitle, ShouldAutoSize
{
    use Exportable;

    protected array $filters;
    protected int $rowNumber = 0;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    /**
     * Query for export
     */
    public function query()
    {
        $query = Document::query()
            ->with(['documentType', 'documentCategory', 'directorate', 'unit', 'creator'])
            ->active();

        // Apply filters
        if (!empty($this->filters['search'])) {
            $search = $this->filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('document_number', 'like', "%{$search}%")
                    ->orWhere('partner_name', 'like', "%{$search}%");
            });
        }

        if (!empty($this->filters['document_type_id'])) {
            $query->where('document_type_id', $this->filters['document_type_id']);
        }

        if (!empty($this->filters['document_category_id'])) {
            $query->where('document_category_id', $this->filters['document_category_id']);
        }

        if (!empty($this->filters['directorate_id'])) {
            $query->where('directorate_id', $this->filters['directorate_id']);
        }

        if (!empty($this->filters['unit_id'])) {
            $query->where('unit_id', $this->filters['unit_id']);
        }

        if (!empty($this->filters['status'])) {
            $status = $this->filters['status'];
            $now = Carbon::now();

            switch ($status) {
                case 'expired':
                    $query->whereNotNull('expiry_date')->where('expiry_date', '<', $now);
                    break;
                case 'critical':
                    $query->whereNotNull('expiry_date')
                        ->where('expiry_date', '>=', $now)
                        ->where('expiry_date', '<=', $now->copy()->addDays(30));
                    break;
                case 'warning':
                    $query->whereNotNull('expiry_date')
                        ->where('expiry_date', '>', $now->copy()->addDays(30))
                        ->where('expiry_date', '<=', $now->copy()->addDays(90));
                    break;
                case 'attention':
                    $query->whereNotNull('expiry_date')
                        ->where('expiry_date', '>', $now->copy()->addDays(90))
                        ->where('expiry_date', '<=', $now->copy()->addDays(180));
                    break;
                case 'active':
                    $query->where(function ($q) use ($now) {
                        $q->whereNull('expiry_date')
                            ->orWhere('expiry_date', '>', $now->copy()->addDays(180));
                    });
                    break;
                case 'perpetual':
                    $query->whereNull('expiry_date');
                    break;
            }
        }

        if (!empty($this->filters['is_distributed'])) {
            $query->where('is_distributed', $this->filters['is_distributed'] === 'yes');
        }

        if (!empty($this->filters['effective_date_from'])) {
            $query->where('effective_date', '>=', $this->filters['effective_date_from']);
        }

        if (!empty($this->filters['effective_date_to'])) {
            $query->where('effective_date', '<=', $this->filters['effective_date_to']);
        }

        if (!empty($this->filters['expiry_date_from'])) {
            $query->where('expiry_date', '>=', $this->filters['expiry_date_from']);
        }

        if (!empty($this->filters['expiry_date_to'])) {
            $query->where('expiry_date', '<=', $this->filters['expiry_date_to']);
        }

        // Default sorting
        $sortBy = $this->filters['sort_by'] ?? 'expiry_date';
        $sortDir = $this->filters['sort_dir'] ?? 'asc';
        $query->orderBy($sortBy, $sortDir);

        return $query;
    }

    /**
     * Column headings
     */
    public function headings(): array
    {
        return [
            'No',
            'Nomor Dokumen',
            'Judul Dokumen',
            'Jenis Dokumen',
            'Tipe Dokumen',
            'Nama Mitra',
            'Direktorat',
            'Unit',
            'Tanggal Ditetapkan',
            'Tanggal Berlaku',
            'Tanggal Berakhir',
            'Sisa Hari',
            'Status',
            'Distribusi',
            'Revisi',
            'Dibuat Oleh',
            'Tanggal Dibuat',
        ];
    }

    /**
     * Map data for each row
     */
    public function map($document): array
    {
        $this->rowNumber++;

        return [
            $this->rowNumber,
            $document->document_number,
            $document->title,
            $document->documentType?->name ?? '-',
            $document->documentCategory?->name ?? '-',
            $document->partner_name ?? '-',
            $document->directorate?->name ?? '-',
            $document->unit?->name ?? '-',
            $document->established_date?->format('d/m/Y') ?? '-',
            $document->effective_date?->format('d/m/Y') ?? '-',
            $document->expiry_date?->format('d/m/Y') ?? 'Tidak ada batas',
            $this->getDaysRemaining($document),
            $this->getStatusLabel($document),
            $document->is_distributed ? 'Sudah' : 'Belum',
            'Rev. ' . $document->revision_number,
            $document->creator?->name ?? '-',
            $document->created_at?->format('d/m/Y H:i') ?? '-',
        ];
    }

    /**
     * Get days remaining text
     */
    private function getDaysRemaining(Document $document): string
    {
        if (!$document->expiry_date) {
            return '∞';
        }

        $days = now()->diffInDays($document->expiry_date, false);
        
        if ($days < 0) {
            return abs($days) . ' hari lalu';
        }
        
        return $days . ' hari';
    }

    /**
     * Get status label
     */
    private function getStatusLabel(Document $document): string
    {
        if (!$document->expiry_date) {
            return 'Berlaku Selamanya';
        }

        $days = now()->diffInDays($document->expiry_date, false);

        if ($days < 0) {
            return 'Kadaluarsa';
        }
        if ($days <= 30) {
            return 'Kritis (≤1 bulan)';
        }
        if ($days <= 90) {
            return 'Perhatian (≤3 bulan)';
        }
        if ($days <= 180) {
            return 'Hampir (≤6 bulan)';
        }
        
        return 'Aktif';
    }

    /**
     * Define column widths
     */
    public function columnWidths(): array
    {
        return [
            'A' => 5,   // No
            'B' => 20,  // Nomor Dokumen
            'C' => 45,  // Judul Dokumen
            'D' => 18,  // Jenis Dokumen
            'E' => 15,  // Tipe Dokumen
            'F' => 25,  // Nama Mitra
            'G' => 20,  // Direktorat
            'H' => 20,  // Unit
            'I' => 15,  // Tanggal Ditetapkan
            'J' => 15,  // Tanggal Berlaku
            'K' => 15,  // Tanggal Berakhir
            'L' => 12,  // Sisa Hari
            'M' => 18,  // Status
            'N' => 10,  // Distribusi
            'O' => 8,   // Revisi
            'P' => 18,  // Dibuat Oleh
            'Q' => 18,  // Tanggal Dibuat
        ];
    }

    /**
     * Apply styles to worksheet
     */
    public function styles(Worksheet $sheet): array
    {
        $lastRow = $sheet->getHighestRow();
        $lastColumn = 'Q';

        // Apply border to all cells
        $sheet->getStyle("A1:{$lastColumn}{$lastRow}")->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['argb' => 'FFD0D0D0'],
                ],
            ],
        ]);

        // Header style
        $sheet->getStyle('A1:Q1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['argb' => 'FFFFFFFF'],
                'size' => 11,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FF00A0B0'], // Primary teal color
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['argb' => 'FF008090'],
                ],
            ],
        ]);

        // Set row height for header
        $sheet->getRowDimension(1)->setRowHeight(30);

        // Data rows styling
        for ($row = 2; $row <= $lastRow; $row++) {
            // Alternating row colors
            if ($row % 2 == 0) {
                $sheet->getStyle("A{$row}:{$lastColumn}{$row}")->applyFromArray([
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['argb' => 'FFF8FAFC'],
                    ],
                ]);
            }

            // Center align specific columns
            $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("I{$row}:L{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("N{$row}:O{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            // Style status column based on status
            $statusCell = $sheet->getCell("M{$row}")->getValue();
            $statusColor = $this->getStatusColor($statusCell);
            if ($statusColor) {
                $sheet->getStyle("M{$row}")->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'color' => ['argb' => $statusColor['text']],
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['argb' => $statusColor['bg']],
                    ],
                ]);
            }
        }

        // Freeze header row
        $sheet->freezePane('A2');

        return [];
    }

    /**
     * Get status color
     */
    private function getStatusColor(string $status): ?array
    {
        return match ($status) {
            'Kadaluarsa' => ['bg' => 'FFFEE2E2', 'text' => 'FFDC2626'],
            'Kritis (≤1 bulan)' => ['bg' => 'FFFEF3C7', 'text' => 'FFD97706'],
            'Perhatian (≤3 bulan)' => ['bg' => 'FFD1FAE5', 'text' => 'FF059669'],
            'Hampir (≤6 bulan)' => ['bg' => 'FFDBEAFE', 'text' => 'FF2563EB'],
            'Aktif' => ['bg' => 'FFF0FDF4', 'text' => 'FF16A34A'],
            'Berlaku Selamanya' => ['bg' => 'FFF3F4F6', 'text' => 'FF4B5563'],
            default => null,
        };
    }

    /**
     * Sheet title
     */
    public function title(): string
    {
        return 'Daftar Dokumen';
    }
}
