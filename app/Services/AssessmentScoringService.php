<?php

namespace App\Services;

use App\Models\AssessmentUtama;
use App\Models\Assessment\AssessmentMasterIndikatorSkor;
use App\Models\Assessment\AssessmentSkorItem;
use App\Models\Assessment\AssessmentRingkasanSkor;

class AssessmentScoringService
{
    /**
     * Hitung dan simpan ringkasan skor assessment.
     * Dipanggil setelah semua sub-assessment selesai diisi.
     */
    public function hitungDanSimpan(AssessmentUtama $assessment): AssessmentRingkasanSkor
    {
        $skorPerDomain = [];

        foreach (['manusia','infrastruktur','lingkungan','ekonomi','sosial'] as $domain) {
            $indikatorDomain = AssessmentMasterIndikatorSkor::where('domain', $domain)
                ->where('aktif', 1)->get();

            if ($indikatorDomain->isEmpty()) {
                $skorPerDomain[$domain] = 0;
                continue;
            }

            $totalBobot  = $indikatorDomain->sum('bobot');
            $totalSkor   = 0;

            foreach ($indikatorDomain as $ind) {
                $item = AssessmentSkorItem::where('id_assessment', $assessment->id_assessment_utama)
                    ->where('id_indikator', $ind->id_indikator)
                    ->first();

                if ($item) {
                    // Normalisasi skor 1-5 ke 0-100
                    $skorNormalisasi = (($item->skor_1_5 - 1) / 4) * 100;
                    $totalSkor += $skorNormalisasi * $ind->bobot;
                }
            }

            $skorPerDomain[$domain] = $totalBobot > 0
                ? round($totalSkor / $totalBobot, 2)
                : 0;
        }

        $ringkasan = AssessmentRingkasanSkor::updateOrCreate(
            ['id_assessment' => $assessment->id_assessment_utama],
            [
                'skor_manusia'       => $skorPerDomain['manusia'],
                'skor_infrastruktur' => $skorPerDomain['infrastruktur'],
                'skor_lingkungan'    => $skorPerDomain['lingkungan'],
                'skor_ekonomi'       => $skorPerDomain['ekonomi'],
                'skor_sosial'        => $skorPerDomain['sosial'],
            ]
        );

        $ringkasan->skor_total = $ringkasan->hitungSkorTotal();
        $ringkasan->tingkat_keparahan = $ringkasan->tentukantingkatKeparahan();
        $ringkasan->rekomendasi_respon = $this->tentukantingkatRespons($ringkasan->skor_total);
        $ringkasan->save();

        return $ringkasan;
    }

    private function tentukantingkatRespons(float $skor): string
    {
        return match(true) {
            $skor < 20 => 'monitoring',
            $skor < 40 => 'siaga',
            $skor < 60 => 'tanggap_cepat',
            $skor < 80 => 'mobilisasi_besar',
            default    => 'eskalasi_nasional',
        };
    }
}
