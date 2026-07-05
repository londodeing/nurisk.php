<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Assessment\AssessmentBiodataKejadian;

class AssessmentBiodataController extends Controller
{
    public function store(Request $request, $insidenId, $assessmentId)
    {
        $validated = $request->validate([
            'desa_terdampak' => 'required|integer',
            'rt_rw_terdampak' => 'required|integer',
            'cakupan_wilayah_km2' => 'nullable|numeric',
            'akses_jalan_utama' => 'required|string',
            'kondisi_cuaca' => 'nullable|string',
            'sumber_informasi' => 'nullable|string',
        ]);

        $biodata = AssessmentBiodataKejadian::updateOrCreate(
            ['id_assessment' => $assessmentId],
            $validated
        );

        return response()->json([
            'success' => true,
            'message' => 'Biodata kejadian berhasil disimpan',
            'data' => $biodata
        ]);
    }
}
