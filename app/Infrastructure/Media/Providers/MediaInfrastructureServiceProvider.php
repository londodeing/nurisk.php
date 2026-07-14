<?php

declare(strict_types=1);

namespace App\Infrastructure\Media\Providers;

use App\Application\Media\Contracts\EventPublisher;
use App\Application\Media\Mappers\MediaEventMapper;
use App\Application\Media\Services\MediaEventPublisher;
use App\Domain\Media\Contracts\MediaRepository;
use App\Infrastructure\Media\Persistence\Mappers\MediaMapper;
use App\Infrastructure\Media\Persistence\Repositories\EloquentMediaRepository;
use App\Infrastructure\Media\Storage\Adapters\LocalStorageProvider;
use App\Infrastructure\Media\Storage\Adapters\MinioStorageProvider;
use App\Infrastructure\Media\Storage\Contracts\StorageProvider;
use Illuminate\Support\ServiceProvider;

/**
 * Registers Infrastructure-layer bindings for the Media module.
 *
 * New code should inject MediaRepository and StorageProvider interfaces.
 * Legacy App\Services\Media layer has been removed (Task 4).
 */
final class MediaInfrastructureServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(MediaMapper::class);

        $this->app->singleton(StorageProvider::class, function ($app) {
            $disk = config('filesystems.default', 'local');

            return match ($disk) {
                's3' => $app->make(MinioStorageProvider::class),
                default => $app->make(LocalStorageProvider::class),
            };
        });

        $this->app->bind(MediaRepository::class, EloquentMediaRepository::class);

        $this->app->singleton(MediaEventMapper::class);
        $this->app->bind(EventPublisher::class, MediaEventPublisher::class);
    }
}
