<?php

declare(strict_types=1);

namespace App\Application\Media\DTOs;

/**
 * Public response DTO for media operations.
 *
 * Conversions are included as a simple array within this response;
 * no separate MediaConversionResponse DTO is needed at this stage.
 */
final readonly class MediaResponse
{
    public function __construct(
        public int $id,
        public string $path,
        public ?string $url,
        public string $mimeType,
        public int $size,
        public string $visibility,
        public string $entityType,
        public int $entityId,
        public array $conversions,
        public string $createdAt,
        public ?string $deletedAt = null,
    ) {}
}
