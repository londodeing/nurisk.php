<?php

declare(strict_types=1);

namespace App\Domain\Media\Entities;

use App\Domain\Media\Enums\MediaVisibility;
use App\Domain\Media\Events\MediaDeleted;
use App\Domain\Media\Events\MediaReplaced;
use App\Domain\Media\ValueObjects\MediaPath;
use App\Domain\Media\ValueObjects\MediaSize;
use App\Domain\Media\ValueObjects\MimeType;

/**
 * Media aggregate root.
 *
 * Represents an uploaded file with its metadata, access control, and derived conversions.
 * All state mutations must go through this aggregate.
 */
final class Media
{
    /** @var MediaConversion[] */
    private array $conversions = [];

    /** @var object[] */
    private array $domainEvents = [];

    public function __construct(
        private ?int $id,
        private MediaPath $path,
        private MimeType $mimeType,
        private MediaSize $size,
        private MediaVisibility $visibility,
        private readonly string $disk,
        private ?string $hash = null,
        private ?string $originalName = null,
        private ?int $width = null,
        private ?int $height = null,
        private ?string $entityType = null,
        private ?int $entityId = null,
        private readonly ?int $uploadedBy = null,
        private readonly ?string $uploadedIp = null,
        private readonly ?string $uploadedUserAgent = null,
        private ?array $metadata = null,
        private int $version = 1,
        private bool $isActive = true,
        private ?\DateTimeImmutable $createdAt = null,
        private ?\DateTimeImmutable $updatedAt = null,
        private ?\DateTimeImmutable $deletedAt = null,
    ) {}

    /**
     * The database identifier (null until persisted).
     */
    public function id(): ?int
    {
        return $this->id;
    }

    /**
     * The storage path of the file.
     */
    public function path(): MediaPath
    {
        return $this->path;
    }

    /**
     * The MIME type of the file.
     */
    public function mimeType(): MimeType
    {
        return $this->mimeType;
    }

    /**
     * The file size.
     */
    public function size(): MediaSize
    {
        return $this->size;
    }

    /**
     * The current visibility level.
     */
    public function visibility(): MediaVisibility
    {
        return $this->visibility;
    }

    /**
     * The storage disk name.
     */
    public function disk(): string
    {
        return $this->disk;
    }

    /**
     * The SHA-256 hash of the file contents.
     */
    public function hash(): ?string
    {
        return $this->hash;
    }

    /**
     * The original uploaded filename.
     */
    public function originalName(): ?string
    {
        return $this->originalName;
    }

    /**
     * The image width in pixels (null for non-images).
     */
    public function width(): ?int
    {
        return $this->width;
    }

    /**
     * The image height in pixels (null for non-images).
     */
    public function height(): ?int
    {
        return $this->height;
    }

    /**
     * The associated entity type (null if unassociated).
     */
    public function entityType(): ?string
    {
        return $this->entityType;
    }

    /**
     * The associated entity identifier (null if unassociated).
     */
    public function entityId(): ?int
    {
        return $this->entityId;
    }

    /**
     * The user who uploaded the file (null for anonymous uploads).
     */
    public function uploadedBy(): ?int
    {
        return $this->uploadedBy;
    }

    /**
     * The uploader's IP address.
     */
    public function uploadedIp(): ?string
    {
        return $this->uploadedIp;
    }

    /**
     * The uploader's User-Agent string.
     */
    public function uploadedUserAgent(): ?string
    {
        return $this->uploadedUserAgent;
    }

    /**
     * Arbitrary metadata stored as a key-value map.
     */
    public function metadata(): ?array
    {
        return $this->metadata;
    }

    /**
     * The version number (increments on replacement).
     */
    public function version(): int
    {
        return $this->version;
    }

    /**
     * Returns true if this is the currently active version.
     */
    public function isActive(): bool
    {
        return $this->isActive;
    }

    /**
     * Returns true if this media has been soft-deleted.
     */
    public function isDeleted(): bool
    {
        return $this->deletedAt !== null;
    }

