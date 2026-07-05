<?php

namespace App\Services\CommandCenter;

class FreshnessService
{
    public function getFreshness(\DateTimeInterface $timestamp): array
    {
        $minutes = now()->diffInMinutes($timestamp);

        return [
            'color' => match (true) {
                $minutes < 15 => 'success',
                $minutes < 60 => 'warning',
                default => 'danger',
            },
            'label' => $this->humanReadable($minutes),
            'minutes' => $minutes,
            'is_stale' => $minutes >= 60,
        ];
    }

    public function humanReadable(int $minutes): string
    {
        return match (true) {
            $minutes < 1 => 'baru saja',
            $minutes < 60 => $minutes . ' menit lalu',
            $minutes < 1440 => intdiv($minutes, 60) . ' jam lalu',
            default => intdiv($minutes, 1440) . ' hari lalu',
        };
    }
}
