<?php

namespace App\Services\Sdui\Runtime\Screens;

use App\Models\AuthUser;
use App\Models\OperasiPenugasan;
use App\Services\Auth\AuthorizationContextService;
use App\Services\Sdui\Runtime\Certification\RuntimeCertificationEngine;
use App\Services\Sdui\Runtime\Serializer\SduiSerializer;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AccountHomeService
{
    public function __construct(
        private AuthorizationContextService $ctx,
        private RuntimeCertificationEngine $certificationEngine,
        private SduiSerializer $serializer
    ) {}

    public function compose(AuthUser $user): array
    {
        $profil        = $this->fetchProfil($user);
        $jabatanAktif  = $this->fetchJabatanAktif($user);
        $keahlian      = $this->fetchKeahlian($user);
        $penugasan     = $this->fetchPenugasanAktif($user);
        $commandCenter = $this->fetchCommandCenter();
        $alertInsiden  = $this->fetchAlertInsiden();

        return [
            'success' => true,
            'data' => [
                'profil'         => $profil,
                'jabatan_aktif'  => $jabatanAktif,
                'keahlian'       => $keahlian,
                'penugasan'      => $penugasan,
                'command_center' => $commandCenter,
                'alert_insiden'  => $alertInsiden,
                'version'        => $this->computeVersion($profil, $user),
            ]
        ];
    }

    private function fetchProfil(AuthUser $user): ?array
    {
        $res = DB::selectOne("
            SELECT u.id_pengguna, u.no_hp, u.status_akun, u.is_tersedia,
                   u.terakhir_masuk, u.default_scope_type, u.default_scope_id, u.diperbarui_pada,
                   r.nama_peran, r.level_otoritas,
                   p.nama_lengkap, p.nik, p.email, p.id_desa_domisili
            FROM auth_users u
            JOIN auth_roles r ON u.id_peran = r.id_peran
            LEFT JOIN auth_pengguna_profil p ON u.id_pengguna = p.id_pengguna
            WHERE u.id_pengguna = ?
        ", [$user->id_pengguna]);

        return $res ? (array) $res : null;
    }

    private function fetchJabatanAktif(AuthUser $user): ?array
    {
        $res = DB::selectOne("
            SELECT mj.nama_jabatan, mj.slug, pj.tipe_lingkup, pj.ditugaskan_pada, pj.berakhir_pada
            FROM pengguna_jabatan pj
            JOIN master_jabatan mj ON pj.id_jabatan_posisi = mj.id_jabatan_posisi
            WHERE pj.id_pengguna = ?
              AND pj.status_aktif = 1
              AND (pj.berakhir_pada IS NULL OR pj.berakhir_pada >= CURRENT_TIMESTAMP)
            ORDER BY pj.ditugaskan_pada DESC
            LIMIT 1
        ", [$user->id_pengguna]);
        return $res ? (array) $res : null;
    }

    private function fetchKeahlian(AuthUser $user): array
    {
        $res = DB::select("
            SELECT ak.nama_keahlian
            FROM auth_pengguna_keahlian apk
            JOIN auth_keahlian_master ak ON apk.id_keahlian = ak.id_keahlian
            WHERE apk.id_pengguna = ?
            ORDER BY ak.id_keahlian ASC
        ", [$user->id_pengguna]);
        return array_map(fn($item) => (array) $item, $res);
    }

    private function fetchPenugasanAktif(AuthUser $user): array
    {
        $res = DB::select("
            SELECT
                op.id_penugasan, op.id_insiden, op.peran_otoritas,
                op.waktu_mulai,
                oi.kode_kejadian, oi.uuid_insiden, oi.status_insiden, oi.status_operasi, oi.prioritas,
                bj.nama_bencana
            FROM operasi_penugasan op
            JOIN operasi_insiden oi ON op.id_insiden = oi.id_insiden
            JOIN bencana_master_jenis bj ON oi.id_jenis_bencana = bj.id_jenis
            WHERE op.id_pengguna = ?
              AND op.status_penugasan IN ('draft', 'aktif', 'assigned', 'ditugaskan', 'on_route', 'on_site')
              AND op.waktu_selesai IS NULL
              AND op.dihapus_pada IS NULL
              AND oi.dihapus_pada IS NULL
            ORDER BY op.waktu_mulai DESC
            LIMIT 3
        ", [$user->id_pengguna]);
        return array_map(fn($item) => (array) $item, $res);
    }

    private function fetchCommandCenter(): ?array
    {
        $role = $this->ctx->getRoleName();
        if (!$role || !in_array($role, ['super_admin', 'pwnu', 'pcnu'], true)) {
            return null;
        }
        if (DB::connection()->getDriverName() === 'sqlite') {
            return [];
        }

        $params = [];
        $scopeFilter = '';

        if ($role === 'pcnu') {
            $scopeId = $this->ctx->getScopeId();
            if ($scopeId) {
                $scopeFilter = "AND i.id_pcnu = ?";
                $params[] = $scopeId;
            }
        }

        $sql = "
            SELECT i.id_insiden, i.kode_kejadian, i.status_insiden,
                   i.status_operasi AS command_state, i.prioritas,
                   TIMESTAMPDIFF(DAY, i.waktu_mulai, NOW()) AS lama_kejadian_hari,
                   COALESCE(mk.nama_klaster, 'Tunggu Aktivasi') AS nama_klaster,
                   (SELECT COUNT(*) FROM operasi_sitrep WHERE id_insiden = i.id_insiden) AS jumlah_sitrep
            FROM operasi_insiden i
            LEFT JOIN operasi_klaster ok ON i.id_insiden = ok.id_insiden
            LEFT JOIN operasi_master_klaster mk ON ok.id_klaster = mk.id_klaster
            WHERE i.status_insiden NOT IN ('selesai', 'dibatalkan')
            {$scopeFilter}
            ORDER BY FIELD(i.prioritas, 'kritis', 'tinggi', 'sedang', 'rendah'), i.waktu_mulai DESC
            LIMIT 5
        ";

        $res = DB::select($sql, $params);
        return array_map(fn($item) => (array) $item, $res);
    }

    private function fetchAlertInsiden(): ?array
    {
        $role = $this->ctx->getRoleName();
        if (!$role || !in_array($role, ['super_admin', 'pwnu', 'pcnu'], true)) {
            return null;
        }
        if (DB::connection()->getDriverName() === 'sqlite') {
            return [];
        }

        $params = [];
        $scopeFilter = '';

        if ($role === 'pcnu') {
            $scopeId = $this->ctx->getScopeId();
            if ($scopeId) {
                $scopeFilter = "AND i.id_pcnu = ?";
                $params[] = $scopeId;
            }
        }

        $sql = "
            SELECT i.kode_kejadian, bj.nama_bencana, p.nama_pcnu, i.waktu_mulai, i.prioritas
            FROM operasi_insiden i
            JOIN bencana_master_jenis bj ON i.id_jenis_bencana = bj.id_jenis
            JOIN organisasi_pcnu p ON i.id_pcnu = p.id_pcnu
            LEFT JOIN operasi_klaster ok ON i.id_insiden = ok.id_insiden
            WHERE ok.id_operasi_klaster IS NULL
              AND i.status_insiden NOT IN ('selesai', 'dibatalkan')
              {$scopeFilter}
            LIMIT 3
        ";

        $res = DB::select($sql, $params);
        return array_map(fn($item) => (array) $item, $res);
    }

    private function computeVersion(?array $profil, AuthUser $user): int
    {
        $base = $profil ? strtotime($profil['diperbarui_pada'] ?? '2020-01-01') : 0;
        $maxOpDate = OperasiPenugasan::where('id_pengguna', $user->id_pengguna)->max('diperbarui_pada');
        $maxOp = $maxOpDate ? strtotime($maxOpDate) : 0;
        return (int) max($base, $maxOp);
    }
}
