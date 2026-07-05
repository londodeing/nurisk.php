<?php

namespace App\Services\Weather;

use App\Models\OrganisasiPcnu;
use App\Models\OrganisasiMwc;
use App\Models\AuthUser;

class TerritoryResolver
{
    public function resolveFromUser(AuthUser $user): array
    {
        if ($user->hasRole('super_admin') || $user->hasRole('pwnu')) {
            return $this->allPcnuTerritories();
        }

        if ($user->hasRole('pcnu')) {
            $pcnuId = $user->default_scope_id;
            return $this->singlePcnu($pcnuId);
        }

        return [];
    }

    public function allPcnuTerritories(): array
    {
        $territories = [
            [
                'code' => 'pwnu:0',
                'type' => 'pwnu',
                'id' => 0,
                'name' => 'Jawa Tengah',
                'lat' => -7.5,
                'lon' => 110.0,
            ],
        ];

        $pcnuList = OrganisasiPcnu::with('unit')->get();
        foreach ($pcnuList as $pcnu) {
            $territories[] = [
                'code' => "pcnu:{$pcnu->id_pcnu}",
                'type' => 'pcnu',
                'id' => $pcnu->id_pcnu,
                'name' => $pcnu->nama_pcnu,
                'lat' => null,
                'lon' => null,
            ];
        }

        return $territories;
    }

    public function singlePcnu(int $pcnuId): array
    {
        $pcnu = OrganisasiPcnu::with('unit')->find($pcnuId);
        if (!$pcnu) return [];

        return [
            [
                'code' => "pcnu:{$pcnu->id_pcnu}",
                'type' => 'pcnu',
                'id' => $pcnu->id_pcnu,
                'name' => $pcnu->nama_pcnu,
                'lat' => null,
                'lon' => null,
            ],
        ];
    }

    public function resolveCoordinate(string $territoryCode): array
    {
        $parts = explode(':', $territoryCode);
        $type = $parts[0] ?? 'pwnu';
        $id = (int) ($parts[1] ?? 0);

        if ($type === 'pwnu') {
            return ['lat' => -7.5, 'lon' => 110.0];
        }

        if ($type === 'pcnu' && $id) {
            $pcnu = OrganisasiPcnu::with('unit')->find($id);
            if ($pcnu && $pcnu->unit) {
                $kab = \App\Models\WilayahKabupaten::find($pcnu->unit->id_wilayah);
                if ($kab) {
                    return $this->approximateCenter($kab->id_kab);
                }
            }
        }

        return ['lat' => -7.5, 'lon' => 110.0];
    }

    private function approximateCenter(string $kabCode): array
    {
        $centers = [
            '3319' => ['lat' => -6.8, 'lon' => 110.84],
            '3320' => ['lat' => -6.58, 'lon' => 110.67],
            '3321' => ['lat' => -6.89, 'lon' => 110.64],
            '3301' => ['lat' => -7.35, 'lon' => 109.0],
            '3302' => ['lat' => -7.45, 'lon' => 109.17],
            '3303' => ['lat' => -7.37, 'lon' => 109.37],
            '3304' => ['lat' => -7.42, 'lon' => 109.5],
            '3305' => ['lat' => -7.68, 'lon' => 109.67],
            '3306' => ['lat' => -7.6, 'lon' => 110.0],
            '3307' => ['lat' => -7.3, 'lon' => 109.9],
            '3308' => ['lat' => -7.55, 'lon' => 110.2],
            '3309' => ['lat' => -7.5, 'lon' => 110.6],
            '3310' => ['lat' => -7.7, 'lon' => 110.6],
            '3311' => ['lat' => -7.1, 'lon' => 110.7],
            '3312' => ['lat' => -7.7, 'lon' => 111.1],
            '3313' => ['lat' => -7.6, 'lon' => 111.3],
            '3314' => ['lat' => -7.6, 'lon' => 110.8],
            '3315' => ['lat' => -7.1, 'lon' => 110.4],
            '3316' => ['lat' => -7.0, 'lon' => 110.4],
            '3317' => ['lat' => -6.7, 'lon' => 111.1],
            '3318' => ['lat' => -6.7, 'lon' => 111.3],
            '3322' => ['lat' => -7.03, 'lon' => 110.1],
            '3323' => ['lat' => -7.1, 'lon' => 110.1],
            '3324' => ['lat' => -6.98, 'lon' => 110.0],
            '3325' => ['lat' => -6.9, 'lon' => 109.75],
            '3326' => ['lat' => -6.95, 'lon' => 109.7],
            '3327' => ['lat' => -7.0, 'lon' => 109.55],
            '3328' => ['lat' => -7.2, 'lon' => 108.9],
            '3329' => ['lat' => -7.0, 'lon' => 108.9],
            '3371' => ['lat' => -6.98, 'lon' => 110.42],
            '3372' => ['lat' => -7.57, 'lon' => 110.82],
            '3373' => ['lat' => -7.33, 'lon' => 109.91],
            '3374' => ['lat' => -6.97, 'lon' => 110.12],
            '3375' => ['lat' => -6.88, 'lon' => 109.67],
            '3376' => ['lat' => -7.8, 'lon' => 110.35],
        ];

        return $centers[$kabCode] ?? ['lat' => -7.5, 'lon' => 110.0];
    }
}
