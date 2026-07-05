<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuthRoleApplication;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RoleApplicationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', AuthRoleApplication::class);
        $items = AuthRoleApplication::with(['pemohon.profil', 'approver.profil'])
            ->when($request->status, fn($q, $v) => $q->where('status_aplikasi', $v))
            ->orderBy('waktu_pengajuan', 'desc')
            ->paginate($request->get('per_page', 15));
        return response()->json(['data' => $items, 'meta' => ['total' => $items->total()]]);
    }

    public function store(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }
        $validated = $request->validate([
            'id_peran_diminta' => 'required|exists:auth_roles,id_peran',
            'catatan' => 'nullable|string|max:500',
        ]);
        $item = AuthRoleApplication::create([
            'id_pengguna' => $user->id_pengguna,
            'id_peran_diminta' => $validated['id_peran_diminta'],
            'status_aplikasi' => 'pending',
            'waktu_pengajuan' => now(),
            'catatan' => $validated['catatan'] ?? null,
        ]);
        return response()->json(['message' => 'Pengajuan peran dikirim.', 'data' => ['id' => $item->id_application]], 201);
    }

    public function show(AuthRoleApplication $roleApplication): JsonResponse
    {
        $this->authorize('view', $roleApplication);
        $roleApplication->load(['pemohon.profil', 'approver.profil']);
        return response()->json(['data' => $roleApplication]);
    }

    public function approve(Request $request, AuthRoleApplication $roleApplication): JsonResponse
    {
        $this->authorize('approve', $roleApplication);
        $roleApplication->update([
            'status_aplikasi' => 'disetujui',
            'waktu_diproses' => now(),
            'id_approver' => $request->user()->id_pengguna,
        ]);
        $roleApplication->pemohon->update(['id_peran' => $roleApplication->id_peran_diminta]);
        return response()->json(['message' => 'Pengajuan peran disetujui.']);
    }

    public function reject(Request $request, AuthRoleApplication $roleApplication): JsonResponse
    {
        $this->authorize('approve', $roleApplication);
        $validated = $request->validate(['catatan' => 'nullable|string|max:500']);
        $roleApplication->update([
            'status_aplikasi' => 'ditolak',
            'waktu_diproses' => now(),
            'id_approver' => $request->user()->id_pengguna,
            'catatan' => $validated['catatan'] ?? $roleApplication->catatan,
        ]);
        return response()->json(['message' => 'Pengajuan peran ditolak.']);
    }
}
