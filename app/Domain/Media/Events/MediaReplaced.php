<?php

declare(strict_types=1);

namespace App\Domain\Media\Events;

/**
 * Fired when a Media aggregate's underlying file is replaced.
 *
 * The aggregate identity (media.id) remains unchanged.
 * Old file cleanup is triggered asynchronously via application event.
 */
final class MediaReplaced
{
    public function __construct(
        public readonly ?int $mediaId,
        public readonly string $oldPath,
        public readonly string $newPath,
        public readonly ?string $newHash = null,
        public readonly int $version = 1,
    ) {}
}
