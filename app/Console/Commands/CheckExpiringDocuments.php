<?php

namespace App\Console\Commands;

use App\Models\Document;
use App\Models\User;
use App\Notifications\DocumentStatusChanged;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CheckExpiringDocuments extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'documents:check-expiring {--days=30 : Number of days before expiry to notify}';

    /**
     * The console command description.
     */
    protected $description = 'Check for documents that are expiring soon and send notifications';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $days = $this->option('days');
        $this->info("Checking for documents expiring within {$days} days...");

        // Get documents expiring soon
        $expiringDocuments = Document::where('status', 'published')
            ->whereNotNull('expiry_date')
            ->whereBetween('expiry_date', [now(), now()->addDays($days)])
            ->whereDoesntHave('notifications', function ($query) {
                $query->where('type', 'expiry_warning')
                    ->where('created_at', '>=', now()->subDays(7)); // Don't notify again within 7 days
            })
            ->with(['creator', 'unit'])
            ->get();

        $this->info("Found {$expiringDocuments->count()} documents expiring soon.");

        foreach ($expiringDocuments as $document) {
            // Notify document creator
            if ($document->creator) {
                $document->creator->notify(new DocumentStatusChanged($document, 'expiring_soon'));
            }

            // Notify unit head if exists
            $unitHead = User::where('unit_id', $document->unit_id)
                ->whereHas('position', function ($q) {
                    $q->where('can_approve_documents', true);
                })
                ->first();

            if ($unitHead && $unitHead->id !== $document->created_by) {
                $unitHead->notify(new DocumentStatusChanged($document, 'expiring_soon'));
            }

            Log::info("Expiry notification sent for document: {$document->document_number}");
        }

        // Update expired documents
        $expiredCount = Document::where('status', 'published')
            ->whereNotNull('expiry_date')
            ->where('expiry_date', '<', now())
            ->update(['status' => 'expired']);

        if ($expiredCount > 0) {
            $this->info("{$expiredCount} documents marked as expired.");

            // Notify about expired documents
            $expiredDocuments = Document::where('status', 'expired')
                ->where('expiry_date', '>=', now()->subDay())
                ->with('creator')
                ->get();

            foreach ($expiredDocuments as $document) {
                if ($document->creator) {
                    $document->creator->notify(new DocumentStatusChanged($document, 'expired'));
                }
            }
        }

        $this->info('Document expiry check completed.');

        return Command::SUCCESS;
    }
}
