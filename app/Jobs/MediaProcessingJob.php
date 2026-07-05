<?php

namespace App\Jobs;

use App\Models\Media;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class MediaProcessingJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public Media $media,
    ) {}

    public function handle(): void
    {
        $disk = Storage::disk($this->media->disk);

        if (!$disk->exists($this->media->path)) {
            Log::warning('MediaProcessingJob: file not found', ['path' => $this->media->path]);
            return;
        }

        $absolutePath = $disk->path($this->media->path);

        if ($this->media->isImage()) {
            $this->generateThumbnail($disk, $absolutePath);
            $this->generateWebP($disk, $absolutePath);
            $this->extractMetadata($absolutePath);
        }
    }

    private function generateThumbnail($disk, string $absolutePath): void
    {
        $info = pathinfo($absolutePath);
        $thumbPath = $info['dirname'] . '/' . $info['filename'] . '-thumb.' . $info['extension'];

        if (file_exists($thumbPath)) {
            return;
        }

        $imageInfo = @getimagesize($absolutePath);
        if (!$imageInfo) {
            return;
        }

        [$width, $height] = $imageInfo;
        $thumbWidth = 150;
        $thumbHeight = (int) ($height * ($thumbWidth / $width));

        $source = $this->openImage($absolutePath, $imageInfo[2]);
        if (!$source) {
            return;
        }

        $thumb = imagecreatetruecolor($thumbWidth, $thumbHeight);
        imagecopyresampled($thumb, $source, 0, 0, 0, 0, $thumbWidth, $thumbHeight, $width, $height);

        $this->saveImage($thumb, $thumbPath, $imageInfo[2]);
        imagedestroy($source);
        imagedestroy($thumb);
    }

    private function generateWebP($disk, string $absolutePath): void
    {
        if (!function_exists('imagewebp')) {
            return;
        }

        $info = pathinfo($absolutePath);
        $webpPath = $info['dirname'] . '/' . $info['filename'] . '.webp';

        if (file_exists($webpPath)) {
            return;
        }

        $imageInfo = @getimagesize($absolutePath);
        if (!$imageInfo) {
            return;
        }

        $source = $this->openImage($absolutePath, $imageInfo[2]);
        if (!$source) {
            return;
        }

        imagewebp($source, $webpPath, 80);
        imagedestroy($source);
    }

    private function extractMetadata(string $absolutePath): void
    {
        $exif = @exif_read_data($absolutePath, 'EXIF', true);
        if ($exif) {
            $metadata = $this->media->metadata ?? [];
            $metadata['exif'] = [
                'make' => $exif['IFD0']['Make'] ?? null,
                'model' => $exif['IFD0']['Model'] ?? null,
                'datetime' => $exif['IFD0']['DateTime'] ?? null,
                'gps_lat' => $exif['GPS']['GPSLatitude'] ?? null,
                'gps_lng' => $exif['GPS']['GPSLongitude'] ?? null,
            ];
            $this->media->updateQuietly(['metadata' => $metadata]);
        }
    }

    private function openImage(string $path, int $imageType)
    {
        return match ($imageType) {
            IMAGETYPE_JPEG => @imagecreatefromjpeg($path),
            IMAGETYPE_PNG => @imagecreatefrompng($path),
            IMAGETYPE_GIF => @imagecreatefromgif($path),
            IMAGETYPE_WEBP => @imagecreatefromwebp($path),
            default => null,
        };
    }

    private function saveImage($resource, string $path, int $imageType): void
    {
        match ($imageType) {
            IMAGETYPE_JPEG => imagejpeg($resource, $path, 85),
            IMAGETYPE_PNG => imagepng($resource, $path, 6),
            IMAGETYPE_GIF => imagegif($resource, $path),
            IMAGETYPE_WEBP => imagewebp($resource, $path, 80),
            default => null,
        };
    }
}
