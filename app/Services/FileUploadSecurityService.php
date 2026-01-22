<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;

/**
 * Service for validating uploaded files for security.
 * 
 * Performs additional security checks beyond standard validation:
 * - Magic bytes verification
 * - File extension validation
 * - Dangerous content detection
 * - File size limits
 */
class FileUploadSecurityService
{
    /**
     * Known magic bytes for allowed file types.
     * 
     * @var array<string, array<string>>
     */
    protected array $magicBytes = [
        'pdf' => ['25504446'], // %PDF
        'doc' => ['D0CF11E0', 'D0CF11E0A1B11AE1'], // OLE Compound Document
        'docx' => ['504B0304'], // ZIP (DOCX is a ZIP archive)
        'xlsx' => ['504B0304'], // ZIP
        'xls' => ['D0CF11E0', 'D0CF11E0A1B11AE1'], // OLE Compound Document
        'png' => ['89504E47'], // PNG signature
        'jpg' => ['FFD8FF'], // JPEG
        'jpeg' => ['FFD8FF'], // JPEG
        'gif' => ['47494638'], // GIF
        'zip' => ['504B0304', '504B0506', '504B0708'],
    ];

    /**
     * Allowed MIME types for each extension.
     * 
     * @var array<string, array<string>>
     */
    protected array $allowedMimeTypes = [
        'pdf' => ['application/pdf'],
        'doc' => ['application/msword', 'application/octet-stream'],
        'docx' => [
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/zip', // Some systems report DOCX as ZIP
        ],
        'xlsx' => [
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/zip',
        ],
        'xls' => [
            'application/vnd.ms-excel',
            'application/octet-stream',
        ],
        'png' => ['image/png'],
        'jpg' => ['image/jpeg'],
        'jpeg' => ['image/jpeg'],
        'gif' => ['image/gif'],
    ];

    /**
     * Maximum file sizes by extension (in bytes).
     * 
     * @var array<string, int>
     */
    protected array $maxFileSizes = [
        'pdf' => 52428800, // 50MB
        'doc' => 52428800, // 50MB
        'docx' => 52428800, // 50MB
        'xlsx' => 26214400, // 25MB
        'xls' => 26214400, // 25MB
        'png' => 10485760, // 10MB
        'jpg' => 10485760, // 10MB
        'jpeg' => 10485760, // 10MB
        'gif' => 5242880, // 5MB
    ];

    /**
     * Validate an uploaded file for security.
     * 
     * @param  \Illuminate\Http\UploadedFile  $file
     * @param  array<string>  $allowedExtensions
     * @return array{valid: bool, error: ?string}
     */
    public function validate(UploadedFile $file, array $allowedExtensions = ['pdf', 'doc', 'docx']): array
    {
        // 1. Check file extension
        $extension = strtolower($file->getClientOriginalExtension());
        
        if (!in_array($extension, $allowedExtensions)) {
            return [
                'valid' => false,
                'error' => "Ekstensi file tidak diizinkan: {$extension}",
            ];
        }

        // 2. Check file size
        $maxSize = $this->maxFileSizes[$extension] ?? 52428800;
        
        if ($file->getSize() > $maxSize) {
            $maxSizeMB = $maxSize / 1048576;
            return [
                'valid' => false,
                'error' => "Ukuran file melebihi batas maksimum ({$maxSizeMB}MB)",
            ];
        }

        // 3. Check MIME type
        $mimeType = $file->getMimeType();
        $allowedMimes = $this->allowedMimeTypes[$extension] ?? [];
        
        if (!empty($allowedMimes) && !in_array($mimeType, $allowedMimes)) {
            // Log suspicious file but allow if magic bytes match
            Log::warning('File MIME type mismatch', [
                'filename' => $file->getClientOriginalName(),
                'extension' => $extension,
                'mime_type' => $mimeType,
                'expected_mimes' => $allowedMimes,
            ]);
        }

        // 4. Verify magic bytes
        $magicBytesResult = $this->verifyMagicBytes($file, $extension);
        
        if (!$magicBytesResult['valid']) {
            Log::warning('File magic bytes mismatch', [
                'filename' => $file->getClientOriginalName(),
                'extension' => $extension,
                'actual_bytes' => $magicBytesResult['actual_bytes'] ?? 'unknown',
            ]);
            
            return [
                'valid' => false,
                'error' => 'File tidak valid atau rusak. Konten file tidak sesuai dengan ekstensi.',
            ];
        }

        // 5. Check for dangerous content in PDFs
        if ($extension === 'pdf') {
            $dangerousContentResult = $this->checkPdfDangerousContent($file);
            
            if (!$dangerousContentResult['valid']) {
                return $dangerousContentResult;
            }
        }

        // 6. Check for executable content
        if ($this->containsExecutableContent($file)) {
            return [
                'valid' => false,
                'error' => 'File mengandung konten berbahaya.',
            ];
        }

        return ['valid' => true, 'error' => null];
    }

