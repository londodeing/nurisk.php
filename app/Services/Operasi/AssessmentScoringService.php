<?php

namespace App\Services\Operasi;

use App\Models\AssessmentUtama;
use App\Models\Assessment\AssessmentMasterIndikatorSkor;
use App\Models\Assessment\AssessmentSkorItem;
use App\Models\Assessment\AssessmentRingkasanSkor;
use Illuminate\Support\Facades\DB;

class AssessmentScoringService
{
    /**
     * Calculate and save scores for an assessment
     */
    public function calculate(AssessmentUtama $assessment): AssessmentRingkasanSkor
    {
        $assessment->loadMissing(['dampakManusia', 'dampakInfrastruktur', 'dampakLingkungan', 'dampakEkonomi']);

        $indicators = AssessmentMasterIndikatorSkor::where('aktif', true)->get();

        return DB::transaction(function () use ($assessment, $indicators) {
            // Delete old items
            AssessmentSkorItem::where('id_assessment', $assessment->id_assessment_utama)->delete();

            $domainScores = [
                'manusia' => ['score' => 0, 'weight' => 0],
                'infrastruktur' => ['score' => 0, 'weight' => 0],
                'lingkungan' => ['score' => 0, 'weight' => 0],
                'ekonomi' => ['score' => 0, 'weight' => 0],
                'sosial' => ['score' => 0, 'weight' => 0],
                'kapasitas' => ['score' => 0, 'weight' => 0],
            ];

            foreach ($indicators as $ind) {
                $measuredValue = $this->extractMeasuredValue($assessment, $ind->kode_indikator);
                $skor15 = $this->calculateScale1To5($ind->kode_indikator, $measuredValue);

                AssessmentSkorItem::create([
                    'id_assessment' => $assessment->id_assessment_utama,
                    'id_indikator' => $ind->id_indikator,
                    'nilai_terukur' => $measuredValue,
                    'skor_1_5' => $skor15,
                    'catatan' => 'Auto-calculated'
                ]);

                // Calculate weighted contribution
                // skor_1_5 is 1 to 5. We normalize to 0-100: (skor_1_5 - 1) / 4 * 100
                $normalized100 = (($skor15 - 1) / 4) * 100;
                
                $domainScores[$ind->domain]['score'] += ($normalized100 * $ind->bobot);
                $domainScores[$ind->domain]['weight'] += $ind->bobot;
            }

            // Calculate final domain scores (0-100)
            $skor_manusia = $domainScores['manusia']['weight'] > 0 ? $domainScores['manusia']['score'] / $domainScores['manusia']['weight'] : 0;
            $skor_infra = $domainScores['infrastruktur']['weight'] > 0 ? $domainScores['infrastruktur']['score'] / $domainScores['infrastruktur']['weight'] : 0;
            $skor_lingkungan = $domainScores['lingkungan']['weight'] > 0 ? $domainScores['lingkungan']['score'] / $domainScores['lingkungan']['weight'] : 0;
            $skor_ekonomi = $domainScores['ekonomi']['weight'] > 0 ? $domainScores['ekonomi']['score'] / $domainScores['ekonomi']['weight'] : 0;
            $skor_sosial = $domainScores['sosial']['weight'] > 0 ? $domainScores['sosial']['score'] / $domainScores['sosial']['weight'] : 0;

            // Total Score is average of active domains (ignoring 0 weight domains)
            $activeDomains = 0;
            $totalScoreSum = 0;
            foreach ($domainScores as $domain => $data) {
                if ($data['weight'] > 0) {
                    $activeDomains++;
                    $totalScoreSum += ($data['score'] / $data['weight']);
                }
            }
            $skor_total = $activeDomains > 0 ? $totalScoreSum / $activeDomains : 0;

            // Determine keparahan and rekomendasi
            $tingkat = 'minor';
            $rekomendasi = 'monitoring';

            if ($skor_total >= 80) {
                $tingkat = 'katastrofik';
                $rekomendasi = 'eskalasi_nasional';
            } elseif ($skor_total >= 60) {
                $tingkat = 'berat';
                $rekomendasi = 'mobilisasi_besar';
            } elseif ($skor_total >= 40) {
                $tingkat = 'signifikan';
                $rekomendasi = 'tanggap_cepat';
            } elseif ($skor_total >= 20) {
                $tingkat = 'sedang';
                $rekomendasi = 'siaga';
            }

            // Save ringkasan
            $ringkasan = AssessmentRingkasanSkor::updateOrCreate(
                ['id_assessment' => $assessment->id_assessment_utama],
                [
                    'skor_manusia' => $skor_manusia,
                    'skor_infrastruktur' => $skor_infra,
                    'skor_lingkungan' => $skor_lingkungan,
                    'skor_ekonomi' => $skor_ekonomi,
                    'skor_sosial' => $skor_sosial,
                    'skor_total' => $skor_total,
                    'tingkat_keparahan' => $tingkat,
                    'rekomendasi_respon' => $rekomendasi
                ]
            );

            return $ringkasan;
        });
    }

