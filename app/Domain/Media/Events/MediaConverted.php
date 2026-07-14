<?php

declare(strict_types=1);

namespace App\Domain\Media\Events;

use App\Domain\Media\ValueObjects\MediaPath;

/**
 * Fired when a media conversion (thumbnail, WebP, etc.) has been created.
 *
 * Triggers: notification that derivative is ready for use.
 */
final class MediaConverted
{
    public function __construct(
        public readonly ?int $mediaId,
        public readonly string $conversionType,
        public readonly MediaPath $path,
    ) {}
}
