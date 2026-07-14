<?php

declare(strict_types=1);

namespace App\Infrastructure\Media\Persistence\Mappers;

use App\Domain\Media\Entities\Media;
use App\Domain\Media\Entities\MediaConversion;
use App\Domain\Media\Enums\MediaVisibility;
use App\Domain\Media\ValueObjects\MediaPath;
use App\Domain\Media\ValueObjects\MediaSize;
use App\Domain\Media\ValueObjects\MimeType;
use App\Infrastructure\Media\Persistence\Models\MediaConversionModel;
use App\Infrastructure\Media\Persistence\Models\MediaModel;

/**
 * Maps between Eloquent models and Domain entities.
 *
 * Pure data transformation — no I/O, no validation, no business decisions.
 */
final class MediaMapper
{
    /**
     * Map an Eloquent MediaModel (with optional conversions) to a Domain Media aggregate.
     */
    public function toDomain(MediaModel $model): Media
    {
        $conversions = $model->relationLoaded('conversions')
            ? $model->conversions->map(fn (MediaConversionModel $cm) => $this->conversionToDomain($cm))->toArray()
            : [];

        $media = new Media(
            id: $model->id,
            path: new MediaPath($model->path),
            mimeType: new MimeType($model->mime_type),
            size: new MediaSize($model->size_bytes),
            visibility: MediaVisibility::tryFrom($model->access_level) ?? MediaVisibility::PUBLIC,
            disk: $model->disk,
            hash: $model->hash_sha256,
            originalName: $model->original_name,
            width: $model->width,
            height: $model->height,
            entityType: $model->entity_type,
            entityId: $model->entity_id,
            uploadedBy: $model->uploaded_by,
            uploadedIp: $model->uploaded_ip,
            uploadedUserAgent: $model->uploaded_user_agent,
            metadata: $model->metadata,
            version: $model->version,
            isActive: $model->is_active,
            createdAt: $model->created_at?->toImmutable(),
            updatedAt: $model->updated_at?->toImmutable(),
            deletedAt: $model->deleted_at?->toImmutable(),
        );

        foreach ($conversions as $conversion) {
            $media->addConversion($conversion);
        }

        return $media;
    }

    /**
     * Map a Domain Media aggregate to an Eloquent-compatible attribute array.
     */
    public function toModelArray(Media $media): array
    {
        return [
            'path' => $media->path()->toString(),
            'mime_type' => $media->mimeType()->toString(),
            'size_bytes' => $media->size()->toBytes(),
            'access_level' => $media->visibility()->value,
            'disk' => $media->disk(),
            'hash_sha256' => $media->hash(),
            'original_name' => $media->originalName(),
            'width' => $media->width(),
            'height' => $media->height(),
            'entity_type' => $media->entityType(),
            'entity_id' => $media->entityId(),
            'uploaded_by' => $media->uploadedBy(),
            'uploaded_ip' => $media->uploadedIp(),
            'uploaded_user_agent' => $media->uploadedUserAgent(),
            'metadata' => $media->metadata(),
            'version' => $media->version(),
            'is_active' => $media->isActive(),
            'deleted_at' => $media->deletedAt(),
        ];
    }

    /**
     * Map an Eloquent MediaConversionModel to a Domain MediaConversion entity.
     */
    public function conversionToDomain(MediaConversionModel $model): MediaConversion
    {
        return new MediaConversion(
            id: $model->id,
            mediaId: $model->media_id,
            conversionType: $model->conversion_type,
            path: new MediaPath($model->path),
            mimeType: new MimeType($model->mime_type),
            size: new MediaSize($model->size_bytes),
            width: $model->width,
            height: $model->height,
            createdAt: $model->created_at?->toImmutable(),
        );
    }

    /**
     * Map a Domain MediaConversion entity to an Eloquent-compatible attribute array.
     */
    public function conversionToModelArray(MediaConversion $conversion): array
    {
        return [
            'media_id' => $conversion->mediaId(),
            'conversion_type' => $conversion->conversionType(),
            'path' => $conversion->path()->toString(),
            'mime_type' => $conversion->mimeType()->toString(),
            'size_bytes' => $conversion->size()->toBytes(),
            'width' => $conversion->width(),
            'height' => $conversion->height(),
        ];
    }
}
