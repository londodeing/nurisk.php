<?php

declare(strict_types=1);

namespace App\Domain\Media\Contracts;

use App\Domain\Media\Entities\Media;
use App\Domain\Media\ValueObjects\MediaPath;

/**
 * Repository contract for Media aggregate persistence.
 *
 * Implementations: EloquentMediaRepository (Infrastructure layer).
 */
interface MediaRepository
{
    /**
     * Persist a Media aggregate (insert or update).
     */
    public function save(Media $media): void;

    /**
     * Find a Media aggregate by its identifier.
     *
     * @return Media|null null if not found
     */
    public function find(int $id): ?Media;

    /**
     * Find a Media aggregate by its storage path and disk.
     *
     * @return Media|null null if not found
     */
    public function findByPath(MediaPath $path, string $disk): ?Media;

    /**
     * Find active media records associated with a specific entity.
     *
     * @return Media[]
     */
    public function findActiveByEntity(string $entityType, int $entityId): array;

    /**
     * Find all media records associated with a specific entity (including inactive).
     *
     * @return Media[]
     */
    public function findAllByEntity(string $entityType, int $entityId): array;

    /**
     * Find an active media record by content hash and entity association.
     *
     * Used by MediaDeduplicationService for content-based deduplication.
     *
     * @return Media|null null if no matching active media exists
     */
    public function findByHashAndEntity(string $hash, string $entityType, int $entityId): ?Media;

    /**
     * Permanently delete a Media aggregate by its identifier.
     */
    public function delete(int $id): void;
}
