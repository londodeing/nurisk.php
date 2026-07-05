<?php

namespace App\Services;

class ClusterCoordinatorGapAnalysisService
{
    public function getMatrix()
    {
        // Matrix Status Posko (Kesehatan, Logistik, Relawan, Perlindungan)
        return [
            ['posko' => 'Posko A', 'kesehatan' => -10, 'logistik' => 20, 'relawan' => -5, 'perlindungan' => 0, 'status' => 'Perlu Intervensi'],
            ['posko' => 'Posko B', 'kesehatan' => 5, 'logistik' => 50, 'relawan' => 10, 'perlindungan' => 2, 'status' => 'Aman (Surplus)'],
            ['posko' => 'Posko C', 'kesehatan' => -20, 'logistik' => -30, 'relawan' => -15, 'perlindungan' => -5, 'status' => 'KRITIS'],
        ];
    }

    public function getRedistribution()
    {
        return [
            [
                'source' => 'Posko B',
                'target' => 'Posko C',
                'resource' => '50 Paket Logistik',
                'action_label' => 'Mutasi B -> C'
            ],
            [
                'source' => 'Posko B',
                'target' => 'Posko A',
                'resource' => '5 Tenaga Medis',
                'action_label' => 'Rotasi B -> A'
            ]
        ];
    }
}
