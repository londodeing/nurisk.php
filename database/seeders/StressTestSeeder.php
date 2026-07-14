<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class StressTestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\OperationalObject::truncate();
        
        $objects = [];
        $now = now();
        
        // Generate 2000 random incidents across Central Java
        for ($i = 1; $i <= 2000; $i++) {
            $lat = -7.15 + (mt_rand(-5000, 5000) / 10000);
            $lng = 110.15 + (mt_rand(-10000, 10000) / 10000);
            
            $objects[] = [
                'id' => 'INC-STRESS-' . $i,
                'object_type' => 'incident',
                'status' => 'VERIFIED',
                'title' => "Incident $i",
                'summary' => "Stress test incident $i",
                'latitude' => $lat,
                'longitude' => $lng,
                'icon' => 'local_fire_department',
                'color' => '#EAB308',
                'priority' => mt_rand(1, 100),
                'popup_json' => json_encode(['header' => "Incident $i", 'summary' => 'Stress Test', 'status' => 'VERIFIED']),
                'timeline_json' => json_encode([]),
                'dashboard_json' => json_encode([]),
                'permissions' => json_encode([]),
                'refresh_interval' => 60,
                'created_at' => $now,
                'updated_at' => $now,
            ];
            
            if (count($objects) === 500) {
                \App\Models\OperationalObject::insert($objects);
                $objects = [];
            }
        }
        
        if (count($objects) > 0) {
            \App\Models\OperationalObject::insert($objects);
        }
    }
}
