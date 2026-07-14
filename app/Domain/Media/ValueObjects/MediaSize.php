<?php

declare(strict_types=1);

namespace App\Domain\Media\ValueObjects;

/**
 * Immutable value object representing a file size in bytes.
 */
readonly class MediaSize
{
    public function __construct(
        public int $bytes,
    ) {
        if ($bytes <= 0) {
            throw new \InvalidArgumentException('File size must be greater than 0 bytes.');
        }
    }

    /**
     * Returns the size in bytes.
     */
    public function toBytes(): int
    {
        return $this->bytes;
    }

    /**
     * Returns the size in kilobytes (rounded up).
     */
    public function toKilobytes(): int
    {
        return (int) ceil($this->bytes / 1024);
    }

    /**
     * Returns the size in megabytes (2 decimal places).
     */
    public function toMegabytes(): float
    {
        return round($this->bytes / (1024 * 1024), 2);
    }

    /**
     * Returns true if both sizes are identical.
     */
    public function equals(self $other): bool
    {
        return $this->bytes === $other->bytes;
    }
}
