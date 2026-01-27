<?php

namespace App\Services;

use App\Services\FpdiWithRotation;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PdfWatermarkService
{
    /**
     * Watermark text to apply
     */
    protected string $watermarkText = 'HUKMAS RS NGOERAH';

    /**
     * Font size for watermark
     */
    protected int $fontSize = 48;

    /**
     * Watermark color (RGB)
     */
    protected array $color = [153, 153, 153]; // #999999

    /**
     * Watermark angle (degrees)
     */
    protected int $angle = 45;

    /**
     * Opacity level (0-1)
     */
    protected float $opacity = 0.3;

    /**
     * Apply watermark to a PDF file
     *
     * @param string $inputPath Path to original PDF file
     * @param string|null $outputPath Path for watermarked PDF (null for temp file)
     * @return string Path to watermarked PDF
     */
    public function apply(string $inputPath, ?string $outputPath = null): string
    {
        // Create output path if not specified
        if ($outputPath === null) {
            $outputPath = storage_path('app/temp/' . Str::uuid() . '_watermarked.pdf');
        }

        // Ensure temp directory exists
        $outputDir = dirname($outputPath);
        if (!is_dir($outputDir)) {
            mkdir($outputDir, 0755, true);
        }

        // Initialize FPDI with rotation support
        $pdf = new FpdiWithRotation();
        
        // Get the total number of pages
        $pageCount = $pdf->setSourceFile($inputPath);
        
        // Process each page
        for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
            // Import page
            $templateId = $pdf->importPage($pageNo);
            $size = $pdf->getTemplateSize($templateId);
            
            // Add a new page with the same size
            $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
            
            // Use the imported page as template
            $pdf->useTemplate($templateId, 0, 0, $size['width'], $size['height']);
            
            // Apply watermark
            $this->addWatermark($pdf, $size['width'], $size['height']);
        }

        // Output the watermarked PDF
        $pdf->Output('F', $outputPath);

        return $outputPath;
    }

    /**
     * Add watermark to current page
     *
     * @param FpdiWithRotation $pdf
     * @param float $pageWidth
     * @param float $pageHeight
     */
    protected function addWatermark(FpdiWithRotation $pdf, float $pageWidth, float $pageHeight): void
    {
        $autoPageBreak = $pdf->getAutoPageBreak();
        $bottomMargin = $pdf->getBottomMargin();
        $pdf->SetAutoPageBreak(false, 0);

        // Set font
        $pdf->SetFont('Arial', 'B', $this->fontSize);
        
        // Set color with transparency simulation
        // FPDI doesn't support true transparency, so we use a lighter color
        $r = $this->color[0] + (255 - $this->color[0]) * (1 - $this->opacity);
        $g = $this->color[1] + (255 - $this->color[1]) * (1 - $this->opacity);
        $b = $this->color[2] + (255 - $this->color[2]) * (1 - $this->opacity);
        $pdf->SetTextColor((int)$r, (int)$g, (int)$b);

        // Calculate text dimensions
        $textWidth = $pdf->GetStringWidth($this->watermarkText);
        
        // Calculate center position
        $centerX = $pageWidth / 2;
        $centerY = $pageHeight / 2;

        // Apply rotation around center
        $pdf->Rotate($this->angle, $centerX, $centerY);

        // Position text at center
        $x = $centerX - ($textWidth / 2);
        $y = $centerY;

        // Add the watermark text
        $pdf->Text($x, $y, $this->watermarkText);

        // Reset rotation before pattern
        $pdf->Rotate(0);

        // Add additional diagonal watermarks for coverage
        $this->addDiagonalPattern($pdf, $pageWidth, $pageHeight);

        $pdf->SetAutoPageBreak($autoPageBreak, $bottomMargin);
    }

    /**
     * Add diagonal pattern of watermarks across the page
     *
     * @param FpdiWithRotation $pdf
     * @param float $pageWidth
     * @param float $pageHeight
     */
    protected function addDiagonalPattern(FpdiWithRotation $pdf, float $pageWidth, float $pageHeight): void
    {
        // Smaller font for pattern
        $pdf->SetFont('Arial', 'B', 18);

        // Light gray color
        $pdf->SetTextColor(220, 220, 220);

        $textWidth = $pdf->GetStringWidth($this->watermarkText);
        $spacingX = max(140, $textWidth + 80);
        $spacingY = 90;
        $startX = -$pageWidth;
        $endX = $pageWidth * 2;
        $startY = -$pageHeight;
        $endY = $pageHeight * 2;

        // Create a grid of watermarks
        for ($y = $startY; $y < $endY; $y += $spacingY) {
            for ($x = $startX; $x < $endX; $x += $spacingX) {
                $pdf->RotatedText($this->angle, $x, $y, $this->watermarkText);
            }
        }
    }

    /**
     * Apply watermark from storage disk
     *
     * @param string $storagePath Relative path in storage
     * @param string $disk Storage disk name
     * @return string Path to watermarked PDF
     */
    public function applyFromStorage(string $storagePath, string $disk = 'local'): string
    {
        $fullPath = Storage::disk($disk)->path($storagePath);
        return $this->apply($fullPath);
    }

    /**
     * Set custom watermark text
     *
     * @param string $text
     * @return self
     */
    public function setText(string $text): self
    {
        $this->watermarkText = $text;
        return $this;
    }

    /**
     * Set font size
     *
     * @param int $size
     * @return self
     */
    public function setFontSize(int $size): self
    {
        $this->fontSize = $size;
        return $this;
    }

    /**
     * Set watermark color
     *
     * @param int $r Red (0-255)
     * @param int $g Green (0-255)
     * @param int $b Blue (0-255)
     * @return self
     */
    public function setColor(int $r, int $g, int $b): self
    {
        $this->color = [$r, $g, $b];
        return $this;
    }

    /**
     * Set rotation angle
     *
     * @param int $angle Degrees
     * @return self
     */
    public function setAngle(int $angle): self
    {
        $this->angle = $angle;
        return $this;
    }

    /**
     * Set opacity level
     *
     * @param float $opacity 0-1
     * @return self
     */
    public function setOpacity(float $opacity): self
    {
        $this->opacity = max(0, min(1, $opacity));
        return $this;
    }

    /**
     * Clean up temporary watermarked files
     *
     * @param int $olderThanMinutes Delete files older than X minutes
     */
    public static function cleanupTempFiles(int $olderThanMinutes = 60): void
    {
        $tempDir = storage_path('app/temp');
        
        if (!is_dir($tempDir)) {
            return;
        }

        $files = glob($tempDir . '/*_watermarked.pdf');
        $threshold = time() - ($olderThanMinutes * 60);

        foreach ($files as $file) {
            if (filemtime($file) < $threshold) {
                unlink($file);
            }
        }
    }
}
