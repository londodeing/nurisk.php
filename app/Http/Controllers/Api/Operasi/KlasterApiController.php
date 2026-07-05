<?php

namespace App\Http\Controllers\Api\Operasi;

use App\Http\Controllers\Controller;
use App\Http\Requests\Operasi\StoreKlasterRequest;
use App\Http\Resources\Operasi\KlasterResource;
use App\Models\OperasiInsiden;
use App\Models\OperasiKlaster;
use App\Services\Operasi\KlasterService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class KlasterApiController extends Controller
{
    private KlasterService $service;

    public function __construct(KlasterService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request): JsonResponse
    {
        $request->validate(['uuid_insiden' => 'required|exists:operasi_insiden,uuid_insiden']);
        $insiden = OperasiInsiden::where('uuid_insiden', $request->query('uuid_insiden'))->firstOrFail();

        $this->authorize('viewAny', [OperasiKlaster::class, $insiden]);

        $query = OperasiKlaster::with('masterKlaster')
            ->where('id_insiden', $insiden->id_insiden);

        // Incremental sync
        if ($request->has('updated_since')) {
            $query->where('diperbarui_pada', '>', $request->query('updated_since'));
        }

        // Filtering standard
        $filterable = ['status_klaster', 'prioritas', 'id_master_klaster', 'id_pembuat'];
        foreach ($filterable as $field) {
            if ($request->has($field)) {
                $query->where($field, $request->query($field));
            }
        }

        // Sorting standard
        $sortBy = $request->query('sort_by', 'waktu_aktivasi');
        $sortOrder = $request->query('sort_order', 'desc');
        $allowedSortColumns = ['waktu_aktivasi', 'waktu_ditutup', 'dibuat_pada', 'diperbarui_pada', 'status_klaster', 'prioritas', 'progres_persen'];
        
        if (!in_array($sortBy, $allowedSortColumns)) {
            $sortBy = 'waktu_aktivasi';
        }
        if (!in_array(strtolower($sortOrder), ['asc', 'desc'])) {
            $sortOrder = 'desc';
        }

        $klaster = $query->orderBy($sortBy, $sortOrder)->paginate(15);
            
        return $this->apiPaginatedResponse($klaster, KlasterResource::class);
    }

    public function store(StoreKlasterRequest $request): JsonResponse
    {
        $data = $request->validated();
        $insiden = OperasiInsiden::where('uuid_insiden', $data['uuid_insiden'])->firstOrFail();
        $data['id_insiden'] = $insiden->id_insiden;

        
    \Log::info("In store. User ID: " . \Auth::id() . " Role: " . app(\App\Services\Auth\AuthorizationContextService::class)->getRoleName());
    \Log::info("Gate create: " . (\Gate::check('create', [OperasiKlaster::class, $insiden]) ? "TRUE" : "FALSE"));
    $this->authorize('create', OperasiKlaster::class);
    

        $klaster = $this->service->createKlaster($data);
        $klaster->load('masterKlaster');

        return $this->apiResponse(new KlasterResource($klaster), 'Klaster berhasil diaktivasi', 201);
    }

    public function show(string $uuid): JsonResponse
    {
        $klaster = OperasiKlaster::with('masterKlaster')
            ->where('uuid_klaster_operasi', $uuid)
            ->firstOrFail();

        $this->authorize('view', $klaster);

        return $this->apiResponse(new KlasterResource($klaster));
    }

    public function update(\App\Http\Requests\Operasi\UpdateKlasterRequest $request, string $uuid): JsonResponse
    {
        $klaster = OperasiKlaster::where('uuid_klaster_operasi', $uuid)->firstOrFail();
        $this->authorize('update', $klaster);

        $klaster = $this->service->updateKlaster($klaster, $request->validated());
        $klaster->load('masterKlaster');

        return $this->apiResponse(new KlasterResource($klaster), 'Klaster berhasil diperbarui');
    }

    public function destroy(string $uuid): JsonResponse
    {
        $klaster = OperasiKlaster::where('uuid_klaster_operasi', $uuid)->firstOrFail();
        $this->authorize('delete', $klaster);

        $this->service->deleteKlaster($klaster);

        return $this->apiResponse(null, 'Klaster berhasil dihapus');
    }
}
