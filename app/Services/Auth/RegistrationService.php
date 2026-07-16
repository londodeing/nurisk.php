<?php

namespace App\Services\Auth;

use App\Models\AuthUser;
use App\Models\AuthRole;
use App\Models\AuthPenggunaProfil;
use App\Models\AuthPenggunaKeahlian;
use App\Models\AuthKeahlianMaster;
use App\Models\JabatanPosisi;
use App\Models\PenggunaJabatan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class RegistrationService
{
    const JENIS_RELAWAN    = 'relawan';
    const JENIS_TRC_PCNU   = 'trc_pcnu';
    const JENIS_TRC_PWNU   = 'trc_pwnu';
    const JENIS_ADMIN_PCNU = 'admin_pcnu';
    const JENIS_ADMIN_PWNU = 'admin_pwnu';

    const JENIS_PERLU_APPROVAL = [
        self::JENIS_TRC_PCNU,
        self::JENIS_TRC_PWNU,
        self::JENIS_ADMIN_PCNU,
        self::JENIS_ADMIN_PWNU,
    ];

    const SEMUA_JENIS = [
        self::JENIS_RELAWAN,
        self::JENIS_TRC_PCNU,
        self::JENIS_TRC_PWNU,
        self::JENIS_ADMIN_PCNU,
        self::JENIS_ADMIN_PWNU,
    ];

    private function roleUntukJenis(string $jenis): string
    {
        return match($jenis) {
            self::JENIS_RELAWAN, self::JENIS_TRC_PCNU, self::JENIS_TRC_PWNU => 'relawan',
            self::JENIS_ADMIN_PCNU => 'pcnu',
            self::JENIS_ADMIN_PWNU => 'pwnu',
            default => throw new \InvalidArgumentException("Jenis pendaftaran tidak valid: {$jenis}")
        };
    }

    private function jabatanUntukJenis(string $jenis): string
    {
        return match($jenis) {
            self::JENIS_RELAWAN    => 'relawan-umum',
            self::JENIS_TRC_PCNU   => 'anggota-trc-pcnu',
            self::JENIS_TRC_PWNU   => 'anggota-trc-pwnu',
            self::JENIS_ADMIN_PCNU => 'admin-pcnu',
            self::JENIS_ADMIN_PWNU => 'admin-pwnu',
            default => throw new \InvalidArgumentException("Jenis tidak ada jabatannya: {$jenis}")
        };
    }

    public function daftar(array $data, string $jenis): AuthUser
    {
        $namaRole = $this->roleUntukJenis($jenis);
        $slug     = $this->jabatanUntukJenis($jenis);

        return DB::transaction(function () use ($data, $jenis, $namaRole, $slug) {
            $role = AuthRole::where('nama_peran', $namaRole)->firstOrFail();

            $statusAwal = in_array($jenis, self::JENIS_PERLU_APPROVAL)
                ? AuthUser::STATUS_MENUNGGU
                : AuthUser::STATUS_AKTIF;

            $scopeType = null;
            $scopeId   = null;
            if ($jenis === self::JENIS_TRC_PCNU && isset($data['id_pcnu'])) {
                $scopeType = 'pcnu';
                $scopeId   = (int) $data['id_pcnu'];
            } elseif ($jenis === self::JENIS_ADMIN_PCNU && isset($data['id_desa'])) {
                // Find id_kab from id_desa
                $desa = \Illuminate\Support\Facades\DB::table('wilayah_desa')->where('id_desa', $data['id_desa'])->first();
                if (!$desa) throw new \Exception("Desa domisili tidak valid.");
                
                $kec = \Illuminate\Support\Facades\DB::table('wilayah_kecamatan')->where('id_kec', $desa->id_kec)->first();
                if (!$kec) throw new \Exception("Kecamatan domisili tidak valid.");

                // Find PCNU by id_kab
                $pcnu = \Illuminate\Support\Facades\DB::table('organisasi_pcnu')
                    ->join('organisasi_unit', 'organisasi_pcnu.id_unit', '=', 'organisasi_unit.id_unit')
                    ->where('organisasi_unit.id_wilayah', $kec->id_kab)
                    ->first();
                
                if ($pcnu) {
                    $scopeType = 'pcnu';
                    $scopeId   = $pcnu->id_pcnu;
                } else {
                    throw new \Exception("Wilayah domisili Anda belum memiliki kepengurusan PCNU yang terdaftar.");
                }
            } elseif ($jenis === self::JENIS_TRC_PWNU || $jenis === self::JENIS_ADMIN_PWNU) {
                $scopeType = 'pwnu';
                $scopeId   = 1;
            }

            $user = AuthUser::create([
                'id_peran'           => $role->id_peran,
                'no_hp'              => $data['no_hp'],
                'kata_sandi'         => Hash::make($data['kata_sandi']),
                'status_akun'        => $statusAwal,
                'is_tersedia'        => $statusAwal === AuthUser::STATUS_AKTIF,
                'default_scope_type' => $scopeType,
                'default_scope_id'   => $scopeId,
            ]);

            AuthPenggunaProfil::create([
                'id_pengguna'              => $user->id_pengguna,
                'nama_lengkap'             => $data['nama_lengkap'],
                'nik'                      => $data['nik'] ?? null,
                'email'                    => $data['email'] ?? null,
                'id_desa_domisili'         => $data['id_desa'],
                'alamat'                   => $data['alamat_deskriptif'] ?? null,
                'tanggal_lahir'            => $data['tanggal_lahir'] ?? null,
                'jenis_kelamin'            => $data['jenis_kelamin'] ?? null,
                'tempat_lahir'             => $data['tempat_lahir'] ?? null,
                'profesi'                  => $data['profesi'] ?? null,
                'pengalaman_kebencanaan'   => $data['pengalaman_kebencanaan'] ?? null,
            ]);

            if (!empty($data['keahlian']) && is_array($data['keahlian'])) {
                $validKeahlian = AuthKeahlianMaster::whereIn('id_keahlian', $data['keahlian'])
                    ->pluck('id_keahlian')
                    ->toArray();
                foreach ($validKeahlian as $idKeahlian) {
                    AuthPenggunaKeahlian::create([
                        'id_pengguna' => $user->id_pengguna,
                        'id_keahlian' => $idKeahlian,
                    ]);
                }
            }

            $jabatan = JabatanPosisi::where('slug', $slug)->first();
            if ($jabatan) {
                PenggunaJabatan::create([
                    'id_pengguna'       => $user->id_pengguna,
                    'id_jabatan_posisi' => $jabatan->id_jabatan_posisi,
                    'tipe_lingkup'      => $scopeType ?? 'pwnu',
                    'id_lingkup'        => $scopeId ?? 1,
                    'ditugaskan_pada'   => now(),
                    'status_aktif'      => $statusAwal === AuthUser::STATUS_AKTIF ? 1 : 0,
                ]);
            }

            if ($statusAwal === AuthUser::STATUS_MENUNGGU) {
                $this->notifikasiApprover($user, $jenis);
            }

            return $user;
        });
    }

    private function notifikasiApprover(AuthUser $user, string $jenis): void
    {
        $pesanLog = match($jenis) {
            self::JENIS_TRC_PCNU    => "Pendaftaran TRC PCNU baru dari {$user->profil?->nama_lengkap} (no_hp: {$user->no_hp}) menunggu persetujuan Admin PCNU.",
            self::JENIS_TRC_PWNU    => "Pendaftaran TRC PWNU baru dari {$user->profil?->nama_lengkap} menunggu persetujuan Admin PCNU → Admin PWNU.",
            self::JENIS_ADMIN_PCNU  => "Permintaan Admin PCNU baru dari {$user->profil?->nama_lengkap} menunggu persetujuan Admin PWNU.",
            self::JENIS_ADMIN_PWNU  => "Permintaan Admin PWNU baru dari {$user->profil?->nama_lengkap} menunggu persetujuan Super Admin.",
            default                 => "Pendaftaran baru menunggu persetujuan.",
        };

        Log::info('[REGISTRASI] ' . $pesanLog, [
            'id_pengguna' => $user->id_pengguna,
            'jenis'       => $jenis,
        ]);
    }
}
