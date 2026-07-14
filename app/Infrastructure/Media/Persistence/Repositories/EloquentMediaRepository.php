<?php

declare(strict_types=1);

namespace App\Infrastructure\Media\Persistence\Repositories;

use App\Domain\Media\Contracts\MediaRepository;
use App\Domain\Media\Entities\Media;
use App\Domain\Media\ValueObjects\MediaPath;
use App\Infrastructure\Media\Persistence\Mappers\MediaMapper;
use App\Infrastructure\Media\Persistence\Models\MediaConversionModel;
use App\Infrastructure\Media\Persistence\Models\MediaModel;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;

/**
 * Eloquent implementation of MediaRepository.
 *
 * Translates between Domain contracts and Eloquent persistence.
 * No business logic — pure data access.
 */
final class EloquentMediaRepository implements MediaRepository
{
    public function __construct(
        private readonly MediaMapper $mapper,
    ) {}

    public function save(Media $media): void
    {
        $data = $this->mapper->toModelArray($media);

        if ($media->id() === null) {
            $model = MediaModel::create($data);
            $this->syncConversions($model->id, $media);
            $this->reconstituteId($media, $model->id);
        } else {
            MediaModel::where('id', $media->id())->update($data);
            $this->syncConversions($media->id(), $media);
        }
    }

    public function find(int $id): ?Media
    {
        $model = MediaModel::with('conversions')->find($id);

        if ($model === null) {
            return null;
        }

        return $this->mapper->toDomain($model);
    }

    public function findByPath(MediaPath $path, string $disk): ?Media
    {
        $model = MediaModel::with('conversions')
            ->where('path', $path->toString())
            ->where('disk', $disk)
            ->first();

        if ($model === null) {
            return null;
        }

        return $this->mapper->toDomain($model);
    }

    public function findActiveByEntity(string $entityType, int $entityId): array
    {
        return MediaModel::with('conversions')
            ->where('entity_type', $entityType)
            ->where('entity_id', $entityId)
            ->where('is_active', true)
            ->get()
            ->map(fn (MediaModel $m) => $this->mapper->toDomain($m))
            ->toArray();
    }

    public function findAllByEntity(string $entityType, int $entityId): array
    {
        return MediaModel::with('conversions')
            ->where('entity_type', $entityType)
            ->where('entity_id', $entityId)
            ->withTrashed()
            ->get()
            ->map(fn (MediaModel $m) => $this->mapper->toDomain($m))
            ->toArray();
    }

    public function findByHashAndEntity(string $hash, string $entityType, int $entityId): ?Media
    {
        $model = MediaModel::with('conversions')
            ->where('hash_sha256', $hash)
            ->where('entity_type', $entityType)
            ->where('entity_id', $entityId)
            ->where('is_active', true)
            ->first();

        if ($model === null) {
            return null;
        }

        return $this->mapper->toDomain($model);
    }

    public function delete(int $id): void
    {
        MediaConversionModel::where('media_id', $id)->delete();
        MediaModel::where('id', $id)->forceDelete();
    }

    /**
     * Synchronize the conversions collection using upsert semantics.
     *
     * Fetches existing rows, compares by conversion_type, updates only
     * changed rows, inserts new ones, and prunes removed types.
     */
    private function syncConversions(int $mediaId, Media $media): void
    {
        $domainConversions = $media->conversions();
        $disk = $media->disk();

        $existing = MediaConversionModel::where('media_id', $mediaId)
            ->get()
            ->keyBy('conversion_type');

        $keepTypes = [];

        foreach ($domainConversions as $conversion) {
            $type = $conversion->conversionType();
            $keepTypes[] = $type;

            $data = [
                'disk' => $disk,
                'path' => $conversion->path()->toString(),
                'mime_type' => $conversion->mimeType()->toString(),
                'size_bytes' => $conversion->size()->toBytes(),
                'width' => $conversion->width(),
                'height' => $conversion->height(),
            ];

            if (isset($existing[$type])) {
                $row = $existing[$type];
                $changed = (
                    (string) $data['disk'] !== (string) $row->disk
                    || (string) $data['path'] !== (string) $row->path
                    || (string) $data['mime_type'] !== (string) $row->mime_type
                    || (int) $data['size_bytes'] !== (int) $row->size_bytes
                    || (int) $data['width'] !== (int) $row->width
                    || (int) $data['height'] !== (int) $row->height
                );

                if ($changed) {
                    MediaConversionModel::where('id', $row->id)->update($data);
                }
            } else {
                MediaConversionModel::create([
                    'media_id' => $mediaId,
                    'conversion_type' => $type,
                    ...$data,
                ]);
            }
        }

        $pruned = MediaConversionModel::where('media_id', $mediaId)
            ->whereNotIn('conversion_type', $keepTypes)
            ->get();

        foreach ($pruned as $row) {
            $diskName = $row->disk ?? Config::get('filesystems.default');
            Storage::disk($diskName)->delete($row->path);
        }

        MediaConversionModel::where('media_id', $mediaId)
            ->whereNotIn('conversion_type', $keepTypes)
            ->delete();
    }

    /**
     * Set the generated ID back on the aggregate after insert.
     */
    private function reconstituteId(Media $media, int $id): void
    {
        // Uses reflection to set private readonly id
        // This is the only place where aggregate identity is mutated after construction
        $reflection = new \ReflectionProperty($media, 'id');
        $reflection->setAccessible(true);
        $reflection->setValue($media, $id);
    }
}
