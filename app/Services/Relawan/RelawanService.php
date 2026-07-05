<?php

namespace App\Services\Relawan;

use App\Models\AuthPenggunaProfil;
use App\Models\AuthUser;
use App\Models\RelawanKebutuhan;
use App\Models\RelawanPendaftaran;
use App\Models\RelawanPenugasan;
use App\Services\Auth\AuthorizationContextService;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;

class RelawanService
{
    protected AuthorizationContextService $authContext;

    public function __construct(AuthorizationContextService $authContext)
    {
        $this->authContext = $authContext;
    }

    /**
     * Mendaftarkan pengguna (relawan) ke kebutuhan relawan tertentu.
     * State: pendaftaran menjadi 'seleksi'
     */
    public function registerVolunteer(int $idPengguna, int $idKebutuhan, string $motivasi = ''): RelawanPendaftaran
    {
        $kebutuhan = RelawanKebutuhan::findOrFail($idKebutuhan);

        if ($kebutuhan->status_rekrutmen !== 'dibuka') {
            throw ValidationException::withMessages(['kebutuhan' => 'Kebutuhan relawan sudah tidak dibuka.']);
        }

        $existing = RelawanPendaftaran::where('id_pengguna', $idPengguna)
            ->where('id_relawan_kebutuhan', $idKebutuhan)
            ->first();

        if ($existing) {
            throw ValidationException::withMessages(['pendaftaran' => 'Anda sudah terdaftar untuk kebutuhan ini.']);
        }

        return RelawanPendaftaran::create([
            'id_pengguna' => $idPengguna,
            'id_relawan_kebutuhan' => $idKebutuhan,
            'status_pendaftaran' => 'seleksi',
            'motivasi_singkat' => $motivasi,
        ]);
    }

    /**
     * Menyetujui pendaftaran relawan.
     * State: pendaftaran menjadi 'diterima'
     */
    public function approveRegistration(int $idPendaftaran): RelawanPendaftaran
    {
        $pendaftaran = RelawanPendaftaran::findOrFail($idPendaftaran);

        if ($pendaftaran->status_pendaftaran !== 'seleksi') {
            throw ValidationException::withMessages(['status' => 'Hanya pendaftaran berstatus seleksi yang dapat disetujui.']);
        }

        $pendaftaran->update(['status_pendaftaran' => 'diterima']);

        return $pendaftaran;
    }

    /**
     * Menolak pendaftaran relawan dengan catatan.
     * State: pendaftaran menjadi 'ditolak'
     */
    public function rejectRegistration(int $idPendaftaran, string $catatan): RelawanPendaftaran
    {
        $pendaftaran = RelawanPendaftaran::findOrFail($idPendaftaran);

        if ($pendaftaran->status_pendaftaran !== 'seleksi') {
            throw ValidationException::withMessages(['status' => 'Hanya pendaftaran berstatus seleksi yang dapat ditolak.']);
        }

        $pendaftaran->update([
            'status_pendaftaran' => 'ditolak',
            'catatan_verifikator' => $catatan
        ]);

        return $pendaftaran;
    }

