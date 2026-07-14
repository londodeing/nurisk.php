<?php

namespace App\Http\Controllers\Api;

use App\Application\Media\Commands\DeleteMediaCommand;
use App\Application\Media\Commands\ReplaceMediaCommand;
use App\Application\Media\Commands\UploadMediaCommand;
use App\Application\Media\DTOs\MediaResponse;
use App\Application\Media\Handlers\DeleteMediaHandler;
use App\Application\Media\Handlers\ReplaceMediaHandler;
use App\Application\Media\Handlers\UploadMediaHandler;
use App\Domain\Media\Contracts\MediaRepository;
use App\Domain\Media\Exceptions\MediaNotFoundException;
use App\Http\Controllers\Controller;
use App\Http\Requests\MediaUploadRequest;
use App\Http\Resources\MediaResource;
use App\Infrastructure\Media\Persistence\Models\MediaModel;
use App\Infrastructure\Media\Storage\Contracts\StorageProvider;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MediaController extends Controller
{
    public function store(MediaUploadRequest $request, UploadMediaHandler $handler): JsonResponse
    {
        $user = $request->user();

        $command = new UploadMediaCommand(
            entityType: $request->input('entity_type'),
            entityId: (int) $request->input('entity_id'),
            file: $request->file('file'),
            visibility: $request->input('visibility', 'PUBLIC'),
            uploadedBy: $user?->getKey(),
            uploadedIp: $request->ip(),
            uploadedUserAgent: $request->userAgent(),
        );

        $media = $handler->handle($command);

        return response()->json(new MediaResource($media), 201);
    }

    public function show(int $id): JsonResponse
    {
        $mediaModel = MediaModel::find($id);

        if ($mediaModel === null) {
            return response()->json(['message' => 'Media not found.'], 404);
        }

        $this->authorize('view', $mediaModel);

        $media = app(MediaRepository::class)->find($id);

        if ($media === null) {
            return response()->json(['message' => 'Media not found.'], 404);
        }

        $response = new MediaResponse(
            id: $media->id(),
            path: $media->path()->toString(),
            url: app(StorageProvider::class)->url($media->path()->toString()),
            mimeType: $media->mimeType()->toString(),
            size: $media->size()->toBytes(),
            visibility: $media->visibility()->value,
            entityType: $media->entityType() ?? '',
            entityId: $media->entityId() ?? 0,
            conversions: array_map(
                fn ($c) => [
                    'id' => $c->id(),
                    'conversion_type' => $c->conversionType(),
                    'path' => $c->path()->toString(),
                    'mime_type' => $c->mimeType()->toString(),
                    'size_bytes' => $c->size()->toBytes(),
                ],
                $media->conversions(),
            ),
            createdAt: $media->createdAt()?->format('c') ?? '',
            deletedAt: $media->deletedAt()?->format('c'),
        );

        return response()->json(new MediaResource($response));
    }

    public function destroy(int $id, DeleteMediaHandler $handler): JsonResponse
    {
        $mediaModel = MediaModel::find($id);

        if ($mediaModel === null) {
            throw MediaNotFoundException::withId($id);
        }

        $this->authorize('delete', $mediaModel);

        $handler->handle(new DeleteMediaCommand(mediaId: $id));

        return response()->json(['message' => 'Media deleted.'], 200);
    }

    public function replace(Request $request, int $id, ReplaceMediaHandler $handler): JsonResponse
    {
        $request->validate(['file' => ['required', 'file', 'max:10240']]);

        $mediaModel = MediaModel::find($id);

        if ($mediaModel === null) {
            throw MediaNotFoundException::withId($id);
        }

        $this->authorize('replace', $mediaModel);

        $media = $handler->handle(
            new ReplaceMediaCommand(mediaId: $id, file: $request->file('file')),
        );

        return response()->json(new MediaResource($media));
    }
}
