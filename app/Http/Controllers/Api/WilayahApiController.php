<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\MasterData\MasterDataService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class WilayahApiController extends Controller
{
    /**
     * Injeksi MasterDataService
     */
    public function __construct(private MasterDataService $masterDataService) {}

    /**
     * GET /api/wilayah/kabupaten
     * Daftar seluruh kabupaten/kota Jawa Tengah (flat array untuk dropdown)
     */
    public function kabupaten(Request $request): JsonResponse
    {
        $kabupaten = $this->masterDataService->getKabupatenList();
        return response()->json(
            $kabupaten->map(fn($k) => [
                'id_kab'  => $k->id_kab,
                'nama_kab' => $k->tipe . ' ' . $k->nama_kab,
            ])->values()->toArray()
        );
    }

    /**
     * GET /api/wilayah/kecamatan?id_kab=3301
     * Daftar kecamatan berdasarkan kabupaten (flat array untuk dropdown)
     */
    public function kecamatan(Request $request): JsonResponse
    {
        $request->validate([
            'id_kab' => ['required', 'string', 'size:4', 'exists:wilayah_kabupaten,id_kab']
        ]);

        $kecamatan = $this->masterDataService->getKecamatanByKabupaten($request->id_kab);
        return response()->json(
            $kecamatan->map(fn($k) => [
                'id_kec'  => $k->id_kec,
                'nama_kec' => $k->nama_kec,
            ])->values()->toArray()
        );
    }

    /**
     * GET /api/wilayah/desa?id_kec=330101
     * Daftar desa berdasarkan kecamatan (flat array untuk dropdown)
     */
    public function desa(Request $request): JsonResponse
    {
        $request->validate([
            'id_kec' => ['required', 'string', 'size:6', 'exists:wilayah_kecamatan,id_kec']
        ]);

        $desa = $this->masterDataService->getDesaByKecamatan($request->id_kec);
        return response()->json(
            $desa->map(fn($d) => [
                'id_desa'  => $d->id_desa,
                'nama_desa' => $d->nama_desa,
            ])->values()->toArray()
        );
    }

    /**
     * GET /api/wilayah/pcnu
     * Daftar seluruh PCNU (untuk dropdown scope selector)
     */
    public function pcnu(): JsonResponse
    {
        $pcnu = $this->masterDataService->getPcnuList();
        return response()->json([
            'data' => $pcnu->map(fn($p) => [
                'id'   => $p->id_pcnu,
                'nama' => $p->nama_pcnu,
            ]),
        ]);
    }
}
