<?php

namespace App\Jobs\Media;

use App\Application\Media\Events\WebpConversionRequested;
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

class GenerateWebpJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    public int $tries = 3;
    public int $backoff = 30;
    public int $timeout = 60;

    public readonly string $correlationId;

    public function __construct(
        public readonly WebpConversionRequested $event,
    ) {
        $this->correlationId = (string) Str::uuid();
    }

    public function handle(
        EloquentMediaRepository $repository,
        MediaFactory $factory,
        StorageProvider $storage,
    ): void {
        Log::info('GenerateWebpJob started', [
            'mediaId' => $this->event->mediaId,
            'correlationId' => $this->correlationId,
        ]);

        $media = $repository->find($this->event->mediaId);

        if ($media === null) {
            Log::warning('GenerateWebpJob skipped: media not found', [
                'mediaId' => $this->event->mediaId,
                'correlationId' => $this->correlationId,
            ]);

            return;
        }

        foreach ($media->conversions() as $conv) {
            if ($conv->conversionType() === 'webp') {
                Log::info('GenerateWebpJob skipped: webp already exists', [
                    'mediaId' => $this->event->mediaId,
                    'correlationId' => $this->correlationId,
                ]);

                return;
            }
        }

        $sourcePath = $media->path()->toString();
        $disk = Storage::disk($media->disk());

        if (! $disk->exists($sourcePath)) {
            Log::warning('GenerateWebpJob skipped: source file not found on disk', [
                'mediaId' => $this->event->mediaId,
                'path' => $sourcePath,
                'disk' => $media->disk(),
                'correlationId' => $this->correlationId,
            ]);

            return;
        }

        $mime = $media->mimeType()->toString();
        if (! in_array($mime, ['image/jpeg', 'image/png', 'image/gif', 'image/webp'], true)) {
            Log::info('GenerateWebpJob skipped: unsupported MIME type', [
                'mediaId' => $this->event->mediaId,
                'mime' => $mime,
                'correlationId' => $this->correlationId,
            ]);

            return;
        }

        $webpPath = $this->webpPath($media);
        $sourceTemp = tempnam(sys_get_temp_dir(), 'webp_src');
        $webpLocal = tempnam(sys_get_temp_dir(), 'webp_out');

        try {
            $stream = $disk->readStream($sourcePath);
            if (! $stream) {
                Log::warning('GenerateWebpJob skipped: could not read stream', [
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

            $src = match ($mime) {
                'image/jpeg' => imagecreatefromjpeg($sourceTemp),
                'image/png' => imagecreatefrompng($sourceTemp),
                'image/gif' => imagecreatefromgif($sourceTemp),
                'image/webp' => imagecreatefromwebp($sourceTemp),
                default => null,
            };

            if ($src === null) {
                Log::warning('GenerateWebpJob skipped: could not decode image', [
                    'mediaId' => $this->event->mediaId,
                    'mime' => $mime,
                    'correlationId' => $this->correlationId,
                ]);

                return;
            }

            imagewebp($src, $webpLocal, 85);
            imagedestroy($src);

            $storage->store($webpPath, $webpLocal);

            $conversion = $factory->createConversion(
                media: $media,
                conversionType: 'webp',
                path: new MediaPath($webpPath),
                mimeType: new MimeType('image/webp'),
                size: new MediaSize(filesize($webpLocal)),
                width: $media->width(),
                height: $media->height(),
            );

            $media->addConversion($conversion);

            $repository->save($media);

            $media->releaseEvents();

            Log::info('GenerateWebpJob completed', [
                'mediaId' => $this->event->mediaId,
                'webpPath' => $webpPath,
                'correlationId' => $this->correlationId,
            ]);
        } finally {
            if (file_exists($sourceTemp)) {
                unlink($sourceTemp);
            }
            if (file_exists($webpLocal)) {
                unlink($webpLocal);
            }
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('GenerateWebpJob failed', [
            'mediaId' => $this->event->mediaId,
            'correlationId' => $this->correlationId,
            'operation' => 'webp_conversion',
            'attempts' => $this->attempts(),
            'exception' => $exception->getMessage(),
            'stack' => $exception->getTraceAsString(),
        ]);
    }

    private function webpPath(Media $media): string
    {
        $dir = dirname($media->path()->toString());

        return sprintf('%s/webp_%s.webp', $dir, Str::random(16));
    }
}
