<?php

namespace App\Services;

use App\Jobs\GenerateSuratPdfJob;
use App\Models\AuthUser;
use App\Models\DokumenSuratParaf;
use App\Models\DokumenSuratUtama;
use App\Models\MasterSuratJenis;
use App\Models\OperasiInsiden;
use App\Models\OperasiJurnal;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SuratService
{
    public function __construct(
        private NomorSuratService $nomorService,
        private SuratPdfService $pdfService
    ) {}

    public function buatSurat(array $data): DokumenSuratUtama
    {
        $jenis = MasterSuratJenis::findOrFail($data['id_jenis_surat']);
        $insiden = isset($data['id_insiden']) ? OperasiInsiden::find($data['id_insiden']) : null;

        $data['nomor_surat_resmi'] = $this->nomorService->generate(
            $jenis,
            now()->year,
            $insiden
        );

        return DB::transaction(fn() => DokumenSuratUtama::create($data));
    }

    public function tambahParaf(DokumenSuratUtama $surat, int $idPengguna, int $urutan): DokumenSuratParaf
    {
        if (!$surat->isDraft()) {
            throw new \RuntimeException('Paraf hanya bisa ditambahkan saat surat berstatus draft.');
        }

        return $surat->paraf()->create([
            'id_pengguna' => $idPengguna,
            'urutan' => $urutan,
            'status_paraf' => 'menunggu',
        ]);
    }

    public function kirimKeReview(DokumenSuratUtama $surat): DokumenSuratUtama
    {
        if (!$surat->isDraft()) {
            throw new \RuntimeException('Hanya surat berstatus draft yang bisa dikirim ke review.');
        }
        if ($surat->paraf()->count() === 0) {
            throw new \RuntimeException('Surat wajib memiliki minimal 1 paraf sebelum dikirim ke review.');
        }

        $surat->update(['status_surat' => 'review_paraf']);
        return $surat->fresh();
    }

    public function prosesParaf(
        DokumenSuratUtama $surat,
        DokumenSuratParaf $parafRecord,
        string $statusParaf,
        ?string $catatan,
        AuthUser $aktor
    ): void {
        if ($aktor->id_pengguna !== $parafRecord->id_pengguna) {
            throw new \RuntimeException('Anda tidak berwenang memproses paraf ini.');
        }
        if ($parafRecord->status_paraf !== 'menunggu') {
            throw new \RuntimeException('Paraf ini sudah diproses sebelumnya.');
        }

        $previousUnapproved = $surat->paraf()
            ->where('urutan', '<', $parafRecord->urutan)
            ->where('status_paraf', '!=', 'disetujui')
            ->exists();

        if ($previousUnapproved) {
            throw new \RuntimeException('Paraf sebelumnya belum disetujui.');
        }

        DB::transaction(function () use ($surat, $parafRecord, $statusParaf, $catatan, $aktor) {
            $statusSebelum = $parafRecord->status_paraf;

            $parafRecord->update([
                'status_paraf' => $statusParaf,
                'catatan' => $catatan,
                'waktu_paraf' => now(),
                'approval_user_status' => $aktor->status_akun,
                'approval_role_snapshot' => optional($aktor->peran)->nama_peran ?? 'unknown',
            ]);

            $statusSesudah = $statusParaf;

            if ($statusParaf === 'ditolak') {
                $surat->update(['status_surat' => 'draft']);
                $surat->paraf()
                    ->where('urutan', '>', $parafRecord->urutan)
                    ->update(['status_paraf' => 'menunggu', 'catatan' => null, 'waktu_paraf' => null]);
            } elseif ($statusParaf === 'disetujui') {
                $parafBerikutnya = $surat->paraf()
                    ->where('urutan', '>', $parafRecord->urutan)
                    ->where('status_paraf', 'menunggu')
                    ->orderBy('urutan')
                    ->first();

                if (!$parafBerikutnya) {
                    $surat->update(['status_surat' => 'siap_tanda_tangan']);
                }
            }

            if (Schema::hasTable('operasi_jurnal')) {
                OperasiJurnal::create([
                    'id_insiden' => $surat->id_insiden,
                    'id_pengguna' => $aktor->id_pengguna,
                    'kategori_event' => 'aktivasi',
                    'judul_event' => 'Paraf ' . ($statusParaf === 'disetujui' ? 'disetujui' : 'ditolak'),
                    'deskripsi_event' => 'Surat: ' . $surat->nomor_surat_resmi . ' | Paraf urutan ' . $parafRecord->urutan . ' | Status: ' . $statusSebelum . ' → ' . $statusSesudah,
                    'id_referensi' => $surat->id_surat,
                    'tabel_referensi' => 'operasi_surat_keluar',
                ]);
            }
        });
    }

    public function finalisasi(DokumenSuratUtama $surat, AuthUser $aktor, ?string $isiSnapshot = null): DokumenSuratUtama
    {
        if ($surat->status_surat !== 'siap_tanda_tangan') {
            throw new \RuntimeException(
                'Surat wajib berstatus "siap_tanda_tangan" sebelum dapat difinalisasi. Status saat ini: ' . $surat->status_surat
            );
        }

        $snapshot = $isiSnapshot ?? 'Snapshot tidak tersedia.';

        $surat->update([
            'status_surat' => 'ditandatangani',
            'isi_surat_snapshot' => $snapshot,
        ]);

        GenerateSuratPdfJob::dispatch($surat->id_surat, $snapshot);

        return $surat->fresh();
    }
}
