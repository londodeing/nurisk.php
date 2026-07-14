<?php

declare(strict_types=1);

namespace App\Domain\Media\ValueObjects;

/**
 * Immutable value object representing a file storage path.
 */
readonly class MediaPath
{
    public function __construct(
        public string $path,
    ) {
        if (trim($path) === '') {
            throw new \InvalidArgumentException('Media path cannot be empty.');
        }

        if (mb_strlen($path) > 500) {
            throw new \InvalidArgumentException('Media path cannot exceed 500 characters.');
        }
    }

    /**
     * Returns the raw path string.
     */
    public function toString(): string
    {
        return $this->path;
    }

    /**
     * Returns true if both paths are identical.
     */
    public function equals(self $other): bool
    {
        return $this->path === $other->path;
    }

    /**
     * Returns the file extension (lowercase, without dot).
     */
    public function extension(): string
    {
        return strtolower(pathinfo($this->path, PATHINFO_EXTENSION));
    }

    /**
     * Returns the directory portion of the path.
     */
    public function dirname(): string
    {
        $dir = pathinfo($this->path, PATHINFO_DIRNAME);

        return $dir === '.' ? '' : $dir;
    }

    /**
     * Returns the filename without extension.
     */
    public function filename(): string
    {
        return pathinfo($this->path, PATHINFO_FILENAME);
    }
}
