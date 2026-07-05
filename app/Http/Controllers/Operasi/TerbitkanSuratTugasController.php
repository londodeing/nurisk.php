<?php

namespace App\Http\Controllers\Operasi;

use App\Http\Controllers\Controller;
use App\Models\AssessmentUtama;
use App\Models\MasterSuratJenis;
use App\Models\OperasiInsiden;
use App\Models\OperasiPenugasan;
use App\Services\SuratPdfService;
use App\Services\SuratService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TerbitkanSuratTugasController extends Controller
{
    public function __construct(
        private SuratService $suratService,
        private SuratPdfService $pdfService,
    ) {}

    public function store(Request $request, OperasiInsiden $insiden, OperasiPenugasan $penugasan)
    {
        $this->authorize('update', $penugasan);

        if ($penugasan->status_penugasan !== 'draft') {
            return back()->with('error', 'Hanya penugasan draft yang dapat diterbitkan surat tugasnya.');
        }

        DB::beginTransaction();
        try {
            $jenisSurat = MasterSuratJenis::where('kode_jenis', 'ST')
                ->orWhere('nama_jenis', 'like', '%tugas%')
                ->first();

            if (!$jenisSurat) {
                throw new \Exception('Jenis Surat "Surat Tugas" belum tersedia di sistem.');
            }

            $penerima = $penugasan->pengguna;
            $namaPenerima = $penerima?->profil?->nama_lengkap ?? $penerima?->no_hp ?? 'Anggota TRC';
            $peranLabel = str_replace('_', ' ', $penugasan->peran_otoritas);

            $isiSnapshot = "Memberikan tugas kepada:\n"
                         . "Nama: " . $namaPenerima . "\n"
                         . "Peran: " . ucfirst($peranLabel) . "\n\n"
                         . "Untuk melaksanakan tugas operasi atas insiden " . $insiden->kode_kejadian
                         . " di wilayah PCNU " . optional($insiden->pcnu)->nama_pcnu . ".\n\n"
                         . "Rincian Tugas:\n"
                         . $penugasan->catatan . "\n\n"
                         . "Laporan assessment situasi darurat terlampir dalam dokumen ini.";

            $surat = $this->suratService->buatSurat([
                'id_insiden'        => $insiden->id_insiden,
                'id_jenis_surat'    => $jenisSurat->id_jenis_surat,
                'perihal'           => 'Surat Tugas Operasi — ' . $insiden->kode_kejadian,
                'tgl_terbit'        => now(),
                'id_pengguna_ttd'   => Auth::id(),
                'status_surat'      => 'siap_tanda_tangan',
                'isi_surat_snapshot' => $isiSnapshot,
            ]);

            $assessment = AssessmentUtama::where('id_insiden', $insiden->id_insiden)
                ->where('is_latest', true)
                ->first();

            $pdfPath = $this->pdfService->generateWithLampiran($surat, $assessment);

            $surat->update([
                'isi_surat_snapshot' => $isiSnapshot,
                'status_surat' => 'ditandatangani',
                'file_pdf_path' => $pdfPath,
            ]);

            $penugasan->update([
                'id_surat_tugas' => $surat->id_surat,
                'status_penugasan' => 'assigned',
            ]);

            DB::commit();

            return back()->with('success', 'Surat Tugas berhasil diterbitkan. Nomor: ' . $surat->nomor_surat_resmi);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menerbitkan Surat Tugas: ' . $e->getMessage());
        }
    }
}
