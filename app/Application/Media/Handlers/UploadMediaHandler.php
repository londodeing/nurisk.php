<?php

declare(strict_types=1);

namespace App\Application\Media\Handlers;

use App\Application\Media\Commands\UploadMediaCommand;
use App\Application\Media\Contracts\EventPublisher;
use App\Application\Media\DTOs\MediaResponse;
use App\Domain\Media\Contracts\MediaRepository;
use App\Domain\Media\Entities\Media;
use App\Domain\Media\Entities\MediaConversion;
use App\Domain\Media\Enums\MediaVisibility;
use App\Domain\Media\Factories\MediaFactory;
use App\Domain\Media\Services\MediaDeduplicationService;
use App\Domain\Media\ValueObjects\MediaPath;
use App\Domain\Media\ValueObjects\MediaSize;
use App\Domain\Media\ValueObjects\MimeType;
use App\Infrastructure\Media\Storage\Contracts\StorageProvider;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Handles media file uploads.
 *
 * Orchestrates the upload flow: deduplication check, file storage,
 * aggregate creation, persistence, and event dispatch.
 *
 * Contains zero business rules — all domain decisions delegate to
 * Domain services, Value Objects, or the Aggregate.
 */
final class UploadMediaHandler
{
    public function __construct(
        private readonly MediaFactory $factory,
        private readonly MediaRepository $repository,
        private readonly MediaDeduplicationService $deduplicationService,
        private readonly EventPublisher $eventPublisher,
        private readonly StorageProvider $storage,
    ) {}

    public function handle(UploadMediaCommand $command): MediaResponse
    {
        $hash = $this->computeHash($command);
        $mimeType = new MimeType($command->file->getMimeType());
        $size = new MediaSize($command->file->getSize());
        $visibility = MediaVisibility::from($command->visibility);

        $reusable = $this->deduplicationService->findReusable(
            $hash,
            $command->entityType,
            $command->entityId,
        );

        if ($reusable !== null) {
            return $this->buildResponse($reusable);
        }

        $path = $this->generatePath($command, $mimeType);

        if (! $this->storage->store($path, $command->file->getRealPath())) {
            throw new \RuntimeException('Failed to store file to object storage');
        }

        try {
            $media = $this->factory->createUploaded(
                path: new MediaPath($path),
                mimeType: $mimeType,
                size: $size,
                visibility: $visibility,
                disk: config('filesystems.default'),
                hash: $hash,
                originalName: $command->file->getClientOriginalName(),
                entityType: $command->entityType,
                entityId: $command->entityId,
                uploadedBy: $command->uploadedBy,
                uploadedIp: $command->uploadedIp,
                uploadedUserAgent: $command->uploadedUserAgent,
            );

            $this->repository->save($media);
        } catch (\Throwable $e) {
            $this->storage->delete($path);

            throw $e;
        }

        try {
            $this->eventPublisher->publish($media->releaseEvents(), $media);
        } catch (\Throwable $e) {
            Log::error('UploadMediaHandler: event publish failed', [
                'mediaId' => $media->id(),
                'error' => $e->getMessage(),
            ]);
        }

        return $this->buildResponse($media);
    }

    private function computeHash(UploadMediaCommand $command): string
    {
        return hash_file('sha256', $command->file->getRealPath());
    }

    private function generatePath(UploadMediaCommand $command, MimeType $mimeType): string
    {
        $extension = $command->file->getClientOriginalExtension();
        $basename = Str::random(40);

        if ($extension !== '') {
            $basename .= '.'.$extension;
        }

        return sprintf(
            '%s/%s/%s',
            $command->entityType,
            $command->entityId,
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
