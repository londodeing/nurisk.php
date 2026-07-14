<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DemoBanjirKudusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\OperationalObject::truncate();
        
        $now = now();
        
        // Fase 1: Laporan Diterima
        \App\Models\OperationalObject::create([
            'id' => 'KUDUS-001',
            'object_type' => 'incident',
            'status' => 'DRAFT',
            'title' => 'Laporan Banjir Jati Wetan',
            'summary' => 'Air meluap setinggi lutut di Desa Jati Wetan.',
            'latitude' => -6.8228,
            'longitude' => 110.8354,
            'icon' => 'water_drop',
            'color' => '#9CA3AF', // Gray
            'priority' => 10,
            'popup_json' => [
                'header' => 'Laporan Masyarakat',
                'summary' => 'Air meluap setinggi lutut di Desa Jati Wetan.',
                'status' => 'DRAFT',
                'buttons' => ['verify_report']
            ],
            'timeline_json' => [
                [
                    'time' => $now->copy()->subMinutes(10)->toIso8601String(),
                    'status' => 'DRAFT',
                    'title' => 'Laporan Diterima via Aplikasi',
                ]
            ],
            'dashboard_json' => [],
            'permissions' => [],
            'refresh_interval' => 15,
        ]);
    }
}
