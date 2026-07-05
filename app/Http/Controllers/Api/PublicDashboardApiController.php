<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\OperasiInsiden;
use App\Models\OperasiPenugasan;
use App\Models\Assessment\AssessmentKebutuhanNumerik;
use App\Models\AssessmentUtama;
use App\Models\Assessment\AssessmentKebutuhanNumerikMaster;

class PublicDashboardApiController extends Controller
{
    public function data(Request $request): JsonResponse
    {
        $insidenAktif = OperasiInsiden::query()
            ->whereIn('status_insiden', ['respon', 'pemulihan'])
            ->whereNull('dihapus_pada')
            ->with([
                'jenisBencana:id_jenis,nama_bencana,slug,ikon_map',
                'pcnu:id_pcnu,nama_pcnu',
                'laporanAsal:id_laporan_kejadian,latitude,longitude,keterangan_situasi',
            ])
            ->get()
            ->map(function ($i) {
                $needsNumeric = [];
                try {
                    $latestAssessment = AssessmentUtama::where('id_insiden', $i->id_insiden)
                        ->latest('dibuat_pada')
                        ->first();
                    if ($latestAssessment) {
                        $needs = AssessmentKebutuhanNumerik::where('id_assessment', $latestAssessment->id_assessment_utama)
                            ->with('item:id_item,nama_item')
                            ->get();
                        foreach ($needs as $n) {
                            $gap = max(0, (float)$n->jumlah_dibutuhkan - (float)$n->jumlah_tersedia);
                            if ($gap > 0) {
                                $key = $n->item?->nama_item ?? 'item_'.$n->id_item;
                                $needsNumeric[$key] = (int) ceil($gap);
                            }
                        }
                    }
                } catch (\Exception $e) {
                    // assessment module mungkin belum tersedia
                }

                return [
                    'id'             => $i->id_insiden,
                    'kode'           => $i->kode_kejadian,
                    'status'         => $i->status_insiden,
                    'disaster_type'  => $i->jenisBencana?->slug ?? $i->jenisBencana?->nama_bencana,
                    'jenis'          => $i->jenisBencana?->nama_bencana,
                    'ikon_map'       => $i->jenisBencana?->ikon_map ?? 'default.png',
                    'pcnu'           => $i->pcnu?->nama_pcnu,
                    'waktu_mulai'    => $i->waktu_mulai?->toIso8601String(),
                    'description'    => $i->laporanAsal?->keterangan_situasi,
                    'lat'            => $i->laporanAsal?->latitude,
                    'lng'            => $i->laporanAsal?->longitude,
                    'has_koordinat'  => !is_null($i->laporanAsal?->latitude) && !is_null($i->laporanAsal?->longitude),
                    'needs_numeric'  => $needsNumeric,
                ];
            });

        $totalInsiden = $insidenAktif->count();

        $totalPersonel = OperasiPenugasan::aktif()->count();

        $korbanTerdampak = 0;
        try {
            $incidentIds = $insidenAktif->pluck('id')->toArray();
            if (!empty($incidentIds) && \Illuminate\Support\Facades\Schema::hasTable('operasi_sitrep_dampak')) {
                $latestIds = \App\Models\OperasiSitrep::selectRaw('MAX(id_sitrep) as id')
                    ->whereIn('id_insiden', $incidentIds)
                    ->groupBy('id_insiden')
                    ->pluck('id');
                if ($latestIds->isNotEmpty()) {
                    $korbanTerdampak = \App\Models\OperasiSitrepDampak::whereIn('id_sitrep', $latestIds)
                        ->sum(\Illuminate\Support\Facades\DB::raw('COALESCE(mengungsi,0) + COALESCE(luka_ringan,0) + COALESCE(luka_berat,0) + COALESCE(meninggal,0) + COALESCE(hilang,0)')) ?? 0;
                }
            }
        } catch (\Exception $e) {
            $korbanTerdampak = 0;
        }

        $totalKebutuhanGap = 0;
        foreach ($insidenAktif as $i) {
            if (!empty($i['needs_numeric'])) {
                $totalKebutuhanGap += array_sum(array_values($i['needs_numeric']));
            }
        }

        return response()->json([
            'kpi' => [
                'total_insiden'    => $totalInsiden,
                'total_personel'   => $totalPersonel,
                'korban_terdampak' => $korbanTerdampak,
                'kebutuhan_gap'    => $totalKebutuhanGap,
            ],
            'insiden'    => $insidenAktif,
            'updated_at' => now()->toIso8601String(),
        ]);
    }
}
