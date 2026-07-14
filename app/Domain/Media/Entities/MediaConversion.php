<?php

declare(strict_types=1);

namespace App\Domain\Media\Entities;

use App\Domain\Media\ValueObjects\MediaPath;
use App\Domain\Media\ValueObjects\MediaSize;
use App\Domain\Media\ValueObjects\MimeType;

/**
 * MediaConversion entity.
 *
 * Represents a derived file (thumbnail, WebP, resized variant) owned by a Media aggregate.
 * Not an aggregate root — always accessed through the parent Media.
 */
final class MediaConversion
{
    public function __construct(
        private ?int $id,
        private ?int $mediaId,
        private readonly string $conversionType,
        private readonly MediaPath $path,
        private readonly MimeType $mimeType,
        private readonly MediaSize $size,
        private readonly ?int $width = null,
        private readonly ?int $height = null,
        private readonly ?\DateTimeImmutable $createdAt = null,
    ) {}

    /**
     * The database identifier (null until persisted).
     */
    public function id(): ?int
    {
        return $this->id;
    }

    /**
     * The parent Media aggregate identifier.
     */
    public function mediaId(): ?int
    {
        return $this->mediaId;
    }

    /**
     * The conversion type (e.g. thumbnail, webp, medium).
     */
    public function conversionType(): string
    {
        return $this->conversionType;
    }

    /**
     * The storage path of the converted file.
     */
    public function path(): MediaPath
    {
        return $this->path;
    }

    /**
     * The MIME type of the converted file.
     */
    public function mimeType(): MimeType
    {
        return $this->mimeType;
    }

    /**
     * The file size of the converted file.
     */
    public function size(): MediaSize
    {
        return $this->size;
    }

    /**
     * The image width (null for non-images).
     */
    public function width(): ?int
    {
        return $this->width;
    }

    /**
     * The image height (null for non-images).
     */
    public function height(): ?int
    {
        return $this->height;
    }

    /**
     * The creation timestamp.
     */
    public function createdAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }
}
