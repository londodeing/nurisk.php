<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use App\Models\OperasiInsiden;
use App\Models\AssessmentUtama;
use App\Models\Assessment\AssessmentKebutuhanNumerik;
use App\Models\AssessmentDampakManusia;
use App\Models\OperasiSitrep;

class PublicDashboardWebController extends Controller
{
    public function index(): JsonResponse
    {
        $aktifStatuses = ['respon', 'pemulihan', 'terverifikasi'];

        // 1. Total Insiden Aktif
        $totalInsiden = OperasiInsiden::whereIn('status_insiden', $aktifStatuses)->count();

        // 2. Kebutuhan Gap & Korban Terdampak
        $activeIncidentsIds = OperasiInsiden::whereIn('status_insiden', $aktifStatuses)->pluck('id_insiden');
        
        $latestAssessmentsIds = AssessmentUtama::whereIn('id_insiden', $activeIncidentsIds)
            ->whereIn('id_assessment_utama', function ($query) {
                $query->selectRaw('MAX(id_assessment_utama)')
                      ->from('assessment_utama')
                      ->groupBy('id_insiden');
            })
            ->pluck('id_assessment_utama');

        $kebutuhanGap = AssessmentKebutuhanNumerik::whereIn('id_assessment', $latestAssessmentsIds)
            ->selectRaw('SUM(GREATEST(0, jumlah_dibutuhkan - jumlah_tersedia)) as total_gap')
            ->value('total_gap') ?? 0;

        $korbanTerdampak = AssessmentDampakManusia::whereIn('id_assessment_utama', $latestAssessmentsIds)
            ->selectRaw('SUM(meninggal + hilang + luka_berat + luka_ringan + menderita_mengungsi) as total_korban')
            ->value('total_korban') ?? 0;

        $latestSitrepsIds = [];
        if (($korbanTerdampak == 0 || $kebutuhanGap == 0) && count($activeIncidentsIds) > 0) {
            $latestSitrepsIds = OperasiSitrep::whereIn('id_insiden', $activeIncidentsIds)
                ->whereIn('id_sitrep', function ($query) {
                    $query->selectRaw('MAX(id_sitrep)')
                          ->from('operasi_sitrep')
                          ->groupBy('id_insiden');
                })
                ->pluck('id_sitrep');
        }

        // Fallback for korbanTerdampak to latest Sitrep if no assessment
        if ($korbanTerdampak == 0 && count($latestSitrepsIds) > 0) {
            $korbanTerdampak = \App\Models\OperasiSitrepDampak::whereIn('id_sitrep', $latestSitrepsIds)
                ->selectRaw('SUM(meninggal + hilang + luka_berat + luka_ringan + mengungsi) as total_korban')
                ->value('total_korban') ?? 0;
        }

        if ($kebutuhanGap == 0 && count($latestSitrepsIds) > 0) {
            $kebutuhanGap = \App\Models\OperasiSitrepKebutuhan::whereIn('id_sitrep', $latestSitrepsIds)
                ->selectRaw('SUM(jumlah) as total_gap')
                ->value('total_gap') ?? 0;
        }

        // 3. Latest incidents array
        $latestIncidentsQuery = OperasiInsiden::with(['jenisBencana', 'pcnu', 'laporanAsal', 'assessments' => function($q) {
            $q->latest('dibuat_pada')->take(1);
        }])
        ->whereIn('status_insiden', $aktifStatuses)
        ->latest('waktu_mulai')
        ->get();

        $insiden = $latestIncidentsQuery->map(function ($ins) {
            $needsNumeric = [];
            $latestAssessment = $ins->assessments->first();
            $latestSitrep = null;

            if ($latestAssessment) {
                $needs = AssessmentKebutuhanNumerik::where('id_assessment', $latestAssessment->id_assessment_utama)
                    ->with('item')
                    ->get();
                foreach ($needs as $n) {
                    $gap = max(0, (float)$n->jumlah_dibutuhkan - (float)$n->jumlah_tersedia);
                    if ($gap > 0) {
                        $key = $n->item?->nama_item ?? 'item_'.$n->id_item;
                        $needsNumeric[$key] = (int) ceil($gap);
                    }
                }
            }
            if (empty($needsNumeric)) {
                $latestSitrep = \App\Models\OperasiSitrep::where('id_insiden', $ins->id_insiden)
                    ->latest('dibuat_pada')
                    ->first();
                if ($latestSitrep) {
                    $sitrepNeeds = \App\Models\OperasiSitrepKebutuhan::where('id_sitrep', $latestSitrep->id_sitrep)->get();
                    foreach ($sitrepNeeds as $sn) {
                        $needsNumeric[$sn->nama_kebutuhan] = (int) $sn->jumlah;
                    }
                }
            }

            $totalKorban = 0;
            if ($latestAssessment) {
                $dm = AssessmentDampakManusia::where('id_assessment_utama', $latestAssessment->id_assessment_utama)->first();
                if ($dm) {
                    $totalKorban = $dm->meninggal + $dm->hilang + $dm->luka_berat + $dm->luka_ringan + $dm->menderita_mengungsi;
                } else {
                    $dm2 = \App\Models\Assessment\AssessmentDampakManusiaV2::where('id_assessment', $latestAssessment->id_assessment_utama)->first();
                    if ($dm2) {
                        $totalKorban = $dm2->meninggal + $dm2->hilang + $dm2->luka_berat + $dm2->luka_ringan + $dm2->pengungsi_jiwa;
                    }
                }
            }
            if ($totalKorban == 0) {
                if (!$latestSitrep) {
                    $latestSitrep = \App\Models\OperasiSitrep::where('id_insiden', $ins->id_insiden)->latest('dibuat_pada')->first();
                }
                if ($latestSitrep) {
                    $sd = \App\Models\OperasiSitrepDampak::where('id_sitrep', $latestSitrep->id_sitrep)->first();
                    if ($sd) {
                        $totalKorban = $sd->meninggal + $sd->hilang + $sd->luka_berat + $sd->luka_ringan + $sd->mengungsi;
                    }
                }
            }

            return [
                'id' => $ins->id_insiden,
                'kode' => $ins->kode_kejadian,
                'status' => $ins->status_insiden,
                'waktu_mulai' => $ins->waktu_mulai?->toIso8601String(),
                'description' => $ins->laporanAsal?->keterangan_situasi ?? 'Sedang dalam penanganan.',
                'jenis' => $ins->jenisBencana?->nama_bencana ?? 'Kejadian',
                'pcnu' => $ins->pcnu?->nama_pcnu,
                'needs_numeric' => $needsNumeric,
                'korban_summary' => (int) $totalKorban,
                'lat' => $ins->laporanAsal?->latitude,
                'lng' => $ins->laporanAsal?->longitude,
            ];
        });

        return response()->json([
            'kpi' => [
                'total_insiden' => $totalInsiden,
                'kebutuhan_gap' => (int) ceil($kebutuhanGap),
                'korban_terdampak' => (int) $korbanTerdampak,
            ],
            'insiden' => $insiden
        ]);
    }
}
