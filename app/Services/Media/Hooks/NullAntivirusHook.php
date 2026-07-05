<?php

namespace App\Services\Media\Hooks;

use App\Services\Media\Contracts\MediaAntivirusHook;

class NullAntivirusHook implements MediaAntivirusHook
{
    public function scan(string $filePath): array
    {
        return ['clean' => true];
    }
}
