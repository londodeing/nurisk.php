<?php

namespace App\Services\Dashboard\Projection;

use Illuminate\Support\Facades\Cache;

class ResourceProjectionService
{
    public function getStandbyResources(): array
    {
        return Cache::remember('projection_standby_resources', 60, function () {
            return [
                'personnel' => [
                    'active' => 45,
                    'standby' => 120,
                ],
                'logistics' => [
                    ['item' => 'Sembako', 'quantity' => 350, 'unit' => 'paket'],
                    ['item' => 'Perahu Karet', 'quantity' => 4, 'unit' => 'unit'],
                    ['item' => 'Tenda Darurat', 'quantity' => 10, 'unit' => 'unit'],
                ],
            ];
        });
    }
}
