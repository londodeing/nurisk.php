<?php

namespace App\Services;

use App\Models\Histori\HistoriBencanaWilayah;
use App\Models\Histori\HistoriAnalisisMusiman;

class HistoriTrendService
{
    /**
     * Hitung analisis musiman dari data historis.
     * Jalankan via: php artisan nurisk:hitung-analisis-musiman
     */
    public function hitungAnalisisiMusiman(string $idKab, int $idJenis): void
    {
        $dataHistori = HistoriBencanaWilayah::where('id_kab', $idKab)
            ->where('id_jenis_bencana', $idJenis)
            ->where('is_terverifikasi', 1)
            ->get();

        if ($dataHistori->count() < 3) return; // Butuh minimal 3 data

        $tahunMin = $dataHistori->min('tahun');
        $tahunMax = $dataHistori->max('tahun');
        $jumlahTahun = $tahunMax - $tahunMin + 1;
        $rataPerTahun = $dataHistori->count() / $jumlahTahun;

        for ($bulan = 1; $bulan <= 12; $bulan++) {
            $frekuensiBulanIni = $dataHistori->where('bulan', $bulan)->count();
            $indeksMusiman = $rataPerTahun > 0
                ? round(($frekuensiBulanIni / $jumlahTahun) / ($rataPerTahun / 12), 3)
                : 0;

            HistoriAnalisisMusiman::updateOrCreate(
                ['id_kab' => $idKab, 'id_jenis_bencana' => $idJenis, 'bulan' => $bulan],
                [
                    'frekuensi_total'     => $frekuensiBulanIni,
                    'tahun_data_mulai'    => $tahunMin,
                    'tahun_data_akhir'    => $tahunMax,
                    'jumlah_tahun_data'   => $jumlahTahun,
                    'rata_rata_per_tahun' => round($rataPerTahun, 3),
                    'indeks_musiman'      => $indeksMusiman,
                    'dihitung_pada'       => now(),
                ]
            );
        }
    }

    /**
     * Hitung probabilitas bencana untuk 30/90 hari ke depan.
     * Metode: Frekuensi historis × indeks musiman bulan berjalan
     */
    public function hitungProbabilitas(string $idKab, int $idJenis, string $periode): float
    {
        $bulanSekarang = now()->month;

        $analisisMusiman = HistoriAnalisisMusiman::where('id_kab', $idKab)
            ->where('id_jenis_bencana', $idJenis)
            ->where('bulan', $bulanSekarang)
            ->first();

        if (!$analisisMusiman) return 0;

        // Base probability dari rata-rata frekuensi tahunan
        $baseProbTahunan = min($analisisMusiman->rata_rata_per_tahun, 1.0);

        // Adjust berdasarkan indeks musiman dan periode
        $faktoryPeriode = match($periode) {
            '30_hari'  => 30 / 365,
            '90_hari'  => 90 / 365,
            '6_bulan'  => 0.5,
            '1_tahun'  => 1.0,
            default    => 30 / 365,
        };

        $prob = $baseProbTahunan * $faktoryPeriode * $analisisMusiman->indeks_musiman;

        return round(min($prob, 0.9999), 4); // Max 99.99%
    }
}
