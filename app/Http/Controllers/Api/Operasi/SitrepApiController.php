<?php

namespace App\Http\Controllers\Api\Operasi;

use App\Http\Controllers\Controller;
use App\Http\Requests\Operasi\StoreSitrepRequest;
use App\Models\OperasiInsiden;
use App\Models\OperasiSitrep;
use App\Models\OperasiSitrepDampak;
use App\Models\OperasiSitrepKebutuhan;
use App\Services\Operasi\SitrepService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Resources\Operasi\SitrepResource;

class SitrepApiController extends Controller
{
    private SitrepService $service;

    public function __construct(SitrepService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request): JsonResponse
    {
        $request->validate(['uuid_insiden' => 'required|exists:operasi_insiden,uuid_insiden']);
        $insiden = OperasiInsiden::where('uuid_insiden', $request->query('uuid_insiden'))->firstOrFail();

        $this->authorize('viewAny', [OperasiSitrep::class, $insiden]);

        $query = OperasiSitrep::with(['dampak', 'kebutuhan'])
            ->where('id_insiden', $insiden->id_insiden);

        // Incremental sync
        if ($request->has('updated_since')) {
            $query->where('diperbarui_pada', '>', $request->query('updated_since'));
        }

        // Filtering standard
        $filterable = ['id_pembuat', 'nomor_sitrep'];
        foreach ($filterable as $field) {
            if ($request->has($field)) {
                $query->where($field, $request->query($field));
            }
        }

        // Sorting standard
        $sortBy = $request->query('sort_by', 'waktu_sitrep');
        $sortOrder = $request->query('sort_order', 'desc');
        $allowedSortColumns = ['waktu_sitrep', 'dibuat_pada', 'diperbarui_pada'];
        
        if (!in_array($sortBy, $allowedSortColumns)) {
            $sortBy = 'waktu_sitrep';
        }
        if (!in_array(strtolower($sortOrder), ['asc', 'desc'])) {
            $sortOrder = 'desc';
        }

        $sitreps = $query->orderBy($sortBy, $sortOrder)->paginate(15);

        return $this->apiPaginatedResponse($sitreps, SitrepResource::class);
    }

    public function store(StoreSitrepRequest $request): JsonResponse
    {
        $data = $request->validated();
        $insiden = OperasiInsiden::where('uuid_insiden', $data['uuid_insiden'])->firstOrFail();
        $data['id_insiden'] = $insiden->id_insiden;

        $this->authorize('create', [OperasiSitrep::class, $insiden]);

        $sitrep = $this->service->generateSitrep($data);
        $sitrep->load(['dampak', 'kebutuhan']);

        return $this->apiResponse(new SitrepResource($sitrep), 'Sitrep berhasil di-generate', 201);
    }

    public function show(OperasiSitrep $sitrep, Request $request): JsonResponse
    {
        $sitrep->loadMissing(['insiden', 'dampak', 'kebutuhan', 'pembuat.profil']);
        $this->authorize('view', $sitrep);
        return $this->apiResponse(new SitrepResource($sitrep));
    }

    public function update(Request $request, OperasiSitrep $sitrep): JsonResponse
    {
        $this->authorize('update', $sitrep);

        $validated = $request->validate([
            'catatan' => 'nullable|string',
            'waktu_sitrep' => 'sometimes|date',
        ]);

        if ($request->user()) {
            $sitrep->id_pembuat = $request->user()->id_pengguna;
        }

        $sitrep->update($validated);

        return $this->apiResponse(new SitrepResource($sitrep->fresh(['dampak', 'kebutuhan'])), 'Sitrep diperbarui');
    }

    public function destroy(OperasiSitrep $sitrep): JsonResponse
    {
        $this->authorize('delete', $sitrep);
        $sitrep->delete();
        return response()->json(['message' => 'Sitrep dihapus.']);
    }

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
