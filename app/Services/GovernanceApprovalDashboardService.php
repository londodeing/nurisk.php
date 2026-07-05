<?php

namespace App\Services;

use App\Models\AuthUser;
use App\Models\DokumenSuratParaf;
use App\Models\DokumenSuratUtama;
use App\Models\OperasiPleno;

class GovernanceApprovalDashboardService
{
    /**
     * Kumpulkan semua item menunggu persetujuan dari user yang login.
     *
     * @param AuthUser $user
     * @return array
     */
    public function ringkasanApproval(AuthUser $user): array
    {
        $ctx = app(\App\Services\Auth\AuthorizationContextService::class);

        // 1. Paraf yang menunggu user ini
        $parafMenunggu = DokumenSuratParaf::with(['surat.jenisSurat', 'surat.insiden'])
            ->where('id_pengguna', $user->id_pengguna)
            ->where('status_paraf', 'menunggu')
            ->whereHas('surat', fn($q) => $q->whereNull('dihapus_pada'))
            ->orderBy('id_paraf')  // urutan masuk
            ->limit(20)
            ->get();

        // 2. Pleno yang menunggu finalisasi (status: 'ditinjau')
        // Hanya super_admin dan pwnu yang bisa finalisasi — lihat PlanoPolicy
        $plenoMenunggu = collect();
        if ($ctx->hasAnyRole(['super_admin', 'pwnu'])) {
            $plenoMenunggu = OperasiPleno::with(['insiden', 'pimpinan.profil', 'peserta'])
                ->where('status_pleno', 'ditinjau')
                ->whereNull('dihapus_pada')
                ->latest('dibuat_pada')
                ->limit(20)
                ->get();
        }

        // 3. Surat menunggu tanda tangan (user sebagai id_pengguna_ttd)
        $suratMenungguTtd = DokumenSuratUtama::with(['jenisSurat', 'insiden', 'paraf'])
            ->where('id_pengguna_ttd', $user->id_pengguna)
            ->where('status_surat', 'siap_tanda_tangan')
            ->whereNull('dihapus_pada')
            ->latest('dibuat_pada')
            ->limit(10)
            ->get();

        // 4. Riwayat approval hari ini (paraf + surat finalisasi oleh user ini)
        $riwayatHariIni = DokumenSuratParaf::with(['surat'])
            ->where('id_pengguna', $user->id_pengguna)
            ->whereNotNull('waktu_paraf')
            ->whereDate('waktu_paraf', today())
            ->orderByDesc('waktu_paraf')
            ->limit(10)
            ->get()
            ->map(fn($p) => [
                'waktu'   => $p->waktu_paraf,
                'label'   => 'Paraf ' . $p->status_paraf . ': ' . ($p->surat->nomor_surat_resmi ?? ''),
                'status'  => $p->status_paraf,
                'url'     => route('surat.show', $p->id_surat ?? 0),
            ]);

        $totalPending = $parafMenunggu->count()
                      + $plenoMenunggu->count()
                      + $suratMenungguTtd->count();

        return compact(
            'parafMenunggu',
            'plenoMenunggu',
            'suratMenungguTtd',
            'riwayatHariIni',
            'totalPending',
        );
    }
}
