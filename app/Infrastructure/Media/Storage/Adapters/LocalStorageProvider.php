<?php

declare(strict_types=1);

namespace App\Infrastructure\Media\Storage\Adapters;

use App\Infrastructure\Media\Storage\Contracts\StorageProvider;
use Illuminate\Support\Facades\Storage;

/**
 * Local filesystem storage provider.
 *
 * Wraps Laravel's Storage facade for the local/public disk.
 */
final class LocalStorageProvider implements StorageProvider
{
    public function __construct(
        private readonly string $disk = 'public',
    ) {}

    public function store(string $path, string $sourcePath, array $options = []): bool
    {
        $stream = @fopen($sourcePath, 'r');

        if ($stream === false) {
            return false;
        }

        try {
            return Storage::disk($this->disk)->put($path, $stream, $options);
        } finally {
            if (is_resource($stream)) {
                fclose($stream);
            }
        }
    }

    public function storeContents(string $path, string $contents, array $options = []): bool
    {
        return Storage::disk($this->disk)->put($path, $contents, $options);
    }

    public function delete(string $path): bool
    {
        return Storage::disk($this->disk)->delete($path);
    }

    public function exists(string $path): bool
    {
        return Storage::disk($this->disk)->exists($path);
    }

    public function url(string $path): ?string
    {
        return Storage::disk($this->disk)->url($path);
    }

    public function temporaryUrl(string $path, int $expiresMinutes): ?string
    {
        return Storage::disk($this->disk)->temporaryUrl(
            $path,
            now()->addMinutes($expiresMinutes),
        );
    }

    public function size(string $path): int
    {
        return Storage::disk($this->disk)->size($path);
    }

    public function move(string $fromPath, string $toPath): bool
    {
        return Storage::disk($this->disk)->move($fromPath, $toPath);
    }

    public function copy(string $fromPath, string $toPath): bool
    {
        return Storage::disk($this->disk)->copy($fromPath, $toPath);
    }
}
