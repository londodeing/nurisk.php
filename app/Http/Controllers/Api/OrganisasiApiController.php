<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\OrganisasiPcnu;
use App\Models\OrganisasiMwc;
use App\Models\OrganisasiRanting;
use App\Models\OrganisasiUnit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrganisasiApiController extends Controller
{
    public function pcnu(Request $request): JsonResponse
    {
        $this->authorize('viewAny', OrganisasiPcnu::class);

        $items = OrganisasiPcnu::with('unit')
            ->when($request->search, fn($q, $v) => $q->where('nama_pcnu', 'like', "%{$v}%"))
            ->paginate($request->get('per_page', 50));

        return response()->json([
            'data' => $items->map(fn($p) => [
                'id'        => $p->id_pcnu,
                'nama'      => $p->nama_pcnu,
                'id_unit'   => $p->id_unit,
                'nama_unit' => $p->unit?->nama_unit,
            ]),
            'meta' => ['total' => $items->total()],
        ]);
    }

    public function pcnuDetail(OrganisasiPcnu $pcnu): JsonResponse
    {
        $this->authorize('view', $pcnu);

        $pcnu->load(['unit', 'mwc.unit', 'mwc.ranting.unit']);

        return response()->json([
            'data' => [
                'id'      => $pcnu->id_pcnu,
                'nama'    => $pcnu->nama_pcnu,
                'unit'    => $pcnu->unit?->nama_unit,
                'mwc'     => $pcnu->mwc->map(fn($m) => [
                    'id'      => $m->id_mwc,
                    'nama'    => $m->nama_mwc,
                    'id_unit' => $m->id_unit,
                    'ranting' => $m->ranting->map(fn($r) => [
                        'id'   => $r->id_ranting,
                        'nama' => $r->nama_ranting,
                    ]),
                ]),
                'total_mwc'     => $pcnu->mwc->count(),
                'total_ranting' => $pcnu->ranting()->count(),
            ],
        ]);
    }

    public function mwcRanting(OrganisasiMwc $mwc): JsonResponse
    {
        $this->authorize('view', $mwc);

        $mwc->load('ranting.unit');

        return response()->json([
            'data' => [
                'id'      => $mwc->id_mwc,
                'nama'    => $mwc->nama_mwc,
                'id_pcnu' => $mwc->id_pcnu,
                'ranting' => $mwc->ranting->map(fn($r) => [
                    'id'   => $r->id_ranting,
                    'nama' => $r->nama_ranting,
                ]),
            ],
        ]);
    }

    public function pcnuMwc(OrganisasiPcnu $pcnu): JsonResponse
    {
        $this->authorize('view', $pcnu);

        $pcnu->load('mwc.unit');

        return response()->json([
            'data' => $pcnu->mwc->map(fn($m) => [
                'id'      => $m->id_mwc,
                'nama'    => $m->nama_mwc,
                'id_unit' => $m->id_unit,
            ]),
        ]);
    }

    public function unit(Request $request): JsonResponse
    {
        $this->authorize('viewAny', OrganisasiUnit::class);

        $items = OrganisasiUnit::with('parent')
            ->when($request->tipe_unit, fn($q, $v) => $q->where('tipe_unit', $v))
            ->paginate($request->get('per_page', 50));

        return response()->json([
            'data' => $items->map(fn($u) => [
                'id'        => $u->id_unit,
                'nama'      => $u->nama_unit,
                'tipe'      => $u->tipe_unit,
                'parent'    => $u->parent?->nama_unit,
                'id_wilayah' => $u->id_wilayah,
            ]),
        ]);
    }
}
