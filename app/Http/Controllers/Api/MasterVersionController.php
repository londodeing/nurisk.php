<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WilayahKabupaten;
use App\Models\WilayahKecamatan;
use App\Models\WilayahDesa;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MasterVersionController extends Controller
{
    public function version(): JsonResponse
    {
        $kabCount = WilayahKabupaten::count();
        $kecCount = WilayahKecamatan::count();
        $desaCount = WilayahDesa::count();

        return response()->json([
            'app_version'    => config('app.version', '1.0.0'),
            'master_version' => '2026.07.01',
            'wilayah_version' => '2026.07.01',
            'tier_b_tables'  => [
                'kabupaten' => ['version' => '1.0', 'rows' => $kabCount],
                'kecamatan' => ['version' => '1.0', 'rows' => $kecCount],
                'desa'      => ['version' => '1.0', 'rows' => $desaCount],
            ],
        ]);
    }

    public function delta(Request $request): JsonResponse
    {
        $request->validate([
            'from' => ['required', 'string'],
            'to'   => ['required', 'string'],
        ]);

        $from = $request->input('from');
        $to   = $request->input('to');

        return response()->json([
            'version_from' => $from,
            'version_to'   => $to,
            'updates'      => [
                'kabupaten' => [],
                'kecamatan' => [],
                'desa'      => [],
            ],
            'deletes'      => [
                'kabupaten' => [],
                'kecamatan' => [],
                'desa'      => [],
            ],
            'message'      => 'Snapshot saat ini sudah yang terbaru. Belum ada delta perubahan.',
        ]);
    }
}
