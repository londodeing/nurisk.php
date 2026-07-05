<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\AssessmentUtama;
use App\Models\Assessment\AssessmentRingkasanSkor;
use App\Models\Assessment\AssessmentSkorItem;
use App\Services\AssessmentScoringService;

class AssessmentSkorController extends Controller
{
    protected $scoringService;

    public function __construct(AssessmentScoringService $scoringService)
    {
        $this->scoringService = $scoringService;
    }

    public function store(Request $request, $insidenId, $assessmentId, $indikatorId)
    {
        $validated = $request->validate([
            'skor_1_5' => 'required|integer|min:1|max:5',
            'catatan' => 'nullable|string'
        ]);

        $item = AssessmentSkorItem::updateOrCreate(
            [
                'id_assessment' => $assessmentId,
                'id_indikator' => $indikatorId
            ],
            [
                'skor_1_5' => $validated['skor_1_5'],
                'catatan' => $validated['catatan'],
                'dinilai_oleh' => auth()->id()
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Skor item berhasil disimpan',
            'data' => $item
        ]);
    }

    public function hitung(Request $request, $insidenId, $assessmentId)
    {
        $assessment = AssessmentUtama::findOrFail($assessmentId);
        
        try {
            $ringkasan = $this->scoringService->hitungDanSimpan($assessment);
            return response()->json([
                'success' => true,
                'message' => 'Skor berhasil dikalkulasi',
                'data' => $ringkasan
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengkalkulasi skor: ' . $e->getMessage()
            ], 500);
        }
    }

    public function ringkasan($insidenId, $assessmentId)
    {
        $assessment = AssessmentUtama::findOrFail($assessmentId);
        
        $ringkasan = AssessmentRingkasanSkor::where('id_assessment', $assessmentId)->first();
        if (!$ringkasan) {
            // Auto calculate if doesn't exist
            $ringkasan = $this->scoringService->hitungDanSimpan($assessment);
        }

        $items = AssessmentSkorItem::with('indikator')->where('id_assessment', $assessmentId)->get();

        return response()->json([
            'success' => true,
            'data' => [
                'ringkasan' => $ringkasan,
                'items' => $items
            ]
        ]);
    }
}
