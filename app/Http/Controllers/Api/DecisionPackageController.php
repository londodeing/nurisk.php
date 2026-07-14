<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DecisionPackageController extends Controller
{
    /**
     * Get a fully aggregated Decision Package for Executive Review.
     */
    public function show(Request $request, $id): JsonResponse
    {
        // Mock Aggregation of the Decision Package
        // In reality, this queries DecisionDraft, joins the relevant domain object (e.g. Incident, Laporan),
        // fetches the operational timeline from digital twin, and fetches attachments.
        
        $package = [
            'id' => $id,
            'metadata' => [
                'title' => 'SPK Assessment Banjir Kudus',
                'type' => 'SPK Assessment',
                'status' => 'VERIFIED',
                'severity' => 'HIGH',
                'drafted_at' => now()->subMinutes(30)->toIso8601String(),
                'drafted_by' => 'Admin Posko Kudus',
            ],
            'timeline' => [
                [ 'time' => now()->subHours(2)->format('H:i'), 'event' => 'Laporan diterima' ],
                [ 'time' => now()->subHours(1)->subMinutes(30)->format('H:i'), 'event' => 'TRC berangkat' ],
                [ 'time' => now()->subMinutes(45)->format('H:i'), 'event' => 'Assessment selesai' ],
                [ 'time' => now()->subMinutes(30)->format('H:i'), 'event' => 'Draft SPK dibuat Admin' ]
            ],
            'location' => [
                'lat' => -6.804,
                'lng' => 110.843,
                'polygon_geojson' => null
            ],
            'assessment' => [
                'impact' => [
                    'korban' => '0',
                    'kerusakan' => '18 Rumah, 2 Sekolah, 1 Jembatan',
                    'kebutuhan' => 'Air bersih, Makanan Siap Saji'
                ],
                'attachments' => [
                    [ 'type' => 'pdf', 'url' => 'https://example.com/draft.pdf', 'name' => 'Draft_SPK.pdf' ],
                    [ 'type' => 'image', 'url' => 'https://example.com/foto.jpg', 'name' => 'Kondisi_Lapang.jpg' ]
                ]
            ],
            'admin_recommendation' => [
                'Penerbitan SPK Assessment Resmi',
                'Tetapkan Status Darurat',
                'Mobilisasi Ambulans PCNU Kudus'
            ],
            'permitted_actions' => [
                'APPROVE',
                'REVISION',
                'REJECT'
            ]
        ];

        return response()->json([
            'status' => 'success',
            'data' => $package
        ]);
    }
}
