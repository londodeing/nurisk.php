<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AssessmentUtama;
use App\Services\Operasi\AssessmentService;
use Illuminate\Http\JsonResponse;

class AssessmentLengkapController extends Controller
{
    private AssessmentService $service;

    public function __construct(AssessmentService $service)
    {
        $this->service = $service;
    }

    public function show(AssessmentUtama $assessment): JsonResponse
    {
        $this->authorize('view', $assessment);

        $assessment->loadMissing($this->service->defaultRelations());

        return response()->json([
            'data' => $assessment->toArray(),
            'form_data' => $this->service->keFormData($assessment),
        ]);
    }
}
