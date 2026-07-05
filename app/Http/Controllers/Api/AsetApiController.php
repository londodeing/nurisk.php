<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\OrgAsset;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AsetApiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', OrgAsset::class);

        $items = OrgAsset::query()
            ->when($request->status, fn($q, $v) => $q->where('status', $v))
            ->when($request->category, fn($q, $v) => $q->where('category', $v))
            ->when($request->search, fn($q, $v) => $q->where('asset_code', 'like', "%{$v}%")
                ->orWhere('name', 'like', "%{$v}%"))
            ->paginate($request->get('per_page', 20));

        return response()->json([
            'data' => $items->map(fn($a) => [
                'id'       => $a->id,
                'code'     => $a->asset_code,
                'name'     => $a->name,
                'category' => $a->category,
                'status'   => $a->status,
            ]),
            'meta' => ['total' => $items->total()],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', OrgAsset::class);

        $validated = $request->validate([
            'asset_code'  => 'required|string|max:100|unique:org_assets,asset_code',
            'name'        => 'required|string|max:255',
            'category'    => 'nullable|string|max:100',
            'status'      => 'nullable|string|max:50',
            'readiness'   => 'nullable|in:baik,rusak_ringan,rusak_berat',
        ]);

        $aset = OrgAsset::create($validated);

        return response()->json(['message' => 'Aset berhasil didaftarkan.', 'data' => ['id' => $aset->id]], 201);
    }

    public function show(OrgAsset $aset): JsonResponse
    {
        $this->authorize('view', $aset);

        return response()->json([
            'data' => [
                'id'            => $aset->id,
                'asset_code'    => $aset->asset_code,
                'name'          => $aset->name,
                'category'      => $aset->category,
                'sub_category'  => $aset->sub_category,
                'status'        => $aset->status,
                'readiness'     => $aset->readiness,
                'owner'         => $aset->legal_owner_name,
                'current_location' => $aset->current_territory_code,
            ],
        ]);
    }

    public function update(Request $request, OrgAsset $aset): JsonResponse
    {
        $this->authorize('update', $aset);

        $validated = $request->validate([
            'asset_code'  => 'sometimes|string|max:100|unique:org_assets,asset_code,' . $aset->id,
            'name'        => 'sometimes|string|max:255',
            'category'    => 'nullable|string|max:100',
            'status'      => 'nullable|string|max:50',
            'readiness'   => 'nullable|in:baik,rusak_ringan,rusak_berat',
        ]);

        $aset->update($validated);

        return response()->json(['message' => 'Aset diperbarui.']);
    }

    public function destroy(OrgAsset $aset): JsonResponse
    {
        $this->authorize('delete', $aset);
        $aset->delete();
        return response()->json(['message' => 'Aset dihapus.']);
    }

    public function tersedia(): JsonResponse
    {
        $items = OrgAsset::where('status', 'tersedia')
            ->get()
            ->map(fn($a) => [
                'id'   => $a->id,
                'name' => $a->name,
            ]);

        return response()->json(['data' => $items]);
    }
}
