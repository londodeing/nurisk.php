<?php

namespace App\Services;

use App\Models\OperasiInsiden;
use App\Models\RiwayatStatusInsiden;
use App\Models\AuthUser;
use App\Services\Auth\AuthorizationContextService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class InsidenService
{
    /**
     * Buat insiden baru.
     * Generate kode_kejadian jika tidak disediakan.
     */
    public function buatInsiden(array $data): OperasiInsiden
    {
        if (empty($data['kode_kejadian'])) {
            if (!empty($data['id_laporan_asal'])) {
                $laporanAsal = \App\Models\LaporanKejadian::find($data['id_laporan_asal']);
                if ($laporanAsal) {
                    $data['kode_kejadian'] = $laporanAsal->kode_kejadian;
                }
            }
            
            if (empty($data['kode_kejadian'])) {
                $data['kode_kejadian'] = $this->generateKodeKejadian((int) ($data['id_jenis_bencana'] ?? 0), $data['id_pcnu'] ?? null);
            }
        }
        if (empty($data['waktu_mulai'])) {
            $data['waktu_mulai'] = now();
        }
        return OperasiInsiden::create($data);
    }

    /**
     * Update data insiden yang tidak terkunci.
     * Mereplikasi logic trigger tr_validate_temporal_incident di Laravel level.
     *
     * @throws \RuntimeException jika insiden terkunci
     * @throws \InvalidArgumentException jika waktu_selesai < waktu_mulai
     */
    public function updateInsiden(OperasiInsiden $insiden, array $data): OperasiInsiden
    {
        if ($insiden->isTerkunci()) {
            throw new \RuntimeException('Data Terkunci: Insiden ini sudah Closed dan tidak boleh diubah lagi.');
        }

        // Replikasi trigger temporal di Laravel level (untuk SQLite compatibility)
        $mulai = isset($data['waktu_mulai']) ? $data['waktu_mulai'] : $insiden->waktu_mulai;
        $selesai = isset($data['waktu_selesai']) ? $data['waktu_selesai'] : $insiden->waktu_selesai;

        if ($selesai !== null) {
            $mulaiTime = is_string($mulai) ? new \DateTime($mulai) : $mulai;
            $selesaiTime = is_string($selesai) ? new \DateTime($selesai) : $selesai;

            if ($selesaiTime < $mulaiTime) {
                throw new \InvalidArgumentException('Temporal Error: Waktu selesai tidak boleh sebelum waktu mulai!');
            }
        }

        $insiden->update($data);
        return $insiden->fresh();
    }

    /**
     * Update data SPK insiden.
     * Validasi: pemberi_spk harus memiliki jabatan aktif sebagai
     * Ketua/Koordinator/Komandan di organisasi yang relevan.
     *
     * @throws \InvalidArgumentException jika pemberi tidak punya jabatan aktif
     */
    public function updateSpk(
        OperasiInsiden $insiden,
        array $spkData,
        AuthUser $aktor
    ): OperasiInsiden {
        if (isset($spkData['id_pemberi_spk'])) {
            $this->validasiOtoritasSpk($spkData['id_pemberi_spk'], $insiden);
        }

        $insiden->update(array_intersect_key($spkData, array_flip([
            'no_spk_assesment',
            'tgl_spk_assesment',
            'id_pemberi_spk',
            'id_penerima_spk',
        ])));

        return $insiden->fresh();
    }

    /**
     * Validasi bahwa user yang memberi SPK punya jabatan aktif
     * yang relevan di organisasi insiden terkait.
     *
     * @throws \InvalidArgumentException jika tidak valid
     */
    private function validasiOtoritasSpk(int $idPemberi, OperasiInsiden $insiden): void
    {
        $jabatanBerwenang = [
            'ketua-pwnu',
            'ketua-pcnu',
            'koordinator-trc-pwnu',
            'koordinator-trc-pcnu',
            'komandan-pos-aju',
        ];

        $punyaJabatan = \App\Models\PenggunaJabatan::where('id_pengguna', $idPemberi)
            ->where('status_aktif', 1)
            ->where(function ($q) {
                $q->whereNull('berakhir_pada')
                  ->orWhereDate('berakhir_pada', '>=', now());
            })
            ->whereHas('jabatan', fn($q) => $q->whereIn('slug', $jabatanBerwenang))
            ->exists();

        if (!$punyaJabatan) {
            throw new \InvalidArgumentException(
                'Pemberi SPK harus memiliki jabatan aktif sebagai Ketua, Koordinator TRC, atau Komandan. '.
                'Pastikan jabatan pengguna sudah terdaftar dan masih aktif di sistem.'
            );
        }
    }

    private const ALLOWED_TRANSITIONS = [
        'draft'        => ['terverifikasi', 'dibatalkan'],
        'terverifikasi'=> ['respon', 'dibatalkan'],
        'respon'       => ['pemulihan', 'dibatalkan'],
        'pemulihan'    => ['selesai', 'dibatalkan'],
        'selesai'      => [],
        'dibatalkan'   => [],
    ];

    /**
     * Transisi status insiden beserta pencatatan riwayat.
     * Berjalan di dalam DB::transaction().
     * Mengunci insiden ketika status = 'selesai'.
     */
    public function ubahStatus(
        OperasiInsiden $insiden,
        string $statusBaru,
        AuthUser $pengguna,
        ?string $alasan = null
    ): OperasiInsiden {
        if ($insiden->isTerkunci()) {
            throw new \RuntimeException('Data Terkunci: Insiden ini sudah Closed dan tidak boleh diubah lagi.');
        }

        $statusLama = $insiden->status_insiden;

        if (!$this->isTransitionAllowed($statusLama, $statusBaru)) {
            throw new \InvalidArgumentException(
                "Transisi status tidak diizinkan: {$statusLama} → {$statusBaru}."
            );
        }

        return DB::transaction(function () use ($insiden, $statusBaru, $pengguna, $alasan) {
            $statusLama = $insiden->status_insiden;

            // Catat riwayat transisi status
            RiwayatStatusInsiden::create([
                'id_insiden'        => $insiden->id_insiden,
                'status_sebelumnya' => $statusLama,
                'status_terbaru'    => $statusBaru,
                'id_pengguna'       => $pengguna->id_pengguna,
                'alasan'            => $alasan,
            ]);

            // Tentukan field waktu yang harus diperbarui
            $updateData = ['status_insiden' => $statusBaru];

            if ($statusBaru === 'terverifikasi' && $statusLama === 'draft') {
                $updateData['waktu_verifikasi'] = now();
            } elseif ($statusBaru === 'respon') {
                $updateData['waktu_respon_dimulai'] = now();
            } elseif ($statusBaru === 'pemulihan') {
                $updateData['waktu_pemulihan_dimulai'] = now();
            } elseif ($statusBaru === 'selesai') {
                $updateData['waktu_ditutup']    = now();
                $updateData['waktu_selesai']    = now();
                $updateData['is_locked']        = true;
                $updateData['status_operasi']   = 'selesai';
            } elseif ($statusBaru === 'dibatalkan') {
                $updateData['waktu_ditutup']    = now();
                $updateData['is_locked']        = true;
            }

            $insiden->update($updateData);
            return $insiden->fresh();
        });
    }

    private function isTransitionAllowed(string $from, string $to): bool
    {
        return in_array($to, self::ALLOWED_TRANSITIONS[$from] ?? [], true);
    }

    /**
     * Soft delete insiden (hanya super_admin).
     */
    public function hapusInsiden(OperasiInsiden $insiden): bool
    {
        return (bool) $insiden->delete();
    }

    /**
     * Generate kode kejadian otomatis.
     * Format: INS-[TAHUN][BULAN]-[ID Jenis 2 digit]-[5 digit random]
     * Contoh: INS-2606-01-00123
     */
    private function generateKodeKejadian(int $idJenisBencana, ?int $idPcnu = null): string
    {
        $prefixStr = 'INS';
        if ($idPcnu) {
            $pcnu = \App\Models\OrganisasiPcnu::find($idPcnu);
            if ($pcnu && $pcnu->kode_sni) {
                $prefixStr = $pcnu->kode_sni;
            }
        }

        $prefix = $prefixStr . '-' . now()->format('ymd');

        return DB::transaction(function () use ($prefix) {
            $last = OperasiInsiden::where('kode_kejadian', 'like', $prefix . '-%')
                ->lockForUpdate()
                ->orderBy('kode_kejadian', 'desc')
                ->value('kode_kejadian');

            $next = 1;
            if ($last) {
                $parts = explode('-', $last);
                $next = (int) end($parts) + 1;
            }

            return $prefix . '-' . str_pad($next, 3, '0', STR_PAD_LEFT);
        });
    }

    /**
     * Filter insiden berdasarkan scope pengguna login.
     */
    public function queryByScope(): Builder
    {
        $ctx = app(AuthorizationContextService::class);
        $query = OperasiInsiden::query();

        if ($ctx->isSuperAdmin() || $ctx->hasRole('pwnu')) {
            return $query; // Akses semua insiden
        }

        if ($ctx->hasRole('pcnu') && $ctx->getScopeId()) {
            return $query->byPcnu($ctx->getScopeId());
        }

        // Default: return empty query (tidak ada akses)
        return $query->whereRaw('1 = 0');
    }
}
