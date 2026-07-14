<?php

namespace App\Jobs\Media;

use App\Infrastructure\Media\Storage\Contracts\StorageProvider;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class DeleteObjectJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    public int $tries = 3;
    public int $backoff = 30;
    public int $timeout = 60;

    public readonly string $correlationId;

    public function __construct(
        public readonly string $path,
        public readonly string $disk,
    ) {
        $this->correlationId = (string) Str::uuid();
    }

    public function handle(StorageProvider $storage): void
    {
        Log::info('DeleteObjectJob started', [
            'path' => $this->path,
            'disk' => $this->disk,
            'correlationId' => $this->correlationId,
        ]);

        $storage->delete($this->path);

        Log::info('DeleteObjectJob completed', [
            'path' => $this->path,
            'disk' => $this->disk,
            'correlationId' => $this->correlationId,
        ]);
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('DeleteObjectJob failed', [
            'path' => $this->path,
            'disk' => $this->disk,
            'correlationId' => $this->correlationId,
            'operation' => 'object_deletion',
            'attempts' => $this->attempts(),
            'exception' => $exception->getMessage(),
            'stack' => $exception->getTraceAsString(),
        ]);
    }
}
