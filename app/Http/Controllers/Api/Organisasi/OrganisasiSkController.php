<?php

namespace App\Http\Controllers\Api\Organisasi;

use App\Http\Controllers\Controller;
use App\Models\OrganisasiSk;
use App\Models\OrganisasiSkPengurus;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrganisasiSkController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', OrganisasiSk::class);
        $items = OrganisasiSk::orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 15));
        return response()->json(['data' => $items, 'meta' => ['total' => $items->total()]]);
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', OrganisasiSk::class);
        $validated = $request->validate([
            'nomor_sk' => 'required|string|max:100|unique:organisasi_sk,nomor_sk',
            'tanggal_terbit' => 'required|date',
            'tanggal_mulai' => 'nullable|date',
            'tanggal_berakhir' => 'nullable|date|after:tanggal_mulai',
            'dokumen_file' => 'nullable|string|max:255',
        ]);
        $sk = OrganisasiSk::create($validated);
        return response()->json(['message' => 'SK dibuat.', 'data' => ['id' => $sk->id]], 201);
    }

    public function show(OrganisasiSk $sk): JsonResponse
    {
        $this->authorize('view', $sk);
        $sk->load(['pengurus.user']);
        return response()->json(['data' => $sk]);
    }

    public function update(Request $request, OrganisasiSk $sk): JsonResponse
    {
        $this->authorize('update', $sk);
        $validated = $request->validate([
            'nomor_sk' => 'sometimes|string|max:100|unique:organisasi_sk,nomor_sk,' . $sk->id,
            'dokumen_file' => 'nullable|string|max:255',
        ]);
        $sk->update($validated);
        return response()->json(['message' => 'SK diperbarui.']);
    }

    public function destroy(OrganisasiSk $sk): JsonResponse
    {
        $this->authorize('delete', $sk);
        $sk->delete();
        return response()->json(['message' => 'SK dihapus.']);
    }

    public function pengurus(OrganisasiSk $sk): JsonResponse
    {
        $this->authorize('view', $sk);
        return response()->json(['data' => $sk->pengurus()->with('user.profil')->get()]);
    }

    public function tambahPengurus(Request $request, OrganisasiSk $sk): JsonResponse
    {
        $this->authorize('update', $sk);
        $validated = $request->validate([
            'auth_user_id' => 'required|exists:auth_users,id_pengguna',
            'jabatan_id' => 'nullable|exists:organisasi_jabatan,id',
        ]);
        $pengurus = $sk->pengurus()->create($validated);
        return response()->json(['message' => 'Pengurus ditambahkan.', 'data' => $pengurus], 201);
    }

    public function hapusPengurus(OrganisasiSk $sk, OrganisasiSkPengurus $pengurus): JsonResponse
    {
        $this->authorize('update', $sk);
        if ($pengurus->sk_id !== $sk->id) {
            return response()->json(['message' => 'Not found'], 404);
        }
        $pengurus->delete();
        return response()->json(['message' => 'Pengurus dihapus.']);
    }
}
