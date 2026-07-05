<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuthUser;
use App\Models\AuthPenggunaProfil;
use App\Models\AuthRole;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class PenggunaApiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', AuthUser::class);

        $items = AuthUser::with(['profil', 'peran'])
            ->when($request->status_akun, fn($q, $v) => $q->where('status_akun', $v))
            ->when($request->id_peran, fn($q, $v) => $q->where('id_peran', $v))
            ->when($request->search, fn($q, $v) => $q->whereHas('profil', fn($p) => $p->where('nama_lengkap', 'like', "%{$v}%")))
            ->latest('dibuat_pada')
            ->paginate($request->get('per_page', 20));

        return response()->json([
            'data' => $items->map(fn($u) => [
                'id'            => $u->id_pengguna,
                'nama_lengkap'  => $u->profil?->nama_lengkap,
                'nik'           => $u->profil?->nik,
                'no_hp'         => $u->no_hp,
                'email'         => $u->profil?->email,
                'peran'         => $u->peran?->nama_peran,
                'status_akun'   => $u->status_akun,
                'tersedia'      => $u->is_tersedia,
                'terakhir_masuk' => $u->terakhir_masuk?->diffForHumans(),
                'dibuat_pada'   => $u->dibuat_pada?->toIso8601String(),
            ]),
            'meta' => ['total' => $items->total(), 'current_page' => $items->currentPage()],
        ]);
    }

    public function show(AuthUser $pengguna): JsonResponse
    {
        $this->authorize('view', $pengguna);

        $pengguna->load(['profil', 'peran', 'keahlian', 'jabatanPosisi.jabatan']);

        return response()->json([
            'data' => [
                'id'            => $pengguna->id_pengguna,
                'no_hp'         => $pengguna->no_hp,
                'peran'         => $pengguna->peran?->nama_peran,
                'status_akun'   => $pengguna->status_akun,
                'tersedia'      => $pengguna->is_tersedia,
                'profil'        => $pengguna->profil ? [
                    'nik'           => $pengguna->profil->nik,
                    'nama_lengkap'  => $pengguna->profil->nama_lengkap,
                    'email'         => $pengguna->profil->email,
                    'tempat_lahir'  => $pengguna->profil->tempat_lahir,
                    'tanggal_lahir' => $pengguna->profil->tanggal_lahir,
                    'jenis_kelamin' => $pengguna->profil->jenis_kelamin,
                    'alamat'        => $pengguna->profil->alamat,
                    'profesi'       => $pengguna->profil->profesi,
                ] : null,
                'keahlian'      => $pengguna->keahlian->map(fn($k) => [
                    'id'   => $k->id_keahlian,
                    'nama' => $k->nama_keahlian,
                ]),
                'jabatan'       => $pengguna->jabatanPosisi->map(fn($j) => [
                    'jabatan'     => $j->jabatan?->nama_jabatan,
                    'status'      => $j->status_aktif ? 'aktif' : 'tidak_aktif',
                ]),
                'terakhir_masuk' => $pengguna->terakhir_masuk?->toIso8601String(),
                'dibuat_pada'   => $pengguna->dibuat_pada?->toIso8601String(),
            ],
        ]);
    }

    public function update(Request $request, AuthUser $pengguna): JsonResponse
    {
        $this->authorize('update', $pengguna);

        $validated = $request->validate([
            'status_akun' => 'nullable|in:menunggu,aktif,nonaktif,suspend',
            'id_peran'    => 'nullable|exists:auth_roles,id_peran',
            'is_tersedia' => 'nullable|boolean',
            'kata_sandi'  => 'nullable|string|min:8',
        ]);

        if (isset($validated['kata_sandi'])) {
            $validated['kata_sandi'] = Hash::make($validated['kata_sandi']);
        }

        $pengguna->update($validated);

        return response()->json(['message' => 'Pengguna berhasil diperbarui.']);
    }

    public function menunggu(): JsonResponse
    {
        $this->authorize('viewAny', AuthUser::class);

        $items = AuthUser::with(['profil', 'peran'])
            ->where('status_akun', 'menunggu')
            ->latest('dibuat_pada')
            ->paginate(20);

        return response()->json([
            'data' => $items->map(fn($u) => [
                'id'           => $u->id_pengguna,
                'nama_lengkap' => $u->profil?->nama_lengkap,
                'no_hp'        => $u->no_hp,
                'peran'        => $u->peran?->nama_peran,
                'dibuat_pada'  => $u->dibuat_pada?->toIso8601String(),
            ]),
            'meta' => ['total' => $items->total()],
        ]);
    }

    public function setujui(AuthUser $pengguna): JsonResponse
    {
        $this->authorize('approve', $pengguna);

        if ($pengguna->status_akun !== 'menunggu') {
            return response()->json(['message' => 'User sudah diaktifkan sebelumnya.'], 422);
        }

        $pengguna->update([
            'status_akun' => 'aktif',
            'is_tersedia' => 1
        ]);

        foreach ($pengguna->jabatanPosisi as $jabatan) {
            $jabatan->update(['status_aktif' => 1]);
        }

        return response()->json(['message' => 'Pengguna berhasil disetujui.']);
    }

    public function tolak(Request $request, AuthUser $pengguna): JsonResponse
    {
        $this->authorize('approve', $pengguna);

        $pengguna->update([
            'status_akun' => 'nonaktif',
            'is_tersedia' => 0
        ]);

        return response()->json(['message' => 'Pengguna ditolak.']);
    }
}
