<?php

namespace App\Http\Controllers\Api\Operasi;

use App\Http\Controllers\Controller;
use App\Http\Resources\Operasi\OperasiDistribusiResource;
use App\Http\Resources\Operasi\OperasiDistribusiCollection;
use App\Http\Requests\Operasi\StoreDistribusiRequest;
use App\Http\Requests\Operasi\StoreFeedbackDistribusiRequest;
use App\Models\OperasiDistribusi;
use App\Models\OperasiFeedbackDistribusi;
use App\Models\OperasiInsiden;
use App\Models\OperasiPosaju;
use Illuminate\Http\JsonResponse;

class DistribusiApiController extends Controller
{
    public function index(Request $request, OperasiInsiden $insiden, OperasiPosaju $posaju): OperasiDistribusiCollection
    {
        $this->authorize('viewAny', OperasiDistribusi::class);

        $distribusis = OperasiDistribusi::with(['klasterOperasi.masterKlaster', 'feedback.pengguna.profil'])
            ->where('id_posaju', $posaju->id_posaju)
            ->when($request->status, fn($q, $v) => $q->where('status_distribusi', $v))
            ->when($request->klaster, fn($q, $v) => $q->where('id_klaster_operasi', $v))
            ->orderBy('dibuat_pada', 'desc')
            ->paginate($request->get('per_page', 15));

        return new OperasiDistribusiCollection($distribusis);
    }

    public function store(StoreDistribusiRequest $request, OperasiInsiden $insiden, OperasiPosaju $posaju): JsonResponse
    {
        $this->authorize('create', [OperasiDistribusi::class, $posaju]);

        $distribusi = OperasiDistribusi::create([
            'uuid_distribusi' => \Illuminate\Support\Str::uuid(),
            'id_posaju' => $posaju->id_posaju,
            'id_klaster_operasi' => $request->id_klaster_operasi,
            'id_penugasan' => $request->id_penugasan,
            'id_barang_katalog' => $request->id_barang_katalog,
            'nama_barang' => $request->nama_barang,
            'jumlah' => $request->jumlah,
            'satuan' => $request->satuan,
            'lokasi_tujuan' => $request->lokasi_tujuan,
            'penerima' => $request->penerima,
            'waktu_distribusi' => $request->waktu_distribusi,
            'status_distribusi' => 'direncanakan',
            'dibuat_oleh' => auth()->id(),
        ]);

        return (new OperasiDistribusiResource($distribusi->load(['klasterOperasi.masterKlaster', 'feedback'])))
            ->additional(['message' => 'Distribusi berhasil direncanakan'])
            ->response()
            ->setStatusCode(201);
    }

    public function show(OperasiInsiden $insiden, OperasiPosaju $posaju, OperasiDistribusi $distribusi): OperasiDistribusiResource
    {
        $this->authorize('view', $distribusi);
        $distribusi->load(['klasterOperasi.masterKlaster', 'feedback.pengguna.profil']);
        return new OperasiDistribusiResource($distribusi);
    }

    public function feedback(StoreFeedbackDistribusiRequest $request, OperasiInsiden $insiden, OperasiPosaju $posaju, OperasiDistribusi $distribusi): JsonResponse
    {
        $this->authorize('update', $distribusi);

        if ($distribusi->status_distribusi !== 'diterima') {
            return response()->json(['message' => 'Feedback hanya untuk distribusi yang sudah Diterima'], 422);
        }

        if ($distribusi->feedback) {
            return response()->json(['message' => 'Feedback sudah ada'], 422);
        }

        $feedback = OperasiFeedbackDistribusi::create([
            'id_distribusi' => $distribusi->id_distribusi,
            'id_pengguna' => auth()->id(),
            'kecukupan' => $request->kecukupan,
            'kualitas' => $request->kualitas,
            'tepat_waktu' => $request->boolean('tepat_waktu'),
            'tepat_sasaran' => $request->boolean('tepat_sasaran'),
            'kendala' => $request->kendala,
            'rekomendasi' => $request->rekomendasi,
            'status_feedback' => 'final',
            'dikunci_pada' => now(),
        ]);

        $distribusi->update(['status_distribusi' => 'direview']);

        return (new OperasiDistribusiResource($distribusi->load(['klasterOperasi.masterKlaster', 'feedback.pengguna.profil'])))
            ->additional(['message' => 'Feedback berhasil dikirim dan terkunci'])
            ->response();
    }
}