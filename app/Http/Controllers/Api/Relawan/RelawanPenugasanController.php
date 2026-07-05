<?php

namespace App\Http\Controllers\Api\Relawan;

use App\Http\Controllers\Controller;
use App\Http\Resources\Relawan\RelawanPenugasanResource;
use App\Models\RelawanPenugasan;
use App\Services\Relawan\RelawanService;
use Illuminate\Support\Facades\Gate;

class RelawanPenugasanController extends Controller
{
    protected RelawanService $service;

    public function __construct(RelawanService $service)
    {
        $this->service = $service;
    }

    public function complete(RelawanPenugasan $penugasan)
    {
        Gate::authorize('completePenugasan', $penugasan);

        $penugasan = $this->service->completeAssignment($penugasan->id_penugasan_relawan);
        $penugasan->load(['posaju']);

        return new RelawanPenugasanResource($penugasan);
    }
}
