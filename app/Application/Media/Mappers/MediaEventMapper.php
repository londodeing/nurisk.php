<?php

declare(strict_types=1);

namespace App\Application\Media\Mappers;

use App\Application\Media\Events\MediaDeletionRequested;
use App\Application\Media\Events\MediaReplacementRequested;
use App\Application\Media\Events\ThumbnailGenerationRequested;
use App\Application\Media\Events\WebpConversionRequested;
use App\Domain\Media\Entities\Media;
use App\Domain\Media\Events\MediaDeleted;
use App\Domain\Media\Events\MediaReplaced;
use App\Domain\Media\Events\MediaUploaded;

/**
 * Translates Media domain events into application integration events.
 *
 * Domain events are business facts. Application events are integration
 * boundaries (queue jobs, notifications, cross-service communication).
 */
final class MediaEventMapper
{
    /**
     * Map a single domain event to zero or more application events.
     *
     * @return object[]
     */
    public function map(object $domainEvent, Media $media): array
    {
        return match ($domainEvent::class) {
            MediaUploaded::class => [
                new ThumbnailGenerationRequested(
                    mediaId: $media->id(),
                    mimeType: $media->mimeType()->toString(),
                ),
                new WebpConversionRequested(
                    mediaId: $media->id(),
                ),
            ],
            MediaDeleted::class => [
                new MediaDeletionRequested(
                    mediaId: $media->id(),
                    path: $media->path()->toString(),
                    disk: $media->disk(),
                ),
            ],
            MediaReplaced::class => [
                new MediaReplacementRequested(
                    mediaId: $media->id(),
                    oldPath: $domainEvent->oldPath,
                    disk: $media->disk(),
                ),
                new ThumbnailGenerationRequested(
                    mediaId: $media->id(),
                    mimeType: $media->mimeType()->toString(),
                ),
                new WebpConversionRequested(
                    mediaId: $media->id(),
                ),
            ],
            default => [],
        };
    }
}
