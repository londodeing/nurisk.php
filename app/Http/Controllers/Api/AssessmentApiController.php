<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAssessmentRequest;
use App\Http\Requests\UpdateAssessmentRequest;
use App\Http\Resources\AssessmentLengkapResource;
use App\Models\OperasiInsiden;
use App\Models\AssessmentUtama;
use App\Models\Assessment\AssessmentKebutuhanNumerikMaster;
use App\Services\Operasi\AssessmentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Auth\Access\AuthorizationException;

class AssessmentApiController extends Controller
{
    private AssessmentService $assessmentService;

    public function __construct(AssessmentService $assessmentService)
    {
        $this->assessmentService = $assessmentService;
    }

    /**
     * POST /api/insiden/{insiden}/assessment
     * Body: semua field form dalam satu request (flat JSON)
     */
    public function store(StoreAssessmentRequest $request, OperasiInsiden $insiden): JsonResponse
    {
        $this->authorize('create', [AssessmentUtama::class, $insiden]);
        $user = $request->user();
        if (!$user) {
            throw new AuthorizationException('Authentication required.');
        }
        $assessment = $this->assessmentService->simpanLengkap(
            $request->validated(), $insiden, $user
        );
        return (new AssessmentLengkapResource($assessment))->response()->setStatusCode(201);
    }

    /**
     * GET /api/insiden/{insiden}/assessment/{assessment}
     * Return: seluruh data assessment dalam format form-compatible
     */
    public function show(OperasiInsiden $insiden, AssessmentUtama $assessment): JsonResponse
    {
        $this->authorize('view', $assessment);
        $assessment->load($this->assessmentService->defaultRelations());
        return response()->json([
            'data'      => new AssessmentLengkapResource($assessment),
            'form_data' => $this->assessmentService->keFormData($assessment),
        ]);
    }

    /**
     * PUT /api/insiden/{insiden}/assessment/{assessment}
     * Sama dengan store tapi update (upsert semua extension tables)
     */
    public function update(UpdateAssessmentRequest $request, OperasiInsiden $insiden, AssessmentUtama $assessment): JsonResponse
    {
        $this->authorize('update', $assessment);
        $assessment = $this->assessmentService->updateLengkap(
            $request->validated(), $assessment
        );
        return response()->json(new AssessmentLengkapResource($assessment));
    }

    /**
     * POST /api/insiden/{insiden}/assessment/{assessment}/submit
     */
    public function submit(Request $request, OperasiInsiden $insiden, AssessmentUtama $assessment): JsonResponse
    {
        $this->authorize('submit', $assessment);

        if ($assessment->status_review !== 'draft') {
            return response()->json(['message' => 'Assessment sudah di-submit.'], 422);
        }

        $assessment->update(['status_review' => 'submitted']);

        return response()->json(['message' => 'Assessment diajukan untuk review.']);
    }

    /**
     * POST /api/insiden/{insiden}/assessment/{assessment}/review
     */
    public function review(Request $request, OperasiInsiden $insiden, AssessmentUtama $assessment): JsonResponse
    {
        $validated = $request->validate([
            'action'        => 'required|in:approved,rejected',
            'catatan_review' => 'required_if:action,rejected|nullable|string|max:1000',
        ]);

        if ($assessment->status_review !== 'submitted') {
            return response()->json(['message' => 'Assessment belum di-submit.'], 422);
        }

        $this->authorize($validated['action'] === 'approved' ? 'approve' : 'reject', $assessment);

        $assessment->update([
            'status_review'  => $validated['action'] === 'approved' ? 'in_review' : 'rejected',
            'catatan_review' => $validated['catatan_review'] ?? null,
            'id_reviewer'    => $request->user()->id_pengguna,
            'waktu_review'   => now(),
        ]);

        $message = $validated['action'] === 'approved'
            ? 'Assessment disetujui dan masuk tahap review.'
            : 'Assessment ditolak.';

        return response()->json(['message' => $message]);
    }

    /**
     * GET /api/master/kebutuhan-numerik
     * Return: list master items untuk populate checkbox di form
     */
    public function masterKebutuhanNumerik(): JsonResponse
    {
        $items = AssessmentKebutuhanNumerikMaster::where('aktif', 1)
            ->orderBy('kategori')
            ->orderBy('urutan')
            ->get()
            ->groupBy('kategori');
        return response()->json(['data' => $items]);
    }
}
