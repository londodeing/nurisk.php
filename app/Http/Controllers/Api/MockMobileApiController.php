<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class MockMobileApiController extends Controller
{
    /**
     * Endpoint Mock Login
     * POST /api/auth/login
     */
    public function login(Request $request)
    {
        // Ignore real validation, just return dummy payload
        // The payload must match what Flutter expects
        return response()->json([
            'status' => 'success',
            'message' => 'Mock login berhasil',
            'data' => [
                'token' => 'mock_jwt_token_1234567890',
                'user_id' => '999',
                'name' => 'Fulan bin Fulan (Mock)',
                'role' => 'pcnu', // Lapis 1
                'scope_id' => 1, // Lapis 3
                'scope_type' => 'pcnu',
                'jabatan_name' => 'Ketua PCNU Kudus (Mock)', // Lapis 2
                'mandates' => [
                    [
                        'id' => 'm1',
                        'role' => 'Ketua PCNU (Mock)',
                        'territory' => 'Kudus'
                    ],
                    [
                        'id' => 'm2',
                        'role' => 'Komandan Posko (Mock)',
                        'territory' => 'Demak'
                    ]
                ]
            ]
        ], 200);
    }

    /**
     * Endpoint Mock Register
     * POST /api/auth/register
     */
    public function register(Request $request)
    {
        return response()->json([
            'status' => 'success',
            'message' => 'Pendaftaran akun publik berhasil (Mock).'
        ], 200);
    }

    /**
     * Endpoint Mock Governance Pending Decisions
     * GET /api/governance/pending
     */
    public function pendingDecisions(Request $request)
    {
        return response()->json([
            'status' => 'success',
            'data' => [
                [
                    'id' => 101,
                    'title' => '1. Pleno Bencana Pandeglang',
                    'sla' => 'Merah' // >24 Jam
                ],
                [
                    'id' => 102,
                    'title' => '2. Surat Tugas TRC Tim Alpha',
                    'sla' => 'Hijau' // <12 Jam
                ],
                [
                    'id' => 103,
                    'title' => '3. Surat Peminjaman Genset',
                    'sla' => 'Kuning'
                ]
            ]
        ], 200);
    }

    /**
     * Endpoint Mock Governance Process Decision
     * POST /api/governance/process
     */
    public function processDecision(Request $request)
    {
        // Expects: decision_id, action, notes
        return response()->json([
            'status' => 'success',
            'message' => 'Dokumen berhasil diproses (Mock).'
        ], 200);
    }
}