    /**
     * The creation timestamp.
     */
    public function createdAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    /**
     * The last update timestamp.
     */
    public function updatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    /**
     * The soft-delete timestamp (null if not deleted).
     */
    public function deletedAt(): ?\DateTimeImmutable
    {
        return $this->deletedAt;
    }

    /**
     * The derived conversion entities owned by this aggregate.
     *
     * @return MediaConversion[]
     */
    public function conversions(): array
    {
        return $this->conversions;
    }

    // ─── State mutation methods ───────────────────────────────────────

    /**
     * Associate this media with an entity.
     */
    public function associateTo(string $entityType, int $entityId): void
    {
        $this->entityType = $entityType;
        $this->entityId = $entityId;
        $this->updatedAt = new \DateTimeImmutable;
    }

    /**
     * Change the media visibility level.
     */
    public function changeVisibility(MediaVisibility $visibility): void
    {
        $this->visibility = $visibility;
        $this->updatedAt = new \DateTimeImmutable;
    }

    /**
     * Add a derived conversion to this aggregate.
     */
    public function addConversion(MediaConversion $conversion): void
    {
        $this->conversions[] = $conversion;
        $this->updatedAt = new \DateTimeImmutable;
    }

    /**
     * Remove all conversions of a given type.
     */
    public function removeConversionsOfType(string $conversionType): void
    {
        $this->conversions = array_values(
            array_filter(
                $this->conversions,
                fn (MediaConversion $c) => $c->conversionType() !== $conversionType,
            ),
        );
        $this->updatedAt = new \DateTimeImmutable;
    }

    /**
     * Mark this media as inactive (superseded by a newer version).
     */
    public function markAsInactive(): void
    {
        $this->isActive = false;
        $this->updatedAt = new \DateTimeImmutable;
    }

    /**
     * Soft-delete this media.
     *
     * File on disk is preserved for audit purposes.
     * Use forceDelete() to remove permanently.
     */
    public function delete(): void
    {
        if ($this->isDeleted()) {
            return;
        }

        $this->deletedAt = new \DateTimeImmutable;
        $this->isActive = false;
        $this->updatedAt = new \DateTimeImmutable;

        $this->recordEvent(new MediaDeleted(
            mediaId: $this->id,
            path: $this->path->toString(),
            permanent: false,
        ));
    }

    /**
     * Replace the underlying file and metadata while preserving identity.
     *
     * The old file is NOT deleted here — cleanup happens asynchronously
     * via MediaReplacementRequested application event.
     */
    public function replaceWith(
        MediaPath $newPath,
        MimeType $newMimeType,
        MediaSize $newSize,
        ?string $newHash = null,
        ?string $newOriginalName = null,
        ?int $newWidth = null,
        ?int $newHeight = null,
    ): void {
        $oldPath = $this->path;

        $this->path = $newPath;
        $this->mimeType = $newMimeType;
        $this->size = $newSize;
        $this->hash = $newHash;
        $this->originalName = $newOriginalName;
        $this->width = $newWidth;
        $this->height = $newHeight;
        $this->version++;
        $this->updatedAt = new \DateTimeImmutable;

        $this->recordEvent(new MediaReplaced(
            mediaId: $this->id,
            oldPath: $oldPath->toString(),
            newPath: $newPath->toString(),
            newHash: $newHash,
            version: $this->version,
        ));
    }

    /**
     * Restore a soft-deleted media record.
     */
    public function restore(): void
    {
        $this->deletedAt = null;
        $this->updatedAt = new \DateTimeImmutable;
    }

    // ─── Domain events ───────────────────────────────────────────────

    /**
     * Record a domain event for later dispatch.
     */
    public function recordEvent(object $event): void
    {
        $this->domainEvents[] = $event;
    }

    /**
     * Release and clear all recorded domain events.
     *
     * @return object[]
     */
    public function releaseEvents(): array
    {
        $events = $this->domainEvents;
        $this->domainEvents = [];

        return $events;
    }
}
