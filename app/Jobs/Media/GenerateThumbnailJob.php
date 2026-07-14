<?php

namespace App\Jobs\Media;

use App\Application\Media\Events\ThumbnailGenerationRequested;
use App\Domain\Media\Entities\Media;
use App\Domain\Media\Factories\MediaFactory;
use App\Domain\Media\ValueObjects\MediaPath;
use App\Domain\Media\ValueObjects\MediaSize;
use App\Domain\Media\ValueObjects\MimeType;
use App\Infrastructure\Media\Persistence\Repositories\EloquentMediaRepository;
use App\Infrastructure\Media\Storage\Contracts\StorageProvider;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class GenerateThumbnailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    public int $tries = 3;
    public int $backoff = 30;
    public int $timeout = 60;

    public readonly string $correlationId;

    public function __construct(
        public readonly ThumbnailGenerationRequested $event,
    ) {
        $this->correlationId = (string) Str::uuid();
    }

    public function handle(
        EloquentMediaRepository $repository,
        MediaFactory $factory,
        StorageProvider $storage,
    ): void {
        Log::info('GenerateThumbnailJob started', [
            'mediaId' => $this->event->mediaId,
            'correlationId' => $this->correlationId,
        ]);

        $media = $repository->find($this->event->mediaId);

        if ($media === null) {
            Log::warning('GenerateThumbnailJob skipped: media not found', [
                'mediaId' => $this->event->mediaId,
                'correlationId' => $this->correlationId,
            ]);

            return;
        }

        foreach ($media->conversions() as $conv) {
            if ($conv->conversionType() === 'thumbnail') {
                Log::info('GenerateThumbnailJob skipped: thumbnail already exists', [
                    'mediaId' => $this->event->mediaId,
                    'correlationId' => $this->correlationId,
                ]);

                return;
            }
        }

        $sourcePath = $media->path()->toString();
        $disk = Storage::disk($media->disk());

        if (! $disk->exists($sourcePath)) {
            Log::warning('GenerateThumbnailJob skipped: source file not found on disk', [
                'mediaId' => $this->event->mediaId,
                'path' => $sourcePath,
                'disk' => $media->disk(),
                'correlationId' => $this->correlationId,
            ]);

            return;
        }

        $thumbPath = $this->thumbnailPath($media);
        $sourceTemp = tempnam(sys_get_temp_dir(), 'source');
        $thumbLocal = tempnam(sys_get_temp_dir(), 'thumb');

        try {
            $stream = $disk->readStream($sourcePath);
            if (! $stream) {
                Log::warning('GenerateThumbnailJob skipped: could not read stream', [
                    'mediaId' => $this->event->mediaId,
                    'path' => $sourcePath,
                    'correlationId' => $this->correlationId,
                ]);

                return;
            }

            $out = fopen($sourceTemp, 'wb');
            stream_copy_to_stream($stream, $out);
            fclose($out);
            fclose($stream);

            $this->generateThumbnail($sourceTemp, $thumbLocal, 300);

            $storage->store($thumbPath, $thumbLocal);

            $conversion = $factory->createConversion(
                media: $media,
                conversionType: 'thumbnail',
                path: new MediaPath($thumbPath),
                mimeType: new MimeType('image/jpeg'),
                size: new MediaSize(filesize($thumbLocal)),
                width: 300,
                height: null,
            );

            $media->addConversion($conversion);

            $repository->save($media);

            $media->releaseEvents();

            Log::info('GenerateThumbnailJob completed', [
                'mediaId' => $this->event->mediaId,
                'thumbPath' => $thumbPath,
                'correlationId' => $this->correlationId,
            ]);
        } finally {
            if (file_exists($sourceTemp)) {
                unlink($sourceTemp);
            }
            if (file_exists($thumbLocal)) {
                unlink($thumbLocal);
            }
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('GenerateThumbnailJob failed', [
            'mediaId' => $this->event->mediaId,
            'correlationId' => $this->correlationId,
            'operation' => 'thumbnail_generation',
            'attempts' => $this->attempts(),
            'exception' => $exception->getMessage(),
            'stack' => $exception->getTraceAsString(),
        ]);
    }

    private function thumbnailPath(Media $media): string
    {
        $dir = dirname($media->path()->toString());

        return sprintf('%s/thumb_%s.jpg', $dir, Str::random(16));
    }

    private function generateThumbnail(string $source, string $dest, int $maxDim): void
    {
        $imageSize = getimagesize($source);
        if ($imageSize === false) {
            throw new \RuntimeException("Invalid image file");
        }
        [$width, $height] = $imageSize;

        $memoryFootprint = $width * $height * 4 * 1.5;
        if ($memoryFootprint > 134217728) {
            throw new \RuntimeException("Image dimensions too large for GD processing (Estimated {$memoryFootprint} bytes).");
        }

        $mime = mime_content_type($source);

        $src = match ($mime) {
            'image/jpeg' => imagecreatefromjpeg($source),
            'image/png' => imagecreatefrompng($source),
            'image/gif' => imagecreatefromgif($source),
            'image/webp' => imagecreatefromwebp($source),
            default => throw new \RuntimeException("Unsupported MIME for thumbnail: {$mime}"),
        };

        $ratio = min($maxDim / $width, $maxDim / $height, 1);
        $newW = (int) round($width * $ratio);
        $newH = (int) round($height * $ratio);

        $thumb = imagecreatetruecolor($newW, $newH);
        imagecopyresampled($thumb, $src, 0, 0, 0, 0, $newW, $newH, $width, $height);

        imagejpeg($thumb, $dest, 85);

        imagedestroy($src);
        imagedestroy($thumb);
    }
}
