<?php

namespace App\Services\Sync;

use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Facades\Storage;

class SnapshotStorageService
{
    private Filesystem $disk;

    public function __construct()
    {
        $this->disk = Storage::disk(config('sync.snapshot_disk', 'local'));
    }

    public function store(string $path, string $content): bool
    {
        return $this->disk->put($path, $content);
    }

    public function exists(string $path): bool
    {
        return $this->disk->exists($path);
    }

    public function temporaryUrl(string $path, \DateTimeInterface $expiresAt = null): string
    {
        $expiresAt ??= now()->addHour();

        $driver = config('filesystems.disks.' . config('sync.snapshot_disk', 'local') . '.driver', 'local');

        if ($driver === 'local') {
            return route('api.v1.sync.snapshot-download', [
                'path' => $path,
                'expires' => $expiresAt->timestamp,
                'signature' => $this->sign($path, $expiresAt),
            ]);
        }

        return $this->disk->temporaryUrl($path, $expiresAt);
    }

    public function delete(string $path): bool
    {
        return $this->disk->delete($path);
    }

    public function get(string $path): ?string
    {
        return $this->disk->exists($path) ? $this->disk->get($path) : null;
    }

    public function sign(string $path, \DateTimeInterface $expiresAt): string
    {
        $payload = $path . '|' . $expiresAt->timestamp;
        return hash_hmac('sha256', $payload, config('app.key'));
    }

    public function verify(string $path, int $expiresTimestamp, string $signature): bool
    {
        if (now()->timestamp > $expiresTimestamp) {
            return false;
        }

        $expected = $this->sign($path, (new \DateTimeImmutable())->setTimestamp($expiresTimestamp));
        return hash_equals($expected, $signature);
    }
}