    /**
     * Verify file magic bytes match the expected extension.
     * 
     * @param  \Illuminate\Http\UploadedFile  $file
     * @param  string  $extension
     * @return array{valid: bool, actual_bytes: ?string}
     */
    protected function verifyMagicBytes(UploadedFile $file, string $extension): array
    {
        $expectedMagicBytes = $this->magicBytes[$extension] ?? null;
        
        // If we don't have magic bytes for this extension, skip validation
        if ($expectedMagicBytes === null) {
            return ['valid' => true, 'actual_bytes' => null];
        }

        $handle = fopen($file->getRealPath(), 'rb');
        
        if (!$handle) {
            return ['valid' => false, 'actual_bytes' => null];
        }

        // Read first 8 bytes
        $bytes = fread($handle, 8);
        fclose($handle);

        if ($bytes === false) {
            return ['valid' => false, 'actual_bytes' => null];
        }

        $actualBytes = strtoupper(bin2hex($bytes));

        foreach ($expectedMagicBytes as $expected) {
            if (str_starts_with($actualBytes, $expected)) {
                return ['valid' => true, 'actual_bytes' => $actualBytes];
            }
        }

        return ['valid' => false, 'actual_bytes' => $actualBytes];
    }

    /**
     * Check PDF for dangerous content like JavaScript.
     * 
     * @param  \Illuminate\Http\UploadedFile  $file
     * @return array{valid: bool, error: ?string}
     */
    protected function checkPdfDangerousContent(UploadedFile $file): array
    {
        $content = file_get_contents($file->getRealPath());
        
        if ($content === false) {
            return ['valid' => false, 'error' => 'Gagal membaca file PDF.'];
        }

        // Check for embedded JavaScript
        $dangerousPatterns = [
            '/\/JavaScript/i',
            '/\/JS\s*\(/i',
            '/\/Launch/i',
            '/\/OpenAction.*\/S\s*\/JavaScript/i',
            '/\/AA\s*<<.*\/JavaScript/i', // Additional actions with JavaScript
            '/\/GoToE/i', // Embedded go-to action
            '/\/SubmitForm/i', // Form submission
            '/\/ImportData/i', // Data import
        ];

        foreach ($dangerousPatterns as $pattern) {
            if (preg_match($pattern, $content)) {
                Log::warning('PDF contains dangerous content', [
                    'filename' => $file->getClientOriginalName(),
                    'pattern' => $pattern,
                ]);
                
                return [
                    'valid' => false,
                    'error' => 'File PDF mengandung konten JavaScript yang tidak diizinkan.',
                ];
            }
        }

        return ['valid' => true, 'error' => null];
    }

    /**
     * Check if file contains executable content.
     * 
     * @param  \Illuminate\Http\UploadedFile  $file
     * @return bool
     */
    protected function containsExecutableContent(UploadedFile $file): bool
    {
        $handle = fopen($file->getRealPath(), 'rb');
        
        if (!$handle) {
            return false;
        }

        // Read first 2 bytes
        $bytes = fread($handle, 2);
        fclose($handle);

        if ($bytes === false) {
            return false;
        }

        // Check for executable signatures
        $executableSignatures = [
            "\x4D\x5A", // MZ (Windows executable)
            "\x7F\x45", // ELF (Linux executable) - first 2 bytes of \x7FELF
        ];

        foreach ($executableSignatures as $signature) {
            if ($bytes === $signature) {
                return true;
            }
        }

        return false;
    }

    /**
     * Sanitize filename for storage.
     * 
     * @param  string  $filename
     * @return string
     */
    public function sanitizeFilename(string $filename): string
    {
        // Remove any path components
        $filename = basename($filename);
        
        // Remove null bytes
        $filename = str_replace("\0", '', $filename);
        
        // Replace dangerous characters
        $filename = preg_replace('/[^\w\.\-]/', '_', $filename);
        
        // Remove multiple consecutive underscores or dots
        $filename = preg_replace('/_+/', '_', $filename);
        $filename = preg_replace('/\.+/', '.', $filename);
        
        // Ensure extension is preserved
        $parts = explode('.', $filename);
        $extension = array_pop($parts);
        $name = implode('.', $parts);
        
        // Limit filename length
        if (strlen($name) > 100) {
            $name = substr($name, 0, 100);
        }
        
        return $name . '.' . strtolower($extension);
    }

    /**
     * Generate a secure random filename while preserving extension.
     * 
     * @param  \Illuminate\Http\UploadedFile  $file
     * @param  string  $prefix
     * @return string
     */
    public function generateSecureFilename(UploadedFile $file, string $prefix = ''): string
    {
        $extension = strtolower($file->getClientOriginalExtension());
        $random = bin2hex(random_bytes(16));
        $timestamp = now()->format('YmdHis');
        
        return ($prefix ? "{$prefix}_" : '') . "{$timestamp}_{$random}.{$extension}";
    }
}
