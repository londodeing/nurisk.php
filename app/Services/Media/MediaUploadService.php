<?php

namespace App\Services\Media;

use App\Jobs\MediaProcessingJob;
use App\Models\Media;
use App\Services\Media\Contracts\MediaAntivirusHook;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class MediaUploadService
{
    public function __construct(
        private readonly MediaPolicy $policy,
        private readonly MediaFilenameGenerator $filenameGenerator,
        private readonly ?MediaAntivirusHook $antivirus = null,
    ) {}

    public function upload(
        UploadedFile $file,
        string $entityType,
        ?int $entityId = null,
        string $disk = 'public',
        ?string $subdirectory = null,
        string $accessLevel = Media::ACCESS_PUBLIC,
        ?string $mediaType = null,
        ?int $uploadedBy = null,
        ?string $uploadedIp = null,
        ?string $uploadedUserAgent = null,
    ): MediaUploadResult {
        $extension = strtolower($file->getClientOriginalExtension());
        $mimeType = $file->getMimeType();
        $size = $file->getSize();

        $errors = $this->policy->validate($entityType, $mimeType, $extension, $size);
        if (!empty($errors)) {
            throw new MediaUploadException(implode('; ', $errors));
        }

        $hash = hash_file('sha256', $file->getRealPath());

        $existing = $this->findDuplicate($hash, $entityType);
        if ($existing) {
            return new MediaUploadResult(
                path: $existing->path,
                originalName: $file->getClientOriginalName(),
                mimeType: $mimeType,
                sizeBytes: $size,
                hashSha256: $hash,
                mediaId: $existing->id,
                duplicate: true,
            );
        }

        if ($this->antivirus) {
            $scanResult = $this->antivirus->scan($file->getRealPath());
            if (!$scanResult['clean']) {
                throw new MediaUploadException(
                    'File rejected by security scan: ' . ($scanResult['threat_name'] ?? 'unknown threat')
                );
            }
        }

        $mediaType ??= $this->resolveMediaType($mimeType);
        $nextVersion = $this->nextVersion($entityType, $entityId, $mediaType);

        $directory = $subdirectory ?? $entityType;
        $filename = $this->filenameGenerator->generate($entityType, $extension);
        $path = $file->storeAs($directory, $filename, $disk);

        if ($path === false) {
            throw new MediaUploadException('Failed to store file');
        }

        [$width, $height] = $this->getImageDimensions($file, $mimeType);

        $mediaRecord = DB::transaction(function () use (
            $path, $file, $mimeType, $size, $hash, $width, $height,
            $entityType, $entityId, $disk, $accessLevel, $mediaType, $nextVersion,
            $uploadedBy, $uploadedIp, $uploadedUserAgent
        ) {
            if ($nextVersion > 1) {
                Media::where('entity_type', $entityType)
                    ->where('entity_id', $entityId)
                    ->where('media_type', $mediaType)
                    ->update(['is_active' => false]);
            }

            return Media::create([
                'entity_type' => $entityType,
                'entity_id' => $entityId,
                'disk' => $disk,
                'path' => $path,
                'media_type' => $mediaType,
                'version' => $nextVersion,
                'is_active' => true,
                'access_level' => $accessLevel,
                'original_name' => $file->getClientOriginalName(),
                'mime_type' => $mimeType,
                'size_bytes' => $size,
                'hash_sha256' => $hash,
                'width' => $width,
                'height' => $height,
                'uploaded_by' => $uploadedBy,
                'uploaded_ip' => $uploadedIp,
                'uploaded_user_agent' => $uploadedUserAgent,
            ]);
        });

        dispatch(new MediaProcessingJob($mediaRecord))->afterResponse();

        return new MediaUploadResult(
            path: $path,
            originalName: $file->getClientOriginalName(),
            mimeType: $mimeType,
            sizeBytes: $size,
            hashSha256: $hash,
            width: $width,
            height: $height,
            mediaId: $mediaRecord->id,
            version: $nextVersion,
        );
    }

    public function uploadFromPath(
        string $filePath,
        string $originalName,
        string $entityType,
        ?int $entityId = null,
        string $disk = 'public',
        ?string $subdirectory = null,
        string $accessLevel = Media::ACCESS_PUBLIC,
        ?string $mediaType = null,
        ?int $uploadedBy = null,
        ?string $uploadedIp = null,
        ?string $uploadedUserAgent = null,
    ): MediaUploadResult {
        $mimeType = mime_content_type($filePath) ?: 'application/octet-stream';
        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        $size = filesize($filePath);

        $errors = $this->policy->validate($entityType, $mimeType, $extension, $size);
        if (!empty($errors)) {
            throw new MediaUploadException(implode('; ', $errors));
        }

        $hash = hash_file('sha256', $filePath);

        $existing = $this->findDuplicate($hash, $entityType);
        if ($existing) {
            return new MediaUploadResult(
                path: $existing->path,
                originalName: $originalName,
                mimeType: $mimeType,
                sizeBytes: $size,
                hashSha256: $hash,
                mediaId: $existing->id,
                duplicate: true,
            );
        }

        $mediaType ??= $this->resolveMediaType($mimeType);
        $nextVersion = $this->nextVersion($entityType, $entityId, $mediaType);

        $directory = $subdirectory ?? $entityType;
        $filename = $this->filenameGenerator->generate($entityType, $extension);
        $storagePath = Storage::disk($disk)->putFileAs($directory, $filePath, $filename);

        if ($storagePath === false) {
            throw new MediaUploadException('Failed to store file');
        }

        [$width, $height] = $this->getImageDimensionsFromPath($filePath, $mimeType);

        $mediaRecord = DB::transaction(function () use (
            $storagePath, $originalName, $mimeType, $size, $hash, $width, $height,
            $entityType, $entityId, $disk, $accessLevel, $mediaType, $nextVersion,
            $uploadedBy, $uploadedIp, $uploadedUserAgent
        ) {
            if ($nextVersion > 1) {
                Media::where('entity_type', $entityType)
                    ->where('entity_id', $entityId)
                    ->where('media_type', $mediaType)
                    ->update(['is_active' => false]);
            }

            return Media::create([
                'entity_type' => $entityType,
                'entity_id' => $entityId,
                'disk' => $disk,
                'path' => $storagePath,
                'media_type' => $mediaType,
                'version' => $nextVersion,
                'is_active' => true,
                'access_level' => $accessLevel,
                'original_name' => $originalName,
                'mime_type' => $mimeType,
                'size_bytes' => $size,
                'hash_sha256' => $hash,
                'width' => $width,
                'height' => $height,
                'uploaded_by' => $uploadedBy,
                'uploaded_ip' => $uploadedIp,
                'uploaded_user_agent' => $uploadedUserAgent,
            ]);
        });

        dispatch(new MediaProcessingJob($mediaRecord))->afterResponse();

        return new MediaUploadResult(
            path: $storagePath,
            originalName: $originalName,
            mimeType: $mimeType,
            sizeBytes: $size,
            hashSha256: $hash,
            width: $width,
            height: $height,
            mediaId: $mediaRecord->id,
            version: $nextVersion,
        );
    }

    public function toValidationRules(string $entityType): array
    {
        return $this->policy->toValidationRules($entityType);
    }

    public function findDuplicate(string $hash, string $entityType): ?Media
    {
        return Media::where('hash_sha256', $hash)
            ->where('entity_type', $entityType)
            ->whereNull('deleted_at')
            ->first();
    }

    public function associate(int $mediaId, string $entityType, int $entityId): void
    {
        Media::where('id', $mediaId)->update([
            'entity_type' => $entityType,
            'entity_id' => $entityId,
        ]);
    }

    private function nextVersion(?string $entityType, ?int $entityId, string $mediaType): int
    {
        if (!$entityType || !$entityId) {
            return 1;
        }

        $latest = Media::where('entity_type', $entityType)
            ->where('entity_id', $entityId)
            ->where('media_type', $mediaType)
            ->withTrashed()
            ->max('version');

        return ($latest ?? 0) + 1;
    }

    private function resolveMediaType(string $mimeType): string
    {
        if (str_starts_with($mimeType, 'image/')) {
            return Media::MEDIA_TYPE_IMAGE;
        }
        if (str_starts_with($mimeType, 'video/')) {
            return Media::MEDIA_TYPE_VIDEO;
        }
        if (str_starts_with($mimeType, 'audio/')) {
            return Media::MEDIA_TYPE_AUDIO;
        }
        return Media::MEDIA_TYPE_DOCUMENT;
    }

    private function getImageDimensions(UploadedFile $file, string $mimeType): array
    {
        if (str_starts_with($mimeType, 'image/') && $mimeType !== 'image/svg+xml') {
            $dimensions = @getimagesize($file->getRealPath());
            if ($dimensions) {
                return [$dimensions[0], $dimensions[1]];
            }
        }
        return [null, null];
    }

    private function getImageDimensionsFromPath(string $filePath, string $mimeType): array
    {
        if (str_starts_with($mimeType, 'image/') && $mimeType !== 'image/svg+xml') {
            $dimensions = @getimagesize($filePath);
            if ($dimensions) {
                return [$dimensions[0], $dimensions[1]];
            }
        }
        return [null, null];
    }
}
