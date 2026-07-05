<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AssessmentUtama;
use App\Models\OperasiInsiden;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AssessmentNarasiController extends Controller
{
    public function store(Request $request, OperasiInsiden $insiden, AssessmentUtama $assessment): JsonResponse
    {
        $this->authorize('update', $assessment);

        $validated = $request->validate([
            'fase' => 'required|in:pra_bencana,saat_bencana,pasca_bencana',
            'judul_narasi' => 'required|string|max:255',
            'isi_narasi' => 'required|string',
            'sumber_data' => 'nullable|string|max:255',
        ]);

        $narasi = $assessment->narasiKejadian()->create($validated);

        return response()->json(['message' => 'Narasi berhasil disimpan.', 'data' => $narasi], 201);
    }
}