    private function extractMeasuredValue(AssessmentUtama $assessment, string $kode): float
    {
        switch ($kode) {
            case 'M01': // Korban Meninggal
                return $assessment->dampakManusia ? $assessment->dampakManusia->meninggal : 0;
            case 'M02': // Pengungsi
                return $assessment->dampakManusia ? $assessment->dampakManusia->menderita_mengungsi : 0;
            case 'M03': // Kelompok Rentan (Simplified, just 0 for now unless mapped)
                return 0; 
            case 'I01': // Rumah Rusak Total
                return $assessment->dampakInfrastruktur ? 
                    ($assessment->dampakInfrastruktur->rumah_rusak_berat + $assessment->dampakInfrastruktur->rumah_rusak_sedang) : 0;
            case 'I02': // Jalan Rusak km
                return $assessment->dampakInfrastruktur ? $assessment->dampakInfrastruktur->jalan_rusak_km : 0;
            case 'I03': // Fasilitas Publik
                return $assessment->dampakInfrastruktur ? 
                    ($assessment->dampakInfrastruktur->fasilitas_kesehatan_rusak + 
                     $assessment->dampakInfrastruktur->fasilitas_pendidikan_rusak + 
                     $assessment->dampakInfrastruktur->tempat_ibadah_rusak) : 0;
            case 'L01': // Lahan Pertanian Rusak
                return $assessment->dampakLingkungan ? $assessment->dampakLingkungan->lahan_pertanian_rusak_ha : 0;
            case 'L02': // Pencemaran
                return $assessment->dampakLingkungan ? $assessment->dampakLingkungan->lahan_tercemar_ha : 0;
            case 'E01': // Estimasi Kerugian Total
                if (!$assessment->dampakEkonomi) return 0;
                return $assessment->dampakEkonomi->kerugian_perumahan + 
                       $assessment->dampakEkonomi->kerugian_pertanian + 
                       $assessment->dampakEkonomi->kerugian_infrastruktur + 
                       $assessment->dampakEkonomi->kerugian_lainnya;
            case 'E02': // Mata Pencaharian Hilang
                return 0; // Not gathered in UI
            case 'S01': // Sosial
            case 'S02':
                return 0;
            default:
                return 0;
        }
    }

    private function calculateScale1To5(string $kode, float $val): int
    {
        switch ($kode) {
            case 'M01':
                if ($val >= 100) return 5;
                if ($val >= 20) return 4;
                if ($val >= 5) return 3;
                if ($val > 0) return 2;
                return 1;
            case 'M02':
                if ($val >= 5000) return 5;
                if ($val >= 500) return 4;
                if ($val >= 50) return 3;
                if ($val > 0) return 2;
                return 1;
            case 'I01':
                if ($val >= 500) return 5;
                if ($val >= 100) return 4;
                if ($val >= 10) return 3;
                if ($val > 0) return 2;
                return 1;
            case 'I02':
                if ($val >= 50) return 5;
                if ($val >= 10) return 4;
                if ($val >= 1) return 3;
                if ($val > 0) return 2;
                return 1;
            case 'I03':
                if ($val >= 20) return 5;
                if ($val >= 5) return 4;
                if ($val >= 1) return 3;
                return 1;
            case 'L01':
                if ($val >= 500) return 5;
                if ($val >= 50) return 4;
                if ($val >= 5) return 3;
                if ($val > 0) return 2;
                return 1;
            case 'L02':
                if ($val >= 10) return 5;
                if ($val >= 1) return 3;
                if ($val > 0) return 2;
                return 1;
            case 'E01':
                // Values are in Rp actual. 10B = 10,000,000,000
                if ($val >= 10_000_000_000) return 5;
                if ($val >= 1_000_000_000) return 4;
                if ($val >= 100_000_000) return 3;
                if ($val > 0) return 2;
                return 1;
            default:
                return 1; // Base score
        }
    }
}