    /**
     * Menugaskan relawan yang sudah diterima ke posaju/klaster tertentu.
     * State: pendaftaran menjadi 'ditugaskan', penugasan menjadi aktif.
     */
    public function assignVolunteer(int $idPendaftaran, ?string $idPosaju = null, ?string $peran = null): RelawanPenugasan
    {
        return DB::transaction(function () use ($idPendaftaran, $idPosaju, $peran) {
            $pendaftaran = RelawanPendaftaran::with('kebutuhan')->findOrFail($idPendaftaran);

            if ($pendaftaran->status_pendaftaran !== 'diterima') {
                throw ValidationException::withMessages(['status' => 'Hanya pendaftaran yang diterima yang dapat ditugaskan.']);
            }

            $existingAssignment = RelawanPenugasan::whereHas('pendaftaran', function($q) use ($pendaftaran) {
                $q->where('id_pengguna', $pendaftaran->id_pengguna);
            })->where('status_aktif', true)->first();

            if ($existingAssignment) {
                throw ValidationException::withMessages(['penugasan' => 'Relawan ini sudah memiliki penugasan aktif.']);
            }

            // Validasi Kuota Kebutuhan
            $kebutuhanId = $pendaftaran->id_relawan_kebutuhan;
            if ($kebutuhanId) {
                $kebutuhan = RelawanKebutuhan::where('id_relawan_kebutuhan', $kebutuhanId)->lockForUpdate()->first();
                if ($kebutuhan && $kebutuhan->jumlah_dibutuhkan > 0) {
                    $currentAssigned = RelawanPenugasan::whereHas('pendaftaran', function ($query) use ($kebutuhan) {
                        $query->where('id_relawan_kebutuhan', $kebutuhan->id_relawan_kebutuhan);
                    })->where('status_aktif', true)->count();

                    if ($currentAssigned >= $kebutuhan->jumlah_dibutuhkan) {
                        throw ValidationException::withMessages(['kuota' => 'Kapasitas kebutuhan ini sudah terpenuhi.']);
                    }
                }
            }

            $pendaftaran->update([
                'status_pendaftaran' => 'ditugaskan',
                'waktu_penugasan_dimulai' => now(),
            ]);

            return RelawanPenugasan::create([
                'id_pendaftaran' => $idPendaftaran,
                'id_posaju' => $idPosaju,
                'peran_lapangan' => $peran,
                'status_aktif' => true,
                'tgl_mulai_aktif' => now()->toDateString(),
            ]);
        });
    }

    /**
     * Menyelesaikan penugasan relawan.
     * State: penugasan menjadi tidak aktif, pendaftaran menjadi 'selesai'
     */
    public function completeAssignment(int $idPenugasan): RelawanPenugasan
    {
        return DB::transaction(function () use ($idPenugasan) {
            $penugasan = RelawanPenugasan::findOrFail($idPenugasan);

            if (!$penugasan->status_aktif) {
                throw ValidationException::withMessages(['status' => 'Hanya penugasan aktif yang dapat diselesaikan.']);
            }

            $penugasan->update([
                'status_aktif' => false,
                'tgl_selesai_aktif' => now()->toDateString(),
            ]);

            $pendaftaran = $penugasan->pendaftaran;
            if ($pendaftaran) {
                $pendaftaran->update([
                    'status_pendaftaran' => 'selesai',
                    'waktu_penugasan_selesai' => now(),
                ]);
            }

            return $penugasan;
        });
    }

    /**
     * Sinkronisasi keahlian relawan.
     */
    public function syncVolunteerSkills(int $idPengguna, array $keahlianData): void
    {
        $user = AuthUser::findOrFail($idPengguna);
        $user->keahlian()->sync($keahlianData);
    }

    /**
     * Mendapatkan profil lengkap relawan.
     */
    public function getVolunteerProfile(int $idPengguna): AuthPenggunaProfil
    {
        $profil = AuthPenggunaProfil::with(['pengguna.keahlian'])->where('id_pengguna', $idPengguna)->first();
        
        if (!$profil) {
            $profil = AuthPenggunaProfil::create([
                'id_pengguna' => $idPengguna,
                'nama_lengkap' => 'Profil Belum Lengkap',
                'nik' => 'TEMP-' . uniqid(),
                'email' => 'temp-' . uniqid() . '@example.com',
            ]);
            $profil->load('pengguna.keahlian');
        }

        return $profil;
    }

    /**
     * Mendapatkan relawan yang tersedia (tidak sedang bertugas aktif).
     */
    public function getAvailableVolunteers()
    {
        return AuthUser::where('id_peran', 4)
            ->whereDoesntHave('pendaftaranRelawan.penugasan', function ($query) {
                $query->where('status_aktif', true);
            })
            ->with(['profil', 'keahlian'])
            ->get();
    }
}
