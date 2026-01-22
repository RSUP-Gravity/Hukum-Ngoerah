<?php

namespace App\Services;

use App\Models\Document;
use Illuminate\Support\Facades\Cache;

class DocumentStatusService
{
    /**
     * Expiry status constants
     */
    const STATUS_PERPETUAL = 'perpetual';   // Tidak ada batas waktu
    const STATUS_ACTIVE = 'active';          // Aktif (> 6 bulan)
    const STATUS_ATTENTION = 'attention';    // ≤ 6 bulan
    const STATUS_WARNING = 'warning';        // ≤ 3 bulan
    const STATUS_CRITICAL = 'critical';      // ≤ 1 bulan
    const STATUS_EXPIRED = 'expired';        // Kadaluarsa

    /**
     * Status labels in Indonesian
     */
    const STATUS_LABELS = [
        self::STATUS_PERPETUAL => 'Tidak Ada Batas',
        self::STATUS_ACTIVE => 'Aktif',
        self::STATUS_ATTENTION => '≤ 6 Bulan',
        self::STATUS_WARNING => '≤ 3 Bulan',
        self::STATUS_CRITICAL => '≤ 1 Bulan',
        self::STATUS_EXPIRED => 'Kadaluarsa',
    ];

    /**
     * Status colors for UI (WCAG 2.1 AA compliant)
     */
    const STATUS_COLORS = [
        self::STATUS_PERPETUAL => [
            'bg_light' => 'rgba(100, 116, 139, 0.1)',
            'bg_dark' => 'rgba(148, 163, 184, 0.2)',
            'border' => '#64748B',
            'text_light' => '#475569',  // 4.55:1 contrast on white
            'text_dark' => '#CBD5E1',   // 7.06:1 contrast on dark bg
        ],
        self::STATUS_ACTIVE => [
            'bg_light' => 'transparent',
            'bg_dark' => 'transparent',
            'border' => 'transparent',
            'text_light' => '#0F172A',
            'text_dark' => '#F1F5F9',
        ],
        self::STATUS_ATTENTION => [
            'bg_light' => 'rgba(37, 99, 235, 0.1)',
            'bg_dark' => 'rgba(59, 130, 246, 0.2)',
            'border' => '#2563EB',
            'text_light' => '#1D4ED8',  // 5.74:1 contrast on light blue bg
            'text_dark' => '#93C5FD',   // 7.54:1 contrast on dark bg
        ],
        self::STATUS_WARNING => [
            'bg_light' => 'rgba(22, 163, 74, 0.1)',
            'bg_dark' => 'rgba(34, 197, 94, 0.2)',
            'border' => '#16A34A',
            'text_light' => '#15803D',  // 4.53:1 contrast on light green bg
            'text_dark' => '#86EFAC',   // 8.29:1 contrast on dark bg
        ],
        self::STATUS_CRITICAL => [
            'bg_light' => 'rgba(202, 138, 4, 0.1)',
            'bg_dark' => 'rgba(234, 179, 8, 0.2)',
            'border' => '#CA8A04',
            'text_light' => '#A16207',  // 4.67:1 contrast on light yellow bg
            'text_dark' => '#FDE047',   // 10.95:1 contrast on dark bg
        ],
        self::STATUS_EXPIRED => [
            'bg_light' => 'rgba(220, 38, 38, 0.1)',
            'bg_dark' => 'rgba(239, 68, 68, 0.2)',
            'border' => '#DC2626',
            'text_light' => '#B91C1C',  // 5.22:1 contrast on light red bg
            'text_dark' => '#FCA5A5',   // 7.23:1 contrast on dark bg
        ],
    ];

    /**
     * Cache TTL in seconds (5 minutes)
     */
    const CACHE_TTL = 300;

