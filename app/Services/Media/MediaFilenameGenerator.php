<?php

namespace App\Services\Media;

use Illuminate\Support\Str;

class MediaFilenameGenerator
{
    private int $sequence = 0;

    public function generate(string $entityType, string $extension, ?int $sequence = null, ?string $variant = null): string
    {
        $prefix = $this->prefix($entityType);
        $date = now()->format('Ymd');
        $seq = str_pad((string) ($sequence ?? ++$this->sequence), 6, '0', STR_PAD_LEFT);
        $var = $variant ? "-$variant" : '';

        return "{$prefix}-{$date}-{$seq}{$var}.{$extension}";
    }

    public function generateThumb(string $filename): string
    {
        $info = pathinfo($filename);
        return "{$info['filename']}-thumb.{$info['extension']}";
    }

    public function generateMedium(string $filename): string
    {
        $info = pathinfo($filename);
        return "{$info['filename']}-medium.{$info['extension']}";
    }

    public function parseSequence(string $filename): ?int
    {
        if (preg_match('/-(\d{6})[-.]/', $filename, $m)) {
            return (int) $m[1];
        }
        return null;
    }

    public function nextSequence(string $entityType): int
    {
        $this->sequence++;
        return $this->sequence;
    }

    private function prefix(string $entityType): string
    {
        return match ($entityType) {
            'laporan' => 'LAP',
            'aset' => 'AST',
            'incident' => 'INC',
            'surat' => 'SUR',
            'volunteer' => 'VOL',
            'logo' => 'LOGO',
            default => strtoupper(Str::substr($entityType, 0, 3)),
        };
    }
}
