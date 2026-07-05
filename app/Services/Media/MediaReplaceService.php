<?php

namespace App\Services\Media;

use App\Models\Media;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class MediaReplaceService
{
    public function __construct(
        private readonly MediaUploadService $uploadService,
        private readonly MediaDeleteService $deleteService,
    ) {}

    public function replace(
        UploadedFile $file,
        string $entityType,
        int $entityId,
        ?string $oldPath = null,
        string $disk = 'public',
    ): MediaUploadResult {
        $result = DB::transaction(function () use ($file, $entityType, $entityId, $oldPath, $disk) {
            $result = $this->uploadService->upload($file, $entityType, $entityId, $disk);

            if ($oldPath) {
                Media::where('path', $oldPath)->delete();
                Storage::disk($disk)->delete($oldPath);
            }

            return $result;
        });

        return $result;
    }
}
