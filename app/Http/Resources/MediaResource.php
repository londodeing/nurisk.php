<?php

namespace App\Http\Resources;

use App\Application\Media\DTOs\MediaResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MediaResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /** @var MediaResponse $media */
        $media = $this->resource;

        return [
            'id' => $media->id,
            'path' => $media->path,
            'url' => $media->url,
            'mime_type' => $media->mimeType,
            'size_bytes' => $media->size,
            'visibility' => $media->visibility,
            'entity_type' => $media->entityType,
            'entity_id' => $media->entityId,
            'conversions' => $media->conversions,
            'created_at' => $media->createdAt,
            'deleted_at' => $media->deletedAt,
        ];
    }
}
