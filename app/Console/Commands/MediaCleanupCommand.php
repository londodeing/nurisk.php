<?php

namespace App\Console\Commands;

use App\Infrastructure\Media\Persistence\Models\MediaModel;
use App\Infrastructure\Media\Storage\Contracts\StorageProvider;
use Illuminate\Console\Command;

class MediaCleanupCommand extends Command
{
    protected $signature = 'media:cleanup
        {--dry-run : Show what would be cleaned without deleting}';

    protected $description = 'Clean up orphan media files and expired records';

    public function handle(StorageProvider $storage): int
    {
        $dryRun = (bool) $this->option('dry-run');

        $this->info('Scanning for cleanup targets...');

        // 1. Soft-deleted records older than 30 days → suggest force delete
        $staleDeleted = MediaModel::onlyTrashed()
            ->where('deleted_at', '<', now()->subDays(30))
            ->count();

        if ($staleDeleted > 0) {
            $this->line("  {$staleDeleted} soft-deleted records older than 30 days.");

            if (! $dryRun && $this->confirm("Permanently delete {$staleDeleted} old soft-deleted records?")) {
                MediaModel::onlyTrashed()
                    ->where('deleted_at', '<', now()->subDays(30))
                    ->chunkById(100, function ($chunk) use ($storage) {
                        foreach ($chunk as $model) {
                            $storage->delete($model->path);
                            $model->forceDelete();
                        }
                    });
                $this->info("  Cleaned {$staleDeleted} records.");
            }
        }

        // 2. Active records without storage file → mark as inactive
        $missing = 0;
        MediaModel::whereNull('deleted_at')->where('is_active', true)->chunkById(200, function ($chunk) use ($storage, &$missing) {
            foreach ($chunk as $model) {
                if (! $storage->exists($model->path)) {
                    $model->is_active = false;
                    $model->save();
                    $missing++;
                }
            }
        });

        if ($missing > 0) {
            $this->warn("  Marked {$missing} records as inactive (missing storage file).");
        } else {
            $this->info('  ✓ No missing files among active records.');
        }

        // 3. Unassociated records (no entity_type/entity_id) older than 24h
        $unassociated = MediaModel::whereNull('entity_type')
            ->whereNull('deleted_at')
            ->where('created_at', '<', now()->subHours(24))
            ->count();

        if ($unassociated > 0) {
            $this->line("  {$unassociated} unassociated records older than 24h.");

            if (! $dryRun && $this->confirm("Delete {$unassociated} unassociated records?")) {
                MediaModel::whereNull('entity_type')
                    ->whereNull('deleted_at')
                    ->where('created_at', '<', now()->subHours(24))
                    ->chunkById(100, function ($chunk) use ($storage) {
                        foreach ($chunk as $model) {
                            $storage->delete($model->path);
                            $model->forceDelete();
                        }
                    });
                $this->info("  Cleaned {$unassociated} records.");
            }
        }

        $this->info('Cleanup complete.');

        return self::SUCCESS;
    }
}
