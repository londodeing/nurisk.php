<?php

namespace App\Http\Controllers\Api\Relawan;

use App\Http\Controllers\Controller;
use App\Http\Requests\Relawan\ApproveRelawanRequest;
use App\Http\Requests\Relawan\AssignRelawanRequest;
use App\Http\Requests\Relawan\DaftarRelawanRequest;
use App\Http\Requests\Relawan\RejectRelawanRequest;
use App\Http\Resources\Relawan\RelawanPendaftaranResource;
use App\Http\Resources\Relawan\RelawanPenugasanResource;
use App\Models\RelawanPendaftaran;
use App\Services\Relawan\RelawanService;

class RelawanPendaftaranController extends Controller
{
    protected RelawanService $service;

    public function __construct(RelawanService $service)
    {
        $this->service = $service;
    }

    public function daftar(DaftarRelawanRequest $request)
    {
        $pendaftaran = $this->service->registerVolunteer(
            auth()->id(),
            $request->id_relawan_kebutuhan,
            $request->motivasi_singkat ?? ''
        );

        $pendaftaran->load(['kebutuhan', 'relawan.profil']);

        return (new RelawanPendaftaranResource($pendaftaran))
            ->response()
            ->setStatusCode(201);
    }

    public function approve(ApproveRelawanRequest $request, RelawanPendaftaran $pendaftaran)
    {
        $pendaftaran = $this->service->approveRegistration($pendaftaran->id_pendaftaran);
        $pendaftaran->load(['kebutuhan', 'relawan.profil']);
        return new RelawanPendaftaranResource($pendaftaran);
    }

    public function reject(RejectRelawanRequest $request, RelawanPendaftaran $pendaftaran)
    {
        $pendaftaran = $this->service->rejectRegistration(
            $pendaftaran->id_pendaftaran,
            $request->catatan_verifikator
        );
        $pendaftaran->load(['kebutuhan', 'relawan.profil']);
        return new RelawanPendaftaranResource($pendaftaran);
    }

    public function assign(AssignRelawanRequest $request, RelawanPendaftaran $pendaftaran)
    {
        $penugasan = $this->service->assignVolunteer(
            $pendaftaran->id_pendaftaran,
            $request->id_posaju,
            $request->peran_lapangan
        );
        $penugasan->load(['posaju']);
        return new RelawanPenugasanResource($penugasan);
    }
}
