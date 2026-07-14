<?php

namespace App\Console\Commands;

use App\Domain\Media\Enums\MediaVisibility;
use App\Domain\Media\Factories\MediaFactory;
use App\Domain\Media\ValueObjects\MediaPath;
use App\Domain\Media\ValueObjects\MediaSize;
use App\Domain\Media\ValueObjects\MimeType;
use App\Infrastructure\Media\Persistence\Repositories\EloquentMediaRepository;
use App\Models\Media;
use Illuminate\Console\Command;

class MigrateLegacyMedia extends Command
{
    protected $signature = 'media:migrate-legacy
        {--dry-run : Preview changes without persisting}
        {--force : Skip confirmation prompt}';

    protected $description = 'Migrate existing media from legacy to new DDD structure';

    public function handle(
        EloquentMediaRepository $repository,
        MediaFactory $factory,
    ): int {
        $dryRun = (bool) $this->option('dry-run');

        if ($dryRun) {
            $this->info('DRY RUN — no changes will be persisted.');
        }

        $total = Media::whereNull('migrated_at')->count();

        if ($total === 0) {
            $this->info('No legacy records to migrate.');

            return self::SUCCESS;
        }

        if (! $dryRun && ! $this->option('force')) {
            if (! $this->confirm("Migrate {$total} legacy media records?")) {
                return self::FAILURE;
            }
        }

        $bar = $this->output->createProgressBar($total);
        $bar->start();

        $migrated = 0;
        $errors = [];

        Media::whereNull('migrated_at')
            ->chunkById(100, function ($chunk) use ($factory, $repository, $dryRun, &$migrated, &$errors, $bar) {
                foreach ($chunk as $legacy) {
                    try {
                        $media = $factory->createUploaded(
                            path: new MediaPath($legacy->path),
                            mimeType: new MimeType($legacy->mime_type ?? 'application/octet-stream'),
                            size: new MediaSize($legacy->size_bytes ?? 0),
                            visibility: MediaVisibility::tryFrom($legacy->access_level ?? 'PUBLIC') ?? MediaVisibility::PUBLIC,
                            disk: $legacy->disk ?? 'public',
                            hash: $legacy->hash_sha256,
                            originalName: $legacy->original_name,
                            width: $legacy->width,
                            height: $legacy->height,
                            entityType: $legacy->entity_type,
                            entityId: $legacy->entity_id,
                            uploadedBy: $legacy->uploaded_by,
                        );

                        if (! $dryRun) {
                            $repository->save($media);
                            $media->releaseEvents();

                            $legacy->migrated_at = now();
                            $legacy->save();
                        }

                        $migrated++;
                    } catch (\Throwable $e) {
                        $errors[] = "Record {$legacy->id}: {$e->getMessage()}";
                    }

                    $bar->advance();
                }
            });

        $bar->finish();
        $this->newLine();

        $this->info("Migrated {$migrated}/{$total} records.");

        if (! empty($errors)) {
            $this->warn('Errors:');
            foreach ($errors as $error) {
                $this->line("  • {$error}");
            }

            return self::FAILURE;
        }

        return self::SUCCESS;
    }
}
