<?php

namespace App\Jobs\Media;

use App\Infrastructure\Media\Persistence\Models\MediaConversionModel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CleanSoftDeletedConversionsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 30;
    public int $timeout = 60;

    public readonly string $correlationId;

    public function __construct(
        public readonly int $mediaId,
    ) {
        $this->correlationId = (string) Str::uuid();
    }

    public function handle(): void
    {
        Log::info('CleanSoftDeletedConversionsJob started', [
            'mediaId' => $this->mediaId,
            'correlationId' => $this->correlationId,
        ]);

        $conversions = MediaConversionModel::where('media_id', $this->mediaId)->get();
        $count = 0;

        $defaultDisk = Config::get('filesystems.default');

        foreach ($conversions as $conversion) {
            $diskName = $conversion->disk ?? $defaultDisk;

            if (! Storage::disk($defaultDisk)->delete($conversion->path)) {
                Storage::disk($diskName)->delete($conversion->path);
            }

            $conversion->delete();
            $count++;
        }

        Log::info('CleanSoftDeletedConversionsJob completed', [
            'mediaId' => $this->mediaId,
            'deletedCount' => $count,
            'correlationId' => $this->correlationId,
        ]);
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('CleanSoftDeletedConversionsJob failed', [
            'mediaId' => $this->mediaId,
            'correlationId' => $this->correlationId,
            'operation' => 'cleanup_deleted_conversions',
            'attempts' => $this->attempts(),
            'exception' => $exception->getMessage(),
            'stack' => $exception->getTraceAsString(),
        ]);
    }
}
