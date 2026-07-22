<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuthUser;
use App\Services\Auth\ApprovalService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ApprovalApiController extends Controller
{
    public function __construct(private ApprovalService $approvalService) {}

    public function index(Request $request): JsonResponse
    {
        if (!in_array($request->user()->peran->nama_peran ?? '', ['super_admin', 'pwnu', 'pcnu'])) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $menunggu = $this->approvalService->daftarMenungguApproval($request->user());

        // Transform response so mobile doesn't get unnecessary bloated data
        $data = [];
        foreach ($menunggu as $calon) {
            $data[] = [
                'id_pengguna' => $calon->id_pengguna,
                'no_hp' => $calon->no_hp,
                'email' => $calon->profil->email ?? null,
                'nama_lengkap' => $calon->profil->nama_lengkap ?? 'Tanpa Nama',
                'nik' => $calon->profil->nik ?? null,
                'peran_diajukan' => optional($calon->peran)->nama_peran,
                'jabatan' => $calon->jabatanPosisi->map(fn($j) => $j->jabatan->nama_jabatan)->implode(', '),
                'dibuat_pada' => optional($calon->dibuat_pada)->format('Y-m-d H:i:s'),
                'desa' => optional(optional($calon->profil)->desa)->nama_desa,
                'kecamatan' => optional(optional(optional($calon->profil)->desa)->kecamatan)->nama_kec,
            ];
        }

        return response()->json([
            'data' => $data,
            'meta' => [
                'current_page' => $menunggu->currentPage(),
                'last_page' => $menunggu->lastPage(),
                'total' => $menunggu->total(),
            ]
        ]);
    }

    public function setujui(Request $request, AuthUser $calon): JsonResponse
    {
        $this->authorize('approve', $calon);

        $this->approvalService->setujui($calon, $request->user());

        return response()->json([
            'message' => "Akun {$calon->profil?->nama_lengkap} berhasil disetujui."
        ]);
    }

    public function tolak(Request $request, AuthUser $calon): JsonResponse
    {
        $this->authorize('approve', $calon);

        $request->validate([
            'alasan' => ['required', 'string', 'min:10', 'max:500'],
        ]);

        $this->approvalService->tolak($calon, $request->user(), $request->alasan);

        return response()->json([
            'message' => "Pendaftaran {$calon->profil?->nama_lengkap} ditolak."
        ]);
    }
}
