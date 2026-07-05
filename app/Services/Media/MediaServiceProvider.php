<?php

namespace App\Services\Media;

use App\Services\Media\Contracts\MediaAntivirusHook;
use App\Services\Media\Hooks\NullAntivirusHook;
use Illuminate\Support\ServiceProvider;

class MediaServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(MediaPolicy::class);
        $this->app->singleton(MediaFilenameGenerator::class);
        $this->app->singleton(MediaDeleteService::class);
        $this->app->singleton(MediaUrlService::class);

        $this->app->bind(MediaAntivirusHook::class, NullAntivirusHook::class);

        $this->app->singleton(MediaUploadService::class, function ($app) {
            return new MediaUploadService(
                $app->make(MediaPolicy::class),
                $app->make(MediaFilenameGenerator::class),
                $app->make(MediaAntivirusHook::class),
            );
        });

        $this->app->singleton(MediaReplaceService::class, function ($app) {
            return new MediaReplaceService(
                $app->make(MediaUploadService::class),
                $app->make(MediaDeleteService::class),
            );
        });
    }
}
