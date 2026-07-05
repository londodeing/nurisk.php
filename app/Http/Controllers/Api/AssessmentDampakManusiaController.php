<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AssessmentUtama;
use App\Models\OperasiInsiden;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AssessmentDampakManusiaController extends Controller
{
    public function update(Request $request, OperasiInsiden $insiden, AssessmentUtama $assessment): JsonResponse
    {
        $this->authorize('update', $assessment);

        $validated = $request->validate([
            'meninggal' => 'nullable|integer|min:0',
            'hilang' => 'nullable|integer|min:0',
            'luka_berat' => 'nullable|integer|min:0',
            'luka_ringan' => 'nullable|integer|min:0',
            'terdampak_jiwa' => 'nullable|integer|min:0',
            'terdampak_kk' => 'nullable|integer|min:0',
            'pengungsi_jiwa' => 'nullable|integer|min:0',
            'pengungsi_kk' => 'nullable|integer|min:0',
            'pengungsi_balita' => 'nullable|integer|min:0',
            'pengungsi_lansia' => 'nullable|integer|min:0',
            'pengungsi_disabilitas' => 'nullable|integer|min:0',
            'pengungsi_ibu_hamil' => 'nullable|integer|min:0',
        ]);

        $dampak = $assessment->dampakManusiaV2()->updateOrCreate(
            ['id_assessment' => $assessment->id_assessment_utama],
            $validated
        );

        return response()->json(['message' => 'Data dampak manusia diperbarui.', 'data' => $dampak]);
    }
}
