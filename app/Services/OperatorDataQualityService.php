<?php

namespace App\Services;

class OperatorDataQualityService
{
    public function getQualityQueue()
    {
        $queue = [
            ['type' => 'Assessment #ASM-040', 'issue' => 'Kategori kosong & Lokasi tidak valid', 'severity' => 'Critical', 'badge' => 'danger'],
            ['type' => 'Relawan #R-112', 'issue' => 'Nomor HP dan Wilayah penugasan kosong', 'severity' => 'High', 'badge' => 'warning text-dark'],
            ['type' => 'Logistik Stok', 'issue' => 'Stok Beras menjadi negatif (-5 Kg)', 'severity' => 'Critical', 'badge' => 'danger'],
        ];

        return collect($queue)->sortBy(function ($item) {
            return $item['severity'] === 'Critical' ? 1 : 2;
        })->values()->toArray();
    }
}
