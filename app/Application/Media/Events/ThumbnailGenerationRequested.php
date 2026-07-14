<?php

declare(strict_types=1);

namespace App\Application\Media\Events;

/**
 * Application event requesting async thumbnail generation.
 */
final class ThumbnailGenerationRequested
{
    public function __construct(
        public readonly int $mediaId,
        public readonly string $mimeType,
    ) {}
}
