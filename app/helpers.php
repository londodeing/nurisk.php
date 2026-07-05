<?php

use Illuminate\Support\Facades\Storage;

if (!function_exists('media_url')) {
    function media_url(?string $path, string $disk = 'public'): ?string
    {
        if (empty($path)) {
            return null;
        }
        return Storage::disk($disk)->url($path);
    }
}