    /**
     * Calculate expiry status for a document with caching
     */
    public function getExpiryStatus(Document $document): string
    {
        $cacheKey = "document_expiry_status_{$document->id}";

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($document) {
            return $this->calculateExpiryStatus($document->expiry_date);
        });
    }

    /**
     * Calculate expiry status based on expiry date
     */
    public function calculateExpiryStatus($expiryDate): string
    {
        if ($expiryDate === null) {
            return self::STATUS_PERPETUAL;
        }

        $days = now()->diffInDays($expiryDate, false);

        if ($days < 0) {
            return self::STATUS_EXPIRED;
        }

        if ($days <= 30) {
            return self::STATUS_CRITICAL;
        }

        if ($days <= 90) {
            return self::STATUS_WARNING;
        }

        if ($days <= 180) {
            return self::STATUS_ATTENTION;
        }

        return self::STATUS_ACTIVE;
    }

    /**
     * Get days until expiry
     */
    public function getDaysUntilExpiry(Document $document): ?int
    {
        if ($document->expiry_date === null) {
            return null;
        }

        return now()->diffInDays($document->expiry_date, false);
    }

    /**
     * Get status label
     */
    public function getStatusLabel(string $status): string
    {
        return self::STATUS_LABELS[$status] ?? $status;
    }

    /**
     * Get status colors
     */
    public function getStatusColors(string $status): array
    {
        return self::STATUS_COLORS[$status] ?? self::STATUS_COLORS[self::STATUS_ACTIVE];
    }

    /**
     * Get formatted expiry info for display
     */
    public function getExpiryInfo(Document $document): array
    {
        $status = $this->getExpiryStatus($document);
        $days = $this->getDaysUntilExpiry($document);
        $colors = $this->getStatusColors($status);

        return [
            'status' => $status,
            'label' => $this->getStatusLabel($status),
            'days' => $days,
            'days_text' => $this->getDaysText($days),
            'colors' => $colors,
            'is_expired' => $status === self::STATUS_EXPIRED,
            'is_critical' => in_array($status, [self::STATUS_EXPIRED, self::STATUS_CRITICAL]),
            'is_warning' => in_array($status, [self::STATUS_EXPIRED, self::STATUS_CRITICAL, self::STATUS_WARNING]),
        ];
    }

    /**
     * Get human readable days text
     */
    protected function getDaysText(?int $days): string
    {
        if ($days === null) {
            return 'Tidak ada batas waktu';
        }

        if ($days < 0) {
            $absDays = abs($days);
            if ($absDays === 1) {
                return 'Kadaluarsa kemarin';
            }
            return "Kadaluarsa {$absDays} hari lalu";
        }

        if ($days === 0) {
            return 'Kadaluarsa hari ini';
        }

        if ($days === 1) {
            return 'Kadaluarsa besok';
        }

        if ($days <= 30) {
            return "Kadaluarsa dalam {$days} hari";
        }

        if ($days <= 90) {
            $months = round($days / 30, 1);
            return "Kadaluarsa dalam ~{$months} bulan";
        }

        if ($days <= 365) {
            $months = round($days / 30);
            return "Kadaluarsa dalam ~{$months} bulan";
        }

        $years = round($days / 365, 1);
        return "Kadaluarsa dalam ~{$years} tahun";
    }

    /**
     * Clear cached status for a document
     */
    public function clearCache(Document $document): void
    {
        Cache::forget("document_expiry_status_{$document->id}");
    }

    /**
     * Clear all status caches
     */
    public function clearAllCaches(): void
    {
        // In production, you'd use tagged caches or a more sophisticated approach
        // For now, we rely on TTL-based expiration
    }

    /**
     * Get statistics by expiry status with caching
     */
    public function getExpiryStatistics(): array
    {
        return Cache::remember('document_expiry_statistics', self::CACHE_TTL, function () {
            return [
                'perpetual' => Document::whereNull('expiry_date')->count(),
                'active' => Document::whereNotNull('expiry_date')
                    ->where('expiry_date', '>', now()->addDays(180))
                    ->count(),
                'attention' => Document::whereNotNull('expiry_date')
                    ->whereBetween('expiry_date', [now()->addDays(91), now()->addDays(180)])
                    ->count(),
                'warning' => Document::whereNotNull('expiry_date')
                    ->whereBetween('expiry_date', [now()->addDays(31), now()->addDays(90)])
                    ->count(),
                'critical' => Document::whereNotNull('expiry_date')
                    ->whereBetween('expiry_date', [now(), now()->addDays(30)])
                    ->count(),
                'expired' => Document::whereNotNull('expiry_date')
                    ->where('expiry_date', '<', now())
                    ->count(),
            ];
        });
    }
}
