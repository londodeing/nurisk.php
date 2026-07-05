<?php

namespace App\Services\Operasi;

use App\Models\OperasiInsiden;
use App\Models\AssessmentUtama;
use App\Models\OperasiSitrep;
use App\Models\OperasiSitrepDampak;
use App\Models\OperasiSitrepKebutuhan;
use App\Services\Auth\AuthorizationContextService;
use Illuminate\Support\Facades\DB;
use Exception;

class SitrepService
{
    private AuthorizationContextService $authCtx;

    public function __construct(AuthorizationContextService $authCtx)
    {
        $this->authCtx = $authCtx;
    }

    /**
     * Generate a new Sitrep based on the latest assessment.
     * Implements snapshot logic.
     *
     * @param array $data ['id_insiden', 'periode_sitrep', 'catatan']
     * @return OperasiSitrep
     * @throws Exception
     */
    public function generateSitrep(array $data): OperasiSitrep
    {
        return DB::transaction(function () use ($data) {
            $insiden = OperasiInsiden::lockForUpdate()->findOrFail($data['id_insiden']);

            // Validasi status insiden
            if (!in_array($insiden->status_insiden, ['terverifikasi', 'respon', 'pemulihan'])) {
                throw new Exception("Insiden belum terverifikasi atau sudah selesai, tidak bisa membuat Sitrep.");
            }

            // Cari assessment terbaru (yang menjadi basis Sitrep ini)
            $assessment = AssessmentUtama::with(['dampakManusia', 'kebutuhanMendesak'])
                ->where('id_insiden', $insiden->id_insiden)
                ->where('is_latest', true)
                ->first();

            if (!$assessment) {
                throw new Exception("Belum ada assessment valid untuk insiden ini.");
            }

            // Buat Sitrep
            $user = $this->authCtx->getCurrentUser();
            if (!$user) {
                throw new Exception("User tidak terautentikasi.");
            }

            // Generate nomor urut sitrep (opsional/sederhana)
            $count = OperasiSitrep::where('id_insiden', $insiden->id_insiden)->count();
            $nomorSitrep = 'SITREP-' . $insiden->kode_kejadian . '-' . str_pad($count + 1, 2, '0', STR_PAD_LEFT);

            // Add Operational Aggregates
            $jumlahPersonel = \App\Models\OperasiPenugasan::where('id_insiden', $insiden->id_insiden)
                ->where('status_penugasan', 'aktif')
                ->count();
                
            $jumlahKlasterAktif = \App\Models\OperasiKlaster::where('id_insiden', $insiden->id_insiden)
                ->where('status_klaster', 'aktif')
                ->count();

            $sitrep = OperasiSitrep::create([
                'id_insiden' => $insiden->id_insiden,
                'id_assessment_basis' => $assessment->id_assessment_utama,
                'nomor_sitrep' => $nomorSitrep,
                'periode_sitrep' => $data['periode_sitrep'] ?? null,
                'waktu_sitrep' => now(),
                'id_pembuat' => $user->id_pengguna,
                'catatan' => $data['catatan'] ?? null,
                'jumlah_personel' => $jumlahPersonel,
                'jumlah_klaster_aktif' => $jumlahKlasterAktif,
            ]);

            // Copy Dampak Manusia (Snapshot Immutable)
            if ($assessment->dampakManusia) {
                OperasiSitrepDampak::create([
                    'id_sitrep' => $sitrep->id_sitrep,
                    'meninggal' => $assessment->dampakManusia->meninggal,
                    'hilang' => $assessment->dampakManusia->hilang,
                    'luka_berat' => $assessment->dampakManusia->luka_berat,
                    'luka_ringan' => $assessment->dampakManusia->luka_ringan,
                    'mengungsi' => $assessment->dampakManusia->menderita_mengungsi,
                ]);
            }

            // Copy Kebutuhan Mendesak (Snapshot Immutable)
            if ($assessment->kebutuhanMendesak) {
                foreach ($assessment->kebutuhanMendesak as $kebutuhan) {
                    OperasiSitrepKebutuhan::create([
                        'id_sitrep' => $sitrep->id_sitrep,
                        'nama_kebutuhan' => $kebutuhan->nama_kebutuhan,
                        'jumlah' => $kebutuhan->jumlah,
                        'satuan' => $kebutuhan->satuan,
                    ]);
                }
            }

            return $sitrep;
        });
    }
}
