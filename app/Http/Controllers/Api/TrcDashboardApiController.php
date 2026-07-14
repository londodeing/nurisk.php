<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\OperasiPenugasan;
use Illuminate\Support\Facades\Auth;

class TrcDashboardApiController extends Controller
{
    public function queue(Request $request): JsonResponse
    {
        $userId = Auth::id();
        
        // Ambil penugasan untuk user login, dengan peran 'trc' dan status aktif.
        $penugasan = OperasiPenugasan::with([
            'insiden.jenisBencana:id_jenis,nama_bencana,slug',
            'insiden.pcnu:id_pcnu,nama_pcnu',
            'insiden.laporanAsal:id_laporan_kejadian,latitude,longitude,keterangan_situasi'
        ])
        ->where('id_pengguna', $userId)
        ->where('peran_otoritas', 'trc')
        ->whereIn('status_penugasan', ['aktif', 'terkirim', 'disetujui']) // Status aktif / disetujui (SPK Ttd)
        ->orderBy('dibuat_pada', 'desc')
        ->get()
        ->map(function ($p) {
            $insiden = $p->insiden;
            return [
                'id_penugasan'   => $p->id_penugasan,
                'uuid_penugasan' => $p->uuid_penugasan,
                'status'         => $p->status_penugasan,
                'id_insiden'     => $insiden?->id_insiden,
                'uuid_insiden'   => $insiden?->uuid_insiden,
                'kode_kejadian'  => $insiden?->kode_kejadian,
                'jenis_bencana'  => $insiden?->jenisBencana?->nama_bencana,
                'waktu_mulai'    => $insiden?->waktu_mulai?->toIso8601String(),
                'deskripsi'      => $insiden?->laporanAsal?->keterangan_situasi ?? 'Tugas Assessment Lapangan',
            ];
        });

        return response()->json([
            'status' => 'success',
            'data'   => $penugasan
        ]);
    }

    public function assignable(Request $request): JsonResponse
    {
        $idPcnu = $request->query('id_pcnu');
        if (!$idPcnu) {
            $user = $request->user();
            $idPcnu = ($user->default_scope_type === 'pcnu') ? $user->default_scope_id : null;
        }

        $trcUsers = \App\Models\AuthUser::whereHas('peran', function ($q) {
                $q->where('nama_peran', 'trc');
            })
            ->where('status_akun', \App\Models\AuthUser::STATUS_AKTIF);

        if ($idPcnu) {
            $trcUsers->where('default_scope_type', 'pcnu')
                     ->where('default_scope_id', $idPcnu);
        }

        $result = $trcUsers->with(['profil'])->get()->map(function ($u) {
            return [
                'id_pengguna' => $u->id_pengguna,
                'nama_lengkap' => $u->profil?->nama_lengkap ?? 'Tanpa Nama',
                'alamat_lengkap' => $u->profil?->alamat ?? 'Alamat belum diisi',
                'no_hp' => $u->no_hp,
                'status_ketersediaan' => $u->status_ketersediaan ?? 'ready',
                'is_tersedia' => $u->is_tersedia,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $result
        ]);
    }
}
