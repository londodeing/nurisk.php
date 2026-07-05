<?php

namespace App\Services\Media\Contracts;

interface MediaAntivirusHook
{
    /**
     * Scan a file for malware before storage.
     *
     * @param string $filePath Absolute path to the uploaded file
     * @return array{clean: bool, threat_name?: string, details?: string}
     */
    public function scan(string $filePath): array;
}
