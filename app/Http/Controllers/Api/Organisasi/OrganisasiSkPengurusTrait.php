<?php

namespace App\Http\Controllers\Api\Organisasi;

use App\Http\Controllers\Controller;
use App\Models\OrganisasiSk;
use App\Models\OrganisasiSkPengurus;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

trait OrganisasiSkPengurusTrait
{
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
