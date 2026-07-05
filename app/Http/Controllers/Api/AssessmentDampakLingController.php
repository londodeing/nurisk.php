<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AssessmentUtama;
use App\Models\OperasiInsiden;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AssessmentDampakLingController extends Controller
{
    public function update(Request $request, OperasiInsiden $insiden, AssessmentUtama $assessment): JsonResponse
    {
        $this->authorize('update', $assessment);

        $validated = $request->validate([
            'lahan_pertanian_rusak_ha' => 'nullable|numeric|min:0',
            'hutan_terdampak_ha' => 'nullable|numeric|min:0',
            'lahan_tercemar_ha' => 'nullable|numeric|min:0',
            'sumber_air_tercemar' => 'nullable|boolean',
            'ternak_terdampak_ekor' => 'nullable|integer|min:0',
        ]);

        $assessment->dampakLingkungan()->updateOrCreate(
            ['id_assessment' => $assessment->id_assessment_utama],
            $validated
        );

        return response()->json(['message' => 'Data lingkungan diperbarui.']);
    }
}
