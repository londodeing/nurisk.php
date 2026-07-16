<?php

namespace App\Http\Controllers\Api\Operasi;

use App\Http\Controllers\Controller;
use App\Http\Requests\Operasi\StoreAssessmentRequest;
use App\Models\OperasiInsiden;
use App\Models\AssessmentUtama;
use App\Models\AssessmentDampakManusia;
use App\Models\AssessmentKebutuhanMendesak;
use App\Services\Operasi\AssessmentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Resources\Operasi\AssessmentResource;

class AssessmentApiController extends Controller
{
    private AssessmentService $service;

    public function __construct(AssessmentService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request): JsonResponse
    {
        $request->validate(['uuid_insiden' => 'required|exists:operasi_insiden,uuid_insiden']);
        $insiden = OperasiInsiden::where('uuid_insiden', $request->query('uuid_insiden'))->firstOrFail();

        $this->authorize('viewAny', [AssessmentUtama::class, $insiden]);

        $query = AssessmentUtama::with(['dampakManusiaV2', 'kebutuhanMendesak', 'kebutuhanNumerik'])
            ->where('id_insiden', $insiden->id_insiden);

        // Incremental sync
        if ($request->has('updated_since')) {
            $query->where('diperbarui_pada', '>', $request->query('updated_since'));
        }

        // Filtering standard
        $filterable = ['jenis_laporan'];
        foreach ($filterable as $field) {
            if ($request->has($field)) {
                $query->where($field, $request->query($field));
            }
        }

        // Sorting standard
        $sortBy = $request->query('sort_by', 'waktu_assesment');
        $sortOrder = $request->query('sort_order', 'desc');
        $allowedSortColumns = ['waktu_assesment', 'dibuat_pada', 'diperbarui_pada', 'jenis_laporan'];
        
        if (!in_array($sortBy, $allowedSortColumns)) {
            $sortBy = 'waktu_assesment';
        }
        if (!in_array(strtolower($sortOrder), ['asc', 'desc'])) {
            $sortOrder = 'desc';
        }

        $assessments = $query->orderBy($sortBy, $sortOrder)->paginate(15);

        return $this->apiPaginatedResponse($assessments, AssessmentResource::class);
    }

    public function store(StoreAssessmentRequest $request): JsonResponse
    {
        $data = $request->validated();
        $insiden = OperasiInsiden::where('uuid_insiden', $data['uuid_insiden'])->firstOrFail();
        $data['id_insiden'] = $insiden->id_insiden;

        $this->authorize('create', [AssessmentUtama::class, $insiden]);

        $assessment = $this->service->createAssessment($data);
        $assessment->load(['dampakManusiaV2', 'kebutuhanMendesak', 'kebutuhanNumerik']);

        return $this->apiResponse(new AssessmentResource($assessment), 'Data berhasil disimpan', 201);
    }

    public function show(AssessmentUtama $assessment, Request $request): JsonResponse
    {
        $this->authorize('view', $assessment);
        $assessment->loadMissing(['insiden', 'dampakManusiaV2', 'kebutuhanMendesak', 'kebutuhanNumerik', 'petugas.profil']);
        return $this->apiResponse(new AssessmentResource($assessment));
    }

    public function update(Request $request, AssessmentUtama $assessment): JsonResponse
    {
        $this->authorize('update', $assessment);

        $validated = $request->validate([
            'cakupan_wilayah_deskripsi' => 'nullable|string',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'jenis_laporan' => 'sometimes|in:awal,lanjutan',
        ]);

        $assessment->update($validated);

        return $this->apiResponse(new AssessmentResource($assessment->fresh(['dampakManusiaV2', 'kebutuhanMendesak', 'kebutuhanNumerik'])), 'Assessment diperbarui');
    }

    public function destroy(AssessmentUtama $assessment): JsonResponse
    {
        $this->authorize('delete', $assessment);
        $assessment->delete();
        return response()->json(['message' => 'Assessment dihapus.']);
    }

    public function dampakIndex(AssessmentUtama $assessment): JsonResponse
    {
        $this->authorize('view', $assessment);
        return response()->json(['data' => $assessment->dampakManusia()->get()]);
    }

