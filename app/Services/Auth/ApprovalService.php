<?php

namespace App\Services\Auth;

use App\Models\AuthUser;
use App\Models\PenggunaJabatan;
use App\Models\JabatanPosisi;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ApprovalService
{
    public function setujui(AuthUser $calon, AuthUser $approver, ?string $catatan = null): void
    {
        DB::transaction(function () use ($calon, $approver) {
            $calon->update([
                'status_akun' => AuthUser::STATUS_AKTIF,
                'is_tersedia' => 1
            ]);

            PenggunaJabatan::where('id_pengguna', $calon->id_pengguna)
                ->where('status_aktif', 0)
                ->update([
                    'status_aktif'    => 1,
                    'ditugaskan_pada' => now(),
                ]);
        });

        Log::info('[APPROVAL] Akun disetujui', [
            'id_pengguna' => $calon->id_pengguna,
            'oleh'        => $approver->id_pengguna,
            'catatan'     => $catatan,
        ]);
    }

    public function tolak(AuthUser $calon, AuthUser $approver, string $alasan): void
    {
        $calon->update(['status_akun' => AuthUser::STATUS_NONAKTIF]);

        Log::info('[APPROVAL] Akun ditolak', [
            'id_pengguna' => $calon->id_pengguna,
            'oleh'        => $approver->id_pengguna,
            'alasan'      => $alasan,
        ]);
    }

    public function daftarMenungguApproval(AuthUser $approver): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        $ctx = app(AuthorizationContextService::class);

        $query = AuthUser::where('status_akun', AuthUser::STATUS_MENUNGGU)
            ->with(['profil', 'peran', 'jabatanPosisi.jabatan']);

        if ($ctx->isSuperAdmin()) {
            $query->whereHas('peran', fn($q) => $q->where('nama_peran', 'pwnu'));
        } elseif ($ctx->hasRole('pwnu')) {
            $query->whereHas('jabatanPosisi.jabatan', fn($j) => $j->whereIn('slug', ['anggota-trc-pwnu', 'admin-pcnu']));
        } elseif ($ctx->hasRole('pcnu')) {
            $idPcnu = $ctx->getScopeId();
            $query->whereHas('jabatanPosisi', fn($j) => $j
                ->where('tipe_lingkup', 'pcnu')
                ->where('id_lingkup', $idPcnu)
                ->whereHas('jabatan', fn($jab) => $jab->where('slug', 'anggota-trc-pcnu'))
            );
        } else {
            return new \Illuminate\Pagination\LengthAwarePaginator([], 0, 20);
        }

        return $query->latest('dibuat_pada')->paginate(20);
    }
}
