<?php

namespace App\Http\Controllers\Api\Operasi;

use App\Http\Controllers\Controller;
use App\Models\OperasiTugas;
use App\Services\Operasi\OperasiTugasService;
use App\Http\Requests\Operasi\StoreTugasRequest;
use App\Http\Requests\Operasi\StartTugasRequest;
use App\Http\Requests\Operasi\PauseTugasRequest;
use App\Http\Requests\Operasi\CompleteTugasRequest;
use App\Http\Resources\Operasi\OperasiTugasResource;
use App\Http\Resources\Operasi\OperasiTugasCollection;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class OperasiTugasController extends Controller
{
    public function __construct(protected OperasiTugasService $service)
    {
    }

    public function index(Request $request): OperasiTugasCollection
    {
        $this->authorize('viewAny', OperasiTugas::class);
        $tugas = OperasiTugas::with(['klaster', 'posaju', 'pelaksana'])->paginate($request->get('per_page', 15));
        return new OperasiTugasCollection($tugas);
    }

    public function store(StoreTugasRequest $request): JsonResponse
    {
        $this->authorize('create', OperasiTugas::class);
        
        $tugas = OperasiTugas::create($request->validated());
        $tugas->load(['klaster', 'posaju', 'pelaksana']);

        return (new OperasiTugasResource($tugas))
            ->additional(['message' => 'Tugas berhasil dibuat'])
            ->response()
            ->setStatusCode(201);
    }

    public function show(OperasiTugas $tugas): OperasiTugasResource
    {
        $this->authorize('view', $tugas);
        $tugas->load(['klaster', 'posaju', 'pelaksana']);
        return new OperasiTugasResource($tugas);
    }

    public function update(Request $request, OperasiTugas $tugas): JsonResponse
    {
        $this->authorize('update', $tugas);
        
        $tugas->update($request->validate([
            'id_operasi_klaster' => 'sometimes|int|exists:operasi_klaster,id_klaster_operasi',
            'id_posaju' => 'nullable|int|exists:operasi_posaju,id_posaju',
            'judul_tugas' => 'sometimes|string|max:255',
            'target_indikator' => 'nullable|string',
            'status_tugas' => 'sometimes|string|max:50',
            'progres_persen' => 'nullable|numeric|min:0|max:100',
        ]));
        $tugas = $tugas->fresh();
        $tugas->load(['klaster', 'posaju', 'pelaksana']);

        return (new OperasiTugasResource($tugas))
            ->additional(['message' => 'Tugas berhasil diupdate'])
            ->response();
    }

    public function start(StartTugasRequest $request, OperasiTugas $tugas): JsonResponse
    {
        $this->authorize('start', $tugas);
        
        $tugas = $this->service->start($tugas);
        $tugas->load(['klaster', 'posaju', 'pelaksana']);

        return (new OperasiTugasResource($tugas))
            ->additional(['message' => 'Tugas berhasil dimulai'])
            ->response();
    }

    public function pause(PauseTugasRequest $request, OperasiTugas $tugas): JsonResponse
    {
        $this->authorize('pause', $tugas);
        
        $tugas = $this->service->pause($tugas);
        $tugas->load(['klaster', 'posaju', 'pelaksana']);

        return (new OperasiTugasResource($tugas))
            ->additional(['message' => 'Tugas berhasil dipause'])
            ->response();
    }

    public function complete(CompleteTugasRequest $request, OperasiTugas $tugas): JsonResponse
    {
        $this->authorize('complete', $tugas);
        
        $tugas = $this->service->complete($tugas);
        $tugas->load(['klaster', 'posaju', 'pelaksana']);

        return (new OperasiTugasResource($tugas))
            ->additional(['message' => 'Tugas berhasil diselesaikan'])
            ->response();
    }
}
