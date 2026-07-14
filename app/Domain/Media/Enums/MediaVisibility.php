<?php

declare(strict_types=1);

namespace App\Domain\Media\Enums;

/**
 * Media visibility levels.
 *
 * Horizon 1: PUBLIC and PRIVATE only.
 * Future: INTERNAL, CONFIDENTIAL, SECRET.
 */
enum MediaVisibility: string
{
    case PUBLIC = 'PUBLIC';
    case PRIVATE = 'PRIVATE';

    /**
     * Returns true if this media is publicly accessible.
     */
    public function isPublic(): bool
    {
        return $this === self::PUBLIC;
    }

    /**
     * Returns true if this media requires authentication.
     */
    public function isPrivate(): bool
    {
        return $this === self::PRIVATE;
    }
}
