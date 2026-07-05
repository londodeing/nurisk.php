<?php

namespace App\Services;

class ClusterCoordinatorCoverageService
{
    public function getUnservedAreas()
    {
        // Daftar wilayah yang sama sekali belum disentuh operasi
        return [
            ['area' => 'Desa Cikarang Barat', 'issue' => 'Belum ada assessment', 'duration' => '48 Jam'],
            ['area' => 'Dusun Mekar Jaya', 'issue' => 'Belum ada distribusi bantuan', 'duration' => '24 Jam'],
            ['area' => 'Kampung Nelayan', 'issue' => 'Belum ada sitrep', 'duration' => '12 Jam'],
        ];
    }
}
