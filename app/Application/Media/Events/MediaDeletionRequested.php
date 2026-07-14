<?php

declare(strict_types=1);

namespace App\Application\Media\Events;

/**
 * Application event requesting async deletion of a file from object storage.
 *
 * Dispatched after a Media aggregate has been soft-deleted.
 * A queue listener picks this up and performs the actual storage deletion,
 * enabling retry without blocking the HTTP response.
 */
final class MediaDeletionRequested
{
    public function __construct(
        public readonly int $mediaId,
        public readonly string $path,
        public readonly string $disk,
    ) {}
}
