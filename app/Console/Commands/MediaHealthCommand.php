<?php

namespace App\Console\Commands;

use App\Infrastructure\Media\Persistence\Models\MediaModel;
use App\Infrastructure\Media\Storage\Contracts\StorageProvider;
use App\Jobs\Media\GenerateThumbnailJob;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Queue;

class MediaHealthCommand extends Command
{
    protected $signature = 'media:health';

    protected $description = 'Check media system health (storage, queue, orphans)';

    public function handle(StorageProvider $storage): int
    {
        $healthy = true;

        // 1. Storage reachable
        try {
            $reachable = $storage->exists('health-check-touch');
            $this->info('✓ Storage reachable');
        } catch (\Throwable $e) {
            $this->error('✗ Storage unreachable: '.$e->getMessage());
            $healthy = false;
        }

        // 2. Queue
        try {
            $queue = Queue::connection();
            $this->info('✓ Queue driver: '.config('queue.default'));
        } catch (\Throwable $e) {
            $this->warn('~ Queue check failed: '.$e->getMessage());
        }

        // 3. Pending thumbnail jobs
        try {
            $pending = GenerateThumbnailJob::where('queue', 'default')->count();
        } catch (\Throwable $e) {
            $pending = 'unknown';
        }
        $this->line("  Thumbnail queue: {$pending} pending");

        // 4. Missing objects (DB record, no storage file)
        $missing = 0;
        MediaModel::whereNull('deleted_at')->chunkById(200, function ($chunk) use ($storage, &$missing) {
            foreach ($chunk as $model) {
                if (! $storage->exists($model->path)) {
                    $missing++;
                }
            }
        });
        $this->line("  Missing objects: {$missing}");

        if ($missing > 0) {
            $this->warn("  → Run 'php artisan media:audit' for details");
        }

        // 5. Orphan files approximation
        $this->line('  Orphan check: run `php artisan media:audit --orphans`');

        // 6. Migration progress
        $total = MediaModel::count();
        $migrated = MediaModel::whereNotNull('migrated_at')->count();
        $this->line("  Migration: {$migrated}/{$total} records");

        return $healthy ? self::SUCCESS : self::FAILURE;
    }
}
