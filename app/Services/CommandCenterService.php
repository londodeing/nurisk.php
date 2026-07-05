<?php

namespace App\Services;

use App\Models\AuthUser;
use App\Models\LogistikStok;
use App\Models\OperasiInsiden;
use App\Models\OperasiPenugasan;
use App\Models\OperasiPosaju;
use Illuminate\Support\Facades\DB;

class CommandCenterService
{
    /**
     * Mendapatkan agregasi data utama untuk Dashboard Command Center
     */
    public function getAggregateStats(): array
    {
        // 1. Relawan Aktif di lapangan (status_penugasan = 'aktif' dan peran_otoritas = 'relawan')
        $relawanAktif = OperasiPenugasan::where('peran_otoritas', 'relawan')
            ->where('status_penugasan', 'aktif')
            ->count();

        // 2. Posko Aktif
        $poskoAktif = OperasiPosaju::whereIn('status_alur', ['aktif', 'diperpanjang'])->count();

        // 3. Sebaran Logistik (Total Barang Tersedia dari seluruh stok)
        $totalLogistik = LogistikStok::sum('jumlah_tersedia');

        // 4. Insiden Aktif
        $insidenAktif = OperasiInsiden::whereIn('status_operasi', ['siaga', 'tanggap_darurat', 'pemulihan'])
            ->count();

        return [
            'relawan_aktif' => $relawanAktif,
            'posko_aktif' => $poskoAktif,
            'total_logistik' => $totalLogistik,
            'insiden_aktif' => $insidenAktif,
        ];
    }

    /**
     * Mendapatkan daftar titik insiden dan posko untuk Live Map
     */
    public function getLiveMapPoints(): array
    {
        $points = [];

        // Ambil Posko Aju beserta lokasinya
        $poskoList = OperasiPosaju::whereIn('status_alur', ['aktif', 'diperpanjang'])
            ->with('insiden')
            ->get();

        foreach ($poskoList as $posko) {
            $points[] = [
                'type' => 'posko',
                'id' => $posko->id_posaju,
                'name' => $posko->nama_posko,
                'latitude' => $posko->latitude,
                'longitude' => $posko->longitude,
                'status' => $posko->status_alur,
                'insiden_kode' => $posko->insiden ? $posko->insiden->kode_kejadian : null
            ];
        }

        // Ambil Insiden (dari laporan asal)
        // Laporan kejadian memiliki titik latitude dan longitude
        $insidenList = OperasiInsiden::whereIn('status_operasi', ['siaga', 'tanggap_darurat', 'pemulihan'])
            ->with('laporanAsal')
            ->get();

        foreach ($insidenList as $insiden) {
            if ($insiden->laporanAsal && $insiden->laporanAsal->latitude && $insiden->laporanAsal->longitude) {
                $points[] = [
                    'type' => 'insiden',
                    'id' => $insiden->id_insiden,
                    'name' => 'Insiden ' . $insiden->kode_kejadian,
                    'latitude' => $insiden->laporanAsal->latitude,
                    'longitude' => $insiden->laporanAsal->longitude,
                    'status' => $insiden->status_operasi
                ];
            }
        }

        return $points;
    }
}
