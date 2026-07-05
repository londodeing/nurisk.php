<?php

namespace App\Services\Media;

use App\Models\Media;
use Illuminate\Support\Facades\Storage;

class MediaDeleteService
{
    public function softDelete(Media $media): bool
    {
        $media->delete();
        return true;
    }

    public function forceDelete(Media $media): bool
    {
        $disk = Storage::disk($media->disk);
        $deleted = $disk->delete($media->path);

        $thumbPath = $this->thumbPath($media->path);
        $disk->delete($thumbPath);

        $webpPath = $this->webpPath($media->path);
        $disk->delete($webpPath);

        $media->forceDelete();

        return $deleted;
    }

    public function forceDeleteByPath(string $path, string $disk = 'public'): bool
    {
        $storageDisk = Storage::disk($disk);
        $fileDeleted = $storageDisk->delete($path);
        $storageDisk->delete($this->thumbPath($path));

        Media::where('path', $path)->forceDelete();

        return $fileDeleted;
    }

    public function softDeleteAllForEntity(string $entityType, int $entityId): int
    {
        return Media::where('entity_type', $entityType)
            ->where('entity_id', $entityId)
            ->delete();
    }

    public function forceDeleteAllForEntity(string $entityType, int $entityId): int
    {
        $media = Media::where('entity_type', $entityType)
            ->where('entity_id', $entityId)
            ->get();

        $count = 0;
        foreach ($media as $m) {
            if ($this->forceDelete($m)) {
                $count++;
            }
        }

        return $count;
    }

    public function restore(int $mediaId): bool
    {
        $media = Media::withTrashed()->find($mediaId);
        if (!$media) {
            return false;
        }
        return $media->restore();
    }

    public function purgeTrashed(int $daysOld = 30): int
    {
        $cutoff = now()->subDays($daysOld);
        $trashed = Media::onlyTrashed()->where('deleted_at', '<=', $cutoff)->get();

        $count = 0;
        foreach ($trashed as $m) {
            if ($this->forceDelete($m)) {
                $count++;
            }
        }

        return $count;
    }

    private function thumbPath(string $path): string
    {
        $info = pathinfo($path);
        return "{$info['dirname']}/{$info['filename']}-thumb.{$info['extension']}";
    }

    private function webpPath(string $path): string
    {
        $info = pathinfo($path);
        return "{$info['dirname']}/{$info['filename']}.webp";
    }
}
