<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\OperasiInsiden;
use App\Models\AssessmentUtama;
use App\Models\Assessment\AssessmentKebutuhanNumerik;
use Illuminate\Http\JsonResponse;

class PublicIncidentDetailController extends Controller
{
    public function show(string $id): JsonResponse
    {
        $insiden = OperasiInsiden::with([
            'jenisBencana:id_jenis,nama_bencana,slug,ikon_map',
            'pcnu:id_pcnu,nama_pcnu',
            'laporanAsal',
        ])->where('id_insiden', $id)
          ->whereNull('dihapus_pada')
          ->first();

        if (!$insiden) {
            return response()->json(['error' => 'Insiden tidak ditemukan'], 404);
        }

        $needsNumeric = [];
        $latestAssessment = AssessmentUtama::where('id_insiden', $insiden->id_insiden)
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
                    $needsNumeric[$key] = [
                        'dibutuhkan' => (int) $n->jumlah_dibutuhkan,
                        'tersedia'   => (int) $n->jumlah_tersedia,
                        'gap'        => (int) ceil($gap),
                    ];
                }
            }
        }

        $dampak = null;
        if ($latestAssessment && class_exists(\App\Models\AssessmentDampakManusia::class)) {
            $dampak = \App\Models\AssessmentDampakManusia::where('id_assessment_utama', $latestAssessment->id_assessment_utama)
                ->first();
        }

        $latestSitrep = null;
        if (class_exists(\App\Models\OperasiSitrep::class)) {
            $sitrep = \App\Models\OperasiSitrep::where('id_insiden', $insiden->id_insiden)
                ->latest('dibuat_pada')
                ->with('dampak:id_sitrep,meninggal,hilang,luka_berat,luka_ringan,mengungsi')
                ->first();
            if ($sitrep) {
                $latestSitrep = [
                    'nomor'       => $sitrep->nomor_sitrep,
                    'waktu'       => $sitrep->waktu_sitrep?->toIso8601String(),
                    'personel'    => $sitrep->jumlah_personel,
                    'klaster'     => $sitrep->jumlah_klaster_aktif,
                    'catatan'     => $sitrep->catatan,
                    'dampak'      => $sitrep->dampak ? [
                        'meninggal'   => (int) $sitrep->dampak->meninggal,
                        'hilang'      => (int) $sitrep->dampak->hilang,
                        'luka_berat'  => (int) $sitrep->dampak->luka_berat,
                        'luka_ringan' => (int) $sitrep->dampak->luka_ringan,
                        'mengungsi'   => (int) $sitrep->dampak->mengungsi,
                    ] : null,
                ];
            }
        }

        $laporan = $insiden->laporanAsal;

        return response()->json([
            'id'            => $insiden->id_insiden,
            'kode'          => $insiden->kode_kejadian,
            'status'        => $insiden->status_insiden,
            'jenis'         => $insiden->jenisBencana?->nama_bencana,
            'pcnu'          => $insiden->pcnu?->nama_pcnu,
            'waktu_mulai'   => $insiden->waktu_mulai?->toIso8601String(),
            'waktu_selesai' => $insiden->waktu_selesai?->toIso8601String(),
            'lokasi'        => [
                'lat'          => $laporan?->latitude,
                'lng'          => $laporan?->longitude,
                'alamat'       => $laporan?->alamat_lengkap,
                'titik_kenal'  => $laporan?->titik_kenal,
            ],
            'laporan'       => $laporan ? [
                'pelapor'        => $laporan->nama_pelapor,
                'hp_pelapor'     => $laporan->hp_pelapor,
                'keterangan'     => $laporan->keterangan_situasi,
                'waktu_kejadian' => $laporan->waktu_kejadian?->toIso8601String(),
            ] : null,
            'dampak'        => $dampak ? [
                'meninggal' => (int) $dampak->meninggal,
                'hilang'    => (int) $dampak->hilang,
                'luka_berat' => (int) $dampak->luka_berat,
                'luka_ringan' => (int) $dampak->luka_ringan,
                'mengungsi' => (int) $dampak->menderita_mengungsi,
            ] : ($latestSitrep['dampak'] ?? null),
            'needs_numeric' => $needsNumeric,
            'sitrep'        => $latestSitrep,
            'personel_count' => class_exists(\App\Models\OperasiPenugasan::class)
                ? \App\Models\OperasiPenugasan::where('id_insiden', $insiden->id_insiden)->aktif()->count()
                : 0,
        ]);
    }
}
