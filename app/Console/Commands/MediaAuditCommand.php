<?php

namespace App\Console\Commands;

use App\Infrastructure\Media\Persistence\Models\MediaConversionModel;
use App\Infrastructure\Media\Persistence\Models\MediaModel;
use App\Infrastructure\Media\Storage\Contracts\StorageProvider;
use Illuminate\Console\Command;

class MediaAuditCommand extends Command
{
    protected $signature = 'media:audit
        {--orphans : Show storage files with no DB record}
        {--fix : Delete orphan DB records without storage files}';

    protected $description = 'Audit media DB vs storage consistency';

    public function handle(StorageProvider $storage): int
    {
        $this->info('Auditing media records...');

        $missing = [];
        $orphanDb = [];

        // Check DB records where file is missing from storage
        MediaModel::with('conversions')->whereNull('deleted_at')->chunkById(200, function ($chunk) use ($storage, &$missing) {
            foreach ($chunk as $model) {
                if (! $storage->exists($model->path)) {
                    $missing[] = [
                        'id' => $model->id,
                        'path' => $model->path,
                        'disk' => $model->disk,
                        'entity' => "{$model->entity_type}#{$model->entity_id}",
                    ];
                }

                foreach ($model->conversions as $conv) {
                    if (! $storage->exists($conv->path)) {
                        $missing[] = [
                            'id' => $conv->id,
                            'path' => $conv->path,
                            'disk' => $model->disk,
                            'entity' => "conversion of media#{$model->id}",
                        ];
                    }
                }
            }
        });

        if (empty($missing)) {
            $this->info('✓ All records have files in storage.');
        } else {
            $this->warn(count($missing).' records missing files:');
            $this->table(['ID', 'Path', 'Disk', 'Entity'], array_slice($missing, 0, 20));

            if (count($missing) > 20) {
                $this->line('  ... and '.(count($missing) - 20).' more');
            }

            if ($this->option('fix')) {
                if ($this->confirm('Delete '.count($missing).' orphan DB records?')) {
                    $bar = $this->output->createProgressBar(count($missing));
                    $deleted = 0;

                    foreach ($missing as $item) {
                        MediaModel::where('id', $item['id'])->forceDelete();
                        MediaConversionModel::where('id', $item['id'])->delete();
                        $deleted++;
                        $bar->advance();
                    }

                    $bar->finish();
                    $this->newLine();
                    $this->info("Deleted {$deleted} orphan records.");
                }
            }
        }

        // Orphan storage scan
        if ($this->option('orphans')) {
            $this->info('Orphan storage scan is not implemented — requires full bucket listing.');
            $this->line('For now, use `media:audit` without --orphans to find DB-side issues.');
        }

        return self::SUCCESS;
    }
}
