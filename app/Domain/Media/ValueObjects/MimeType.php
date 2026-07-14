<?php

declare(strict_types=1);

namespace App\Domain\Media\ValueObjects;

/**
 * Immutable value object representing a MIME type.
 */
readonly class MimeType
{
    private const IMAGE_PREFIX = 'image/';

    private const VIDEO_PREFIX = 'video/';

    private const AUDIO_PREFIX = 'audio/';

    public function __construct(
        public string $type,
    ) {
        if (trim($type) === '') {
            throw new \InvalidArgumentException('MIME type cannot be empty.');
        }

        if (! str_contains($type, '/')) {
            throw new \InvalidArgumentException("Invalid MIME type format: {$type}");
        }
    }

    /**
     * Returns the raw MIME type string.
     */
    public function toString(): string
    {
        return $this->type;
    }

    /**
     * Returns true if both MIME types are identical.
     */
    public function equals(self $other): bool
    {
        return $this->type === $other->type;
    }

    /**
     * Returns true if this is an image type (image/*).
     */
    public function isImage(): bool
    {
        return str_starts_with($this->type, self::IMAGE_PREFIX);
    }

    /**
     * Returns true if this is a video type (video/*).
     */
    public function isVideo(): bool
    {
        return str_starts_with($this->type, self::VIDEO_PREFIX);
    }

    /**
     * Returns true if this is an audio type (audio/*).
     */
    public function isAudio(): bool
    {
        return str_starts_with($this->type, self::AUDIO_PREFIX);
    }

    /**
     * Returns true if this is a document type (not image, video, or audio).
     */
    public function isDocument(): bool
    {
        return ! $this->isImage() && ! $this->isVideo() && ! $this->isAudio();
    }
}
