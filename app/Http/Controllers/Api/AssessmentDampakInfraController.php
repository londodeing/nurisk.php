<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AssessmentUtama;
use App\Models\OperasiInsiden;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AssessmentDampakInfraController extends Controller
{
    public function update(Request $request, OperasiInsiden $insiden, AssessmentUtama $assessment): JsonResponse
    {
        $this->authorize('update', $assessment);

        $validated = $request->validate([
            'rumah_rusak_berat' => 'nullable|integer|min:0',
            'rumah_rusak_sedang' => 'nullable|integer|min:0',
            'rumah_rusak_ringan' => 'nullable|integer|min:0',
            'rumah_terendam' => 'nullable|integer|min:0',
            'jalan_rusak_km' => 'nullable|numeric|min:0',
            'jembatan_putus' => 'nullable|integer|min:0',
            'jembatan_rusak' => 'nullable|integer|min:0',
            'fasilitas_kesehatan_rusak' => 'nullable|integer|min:0',
            'fasilitas_pendidikan_rusak' => 'nullable|integer|min:0',
            'tempat_ibadah_rusak' => 'nullable|integer|min:0',
            'kantor_pemerintah_rusak' => 'nullable|integer|min:0',
        ]);

        $assessment->dampakInfrastruktur()->updateOrCreate(
            ['id_assessment' => $assessment->id_assessment_utama],
            $validated
        );

        return response()->json(['message' => 'Data infrastruktur diperbarui.']);
    }
}