    public function dampakStore(Request $request, AssessmentUtama $assessment): JsonResponse
    {
        $this->authorize('update', $assessment);
        $validated = $request->validate([
            'meninggal' => 'nullable|integer|min:0',
            'hilang' => 'nullable|integer|min:0',
            'luka_berat' => 'nullable|integer|min:0',
            'luka_ringan' => 'nullable|integer|min:0',
            'menderita_mengungsi' => 'nullable|integer|min:0',
        ]);
        $dampak = $assessment->dampakManusia()->create($validated);
        return response()->json(['message' => 'Data dampak ditambahkan.', 'data' => $dampak], 201);
    }

    public function dampakUpdate(Request $request, AssessmentUtama $assessment, AssessmentDampakManusia $dampak): JsonResponse
    {
        $this->authorize('update', $assessment);
        if ($dampak->id_assessment_utama !== $assessment->id_assessment_utama) {
            return response()->json(['message' => 'Not found'], 404);
        }
        $dampak->update($request->validate([
            'meninggal' => 'nullable|integer|min:0',
            'hilang' => 'nullable|integer|min:0',
            'luka_berat' => 'nullable|integer|min:0',
            'luka_ringan' => 'nullable|integer|min:0',
            'menderita_mengungsi' => 'nullable|integer|min:0',
        ]));
        return response()->json(['message' => 'Data dampak diperbarui.']);
    }

    public function dampakDestroy(AssessmentUtama $assessment, AssessmentDampakManusia $dampak): JsonResponse
    {
        $this->authorize('update', $assessment);
        if ($dampak->id_assessment_utama !== $assessment->id_assessment_utama) {
            return response()->json(['message' => 'Not found'], 404);
        }
        $dampak->delete();
        return response()->json(['message' => 'Data dampak dihapus.']);
    }

    public function kebutuhanIndex(AssessmentUtama $assessment): JsonResponse
    {
        $this->authorize('view', $assessment);
        return response()->json(['data' => $assessment->kebutuhanMendesak()->get()]);
    }

    public function kebutuhanStore(Request $request, AssessmentUtama $assessment): JsonResponse
    {
        $this->authorize('update', $assessment);
        $validated = $request->validate([
            'nama_kebutuhan' => 'required|string|max:200',
            'jumlah' => 'required|integer|min:1',
            'satuan' => 'nullable|string|max:50',
        ]);
        $kebutuhan = $assessment->kebutuhanMendesak()->create($validated);
        return response()->json(['message' => 'Kebutuhan ditambahkan.', 'data' => $kebutuhan], 201);
    }

    public function kebutuhanUpdate(Request $request, AssessmentUtama $assessment, AssessmentKebutuhanMendesak $kebutuhan): JsonResponse
    {
        $this->authorize('update', $assessment);
        if ($kebutuhan->id_assessment_utama !== $assessment->id_assessment_utama) {
            return response()->json(['message' => 'Not found'], 404);
        }
        $kebutuhan->update($request->validate([
            'nama_kebutuhan' => 'sometimes|string|max:200',
            'jumlah' => 'sometimes|integer|min:1',
            'satuan' => 'nullable|string|max:50',
        ]));
        return response()->json(['message' => 'Kebutuhan diperbarui.']);
    }

    public function kebutuhanDestroy(AssessmentUtama $assessment, AssessmentKebutuhanMendesak $kebutuhan): JsonResponse
    {
        $this->authorize('update', $assessment);
        if ($kebutuhan->id_assessment_utama !== $assessment->id_assessment_utama) {
            return response()->json(['message' => 'Not found'], 404);
        }
        $kebutuhan->delete();
        return response()->json(['message' => 'Kebutuhan dihapus.']);
    }

    public function downloadPdf(AssessmentUtama $assessment)
    {
        $this->authorize('view', $assessment);

        $pdfService = app(\App\Services\SuratPdfService::class);
        $path = $pdfService->generateAssessmentOnlyPdf($assessment);

        return \Illuminate\Support\Facades\Storage::download($path);
    }
}
