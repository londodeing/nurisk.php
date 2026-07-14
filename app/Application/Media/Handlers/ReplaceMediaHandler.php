<?php

declare(strict_types=1);

namespace App\Application\Media\Handlers;

use App\Application\Media\Commands\ReplaceMediaCommand;
use App\Application\Media\Contracts\EventPublisher;
use App\Application\Media\DTOs\MediaResponse;
use App\Domain\Media\Contracts\MediaRepository;
use App\Domain\Media\Entities\Media;
use App\Domain\Media\Entities\MediaConversion;
use App\Domain\Media\Exceptions\MediaNotFoundException;
use App\Domain\Media\ValueObjects\MediaPath;
use App\Domain\Media\ValueObjects\MediaSize;
use App\Domain\Media\ValueObjects\MimeType;
use App\Infrastructure\Media\Storage\Contracts\StorageProvider;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Handles replacement of an existing media file.
 *
 * Orchestrates: find → store new file → aggregate.replaceWith() →
 * persist → dispatch async cleanup of old file.
 *
 * This is NOT delete+upload — it is a single domain operation
 * that preserves aggregate identity and entity associations.
 */
final class ReplaceMediaHandler
{
    public function __construct(
        private readonly MediaRepository $repository,
        private readonly EventPublisher $eventPublisher,
        private readonly StorageProvider $storage,
    ) {}

    public function handle(ReplaceMediaCommand $command): MediaResponse
    {
        $media = $this->repository->find($command->mediaId);

        if ($media === null) {
            throw MediaNotFoundException::withId($command->mediaId);
        }

        $hash = hash_file('sha256', $command->file->getRealPath());
        $mimeType = new MimeType($command->file->getMimeType());
        $size = new MediaSize($command->file->getSize());

        $newPath = $this->generatePath($media, $mimeType);

        if (! $this->storage->store($newPath, $command->file->getRealPath())) {
            throw new \RuntimeException('Failed to store replacement file to object storage');
        }

        try {
            $media->replaceWith(
                newPath: new MediaPath($newPath),
                newMimeType: $mimeType,
                newSize: $size,
                newHash: $hash,
                newOriginalName: $command->file->getClientOriginalName(),
                newWidth: null,
                newHeight: null,
            );

            $media->removeConversionsOfType('thumbnail');
            $media->removeConversionsOfType('webp');

            $this->repository->save($media);
        } catch (\Throwable $e) {
            $this->storage->delete($newPath);

            throw $e;
        }

        try {
            $this->eventPublisher->publish($media->releaseEvents(), $media);
        } catch (\Throwable $e) {
            Log::error('ReplaceMediaHandler: event publish failed', [
                'mediaId' => $media->id(),
                'error' => $e->getMessage(),
            ]);
        }

        return $this->buildResponse($media);
    }

    private function generatePath(Media $media, MimeType $mimeType): string
    {
        $extension = pathinfo($media->originalName() ?? 'file', PATHINFO_EXTENSION);
        $basename = Str::random(40);

        if ($extension !== '') {
            $basename .= '.'.$extension;
        }

        return sprintf(
            '%s/%s/%s',
            $media->entityType() ?? 'unknown',
            $media->entityId() ?? 0,
            $basename,
        );
    }

    private function buildResponse(Media $media): MediaResponse
    {
        $conversions = array_map(
            fn (MediaConversion $c) => [
                'id' => $c->id(),
                'conversion_type' => $c->conversionType(),
                'path' => $c->path()->toString(),
                'mime_type' => $c->mimeType()->toString(),
                'size_bytes' => $c->size()->toBytes(),
            ],
            $media->conversions(),
        );

        return new MediaResponse(
            id: $media->id(),
            path: $media->path()->toString(),
            url: $this->storage->url($media->path()->toString()),
            mimeType: $media->mimeType()->toString(),
            size: $media->size()->toBytes(),
            visibility: $media->visibility()->value,
            entityType: $media->entityType() ?? '',
            entityId: $media->entityId() ?? 0,
            conversions: $conversions,
            createdAt: $media->createdAt()?->format('c') ?? '',
            deletedAt: $media->deletedAt()?->format('c'),
        );
    }
}
