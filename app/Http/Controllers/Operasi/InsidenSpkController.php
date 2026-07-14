<?php

namespace App\Http\Controllers\Operasi;

use App\Http\Controllers\Controller;
use App\Models\MasterSuratJenis;
use App\Models\OperasiInsiden;
use App\Models\OperasiPenugasan;
use App\Models\OperasiPenugasanHistory;
use App\Models\OperasiSuratKeluar;
use App\Services\InsidenService;
use App\Services\SuratService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class InsidenSpkController extends Controller
{
    public function __construct(
        private InsidenService $insidenService,
        private SuratService $suratService
    ) {}

    public function store(Request $request, OperasiInsiden $insiden)
    {
        $this->authorize('issueSpk', $insiden);

        $validated = $request->validate([
            'petugas_trc_ids' => ['required', 'array'],
            'petugas_trc_ids.*' => ['integer', 'exists:auth_users,id_pengguna'],
            'catatan_penugasan' => ['nullable', 'string', 'max:500'],
        ]);

        try {
            DB::beginTransaction();

            $jenisSurat = MasterSuratJenis::where('kode_jenis', 'ST')
                ->orWhere('nama_jenis', 'like', '%tugas%')
                ->first();

            if (!$jenisSurat) {
                throw new \Exception('Jenis Surat "Surat Tugas" belum tersedia di sistem. Silakan tambahkan di Master Data.');
            }

            $trcNames = [];
            foreach ($validated['petugas_trc_ids'] as $trcId) {
                $penerima = \App\Models\AuthUser::find($trcId);
                $trcNames[] = ($penerima->profil->nama_lengkap ?? $penerima->no_hp ?? 'Anggota TRC') . ' (' . ($penerima->peran->nama_peran ?? 'TRC') . ')';
            }
            $namaPenerimaList = implode("\n- ", $trcNames);

            $isiSnapshot = "Memberikan tugas kepada:\n- " . $namaPenerimaList . "\n\n"
                         . "Untuk melaksanakan tugas assessment atas insiden " . $insiden->kode_kejadian . " di wilayah PCNU " . optional($insiden->pcnu)->nama_pcnu . ".\n"
                         . "Catatan Tambahan: " . ($validated['catatan_penugasan'] ?: '-');

            // 1. Buat Surat Tugas menggunakan SuratService
            $surat = $this->suratService->buatSurat([
                'id_insiden' => $insiden->id_insiden,
                'id_jenis_surat' => $jenisSurat->id_jenis_surat,
                'perihal' => 'Surat Perintah Tugas Assessment (SPK)',
                'tgl_terbit' => now(),
                'id_pengguna_ttd' => Auth::id(),
                'status_surat' => 'siap_tanda_tangan', // Menunggu persetujuan Ketua PCNU (M5)
                'isi_surat_snapshot' => $isiSnapshot,
            ]);

            // 2. Update Operasi Insiden dengan detail SPK
            // Perhatikan bahwa updateSpk membutuhkan jabatan aktif untuk pemberi (jika menggunakan id_pemberi_spk).
            // Namun untuk fleksibilitas (agar tidak error jika admin belum diset jabatannya), kita langsung update model.
            $insiden->update([
                'no_spk_assesment' => $surat->nomor_surat_resmi,
                'tgl_spk_assesment' => now(),
                'id_pemberi_spk' => Auth::id(),
                'id_penerima_spk' => $validated['petugas_trc_ids'][0] ?? null,
            ]);

            // 3. Auto-create penugasan untuk semua penerima SPK dengan status 'draft'
            foreach ($validated['petugas_trc_ids'] as $trcId) {
                $alreadyAssigned = OperasiPenugasan::where('id_insiden', $insiden->id_insiden)
                    ->where('id_pengguna', $trcId)
                    ->whereNotIn('status_penugasan', ['completed', 'cancelled', 'rejected'])
                    ->exists();

                if (!$alreadyAssigned) {
                    $penugasan = OperasiPenugasan::create([
                        'uuid_penugasan'   => (string) Str::uuid(),
                        'id_insiden'       => $insiden->id_insiden,
                        'id_pengguna'      => $trcId,
                        'peran_otoritas'   => 'trc',
                        'status_penugasan' => 'draft', // Draft karena SPK belum disetujui Ketua
                        'waktu_mulai'      => now(),
                        'ditugaskan_oleh'  => Auth::id(),
                        'id_surat_tugas'   => $surat->id_surat,
                        'catatan'          => 'Auto-created dari penerbitan SPK: ' . ($validated['catatan_penugasan'] ?? ''),
                    ]);

                    OperasiPenugasanHistory::create([
                        'id_penugasan'      => $penugasan->id_penugasan,
                        'status_sebelumnya'  => null,
                        'status_baru'        => 'draft',
                        'waktu_perubahan'    => now(),
                        'diubah_oleh'        => Auth::id(),
                    ]);
                }
            }

            DB::commit();

            return back()->with('success', 'Berhasil menerbitkan SPK dan Surat Tugas dengan Nomor: ' . $surat->nomor_surat_resmi);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menerbitkan Surat Tugas: ' . $e->getMessage());
        }
    }
}
