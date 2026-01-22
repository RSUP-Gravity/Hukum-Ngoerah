<?php

namespace App\Console\Commands;

use App\Services\PdfWatermarkService;
use Illuminate\Console\Command;

class CleanupTempFiles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'temp:cleanup {--minutes=60 : Delete files older than X minutes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up temporary files including watermarked PDFs';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $minutes = (int) $this->option('minutes');

        $this->info("Cleaning up temporary files older than {$minutes} minutes...");

        // Clean watermarked PDFs
        PdfWatermarkService::cleanupTempFiles($minutes);

        // Clean other temp files
        $tempDir = storage_path('app/temp');
        if (is_dir($tempDir)) {
            $files = glob($tempDir . '/*');
            $threshold = time() - ($minutes * 60);
            $cleaned = 0;

            foreach ($files as $file) {
                if (is_file($file) && filemtime($file) < $threshold) {
                    unlink($file);
                    $cleaned++;
                }
            }

            $this->info("Cleaned {$cleaned} temporary files.");
        }

        return Command::SUCCESS;
    }
}
