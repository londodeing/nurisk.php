<?php

declare(strict_types=1);

namespace App\Domain\Media\Events;

use App\Domain\Media\Enums\MediaVisibility;
use App\Domain\Media\ValueObjects\MediaPath;
use App\Domain\Media\ValueObjects\MediaSize;
use App\Domain\Media\ValueObjects\MimeType;

/**
 * Fired when a new media file has been uploaded and stored.
 *
 * Triggers: async thumbnail generation, WebP conversion, audit log.
 */
final class MediaUploaded
{
    public function __construct(
        public readonly MediaPath $path,
        public readonly MimeType $mimeType,
        public readonly MediaSize $size,
        public readonly MediaVisibility $visibility,
        public readonly ?string $hash = null,
        public readonly ?string $entityType = null,
        public readonly ?int $entityId = null,
    ) {}
}
