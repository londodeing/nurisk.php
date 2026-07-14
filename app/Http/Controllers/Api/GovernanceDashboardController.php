<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GovernanceDashboardController extends Controller
{
    /**
     * Get pending decisions for the currently authenticated executive.
     * In the MVP, we mock the logic since authentication context might not be fully seeded.
     */
    public function pendingDecisions(Request $request): JsonResponse
    {
        // Mock data representing the Drafts awaiting approval from the Chairman
        $pending = [
            [
                'id' => 'DEC-01-001',
                'type' => 'SPK Assessment',
                'title' => 'SPK Assessment Banjir Kudus',
                'urgency' => 'HIGH',
                'submitted_at' => now()->subHours(2)->toIso8601String(),
                'submitted_by' => 'Admin Posko Kudus',
                'action_type' => 'Approve/Reject'
            ],
            [
                'id' => 'DEC-02-001',
                'type' => 'Aktivasi Posko',
                'title' => 'Aktivasi Posko Darurat Demak',
                'urgency' => 'MEDIUM',
                'submitted_at' => now()->subMinutes(45)->toIso8601String(),
                'submitted_by' => 'Admin Operasi',
                'action_type' => 'Approve/Reject'
            ],
            [
                'id' => 'DEC-04-001',
                'type' => 'Mobilisasi Relawan',
                'title' => 'Mobilisasi Ambulans Jepara',
                'urgency' => 'HIGH',
                'submitted_at' => now()->subMinutes(30)->toIso8601String(),
                'submitted_by' => 'Admin Operasi',
                'action_type' => 'Approve/Reject'
            ]
        ];

        return response()->json([
            'status' => 'success',
            'data' => $pending
        ]);
    }

    /**
     * Get the Governance Timeline Feed (Recent completed tasks/decisions).
     */
    public function timeline(Request $request): JsonResponse
    {
        // Mock data representing the timeline of events that have been fully executed
        $timeline = [
            [
                'id' => 'TL-1',
                'time' => now()->subMinutes(15)->toIso8601String(),
                'title' => 'SPK Kudus disetujui',
                'type' => 'DECISION_FINALIZED',
                'actor' => 'Ketua LPBI',
            ],
            [
                'id' => 'TL-2',
                'time' => now()->subMinutes(30)->toIso8601String(),
                'title' => 'Surat BNPB ditandatangani',
                'type' => 'DOCUMENT_SIGNED',
                'actor' => 'Ketua LPBI',
            ],
            [
                'id' => 'TL-3',
                'time' => now()->subHours(1)->toIso8601String(),
                'title' => 'Posko Demak aktif',
                'type' => 'EXECUTION_DONE',
                'actor' => 'Komandan Posko',
            ],
            [
                'id' => 'TL-4',
                'time' => now()->subHours(1)->subMinutes(20)->toIso8601String(),
                'title' => 'Assessment Banjir Kudus selesai',
                'type' => 'EXECUTION_DONE',
                'actor' => 'TRC Kudus',
            ]
        ];

        return response()->json([
            'status' => 'success',
            'data' => $timeline
        ]);
    }
}
