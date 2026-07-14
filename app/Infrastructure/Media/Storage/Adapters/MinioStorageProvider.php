<?php

declare(strict_types=1);

namespace App\Infrastructure\Media\Storage\Adapters;

use App\Infrastructure\Media\Storage\Contracts\StorageProvider;
use Illuminate\Support\Facades\Storage;

/**
 * MinIO (S3-compatible) storage provider.
 *
 * Wraps Laravel's S3 filesystem driver configured for MinIO.
 * Environment variables in .env:
 *   FILESYSTEM_DISK=s3
 *   AWS_ACCESS_KEY_ID=...
 *   AWS_SECRET_ACCESS_KEY=...
 *   AWS_DEFAULT_REGION=auto
 *   AWS_BUCKET=nurisk-media
 *   AWS_ENDPOINT=http://minio:9000
 *   AWS_USE_PATH_STYLE_ENDPOINT=true
 */
final class MinioStorageProvider implements StorageProvider
{
    public function __construct(
        private readonly string $disk = 's3',
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
        return Storage::disk($this->disk)->temporaryUrl(
            $path,
            now()->addHours(24),
        );
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
