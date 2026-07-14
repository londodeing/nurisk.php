<?php

namespace App\Http\Controllers\Api\Operasi;

use App\Http\Controllers\Controller;
use App\Models\OperasiPosaju;
use App\Services\Operasi\OperasiPosajuService;
use App\Http\Requests\Operasi\StorePosajuRequest;
use App\Http\Requests\Operasi\UpdatePosajuRequest;
use App\Http\Requests\Operasi\ActivatePosajuRequest;
use App\Http\Requests\Operasi\ExtendPosajuRequest;
use App\Http\Requests\Operasi\ClosePosajuRequest;
use App\Http\Resources\Operasi\OperasiPosajuResource;
use App\Http\Resources\Operasi\OperasiPosajuCollection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OperasiPosajuController extends Controller
{
    public function __construct(protected OperasiPosajuService $service)
    {
    }

    public function index(Request $request): OperasiPosajuCollection
    {
        $this->authorize('viewAny', OperasiPosaju::class);
        $posajus = OperasiPosaju::with(['insiden', 'pj'])->paginate($request->get('per_page', 15));
        return new OperasiPosajuCollection($posajus);
    }

    public function store(StorePosajuRequest $request): JsonResponse
    {
        $insiden = \App\Models\OperasiInsiden::where('uuid_insiden', $request->validated('uuid_insiden'))->first();
        $this->authorize('create', [OperasiPosaju::class, $insiden]);

        $posaju = OperasiPosaju::create($request->validated());
        $posaju->load(['insiden', 'pj']);

        return (new OperasiPosajuResource($posaju))
            ->additional(['message' => 'Pos Aju berhasil dibuat'])
            ->response()
            ->setStatusCode(201);
    }

    public function show(OperasiPosaju $posaju): OperasiPosajuResource
    {
        $this->authorize('view', $posaju);
        $posaju->load(['insiden', 'pj']);
        return new OperasiPosajuResource($posaju);
    }

    public function update(UpdatePosajuRequest $request, OperasiPosaju $posaju): JsonResponse
    {
        $this->authorize('update', $posaju);
        
        $posaju->update($request->validated());
        $posaju->load(['insiden', 'pj']);

        return (new OperasiPosajuResource($posaju->fresh()))
            ->additional(['message' => 'Pos Aju berhasil diupdate'])
            ->response();
    }

    public function activate(ActivatePosajuRequest $request, OperasiPosaju $posaju): JsonResponse
    {
        $this->authorize('activate', $posaju);
        
        $posaju = $this->service->activate($posaju);
        $posaju->load(['insiden', 'pj']);

        return (new OperasiPosajuResource($posaju))
            ->additional(['message' => 'Pos Aju berhasil diaktifkan'])
            ->response();
    }

    public function extend(ExtendPosajuRequest $request, OperasiPosaju $posaju): JsonResponse
    {
        $this->authorize('extend', $posaju);
        
        $validated = $request->validated();
        $posaju = $this->service->extend(
            $posaju,
            \Carbon\Carbon::parse($validated['diperpanjang_hingga']),
            $validated['alasan_perpanjangan'] ?? null
        );
        $posaju->load(['insiden', 'pj']);

        return (new OperasiPosajuResource($posaju))
            ->additional(['message' => 'Pos Aju berhasil diperpanjang'])
            ->response();
    }

    public function close(ClosePosajuRequest $request, OperasiPosaju $posaju): JsonResponse
    {
        $this->authorize('close', $posaju);
        
        $validated = $request->validated();
        $posaju = $this->service->close($posaju, $validated['alasan_penutupan'] ?? null);
        $posaju->load(['insiden', 'pj']);

        return (new OperasiPosajuResource($posaju))
            ->additional(['message' => 'Pos Aju berhasil ditutup'])
            ->response();
    }

    public function activeByInsiden(\App\Models\OperasiInsiden $insiden): OperasiPosajuCollection
    {
        $this->authorize('viewAny', OperasiPosaju::class);

        $posajus = OperasiPosaju::with(['insiden', 'pj', 'komandanAktif.pengguna.profil'])
            ->where('id_insiden', $insiden->id_insiden)
            ->where('status_alur', 'aktif')
            ->orderBy('nama_posaju')
            ->get();

        return new OperasiPosajuCollection($posajus);
    }
}
