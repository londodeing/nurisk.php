<?php

namespace App\Services\Media;

class MediaUploadResult
{
    public function __construct(
        public readonly string $path,
        public readonly string $originalName,
        public readonly string $mimeType,
        public readonly int $sizeBytes,
        public readonly ?string $hashSha256 = null,
        public readonly ?int $width = null,
        public readonly ?int $height = null,
        public readonly ?int $mediaId = null,
        public readonly bool $duplicate = false,
        public readonly ?int $version = null,
    ) {}
}
