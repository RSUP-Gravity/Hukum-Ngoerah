<?php

namespace App\Console\Commands;

use App\Models\Notification;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CleanOldNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notifications:clean 
                            {--days=30 : Number of days to keep read notifications}
                            {--dry-run : Show what would be deleted without actually deleting}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean old read notifications older than specified days';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $days = (int) $this->option('days');
        $dryRun = $this->option('dry-run');

        $cutoffDate = Carbon::now()->subDays($days);

        $this->info("Cleaning read notifications older than {$days} days (before {$cutoffDate->format('Y-m-d H:i:s')})...");

        // Query for old read notifications
        $query = Notification::whereNotNull('read_at')
            ->where('created_at', '<', $cutoffDate);

        $count = $query->count();

        if ($count === 0) {
            $this->info('No old notifications to clean.');
            return Command::SUCCESS;
        }

        if ($dryRun) {
            $this->warn("[DRY RUN] Would delete {$count} notifications.");
            
            // Show sample of what would be deleted
            $samples = $query->limit(5)->get(['id', 'type', 'title', 'created_at', 'read_at']);
            if ($samples->isNotEmpty()) {
                $this->table(
                    ['ID', 'Type', 'Title', 'Created At', 'Read At'],
                    $samples->map(fn ($n) => [
                        $n->id,
                        $n->type,
                        \Str::limit($n->title, 30),
                        $n->created_at->format('Y-m-d H:i'),
                        $n->read_at->format('Y-m-d H:i'),
                    ])
                );
            }
            
            return Command::SUCCESS;
        }

        // Delete in chunks to avoid memory issues
        $deleted = 0;
        $chunkSize = 1000;

        $this->withProgressBar($count, function () use ($query, $chunkSize, &$deleted) {
            while (true) {
                $deletedChunk = Notification::whereNotNull('read_at')
                    ->where('created_at', '<', Carbon::now()->subDays((int) $this->option('days')))
                    ->limit($chunkSize)
                    ->delete();

                if ($deletedChunk === 0) {
                    break;
                }

                $deleted += $deletedChunk;
            }
        });

        $this->newLine(2);
        $this->info("Successfully deleted {$deleted} old read notifications.");

        return Command::SUCCESS;
    }
}
