<?php

declare(strict_types=1);

namespace App\Application\Media\Events;

/**
 * Application event requesting async cleanup of the replaced file from object storage.
 *
 * Dispatched after replaceWith() has persisted the new file metadata.
 * A queue listener picks this up and deletes the old file,
 * enabling retry without blocking the HTTP response.
 */
final class MediaReplacementRequested
{
    public function __construct(
        public readonly int $mediaId,
        public readonly string $oldPath,
        public readonly string $disk,
    ) {}
}
