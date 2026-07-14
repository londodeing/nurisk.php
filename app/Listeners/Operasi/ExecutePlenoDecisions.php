<?php

namespace App\Listeners\Operasi;

use App\Events\Operasi\PlenoFinalized;
use App\Models\AssessmentUtama;
use App\Models\AuthUser;
use App\Models\MasterSuratJenis;
use App\Models\OperasiInsiden;
use App\Models\OperasiKlaster;
use App\Models\OperasiPenugasan;
use App\Models\OperasiPosaju;
use App\Models\OperasiPosajuKomandan;
use App\Services\NomorSuratService;
use App\Services\SuratPdfService;
use App\Services\SuratService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ExecutePlenoDecisions
{
    private SuratService $suratService;
    private SuratPdfService $pdfService;

    public function __construct(SuratService $suratService, SuratPdfService $pdfService)
    {
        $this->suratService = $suratService;
        $this->pdfService = $pdfService;
    }

    public function handle(PlenoFinalized $event): void
    {
        $pleno = $event->pleno;
        $pleno->load('keputusan');
        Log::info("Executing decisions for Pleno ID: {$pleno->id_pleno}");

        foreach ($pleno->keputusan as $keputusan) {
            if (in_array($keputusan->status_pelaksanaan, ['selesai', 'dieksekusi'])) {
                continue;
            }

            try {
                DB::transaction(function () use ($pleno, $keputusan) {
                    $kategori = $keputusan->kategori_objek;

                    if ($kategori === 'aktivasi_posko') {
                        $this->executeAktivasiPosko($pleno, $keputusan);
                    } elseif ($kategori === 'aktivasi_klaster') {
                        $this->executeAktivasiKlaster($pleno, $keputusan);
                    }

                    if ($kategori === 'logistik') {
                        $this->executeLogistik($pleno, $keputusan);
                    }

                    $keputusan->update(['status_pelaksanaan' => 'selesai']);
                });
            } catch (\Exception $e) {
                Log::error("Failed to execute keputusan {$keputusan->id_keputusan}: " . $e->getMessage());
            }
        }
    }

    private function executeAktivasiPosko($pleno, $keputusan)
    {
        $payload = $keputusan->payload_eksekusi ?? [];

        if (!empty($payload['id_koordinator'])) {
            $this->validasiKoordinatorScope($pleno->id_insiden, $payload['id_koordinator']);
        }

        $posAju = OperasiPosaju::create([
            'id_insiden' => $pleno->id_insiden,
            'id_pleno_pendirian' => $pleno->id_pleno,
            'id_pleno_keputusan' => $keputusan->id_keputusan,
            'nama_posaju' => $payload['nama_posaju'] ?? 'Pos Aju Utama',
            'alamat_lokasi' => $payload['lokasi_posaju'] ?? $pleno->lokasi_pleno,
            'pj_posaju' => $payload['id_koordinator'] ?? null,
            'status_alur' => 'aktif',
            'waktu_diaktifkan' => now(),
        ]);

        $keputusan->update([
            'referensi_tabel' => 'operasi_posaju',
            'referensi_id' => $posAju->id_posaju,
        ]);

        if (!empty($payload['id_koordinator'])) {
            OperasiPosajuKomandan::create([
                'id_posaju' => $posAju->id_posaju,
                'id_pengguna' => $payload['id_koordinator'],
                'id_pleno_keputusan' => $keputusan->id_keputusan,
                'waktu_mulai_tugas' => now(),
            ]);

            $penugasan = OperasiPenugasan::create([
                'id_insiden' => $pleno->id_insiden,
                'id_pengguna' => $payload['id_koordinator'],
                'peran_otoritas' => 'koordinator_pos',
                'status_penugasan' => 'draft',
                'waktu_mulai' => now(),
                'ditugaskan_oleh' => $pleno->disetujui_oleh,
                'catatan' => 'Auto-assigned by Pleno ID: ' . $pleno->id_pleno . ' | Pos Aju: ' . $posAju->nama_posaju,
            ]);

            $this->buatSuratTugasUntukPenugasan($penugasan, $pleno);
        }
    }

    private function executeAktivasiKlaster($pleno, $keputusan)
    {
        $payload = $keputusan->payload_eksekusi ?? [];
        $jenisKlasterArray = $payload['jenis_klaster'] ?? [];
        $idKoordinator = $payload['id_koordinator'] ?? null;

        if ($idKoordinator) {
            $this->validasiKoordinatorScope($pleno->id_insiden, $idKoordinator);
        }

        foreach ($jenisKlasterArray as $idMasterKlaster) {
            $existing = OperasiKlaster::where('id_insiden', $pleno->id_insiden)
                ->where('id_master_klaster', $idMasterKlaster)
                ->whereNull('waktu_ditutup')
                ->first();

            if (!$existing) {
                $klaster = OperasiKlaster::create([
                    'id_insiden' => $pleno->id_insiden,
                    'id_master_klaster' => $idMasterKlaster,
                    'status_klaster' => 'aktif',
                    'waktu_aktivasi' => now(),
                    'id_pembuat' => $pleno->disetujui_oleh,
                    'dibutuhkan' => true,
                    'catatan' => 'Activated by Pleno ID: ' . $pleno->id_pleno,
                ]);

                if ($idKoordinator) {
                    $penugasan = OperasiPenugasan::create([
                        'id_insiden' => $pleno->id_insiden,
                        'id_pengguna' => $idKoordinator,
                        'id_klaster_operasi' => $klaster->id_klaster_operasi,
                        'peran_otoritas' => 'koordinator_klaster',
                        'status_penugasan' => 'draft',
                        'waktu_mulai' => now(),
                        'ditugaskan_oleh' => $pleno->disetujui_oleh,
                        'catatan' => 'Auto-assigned by Pleno ID: ' . $pleno->id_pleno . ' | Klaster ID: ' . $idMasterKlaster,
                    ]);

                    $this->buatSuratTugasUntukPenugasan($penugasan, $pleno);
                }
            }
        }
    }

    private function executeLogistik($pleno, $keputusan)
    {
        $payload = $keputusan->payload_eksekusi ?? [];
        $masterKlasterLogistik = \App\Models\MasterKlaster::where('nama_klaster', 'like', '%logistik%')->first();

        if ($masterKlasterLogistik) {
            $existing = OperasiKlaster::where('id_insiden', $pleno->id_insiden)
                ->where('id_master_klaster', $masterKlasterLogistik->id_master_klaster)
                ->whereNull('waktu_ditutup')
                ->first();

            if (!$existing) {
                OperasiKlaster::create([
                    'id_insiden' => $pleno->id_insiden,
                    'id_master_klaster' => $masterKlasterLogistik->id_master_klaster,
                    'status_klaster' => 'aktif',
                    'waktu_aktivasi' => now(),
                    'id_pembuat' => $pleno->disetujui_oleh,
                    'dibutuhkan' => true,
                    'catatan' => 'Auto-activated by Pleno logistik decision ID: ' . $keputusan->id_keputusan,
                ]);
            }
        }
    }

    private function buatSuratTugasUntukPenugasan($penugasan, $pleno): void
    {
        $insiden = OperasiInsiden::find($pleno->id_insiden);
        if (!$insiden) return;

        $jenisSurat = MasterSuratJenis::where('kode_jenis', 'ST')
            ->orWhere('nama_jenis', 'like', '%tugas%')
            ->first();
        if (!$jenisSurat) return;

        $nomorSurat = app(NomorSuratService::class)->generate($jenisSurat, now()->year, $insiden);

        $penerima = AuthUser::find($penugasan->id_pengguna);
        $namaPenerima = $penerima?->profil?->nama_lengkap ?? $penerima?->no_hp ?? 'Personel';
        $peranLabel = str_replace('_', ' ', $penugasan->peran_otoritas);

        $isiSnapshot = "Memberikan tugas kepada:\n"
                     . "Nama: " . $namaPenerima . "\n"
                     . "Peran: " . ucfirst($peranLabel) . "\n\n"
                     . "Untuk melaksanakan tugas operasi atas insiden " . $insiden->kode_kejadian
                     . " di wilayah PCNU " . optional($insiden->pcnu)->nama_pcnu . ".\n\n"
                     . "Rincian Tugas:\n"
                     . ($penugasan->catatan ?? '-') . "\n\n"
                     . "Laporan assessment situasi darurat terlampir dalam dokumen ini untuk bahan review.";

        $surat = \App\Models\DokumenSuratUtama::create([
            'id_insiden' => $insiden->id_insiden,
            'id_jenis_surat' => $jenisSurat->id_jenis_surat,
            'nomor_surat_resmi' => $nomorSurat,
            'perihal' => 'Surat Tugas Operasi — ' . $insiden->kode_kejadian,
            'tgl_terbit' => now(),
            'id_pengguna_ttd' => $pleno->disetujui_oleh,
            'status_surat' => 'siap_tanda_tangan',
            'isi_surat_snapshot' => $isiSnapshot,
        ]);

        $assessment = AssessmentUtama::where('id_insiden', $insiden->id_insiden)
            ->where('is_latest', true)
            ->first();

        try {
            $pdfPath = $this->pdfService->generateWithLampiran($surat, $assessment);

            $surat->update([
                'isi_surat_snapshot' => $isiSnapshot,
                'status_surat' => 'ditandatangani',
                'file_pdf_path' => $pdfPath,
            ]);

            $penugasan->update(['id_surat_tugas' => $surat->id_surat]);
        } catch (\Exception $e) {
            Log::warning("Gagal generate PDF Surat Tugas: " . $e->getMessage());
        }
    }

    private function validasiKoordinatorScope(int $idInsiden, int $idPengguna): void
    {
        $insiden = OperasiInsiden::findOrFail($idInsiden);
        $user = AuthUser::find($idPengguna);

        if (!$user) {
            throw new \Exception("User koordinator tidak ditemukan.");
        }

        if ($user->default_scope_type === 'pcnu' && $user->default_scope_id !== $insiden->id_pcnu) {
            throw new \Exception("Koordinator harus berasal dari PCNU setempat.");
        }
    }
}
