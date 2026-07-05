<?php

namespace App\Services;

use App\Models\AuthUser;
use App\Models\OperasiInsiden;
use App\Models\OperasiPenugasan;
use App\Models\RelawanPenugasan;

class AssignmentContextService
{
    /**
     * Apakah user punya OTORITAS di insiden? (command staff)
     * → Cek operasi_penugasan (penugasan langsung dari PCNU/PWNU)
     */
    public function hasCommandAuthority(AuthUser $user, int $idInsiden, ?string $peran = null): bool
    {
        return OperasiPenugasan::where('id_pengguna', $user->id_pengguna)
            ->where('id_insiden', $idInsiden)
            ->where('status_penugasan', 'aktif')
            ->whereNull('waktu_selesai')
            ->whereNull('dihapus_pada')
            ->when($peran, fn($q) => $q->where('peran_otoritas', $peran))
            ->exists();
    }

    /**
     * Apakah user punya PENUGASAN LAPANGAN di insiden? (relawan turun fisik)
     * → Cek relawan_penugasan via relawan_pendaftaran
     */
    public function hasFieldAssignment(AuthUser $user, int $idInsiden, ?int $idPosaju = null): bool
    {
        return RelawanPenugasan::where('status_aktif', 1)
            ->whereNull('dihapus_pada')
            ->when($idPosaju, fn($q) => $q->where('id_posaju', $idPosaju))
            ->whereHas('pendaftaran', fn($q) => $q
                ->where('id_pengguna', $user->id_pengguna)
                ->whereIn('status_pendaftaran', ['diterima', 'ditugaskan'])
            )
            ->whereHas('penugasanInsiden', fn($q) => $q
                ->where('id_insiden', $idInsiden)
            )
            ->exists();
    }

    /**
     * Gabungan: apakah user punya akses operasional di insiden?
     * (baik sebagai command staff ATAU relawan lapangan)
     */
    public function hasAnyAssignment(AuthUser $user, int $idInsiden): bool
    {
        return $this->hasCommandAuthority($user, $idInsiden)
            || $this->hasFieldAssignment($user, $idInsiden);
    }

    /**
     * Ambil peran tertinggi user di insiden.
     */
    public function peranTertinggi(AuthUser $user, int $idInsiden): ?string
    {
        $hierarki = ['komandan_insiden', 'trc', 'medis', 'logistik', 'operator', 'relawan'];

        $penugasan = OperasiPenugasan::where('id_pengguna', $user->id_pengguna)
            ->where('id_insiden', $idInsiden)
            ->where('status_penugasan', 'aktif')
            ->whereNull('waktu_selesai')
            ->whereNull('dihapus_pada')
            ->pluck('peran_otoritas');

        foreach ($hierarki as $peran) {
            if ($penugasan->contains($peran)) return $peran;
        }

        if ($this->hasFieldAssignment($user, $idInsiden)) {
            return 'relawan_lapangan';
        }

        return null;
    }

    /**
     * Daftar semua personel di insiden — command + lapangan.
     */
    public function semuaPersonelInsiden(int $idInsiden): array
    {
        $commandStaff = OperasiPenugasan::where('id_insiden', $idInsiden)
            ->where('status_penugasan', 'aktif')
            ->whereNull('waktu_selesai')
            ->whereNull('dihapus_pada')
            ->with('pengguna.profil')
            ->get()
            ->map(fn($p) => [
                'tipe'         => 'command',
                'id_pengguna'  => $p->id_pengguna,
                'nama'         => $p->pengguna?->profil?->nama_lengkap ?? '-',
                'peran'        => $p->peran_otoritas,
                'asal'         => $p->asal_lingkup,
                'pos'          => null,
            ]);

        $relawanLapangan = RelawanPenugasan::where('status_aktif', 1)
            ->whereNull('dihapus_pada')
            ->whereHas('penugasanInsiden', fn($q) => $q->where('id_insiden', $idInsiden))
            ->with(['pendaftaran.pengguna.profil', 'posaju:id_posaju,nama_posaju'])
            ->get()
            ->map(fn($r) => [
                'tipe'         => 'lapangan',
                'id_pengguna'  => $r->pendaftaran?->id_pengguna,
                'nama'         => $r->pendaftaran?->pengguna?->profil?->nama_lengkap ?? '-',
                'peran'        => $r->peran_lapangan ?? 'Relawan',
                'asal'         => null,
                'pos'          => $r->posaju?->nama_posaju,
            ]);

        return [
            'command_staff'    => $commandStaff,
            'relawan_lapangan' => $relawanLapangan,
            'total'            => $commandStaff->count() + $relawanLapangan->count(),
        ];
    }
}
