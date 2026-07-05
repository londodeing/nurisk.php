<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;

class BmkgProxyController extends Controller
{
    public function gempa(): JsonResponse
    {
        try {
            $response = Http::timeout(10)
                ->get('https://data.bmkg.go.id/DataMKG/TEWS/gempaterkini.json');

            if ($response->successful()) {
                return response()->json($response->json());
            }

            return response()->json(['error' => 'BMKG API tidak tersedia'], 502);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Gagal menghubungi BMKG: ' . $e->getMessage()], 502);
        }
    }
}
