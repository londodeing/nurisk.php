<?php

declare(strict_types=1);

namespace App\Application\Media\Handlers;

use App\Application\Media\Commands\DeleteMediaCommand;
use App\Application\Media\Contracts\EventPublisher;
use App\Application\Media\DTOs\MediaResponse;
use App\Domain\Media\Contracts\MediaRepository;
use App\Domain\Media\Exceptions\MediaNotFoundException;
use App\Infrastructure\Media\Storage\Contracts\StorageProvider;
use App\Jobs\Media\CleanSoftDeletedConversionsJob;
use Illuminate\Support\Facades\Log;

/**
 * Handles soft-deletion of a media record.
 *
 * Orchestrates: find → soft delete (aggregate) → persist → emit
 * application event → async object storage cleanup.
 *
 * The actual file deletion from object storage happens asynchronously
 * via the MediaDeletionRequested event, keeping the HTTP response fast.
 */
final class DeleteMediaHandler
{
    public function __construct(
        private readonly MediaRepository $repository,
        private readonly EventPublisher $eventPublisher,
        private readonly StorageProvider $storage,
    ) {}

    public function handle(DeleteMediaCommand $command): MediaResponse
    {
        $media = $this->repository->find($command->mediaId);

        if ($media === null) {
            throw MediaNotFoundException::withId($command->mediaId);
        }

        $media->delete();

        $this->repository->save($media);

        CleanSoftDeletedConversionsJob::dispatch($media->id());

        try {
            $this->eventPublisher->publish($media->releaseEvents(), $media);
        } catch (\Throwable $e) {
            Log::error('DeleteMediaHandler: event publish failed', [
                'mediaId' => $media->id(),
                'error' => $e->getMessage(),
            ]);
        }

        return new MediaResponse(
            id: $media->id(),
            path: $media->path()->toString(),
            url: $this->storage->url($media->path()->toString()),
            mimeType: $media->mimeType()->toString(),
            size: $media->size()->toBytes(),
            visibility: $media->visibility()->value,
            entityType: $media->entityType() ?? '',
            entityId: $media->entityId() ?? 0,
            conversions: [],
            createdAt: $media->createdAt()?->format('c') ?? '',
            deletedAt: $media->deletedAt()?->format('c'),
        );
    }
}
