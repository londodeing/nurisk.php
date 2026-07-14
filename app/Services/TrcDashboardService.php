<?php

namespace App\Services;

use App\Models\AssessmentUtama;
use App\Models\OperasiPenugasan;
use App\Models\OperasiInsiden;
use App\Models\OperasiSitrep;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class TrcDashboardService
{
    /**
     * Ambil data penugasan aktif berikut semua field yang dibutuhkan TRC.
     */
    public function getAssignmentData(): ?array
    {
        $user = Auth::user();
        if (!$user) return null;

        $penugasan = OperasiPenugasan::with(['suratTugas', 'insiden.laporanAsal'])
            ->where('id_pengguna', $user->id_pengguna)
            ->whereIn('status_penugasan', ['assigned', 'ditugaskan', 'aktif', 'on_route', 'on_site'])
            ->latest('dibuat_pada')
            ->first();

        if (!$penugasan) return null;

        $insiden = $penugasan->insiden;
        $laporan = $insiden?->laporanAsal;

        $suratTugasUrl = null;
        if ($penugasan->suratTugas?->file_pdf_path) {
            $suratTugasUrl = media_url($penugasan->suratTugas->file_pdf_path);
        }

        return [
            'id_penugasan'      => $penugasan->id_penugasan,
            'id_insiden'        => $insiden?->id_insiden,
            'title'             => $insiden ? 'Insiden: ' . $insiden->kode_kejadian : 'Penugasan Darurat',
            'priority'          => strtoupper($insiden?->prioritas ?? 'SEDANG'),
            'status'            => strtolower($penugasan->status_penugasan),
            'nomor_spk'         => $insiden?->no_spk_assesment ?? '-',
            'alamat'            => $laporan?->alamat_lengkap ?? '-',
            'deskripsi_laporan' => $laporan?->keterangan_situasi ?? null,
            'latitude'          => $laporan?->latitude,
            'longitude'         => $laporan?->longitude,
            'nama_pelapor'      => $laporan?->nama_pelapor,
            'hp_pelapor'        => $laporan?->hp_pelapor,
            'nomor_surat_tugas' => $penugasan->suratTugas?->nomor_surat_resmi,
            'surat_tugas_url'   => $suratTugasUrl,
        ];
    }

    /**
     * Ambil riwayat assessment beserta nomor sitrep (jika sudah dibuat).
     */
    public function getAssessmentHistory(?int $idInsiden): array
    {
        if (!$idInsiden) return [];

        $assessments = AssessmentUtama::where('id_insiden', $idInsiden)
            ->orderBy('waktu_assesment', 'desc')
            ->get();

        return $assessments->map(function ($ass) {
            // Cari sitrep yang dibuat berdasarkan assessment ini
            $sitrep = OperasiSitrep::where('id_assessment_basis', $ass->id_assessment_utama)->first();

            return [
                'id'               => $ass->id_assessment_utama,
                'jenis_laporan'    => $ass->jenis_laporan === 'kaji_cepat' ? 'Kaji Cepat' : 'Pendataan Lanjutan',
                'cakupan'          => $ass->cakupan_wilayah_deskripsi,
                'waktu_assessment' => $ass->waktu_assesment?->format('d/m/Y H:i'),
                'is_latest'        => (bool) $ass->is_latest,
                'sitrep_nomor'     => $sitrep?->nomor_sitrep,
            ];
        })->toArray();
    }

    /**
     * Decision queue — tugas mendesak yang harus dikerjakan TRC.
     */
    public function getDecisionQueue(): array
    {
        $queue  = [];
        $user   = Auth::user();
        if (!$user) return [];

        $penugasan = OperasiPenugasan::where('id_pengguna', $user->id_pengguna)
            ->whereIn('status_penugasan', ['on_route', 'on_site', 'aktif'])
            ->latest('dibuat_pada')
            ->first();

        if ($penugasan) {
            $hasAssessment = AssessmentUtama::where('id_insiden', $penugasan->id_insiden)->exists();
            if (!$hasAssessment) {
                $queue[] = ['priority' => 'critical', 'title' => 'Assessment awal belum dikirim', 'action_url' => '#assessment-modal'];
            }
        }

        return collect($queue)->sortBy(fn($i) => ['critical' => 1, 'high' => 2, 'medium' => 3][$i['priority']] ?? 4)
            ->take(5)->values()->toArray();
    }

    /**
     * Kontak darurat.
     */
    public function getEmergencyContacts(): array
    {
        return [
            ['role' => 'Hotline Darurat BPBD', 'name' => 'BPBD (Nasional)', 'phone' => '112'],
        ];
    }

    /**
     * Main polling data — ringan, semua data gabungan.
     */
    public function getPollingData(): array
    {
        $assignment = $this->getAssignmentData();

        return [
            'timestamp'      => now()->toIso8601String(),
            'assignment'     => $assignment,
            'assessments'    => $this->getAssessmentHistory($assignment['id_insiden'] ?? null),
            'decision_queue' => $this->getDecisionQueue(),
            'contacts'       => $this->getEmergencyContacts(),
        ];
    }
}
