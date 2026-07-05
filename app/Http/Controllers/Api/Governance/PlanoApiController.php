<?php

namespace App\Http\Controllers\Api\Governance;

use App\Http\Controllers\Controller;
use App\Models\OperasiInsiden;
use App\Models\OperasiPleno;
use App\Models\OperasiPlenoKeputusan;
use App\Models\OperasiPlenoPeserta;
use App\Services\PlanoService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class PlanoApiController extends Controller
{
    public function index(Request $request, OperasiInsiden $insiden): JsonResponse
    {
        $items = OperasiPleno::where('id_insiden', $insiden->id_insiden)
            ->with(['keputusan', 'peserta.pengguna.profil', 'pimpinan.profil', 'notulis.profil'])
            ->orderBy('waktu_pleno', 'desc')
            ->paginate($request->get('per_page', 15));

        return response()->json([
            'data' => $items,
            'meta' => ['total' => $items->total()],
        ]);
    }

    public function show(OperasiInsiden $insiden, OperasiPleno $pleno): JsonResponse
    {
        if ($pleno->id_insiden !== $insiden->id_insiden) {
            return response()->json(['message' => 'Not found'], 404);
        }

        $pleno->load(['keputusan', 'peserta.pengguna.profil', 'pimpinan.profil', 'notulis.profil']);

        return response()->json(['data' => $pleno]);
    }

    public function store(Request $request, OperasiInsiden $insiden): JsonResponse
    {
        $this->authorize('create', [OperasiPleno::class, $insiden]);

        $validated = $request->validate([
            'nomor_pleno' => 'nullable|string|max:100',
            'waktu_pleno' => 'required|date',
            'jenis_pleno' => 'required|string|max:50',
            'pimpinan_pleno' => 'required|exists:auth_users,id_pengguna',
            'notulis_pleno' => 'required|exists:auth_users,id_pengguna',
            'lokasi_pleno' => 'nullable|string|max:255',
        ]);

        $validated['id_insiden'] = $insiden->id_insiden;
        $validated['status_pleno'] = 'draft';

        $pleno = app(PlanoService::class)->buatPlano($validated);

        return response()->json(['message' => 'Pleno dibuat.', 'data' => $pleno], 201);
    }

    public function update(Request $request, OperasiInsiden $insiden, OperasiPleno $pleno): JsonResponse
    {
        $this->authorize('update', $pleno);

        if ($pleno->id_insiden !== $insiden->id_insiden) {
            return response()->json(['message' => 'Not found'], 404);
        }

        $validated = $request->validate([
            'nomor_pleno' => 'nullable|string|max:100',
            'waktu_pleno' => 'sometimes|date',
            'jenis_pleno' => 'sometimes|string|max:50',
            'pimpinan_pleno' => 'sometimes|exists:auth_users,id_pengguna',
            'notulis_pleno' => 'sometimes|exists:auth_users,id_pengguna',
            'lokasi_pleno' => 'nullable|string|max:255',
            'hasil_umum' => 'nullable|string',
        ]);

        $pleno->update($validated);

        return response()->json(['message' => 'Pleno diperbarui.', 'data' => $pleno]);
    }

    public function destroy(OperasiInsiden $insiden, OperasiPleno $pleno): JsonResponse
    {
        $this->authorize('delete', $pleno);

        if ($pleno->id_insiden !== $insiden->id_insiden) {
            return response()->json(['message' => 'Not found'], 404);
        }

        $pleno->delete();
        return response()->json(['message' => 'Pleno dihapus.']);
    }

    public function finalisasi(OperasiInsiden $insiden, OperasiPleno $pleno): JsonResponse
    {
        $this->authorize('finalisasi', $pleno);

        if ($pleno->id_insiden !== $insiden->id_insiden) {
            return response()->json(['message' => 'Not found'], 404);
        }

        $pleno->update(['status_pleno' => 'final', 'waktu_difinalisasi' => now()]);
        return response()->json(['message' => 'Pleno difinalisasi.']);
    }

    public function tambahKeputusan(Request $request, OperasiInsiden $insiden, OperasiPleno $pleno): JsonResponse
    {
        $this->authorize('tambahKeputusan', $pleno);

        if ($pleno->id_insiden !== $insiden->id_insiden) {
            return response()->json(['message' => 'Not found'], 404);
        }

        $validated = $request->validate([
            'deskripsi_keputusan' => 'required|string',
        ]);

        $keputusan = $pleno->keputusan()->create($validated);

        return response()->json(['message' => 'Keputusan ditambahkan.', 'data' => $keputusan], 201);
    }

    public function hapusKeputusan(OperasiInsiden $insiden, OperasiPleno $pleno, OperasiPlenoKeputusan $keputusan): JsonResponse
    {
        $this->authorize('tambahKeputusan', $pleno);

        if ($pleno->id_insiden !== $insiden->id_insiden || $keputusan->id_pleno !== $pleno->id_pleno) {
            return response()->json(['message' => 'Not found'], 404);
        }

        $keputusan->delete();
        return response()->json(['message' => 'Keputusan dihapus.']);
    }

    public function tambahPeserta(Request $request, OperasiInsiden $insiden, OperasiPleno $pleno): JsonResponse
    {
        $this->authorize('tambahPeserta', $pleno);

        if ($pleno->id_insiden !== $insiden->id_insiden) {
            return response()->json(['message' => 'Not found'], 404);
        }

        $validated = $request->validate([
            'id_pengguna' => 'required|exists:auth_users,id_pengguna',
            'peran_dalam_rapat' => 'nullable|string|max:100',
        ]);

        $peserta = $pleno->peserta()->create($validated);

        return response()->json(['message' => 'Peserta ditambahkan.', 'data' => $peserta], 201);
    }

    public function hapusPeserta(OperasiInsiden $insiden, OperasiPleno $pleno, OperasiPlenoPeserta $peserta): JsonResponse
    {
        $this->authorize('tambahPeserta', $pleno);

        if ($pleno->id_insiden !== $insiden->id_insiden || $peserta->id_pleno !== $pleno->id_pleno) {
            return response()->json(['message' => 'Not found'], 404);
        }

        $peserta->delete();
        return response()->json(['message' => 'Peserta dihapus.']);
    }
}
