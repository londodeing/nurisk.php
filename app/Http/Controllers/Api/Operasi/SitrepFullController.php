<?php

namespace App\Http\Controllers\Api\Operasi;

use App\Http\Controllers\Controller;
use App\Models\OperasiSitrep;
use App\Models\OperasiSitrepDampak;
use App\Models\OperasiSitrepKebutuhan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

trait SitrepNestedTrait
{
    public function dampakIndex(OperasiSitrep $sitrep): JsonResponse
    {
        $this->authorize('view', $sitrep);
        return response()->json(['data' => $sitrep->dampak()->get()]);
    }

    public function dampakStore(Request $request, OperasiSitrep $sitrep): JsonResponse
    {
        $this->authorize('update', $sitrep);
        $validated = $request->validate([
            'meninggal' => 'nullable|integer|min:0',
            'hilang' => 'nullable|integer|min:0',
            'luka_berat' => 'nullable|integer|min:0',
            'luka_ringan' => 'nullable|integer|min:0',
            'mengungsi' => 'nullable|integer|min:0',
        ]);
        $dampak = $sitrep->dampak()->create($validated);
        return response()->json(['message' => 'Data dampak ditambahkan.', 'data' => $dampak], 201);
    }

    public function dampakUpdate(Request $request, OperasiSitrep $sitrep, OperasiSitrepDampak $dampak): JsonResponse
    {
        $this->authorize('update', $sitrep);
        if ($dampak->id_sitrep !== $sitrep->id_sitrep) {
            return response()->json(['message' => 'Not found'], 404);
        }
        $dampak->update($request->validate([
            'meninggal' => 'nullable|integer|min:0',
            'hilang' => 'nullable|integer|min:0',
            'luka_berat' => 'nullable|integer|min:0',
            'luka_ringan' => 'nullable|integer|min:0',
            'mengungsi' => 'nullable|integer|min:0',
        ]));
        return response()->json(['message' => 'Data dampak diperbarui.']);
    }

    public function dampakDestroy(OperasiSitrep $sitrep, OperasiSitrepDampak $dampak): JsonResponse
    {
        $this->authorize('update', $sitrep);
        if ($dampak->id_sitrep !== $sitrep->id_sitrep) {
            return response()->json(['message' => 'Not found'], 404);
        }
        $dampak->delete();
        return response()->json(['message' => 'Data dampak dihapus.']);
    }

    public function kebutuhanIndex(OperasiSitrep $sitrep): JsonResponse
    {
        $this->authorize('view', $sitrep);
        return response()->json(['data' => $sitrep->kebutuhan()->get()]);
    }

    public function kebutuhanStore(Request $request, OperasiSitrep $sitrep): JsonResponse
    {
        $this->authorize('update', $sitrep);
        $validated = $request->validate([
            'nama_kebutuhan' => 'required|string|max:200',
            'jumlah' => 'required|integer|min:1',
            'satuan' => 'nullable|string|max:50',
        ]);
        $kebutuhan = $sitrep->kebutuhan()->create($validated);
        return response()->json(['message' => 'Kebutuhan ditambahkan.', 'data' => $kebutuhan], 201);
    }

    public function kebutuhanUpdate(Request $request, OperasiSitrep $sitrep, OperasiSitrepKebutuhan $kebutuhan): JsonResponse
    {
        $this->authorize('update', $sitrep);
        if ($kebutuhan->id_sitrep !== $sitrep->id_sitrep) {
            return response()->json(['message' => 'Not found'], 404);
        }
        $kebutuhan->update($request->validate([
            'nama_kebutuhan' => 'sometimes|string|max:200',
            'jumlah' => 'sometimes|integer|min:1',
            'satuan' => 'nullable|string|max:50',
        ]));
        return response()->json(['message' => 'Kebutuhan diperbarui.']);
    }

    public function kebutuhanDestroy(OperasiSitrep $sitrep, OperasiSitrepKebutuhan $kebutuhan): JsonResponse
    {
        $this->authorize('update', $sitrep);
        if ($kebutuhan->id_sitrep !== $sitrep->id_sitrep) {
            return response()->json(['message' => 'Not found'], 404);
        }
        $kebutuhan->delete();
        return response()->json(['message' => 'Kebutuhan dihapus.']);
    }
}

class SitrepFullController extends SitrepApiController
{
    use SitrepNestedTrait;
}
