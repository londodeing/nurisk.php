<?php

namespace App\Services\Media;

use Illuminate\Support\Facades\File;

class MediaAuditService
{
    private array $results = [];

    public function audit(): array
    {
        $this->results = [];
        $app = app_path();
        $files = File::allFiles($app);

        $patterns = [
            'Storage::put(' => ['type' => 'STORAGE_PUT', 'severity' => 'HIGH'],
            'Storage::putFile(' => ['type' => 'STORAGE_PUTFILE', 'severity' => 'HIGH'],
            'Storage::putFileAs(' => ['type' => 'STORAGE_PUTFILEAS', 'severity' => 'HIGH'],
            'Storage::url(' => ['type' => 'STORAGE_URL', 'severity' => 'MEDIUM'],
            'Storage::delete(' => ['type' => 'STORAGE_DELETE', 'severity' => 'HIGH'],
            'Storage::download(' => ['type' => 'STORAGE_DOWNLOAD', 'severity' => 'MEDIUM'],
            '->store(' => ['type' => 'REQUEST_STORE', 'severity' => 'HIGH'],
            '->storeAs(' => ['type' => 'REQUEST_STOREAS', 'severity' => 'HIGH'],
            "asset('storage/" => ['type' => 'ASSET_STORAGE', 'severity' => 'MEDIUM'],
            "asset(\"storage/" => ['type' => 'ASSET_STORAGE', 'severity' => 'MEDIUM'],
            "public_path('storage/" => ['type' => 'PUBLIC_PATH_STORAGE', 'severity' => 'LOW'],
            "public_path(\"storage/" => ['type' => 'PUBLIC_PATH_STORAGE', 'severity' => 'LOW'],
        ];

        foreach ($files as $file) {
            $path = $file->getRealPath();
            $relativePath = str_replace(base_path(), '', $path);
            $content = file_get_contents($path);
            $lines = explode("\n", $content);

            foreach ($patterns as $pattern => $info) {
                foreach ($lines as $lineNum => $line) {
                    if (str_contains($line, $pattern)) {
                        // Skip if this is in the Media service itself or the audit/commands
                        if (str_contains($relativePath, 'Services/Media/')
                            || str_contains($relativePath, 'Console/Commands/Media')
                            || str_contains($relativePath, '/MediaAudit')) {
                            continue;
                        }

                        $trimmedLine = trim($line);
                        $this->results[] = [
                            'file' => ltrim($relativePath, '/'),
                            'line' => $lineNum + 1,
                            'type' => $info['type'],
                            'severity' => $info['severity'],
                            'code' => $trimmedLine,
                        ];
                    }
                }
            }
        }

        // Also scan Blade views for asset('storage/...') and Storage::url()
        $views = File::allFiles(resource_path('views'));
        $bladePatterns = [
            "Storage::url(",
            "asset('storage/",
            "asset(\"storage/",
            "Storage::disk(",
        ];

        foreach ($views as $file) {
            $path = $file->getRealPath();
            $relativePath = str_replace(base_path(), '', $path);
            $content = file_get_contents($path);
            $lines = explode("\n", $content);

            foreach ($bladePatterns as $pattern) {
                foreach ($lines as $lineNum => $line) {
                    if (str_contains($line, $pattern)) {
                        $trimmedLine = trim($line);
                        $this->results[] = [
                            'file' => ltrim($relativePath, '/'),
                            'line' => $lineNum + 1,
                            'type' => 'BLADE_' . ($pattern === "Storage::url(" ? 'STORAGE_URL' : ($pattern === "Storage::disk(" ? 'STORAGE_DISK' : 'ASSET_STORAGE')),
                            'severity' => 'MEDIUM',
                            'code' => $trimmedLine,
                        ];
                    }
                }
            }
        }

        return $this->results;
    }

    public function getSummary(): array
    {
        $high = count(array_filter($this->results, fn($r) => $r['severity'] === 'HIGH'));
        $medium = count(array_filter($this->results, fn($r) => $r['severity'] === 'MEDIUM'));
        $low = count(array_filter($this->results, fn($r) => $r['severity'] === 'LOW'));

        $byType = [];
        foreach ($this->results as $r) {
            $byType[$r['type']] = ($byType[$r['type']] ?? 0) + 1;
        }

        $byFile = [];
        foreach ($this->results as $r) {
            $file = $r['file'];
            $byFile[$file] = ($byFile[$file] ?? 0) + 1;
        }

        return [
            'total' => count($this->results),
            'high' => $high,
            'medium' => $medium,
            'low' => $low,
            'by_type' => $byType,
            'by_file' => $byFile,
        ];
    }
}
