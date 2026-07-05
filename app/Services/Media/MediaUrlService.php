<?php

namespace App\Services\Media;

use Illuminate\Support\Facades\Storage;

class MediaUrlService
{
    public function url(string $path, ?string $disk = 'public'): ?string
    {
        if (empty($path)) {
            return null;
        }

        return Storage::disk($disk)->url($path);
    }

    public function exists(string $path, ?string $disk = 'public'): bool
    {
        if (empty($path)) {
            return false;
        }

        return Storage::disk($disk)->exists($path);
    }

    public function temporaryUrl(string $path, int $expiresInMinutes = 5, ?string $disk = 'public'): ?string
    {
        if (empty($path)) {
            return null;
        }

        $diskDriver = Storage::disk($disk);

        if (method_exists($diskDriver, 'temporaryUrl')) {
            return $diskDriver->temporaryUrl($path, now()->addMinutes($expiresInMinutes));
        }

        return $diskDriver->url($path);
    }

    public function download(string $path, ?string $name = null, ?string $disk = 'public')
    {
        if (empty($path)) {
            return null;
        }

        return Storage::disk($disk)->download($path, $name);
    }
}
