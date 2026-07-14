<?php

use Illuminate\Support\Facades\Storage;

if (!function_exists('media_url')) {
    function media_url(?string $path, ?string $disk = null): ?string
    {
        if (empty($path)) {
            return null;
        }
        $disk ??= config('filesystems.default', 'public');
        $storage = Storage::disk($disk);
        
        if ($disk === 's3' || config("filesystems.disks.{$disk}.driver") === 's3') {
            return $storage->temporaryUrl($path, now()->addMinutes(15));
        }
        
        return $storage->url($path);
    }
}
