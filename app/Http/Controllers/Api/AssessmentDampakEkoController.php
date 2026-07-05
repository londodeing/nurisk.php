<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AssessmentUtama;
use App\Models\OperasiInsiden;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AssessmentDampakEkoController extends Controller
{
    public function update(Request $request, OperasiInsiden $insiden, AssessmentUtama $assessment): JsonResponse
    {
        $this->authorize('update', $assessment);

        $validated = $request->validate([
            'kerugian_perumahan' => 'nullable|numeric|min:0',
            'kerugian_pertanian' => 'nullable|numeric|min:0',
            'kerugian_peternakan' => 'nullable|numeric|min:0',
            'kerugian_perikanan' => 'nullable|numeric|min:0',
            'kerugian_umkm' => 'nullable|numeric|min:0',
            'kerugian_infrastruktur' => 'nullable|numeric|min:0',
            'kerugian_lainnya' => 'nullable|numeric|min:0',
            'estimasi_kerugian_total' => 'nullable|numeric|min:0',
            'mata_pencaharian_hilang' => 'nullable|integer|min:0',
            'usaha_terdampak' => 'nullable|integer|min:0',
        ]);

        $assessment->dampakEkonomi()->updateOrCreate(
            ['id_assessment' => $assessment->id_assessment_utama],
            $validated
        );

        return response()->json(['message' => 'Data ekonomi diperbarui.']);
    }
}
