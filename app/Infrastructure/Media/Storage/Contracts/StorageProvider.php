<?php

declare(strict_types=1);

namespace App\Infrastructure\Media\Storage\Contracts;

/**
 * Storage provider contract for file persistence.
 *
 * Implementations wrap specific storage backends (local filesystem, MinIO/S3, in-memory for testing).
 * No business logic — pure I/O operations.
 */
interface StorageProvider
{
    /**
     * Store a file from a source path.
     */
    public function store(string $path, string $sourcePath, array $options = []): bool;

    /**
     * Store a file from a string stream.
     */
    public function storeContents(string $path, string $contents, array $options = []): bool;

    /**
     * Delete a file.
     */
    public function delete(string $path): bool;

    /**
     * Check if a file exists.
     */
    public function exists(string $path): bool;

    /**
     * Get the public URL for a file.
     */
    public function url(string $path): ?string;

    /**
     * Get a temporary signed URL for a file.
     */
    public function temporaryUrl(string $path, int $expiresMinutes): ?string;

    /**
     * Get the file size in bytes.
     */
    public function size(string $path): int;

    /**
     * Move a file from one path to another.
     */
    public function move(string $fromPath, string $toPath): bool;

    /**
     * Copy a file from one path to another.
     */
    public function copy(string $fromPath, string $toPath): bool;
}
