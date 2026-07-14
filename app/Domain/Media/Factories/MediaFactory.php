<?php

declare(strict_types=1);

namespace App\Domain\Media\Factories;

use App\Domain\Media\Entities\Media;
use App\Domain\Media\Entities\MediaConversion;
use App\Domain\Media\Enums\MediaVisibility;
use App\Domain\Media\Events\MediaConverted;
use App\Domain\Media\Events\MediaUploaded;
use App\Domain\Media\ValueObjects\MediaPath;
use App\Domain\Media\ValueObjects\MediaSize;
use App\Domain\Media\ValueObjects\MimeType;

/**
 * Factory for creating Media aggregate instances.
 *
 * Ensures all invariants are satisfied at construction time.
 */
class MediaFactory
{
    /**
     * Create a new Media aggregate from an uploaded file.
     *
     * Records a MediaUploaded domain event.
     */
    public function createUploaded(
        MediaPath $path,
        MimeType $mimeType,
        MediaSize $size,
        MediaVisibility $visibility,
        string $disk,
        ?string $hash = null,
        ?string $originalName = null,
        ?int $width = null,
        ?int $height = null,
        ?string $entityType = null,
        ?int $entityId = null,
        ?int $uploadedBy = null,
        ?string $uploadedIp = null,
        ?string $uploadedUserAgent = null,
        ?array $metadata = null,
    ): Media {
        $media = new Media(
            id: null,
            path: $path,
            mimeType: $mimeType,
            size: $size,
            visibility: $visibility,
            disk: $disk,
            hash: $hash,
            originalName: $originalName,
            width: $width,
            height: $height,
            entityType: $entityType,
            entityId: $entityId,
            uploadedBy: $uploadedBy,
            uploadedIp: $uploadedIp,
            uploadedUserAgent: $uploadedUserAgent,
            metadata: $metadata,
            version: 1,
            isActive: true,
        );

        $media->recordEvent(new MediaUploaded(
            path: $path,
            mimeType: $mimeType,
            size: $size,
            visibility: $visibility,
            hash: $hash,
            entityType: $entityType,
            entityId: $entityId,
        ));

        return $media;
    }

    /**
     * Create a MediaConversion owned by a Media aggregate.
     *
     * Records a MediaConverted domain event.
     */
    public function createConversion(
        Media $media,
        string $conversionType,
        MediaPath $path,
        MimeType $mimeType,
        MediaSize $size,
        ?int $width = null,
        ?int $height = null,
    ): MediaConversion {
        $conversion = new MediaConversion(
            id: null,
            mediaId: $media->id(),
            conversionType: $conversionType,
            path: $path,
            mimeType: $mimeType,
            size: $size,
            width: $width,
            height: $height,
        );

        $media->recordEvent(new MediaConverted(
            mediaId: $media->id(),
            conversionType: $conversionType,
            path: $path,
        ));

        return $conversion;
    }
}
