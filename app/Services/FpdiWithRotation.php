<?php

namespace App\Services;

use setasign\Fpdi\Fpdi;

/**
 * Extended FPDI class with rotation support
 */
class FpdiWithRotation extends Fpdi
{
    /**
     * Current rotation angle
     */
    protected int $angle = 0;

    /**
     * Rotate content
     *
     * @param float $angle Rotation angle in degrees
     * @param float|null $x X coordinate of rotation center
     * @param float|null $y Y coordinate of rotation center
     */
    public function Rotate(float $angle, ?float $x = null, ?float $y = null): void
    {
        if ($x === null) {
            $x = $this->x;
        }
        if ($y === null) {
            $y = $this->y;
        }

        if ($this->angle != 0) {
            $this->_out('Q');
        }

        $this->angle = (int)$angle;

        if ($angle != 0) {
            $angle *= M_PI / 180;
            $c = cos($angle);
            $s = sin($angle);
            $cx = $x * $this->k;
            $cy = ($this->h - $y) * $this->k;
            $this->_out(sprintf(
                'q %.5F %.5F %.5F %.5F %.5F %.5F cm 1 0 0 1 %.5F %.5F cm',
                $c, $s, -$s, $c,
                $cx, $cy,
                -$cx, -$cy
            ));
        }
    }

    /**
     * Get current auto page break flag.
     */
    public function getAutoPageBreak(): bool
    {
        return $this->AutoPageBreak;
    }

    /**
     * Get current bottom margin used for auto page breaks.
     */
    public function getBottomMargin(): float
    {
        return $this->bMargin;
    }

    /**
     * Override _endpage to reset rotation
     */
    public function _endpage(): void
    {
        if ($this->angle != 0) {
            $this->angle = 0;
            $this->_out('Q');
        }
        parent::_endpage();
    }

    /**
     * Rotate text at specific position
     *
     * @param float $angle Rotation angle
     * @param float $x X position
     * @param float $y Y position
     * @param string $txt Text to write
     */
    public function RotatedText(float $angle, float $x, float $y, string $txt): void
    {
        $this->Rotate($angle, $x, $y);
        $this->Text($x, $y, $txt);
        $this->Rotate(0);
    }

    /**
     * Add watermark text centered and rotated
     *
     * @param string $text Watermark text
     * @param int $fontSize Font size
     * @param array $color RGB color array [r, g, b]
     * @param float $angle Rotation angle
     * @param float $opacity Opacity (0-1)
     */
    public function addCenteredWatermark(
        string $text,
        int $fontSize = 48,
        array $color = [153, 153, 153],
        float $angle = 45,
        float $opacity = 0.3
    ): void {
        // Get page dimensions
        $pageWidth = $this->GetPageWidth();
        $pageHeight = $this->GetPageHeight();

        // Set font
        $this->SetFont('Arial', 'B', $fontSize);

        // Calculate lighter color for transparency effect
        $r = (int)($color[0] + (255 - $color[0]) * (1 - $opacity));
        $g = (int)($color[1] + (255 - $color[1]) * (1 - $opacity));
        $b = (int)($color[2] + (255 - $color[2]) * (1 - $opacity));
        $this->SetTextColor($r, $g, $b);

        // Calculate center
        $centerX = $pageWidth / 2;
        $centerY = $pageHeight / 2;

        // Get text width
        $textWidth = $this->GetStringWidth($text);

        // Rotate and place text
        $this->Rotate($angle, $centerX, $centerY);
        $this->Text($centerX - ($textWidth / 2), $centerY, $text);
        $this->Rotate(0);
    }

    /**
     * Add pattern of watermarks across the page
     *
     * @param string $text Watermark text
     * @param int $fontSize Font size
     * @param float $spacing Spacing between watermarks
     */
    public function addWatermarkPattern(
        string $text,
        int $fontSize = 16,
        float $spacing = 80
    ): void {
        $pageWidth = $this->GetPageWidth();
        $pageHeight = $this->GetPageHeight();

        $this->SetFont('Arial', '', $fontSize);
        $this->SetTextColor(230, 230, 230);

        $textWidth = $this->GetStringWidth($text);
        $angle = 45;

        for ($y = 0; $y < $pageHeight + $spacing; $y += $spacing) {
            $offset = ($y / $spacing) % 2 == 0 ? 0 : $spacing / 2;
            for ($x = -$textWidth + $offset; $x < $pageWidth + $textWidth; $x += $spacing + $textWidth) {
                $this->RotatedText($angle, $x, $y, $text);
            }
        }
    }
}
