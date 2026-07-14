<?php

declare(strict_types=1);

namespace App\Domain\Media\Events;

/**
 * Fired when a media record is soft-deleted.
 *
 * Triggers: audit trail, cleanup scheduling.
 */
final class MediaDeleted
{
    public function __construct(
        public readonly ?int $mediaId,
        public readonly string $path,
        public readonly bool $permanent = false,
    ) {}
}
