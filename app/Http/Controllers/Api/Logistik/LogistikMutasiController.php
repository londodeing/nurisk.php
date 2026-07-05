<?php

namespace App\Http\Controllers\Api\Logistik;

use App\Http\Controllers\Controller;
use App\Http\Requests\Logistik\StoreMutasiRequest;
use App\Models\LogistikStok;
use App\Services\LogistikMutasiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class LogistikMutasiController extends Controller
{
    protected LogistikMutasiService $mutasiService;

    public function __construct(LogistikMutasiService $mutasiService)
    {
        $this->mutasiService = $mutasiService;
    }

    public function store(StoreMutasiRequest $request): JsonResponse
    {
        $stok = LogistikStok::findOrFail($request->id_stok);

        if ($request->tipe_mutasi === 'keluar') {
            Gate::authorize('mutasiKeluar', [LogistikStok::class, $stok]);
        }

        try {
            $mutasi = $this->mutasiService->catatMutasi(
                $request->validated(),
                Auth::id()
            );

            return response()->json([
                'message' => 'Mutasi berhasil dicatat.',
                'data' => $mutasi
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal mencatat mutasi: ' . $e->getMessage()
            ], 400);
        }
    }
}
