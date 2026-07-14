<?php

declare(strict_types=1);

namespace App\Infrastructure\Media\Storage\Adapters;

use App\Infrastructure\Media\Storage\Contracts\StorageProvider;

/**
 * In-memory fake storage provider for testing.
 *
 * Stores all files in an in-memory array — no disk I/O.
 * Use in unit tests to avoid filesystem dependencies.
 */
final class FakeStorageProvider implements StorageProvider
{
    /** @var array<string, string> path => contents */
    private array $files = [];

    /** @var array<string, int> path => size */
    private array $sizes = [];

    public function store(string $path, string $sourcePath, array $options = []): bool
    {
        $contents = @file_get_contents($sourcePath);

        if ($contents === false) {
            return false;
        }

        $this->files[$path] = $contents;
        $this->sizes[$path] = strlen($contents);

        return true;
    }

    public function storeContents(string $path, string $contents, array $options = []): bool
    {
        $this->files[$path] = $contents;
        $this->sizes[$path] = strlen($contents);

        return true;
    }

    public function delete(string $path): bool
    {
        $exists = isset($this->files[$path]);
        unset($this->files[$path], $this->sizes[$path]);

        return $exists;
    }

    public function exists(string $path): bool
    {
        return isset($this->files[$path]);
    }

    public function url(string $path): ?string
    {
        if (! isset($this->files[$path])) {
            return null;
        }

        return "/storage/{$path}";
    }

    public function temporaryUrl(string $path, int $expiresMinutes): ?string
    {
        return $this->url($path);
    }

    public function size(string $path): int
    {
        return $this->sizes[$path] ?? 0;
    }

    public function move(string $fromPath, string $toPath): bool
    {
        if (! isset($this->files[$fromPath])) {
            return false;
        }

        $this->files[$toPath] = $this->files[$fromPath];
        $this->sizes[$toPath] = $this->sizes[$fromPath];
        unset($this->files[$fromPath], $this->sizes[$fromPath]);

        return true;
    }

    public function copy(string $fromPath, string $toPath): bool
    {
        if (! isset($this->files[$fromPath])) {
            return false;
        }

        $this->files[$toPath] = $this->files[$fromPath];
        $this->sizes[$toPath] = $this->sizes[$fromPath];

        return true;
    }

    /**
     * Get all stored file paths.
     *
     * @return string[]
     */
    public function allPaths(): array
    {
        return array_keys($this->files);
    }

    /**
     * Get the contents of a stored file.
     */
    public function read(string $path): ?string
    {
        return $this->files[$path] ?? null;
    }

    /**
     * Clear all stored files.
     */
    public function clear(): void
    {
        $this->files = [];
        $this->sizes = [];
    }
}
