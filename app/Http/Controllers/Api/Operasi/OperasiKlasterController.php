<?php

namespace App\Http\Controllers\Api\Operasi;

use App\Http\Controllers\Controller;
use App\Models\OperasiKlaster;
use App\Services\Operasi\OperasiKlasterService;
use App\Http\Requests\Operasi\StoreKlasterRequest;
use App\Http\Requests\Operasi\UpdateKlasterProgressRequest;
use App\Http\Requests\Operasi\CompleteKlasterRequest;
use App\Http\Resources\Operasi\OperasiKlasterResource;
use App\Http\Resources\Operasi\OperasiKlasterCollection;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class OperasiKlasterController extends Controller
{
    public function __construct(protected OperasiKlasterService $service)
    {
    }

    public function index(Request $request): OperasiKlasterCollection
    {
        $this->authorize('viewAny', OperasiKlaster::class);
        $klasters = OperasiKlaster::with(['insiden'])->paginate($request->get('per_page', 15));
        return new OperasiKlasterCollection($klasters);
    }

    public function store(StoreKlasterRequest $request): JsonResponse
    {
        
    $this->authorize('create', OperasiKlaster::class);
    
        
        
    $data = $request->validated();
    $insiden = \App\Models\OperasiInsiden::where("uuid_insiden", $data["uuid_insiden"])->firstOrFail();
    $data["id_insiden"] = $insiden->id_insiden;
    unset($data["uuid_insiden"]);
    $klaster = OperasiKlaster::create($data);
    
        $klaster->load(['insiden']);

        return (new OperasiKlasterResource($klaster))
            ->additional(['message' => 'Klaster berhasil dibuat'])
            ->response()
            ->setStatusCode(201);
    }

    public function show(OperasiKlaster $klaster): OperasiKlasterResource
    {
        $this->authorize('view', $klaster);
        $klaster->load(['insiden']);
        return new OperasiKlasterResource($klaster);
    }

    public function update(Request $request, OperasiKlaster $klaster): JsonResponse
    {
        $this->authorize('update', $klaster);
        
        $klaster->update($request->validate([
            'status_klaster' => 'sometimes|in:aktif,selesai',
            'prioritas' => 'nullable|in:rendah,sedang,tinggi,kritis',
            'target_cakupan' => 'nullable|string',
            'catatan' => 'nullable|string',
            'progres_persen' => 'nullable|numeric|min:0|max:100',
        ]));
        $klaster = $klaster->fresh();
        $klaster->load(['insiden']);

        return (new OperasiKlasterResource($klaster))
            ->additional(['message' => 'Klaster berhasil diupdate'])
            ->response();
    }

    public function updateProgress(UpdateKlasterProgressRequest $request, OperasiKlaster $klaster): JsonResponse
    {
        $this->authorize('updateProgress', $klaster);
        
        $klaster = $this->service->updateProgress($klaster, $request->validated('progres_persen'));
        $klaster->load(['insiden']);

        return (new OperasiKlasterResource($klaster))
            ->additional(['message' => 'Progress Klaster berhasil diupdate'])
            ->response();
    }

    public function complete(CompleteKlasterRequest $request, OperasiKlaster $klaster): JsonResponse
    {
        $this->authorize('complete', $klaster);
        
        $klaster = $this->service->complete($klaster);
        $klaster->load(['insiden']);

        return (new OperasiKlasterResource($klaster))
            ->additional(['message' => 'Klaster berhasil diselesaikan'])
            ->response();
    }
}
