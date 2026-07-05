<?php

namespace App\Http\Controllers\Api\Organisasi;

use App\Http\Controllers\Controller;
use App\Models\OrganisasiDelegasi;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrganisasiDelegasiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', OrganisasiDelegasi::class);
        $items = OrganisasiDelegasi::orderBy('id', 'desc')
            ->paginate($request->get('per_page', 15));
        return response()->json(['data' => $items, 'meta' => ['total' => $items->total()]]);
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', OrganisasiDelegasi::class);
        $validated = $request->validate([
            'mandat_asal_id' => 'required|exists:organisasi_mandat,id',
            'mandat_pengganti_id' => 'required|exists:organisasi_mandat,id',
            'mulai' => 'required|date',
            'selesai' => 'nullable|date|after:mulai',
            'alasan' => 'nullable|string|max:500',
        ]);
        $delegasi = OrganisasiDelegasi::create($validated);
        return response()->json(['message' => 'Delegasi dibuat.', 'data' => ['id' => $delegasi->id]], 201);
    }

    public function show(OrganisasiDelegasi $delegasi): JsonResponse
    {
        $this->authorize('view', $delegasi);
        return response()->json(['data' => $delegasi]);
    }

    public function destroy(OrganisasiDelegasi $delegasi): JsonResponse
    {
        $this->authorize('delete', $delegasi);
        $delegasi->delete();
        return response()->json(['message' => 'Delegasi dihapus.']);
    }
}
