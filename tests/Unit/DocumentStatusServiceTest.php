<?php

namespace Tests\Unit;

use App\Models\Document;
use App\Models\DocumentType;
use App\Services\DocumentStatusService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DocumentStatusServiceTest extends TestCase
{
    use RefreshDatabase;

    protected DocumentStatusService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new DocumentStatusService();
    }

    /**
     * Test perpetual status for document without expiry date.
     */
    public function test_perpetual_status_when_no_expiry_date(): void
    {
        $status = $this->service->calculateExpiryStatus(null);

        $this->assertEquals(DocumentStatusService::STATUS_PERPETUAL, $status);
    }

    /**
     * Test expired status when date is in the past.
     */
    public function test_expired_status_when_date_in_past(): void
    {
        $expiryDate = Carbon::now()->subDay();
        $status = $this->service->calculateExpiryStatus($expiryDate);

        $this->assertEquals(DocumentStatusService::STATUS_EXPIRED, $status);
    }

    /**
     * Test critical status when within 30 days.
     */
    public function test_critical_status_when_within_30_days(): void
    {
        // Test at exactly 30 days
        $expiryDate = Carbon::now()->addDays(30);
        $status = $this->service->calculateExpiryStatus($expiryDate);
        $this->assertEquals(DocumentStatusService::STATUS_CRITICAL, $status);

        // Test at 15 days
        $expiryDate = Carbon::now()->addDays(15);
        $status = $this->service->calculateExpiryStatus($expiryDate);
        $this->assertEquals(DocumentStatusService::STATUS_CRITICAL, $status);

        // Test at 1 day
        $expiryDate = Carbon::now()->addDay();
        $status = $this->service->calculateExpiryStatus($expiryDate);
        $this->assertEquals(DocumentStatusService::STATUS_CRITICAL, $status);
    }

    /**
     * Test warning status when between 31 and 90 days.
     */
    public function test_warning_status_when_between_31_and_90_days(): void
    {
        // Test at 31 days (should be warning)
        $expiryDate = Carbon::now()->addDays(31);
        $status = $this->service->calculateExpiryStatus($expiryDate);
        $this->assertEquals(DocumentStatusService::STATUS_WARNING, $status);

        // Test at 60 days
        $expiryDate = Carbon::now()->addDays(60);
        $status = $this->service->calculateExpiryStatus($expiryDate);
        $this->assertEquals(DocumentStatusService::STATUS_WARNING, $status);

        // Test at 90 days
        $expiryDate = Carbon::now()->addDays(90);
        $status = $this->service->calculateExpiryStatus($expiryDate);
        $this->assertEquals(DocumentStatusService::STATUS_WARNING, $status);
    }

    /**
     * Test attention status when between 91 and 180 days.
     */
    public function test_attention_status_when_between_91_and_180_days(): void
    {
        // Test at 91 days (should be attention)
        $expiryDate = Carbon::now()->addDays(91);
        $status = $this->service->calculateExpiryStatus($expiryDate);
        $this->assertEquals(DocumentStatusService::STATUS_ATTENTION, $status);

        // Test at 120 days
        $expiryDate = Carbon::now()->addDays(120);
        $status = $this->service->calculateExpiryStatus($expiryDate);
        $this->assertEquals(DocumentStatusService::STATUS_ATTENTION, $status);

        // Test at 180 days
        $expiryDate = Carbon::now()->addDays(180);
        $status = $this->service->calculateExpiryStatus($expiryDate);
        $this->assertEquals(DocumentStatusService::STATUS_ATTENTION, $status);
    }

    /**
     * Test active status when more than 180 days.
     */
    public function test_active_status_when_more_than_180_days(): void
    {
        // Test at 181 days (should be active)
        $expiryDate = Carbon::now()->addDays(181);
        $status = $this->service->calculateExpiryStatus($expiryDate);
        $this->assertEquals(DocumentStatusService::STATUS_ACTIVE, $status);

        // Test at 365 days
        $expiryDate = Carbon::now()->addYear();
        $status = $this->service->calculateExpiryStatus($expiryDate);
        $this->assertEquals(DocumentStatusService::STATUS_ACTIVE, $status);
    }

    /**
     * Test boundary conditions.
     */
    public function test_boundary_conditions(): void
    {
        // Exactly today (0 days) should be critical
        $expiryDate = Carbon::now();
        $status = $this->service->calculateExpiryStatus($expiryDate);
        $this->assertEquals(DocumentStatusService::STATUS_CRITICAL, $status);

        // Yesterday should be expired
        $expiryDate = Carbon::now()->subDay();
        $status = $this->service->calculateExpiryStatus($expiryDate);
        $this->assertEquals(DocumentStatusService::STATUS_EXPIRED, $status);
    }

    /**
     * Test get days until expiry.
     */
    public function test_get_days_until_expiry(): void
    {
        $documentType = DocumentType::factory()->create();
        
        $document = Document::factory()->create([
            'document_type_id' => $documentType->id,
            'expiry_date' => Carbon::now()->addDays(45),
        ]);

        $days = $this->service->getDaysUntilExpiry($document);

        $this->assertEquals(45, $days);
    }

    /**
     * Test get days until expiry returns null for perpetual.
     */
    public function test_get_days_until_expiry_returns_null_for_perpetual(): void
    {
        $documentType = DocumentType::factory()->create();
        
        $document = Document::factory()->create([
            'document_type_id' => $documentType->id,
            'expiry_date' => null,
        ]);

        $days = $this->service->getDaysUntilExpiry($document);

        $this->assertNull($days);
    }

    /**
     * Test get status label.
     */
    public function test_get_status_label(): void
    {
        $this->assertEquals('Tidak Ada Batas', $this->service->getStatusLabel(DocumentStatusService::STATUS_PERPETUAL));
        $this->assertEquals('Aktif', $this->service->getStatusLabel(DocumentStatusService::STATUS_ACTIVE));
        $this->assertEquals('≤ 6 Bulan', $this->service->getStatusLabel(DocumentStatusService::STATUS_ATTENTION));
        $this->assertEquals('≤ 3 Bulan', $this->service->getStatusLabel(DocumentStatusService::STATUS_WARNING));
        $this->assertEquals('≤ 1 Bulan', $this->service->getStatusLabel(DocumentStatusService::STATUS_CRITICAL));
        $this->assertEquals('Kadaluarsa', $this->service->getStatusLabel(DocumentStatusService::STATUS_EXPIRED));
    }

    /**
     * Test get status colors returns array.
     */
    public function test_get_status_colors_returns_array(): void
    {
        $colors = $this->service->getStatusColors(DocumentStatusService::STATUS_EXPIRED);

        $this->assertIsArray($colors);
        $this->assertArrayHasKey('bg_light', $colors);
        $this->assertArrayHasKey('bg_dark', $colors);
        $this->assertArrayHasKey('border', $colors);
        $this->assertArrayHasKey('text_light', $colors);
        $this->assertArrayHasKey('text_dark', $colors);
    }

    /**
     * Test get status colors for unknown status returns active colors.
     */
    public function test_get_status_colors_returns_active_for_unknown(): void
    {
        $colors = $this->service->getStatusColors('unknown_status');
        $activeColors = $this->service->getStatusColors(DocumentStatusService::STATUS_ACTIVE);

        $this->assertEquals($activeColors, $colors);
    }

    /**
     * Test negative days for expired documents.
     */
    public function test_negative_days_for_expired_documents(): void
    {
        $documentType = DocumentType::factory()->create();
        
        $document = Document::factory()->expired()->create([
            'document_type_id' => $documentType->id,
            'expiry_date' => Carbon::now()->subDays(30),
        ]);

        $days = $this->service->getDaysUntilExpiry($document);

        $this->assertEquals(-30, $days);
    }

    /**
     * Test get expiry status with caching.
     */
    public function test_get_expiry_status_with_document(): void
    {
        $documentType = DocumentType::factory()->create();
        
        $document = Document::factory()->create([
            'document_type_id' => $documentType->id,
            'expiry_date' => Carbon::now()->addDays(60),
        ]);

        $status = $this->service->getExpiryStatus($document);

        $this->assertEquals(DocumentStatusService::STATUS_WARNING, $status);
    }
}
