<?php

declare(strict_types=1);

namespace App\Domain\Media\Services;

use App\Domain\Media\Contracts\MediaRepository;
use App\Domain\Media\Entities\Media;

/**
 * Domain service for content-based media deduplication.
 *
 * Encapsulates the business rule: "a file with the same content hash
 * can be reused for the same entity association."
 *
 * Handlers call this service instead of querying the repository directly.
 */
final class MediaDeduplicationService
{
    public function __construct(
        private readonly MediaRepository $repository,
    ) {}

    /**
     * Find an active, reusable media record for the given content hash
     * and entity association. Returns null when no match exists.
     */
    public function findReusable(string $hash, string $entityType, int $entityId): ?Media
    {
        if ($hash === '') {
            return null;
        }

        return $this->repository->findByHashAndEntity($hash, $entityType, $entityId);
    }
}
