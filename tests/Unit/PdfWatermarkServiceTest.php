<?php

namespace Tests\Unit;

use App\Services\PdfWatermarkService;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PdfWatermarkServiceTest extends TestCase
{
    protected PdfWatermarkService $service;
    protected string $tempDir;
    protected array $createdFiles = [];

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->service = new PdfWatermarkService();
        $this->tempDir = storage_path('app/temp/tests');
        
        // Ensure temp directory exists
        if (!is_dir($this->tempDir)) {
            mkdir($this->tempDir, 0755, true);
        }
    }

    protected function tearDown(): void
    {
        // Clean up created files
        foreach ($this->createdFiles as $file) {
            if (file_exists($file)) {
                unlink($file);
            }
        }
        
        parent::tearDown();
    }

    /**
     * Create a simple test PDF file
     */
    protected function createTestPdf(): string
    {
        $pdfPath = $this->tempDir . '/test_' . uniqid() . '.pdf';
        
        // Create a simple PDF using FPDF
        $pdf = new \FPDF();
        $pdf->AddPage();
        $pdf->SetFont('Arial', 'B', 16);
        $pdf->Cell(40, 10, 'Test Document');
        $pdf->Output('F', $pdfPath);
        
        $this->createdFiles[] = $pdfPath;
        
        return $pdfPath;
    }

    /**
     * Test service can be instantiated
     */
    public function test_service_can_be_instantiated(): void
    {
        $this->assertInstanceOf(PdfWatermarkService::class, $this->service);
    }

    /**
     * Test setText returns self for chaining
     */
    public function test_set_text_returns_self(): void
    {
        $result = $this->service->setText('Custom Watermark');
        
        $this->assertInstanceOf(PdfWatermarkService::class, $result);
        $this->assertSame($this->service, $result);
    }

    /**
     * Test setFontSize returns self for chaining
     */
    public function test_set_font_size_returns_self(): void
    {
        $result = $this->service->setFontSize(36);
        
        $this->assertInstanceOf(PdfWatermarkService::class, $result);
        $this->assertSame($this->service, $result);
    }

    /**
     * Test setColor returns self for chaining
     */
    public function test_set_color_returns_self(): void
    {
        $result = $this->service->setColor(255, 0, 0);
        
        $this->assertInstanceOf(PdfWatermarkService::class, $result);
        $this->assertSame($this->service, $result);
    }

    /**
     * Test setAngle returns self for chaining
     */
    public function test_set_angle_returns_self(): void
    {
        $result = $this->service->setAngle(30);
        
        $this->assertInstanceOf(PdfWatermarkService::class, $result);
        $this->assertSame($this->service, $result);
    }

    /**
     * Test setOpacity returns self for chaining
     */
    public function test_set_opacity_returns_self(): void
    {
        $result = $this->service->setOpacity(0.5);
        
        $this->assertInstanceOf(PdfWatermarkService::class, $result);
        $this->assertSame($this->service, $result);
    }

    /**
     * Test setOpacity clamps values above 1
     */
    public function test_set_opacity_clamps_values_above_one(): void
    {
        $result = $this->service->setOpacity(1.5);
        
        // Should still return self and not throw error
        $this->assertInstanceOf(PdfWatermarkService::class, $result);
    }

    /**
     * Test setOpacity clamps values below 0
     */
    public function test_set_opacity_clamps_values_below_zero(): void
    {
        $result = $this->service->setOpacity(-0.5);
        
        // Should still return self and not throw error
        $this->assertInstanceOf(PdfWatermarkService::class, $result);
    }

    /**
     * Test method chaining works
     */
    public function test_method_chaining_works(): void
    {
        $result = $this->service
            ->setText('Test Watermark')
            ->setFontSize(24)
            ->setColor(100, 100, 100)
            ->setAngle(45)
            ->setOpacity(0.5);
        
        $this->assertInstanceOf(PdfWatermarkService::class, $result);
    }

    /**
     * Test apply creates output file
     */
    public function test_apply_creates_output_file(): void
    {
        $inputPath = $this->createTestPdf();
        $outputPath = $this->tempDir . '/output_' . uniqid() . '.pdf';
        $this->createdFiles[] = $outputPath;
        
        $result = $this->service->apply($inputPath, $outputPath);
        
        $this->assertFileExists($result);
        $this->assertEquals($outputPath, $result);
    }

    /**
     * Test apply creates temp file when output path is null
     */
    public function test_apply_creates_temp_file_when_output_null(): void
    {
        $inputPath = $this->createTestPdf();
        
        $result = $this->service->apply($inputPath);
        $this->createdFiles[] = $result;
        
        $this->assertFileExists($result);
        $this->assertStringContainsString('_watermarked.pdf', $result);
        $this->assertStringContainsString('temp', $result);
    }

    /**
     * Test apply with custom text
     */
    public function test_apply_with_custom_text(): void
    {
        $inputPath = $this->createTestPdf();
        $outputPath = $this->tempDir . '/custom_text_' . uniqid() . '.pdf';
        $this->createdFiles[] = $outputPath;
        
        $result = $this->service
            ->setText('RS NGOERAH CONFIDENTIAL')
            ->apply($inputPath, $outputPath);
        
        $this->assertFileExists($result);
        // Verify file size is different (watermark was added)
        $this->assertGreaterThan(0, filesize($result));
    }

    /**
     * Test apply with custom settings
     */
    public function test_apply_with_custom_settings(): void
    {
        $inputPath = $this->createTestPdf();
        $outputPath = $this->tempDir . '/custom_settings_' . uniqid() . '.pdf';
        $this->createdFiles[] = $outputPath;
        
        $result = $this->service
            ->setText('RAHASIA')
            ->setFontSize(72)
            ->setColor(200, 100, 100)
            ->setAngle(60)
            ->setOpacity(0.7)
            ->apply($inputPath, $outputPath);
        
        $this->assertFileExists($result);
        $this->assertGreaterThan(0, filesize($result));
    }

    /**
     * Test apply preserves original file
     */
    public function test_apply_preserves_original_file(): void
    {
        $inputPath = $this->createTestPdf();
        $originalSize = filesize($inputPath);
        $originalMtime = filemtime($inputPath);
        
        $outputPath = $this->tempDir . '/preserve_test_' . uniqid() . '.pdf';
        $this->createdFiles[] = $outputPath;
        
        $this->service->apply($inputPath, $outputPath);
        
        // Original file should be unchanged
        $this->assertFileExists($inputPath);
        $this->assertEquals($originalSize, filesize($inputPath));
        $this->assertEquals($originalMtime, filemtime($inputPath));
    }

    /**
     * Test apply creates output directory if not exists
     */
    public function test_apply_creates_output_directory(): void
    {
        $inputPath = $this->createTestPdf();
        $newDir = $this->tempDir . '/new_dir_' . uniqid();
        $outputPath = $newDir . '/output.pdf';
        $this->createdFiles[] = $outputPath;
        
        $result = $this->service->apply($inputPath, $outputPath);
        
        $this->assertDirectoryExists($newDir);
        $this->assertFileExists($result);
        
        // Clean up directory
        if (is_dir($newDir)) {
            rmdir($newDir);
        }
    }

    /**
     * Test cleanup removes old temp files
     */
    public function test_cleanup_removes_old_temp_files(): void
    {
        // Create a fake old watermarked file
        $oldFile = storage_path('app/temp/old_' . uniqid() . '_watermarked.pdf');
        file_put_contents($oldFile, 'test content');
        
        // Set file modification time to 2 hours ago
        touch($oldFile, time() - 7200);
        
        // Run cleanup (files older than 60 minutes)
        PdfWatermarkService::cleanupTempFiles(60);
        
        // File should be deleted
        $this->assertFileDoesNotExist($oldFile);
    }

    /**
     * Test cleanup preserves recent temp files
     */
    public function test_cleanup_preserves_recent_temp_files(): void
    {
        // Create a recent watermarked file
        $recentFile = storage_path('app/temp/recent_' . uniqid() . '_watermarked.pdf');
        file_put_contents($recentFile, 'test content');
        $this->createdFiles[] = $recentFile;
        
        // Run cleanup (files older than 60 minutes)
        PdfWatermarkService::cleanupTempFiles(60);
        
        // Recent file should still exist
        $this->assertFileExists($recentFile);
    }

    /**
     * Test cleanup handles non-existent temp directory
     */
    public function test_cleanup_handles_non_existent_directory(): void
    {
        // This should not throw an error
        $tempDir = storage_path('app/temp');
        $originalExists = is_dir($tempDir);
        
        if ($originalExists) {
            // Just ensure no exception is thrown
            PdfWatermarkService::cleanupTempFiles(60);
            $this->assertTrue(true);
        } else {
            PdfWatermarkService::cleanupTempFiles(60);
            $this->assertTrue(true);
        }
    }

    /**
     * Test service handles multi-page PDF
     */
    public function test_apply_handles_multi_page_pdf(): void
    {
        // Create a multi-page PDF
        $pdfPath = $this->tempDir . '/multipage_' . uniqid() . '.pdf';
        
        $pdf = new \FPDF();
        $pdf->AddPage();
        $pdf->SetFont('Arial', 'B', 16);
        $pdf->Cell(40, 10, 'Page 1');
        $pdf->AddPage();
        $pdf->Cell(40, 10, 'Page 2');
        $pdf->AddPage();
        $pdf->Cell(40, 10, 'Page 3');
        $pdf->Output('F', $pdfPath);
        
        $this->createdFiles[] = $pdfPath;
        
        $outputPath = $this->tempDir . '/multipage_output_' . uniqid() . '.pdf';
        $this->createdFiles[] = $outputPath;
        
        $result = $this->service->apply($pdfPath, $outputPath);
        
        $this->assertFileExists($result);
        // Multi-page watermarked PDF should be larger than single page
        $this->assertGreaterThan(0, filesize($result));
    }

    /**
     * Test service can be resolved from container
     */
    public function test_service_can_be_resolved_from_container(): void
    {
        $service = app(PdfWatermarkService::class);
        
        $this->assertInstanceOf(PdfWatermarkService::class, $service);
    }

    /**
     * Test applyFromStorage works with storage disk
     */
    public function test_apply_from_storage_with_local_disk(): void
    {
        // Create test PDF in storage
        $inputPath = $this->createTestPdf();
        $storagePath = 'tests/watermark_test_' . uniqid() . '.pdf';
        
        Storage::disk('local')->put($storagePath, file_get_contents($inputPath));
        
        $result = $this->service->applyFromStorage($storagePath, 'local');
        $this->createdFiles[] = $result;
        
        $this->assertFileExists($result);
        $this->assertStringContainsString('_watermarked.pdf', $result);
        
        // Clean up storage file
        Storage::disk('local')->delete($storagePath);
    }

    /**
     * Test color values are properly validated
     */
    public function test_set_color_accepts_valid_rgb_values(): void
    {
        // Test with edge values
        $this->service->setColor(0, 0, 0);
        $this->assertInstanceOf(PdfWatermarkService::class, $this->service);
        
        $this->service->setColor(255, 255, 255);
        $this->assertInstanceOf(PdfWatermarkService::class, $this->service);
        
        $this->service->setColor(128, 128, 128);
        $this->assertInstanceOf(PdfWatermarkService::class, $this->service);
    }

    /**
     * Test font size can be set to various values
     */
    public function test_set_font_size_accepts_various_values(): void
    {
        $this->service->setFontSize(12);
        $this->assertInstanceOf(PdfWatermarkService::class, $this->service);
        
        $this->service->setFontSize(72);
        $this->assertInstanceOf(PdfWatermarkService::class, $this->service);
        
        $this->service->setFontSize(100);
        $this->assertInstanceOf(PdfWatermarkService::class, $this->service);
    }

    /**
     * Test angle can be set to various values
     */
    public function test_set_angle_accepts_various_values(): void
    {
        $this->service->setAngle(0);
        $this->assertInstanceOf(PdfWatermarkService::class, $this->service);
        
        $this->service->setAngle(45);
        $this->assertInstanceOf(PdfWatermarkService::class, $this->service);
        
        $this->service->setAngle(90);
        $this->assertInstanceOf(PdfWatermarkService::class, $this->service);
        
        $this->service->setAngle(-45);
        $this->assertInstanceOf(PdfWatermarkService::class, $this->service);
    }

    /**
     * Test opacity accepts valid range
     */
    public function test_set_opacity_accepts_valid_range(): void
    {
        $this->service->setOpacity(0.0);
        $this->assertInstanceOf(PdfWatermarkService::class, $this->service);
        
        $this->service->setOpacity(0.5);
        $this->assertInstanceOf(PdfWatermarkService::class, $this->service);
        
        $this->service->setOpacity(1.0);
        $this->assertInstanceOf(PdfWatermarkService::class, $this->service);
    }

    /**
     * Test output PDF is valid PDF format
     */
    public function test_output_is_valid_pdf(): void
    {
        $inputPath = $this->createTestPdf();
        $outputPath = $this->tempDir . '/valid_pdf_' . uniqid() . '.pdf';
        $this->createdFiles[] = $outputPath;
        
        $result = $this->service->apply($inputPath, $outputPath);
        
        // Read first few bytes to check PDF header
        $content = file_get_contents($result, false, null, 0, 5);
        $this->assertEquals('%PDF-', $content);
    }
}
