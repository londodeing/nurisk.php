<?php

declare(strict_types=1);

namespace App\Application\Media\Events;

/**
 * Application event requesting async WebP conversion.
 */
final class WebpConversionRequested
{
    public function __construct(
        public readonly int $mediaId,
    ) {}
}
