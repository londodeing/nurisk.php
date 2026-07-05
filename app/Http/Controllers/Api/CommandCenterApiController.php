<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\OperasiInsiden;
use App\Models\OperasiPenugasan;
use App\Models\OperasiPosaju;
use App\Services\Auth\AuthorizationContextService;

class CommandCenterApiController extends Controller
{
    /**
     * GET /api/command-center/insiden-aktif
     */
    public function insidenAktif(Request $request): JsonResponse
    {
        $ctx = app(AuthorizationContextService::class);
        $scopePcnuId = $ctx->hasRole('pcnu') ? $ctx->getScopeId() : null;

        $insiden = OperasiInsiden::query()
            ->whereIn('status_insiden', ['respon', 'pemulihan'])
            ->whereNull('dihapus_pada')
            ->with([
                'jenisBencana:id_jenis,nama_bencana,ikon_map',
                'pcnu:id_pcnu,nama_pcnu',
                'posaju'  => fn($q) => $q->where('status_alur', 'aktif')
                                          ->select('id_posaju', 'id_insiden', 'nama_posaju', 'latitude', 'longitude', 'status_alur'),
            ])
            ->when($scopePcnuId, fn($q) => $q->where('id_pcnu', $scopePcnuId))
            ->get()
            ->map(function ($i) {
                // Strategi koordinat: pos aju pertama → fallback null
                $lat = $i->posaju->first()?->latitude ?? null;
                $lng = $i->posaju->first()?->longitude ?? null;

                return [
                    'id'           => $i->id_insiden,
                    'kode'         => $i->kode_kejadian,
                    'status'       => $i->status_insiden,
                    'prioritas'    => $i->prioritas,
                    'jenis'        => $i->jenisBencana?->nama_bencana,
                    'ikon_map'     => $i->jenisBencana?->ikon_map ?? 'default.png',
                    'pcnu'         => $i->pcnu?->nama_pcnu,
                    'waktu_mulai'  => $i->waktu_mulai?->toIso8601String(),
                    'lat'          => $lat,
                    'lng'          => $lng,
                    'posaju'       => $i->posaju->map(fn($p) => [
                        'nama' => $p->nama_posaju,
                        'lat'  => $p->latitude,
                        'lng'  => $p->longitude,
                    ]),
                    'has_koordinat' => !is_null($lat) && !is_null($lng),
                ];
            });

        return response()->json(['data' => $insiden, 'updated_at' => now()->toIso8601String()]);
    }

    /**
     * GET /api/command-center/statistik
     */
    public function statistik(Request $request): JsonResponse
    {
        $ctx         = app(AuthorizationContextService::class);
        $scopePcnuId = $ctx->hasRole('pcnu') ? $ctx->getScopeId() : null;

        $insidenAktifIds = OperasiInsiden::whereIn('status_insiden', ['respon', 'pemulihan'])
            ->whereNull('dihapus_pada')
            ->when($scopePcnuId, fn($q) => $q->where('id_pcnu', $scopePcnuId))
            ->pluck('id_insiden');

        // Korban terdampak dari sitrep terbaru per insiden
        $korbanTerdampak = 0;
        try {
            $korbanTerdampak = \App\Models\OperasiSitrep::whereIn('id_insiden', $insidenAktifIds)
                ->where('is_latest', 1)
                ->sum('jumlah_terdampak') ?? 0;
        } catch (\Exception $e) { /* Kolom mungkin belum ada */ }

        return response()->json([
            'total_insiden'     => $insidenAktifIds->count(),
            'total_personel'    => OperasiPenugasan::aktif()
                ->whereIn('id_insiden', $insidenAktifIds)
                ->count(),
            'total_posaju'      => OperasiPosaju::where('status_alur', 'aktif')
                ->whereIn('id_insiden', $insidenAktifIds)
                ->count(),
            'korban_terdampak'  => $korbanTerdampak,
            'updated_at'        => now()->toIso8601String(),
        ]);
    }

    /**
     * GET /api/command-center/stok-kritis
     */
    public function stokKritis(Request $request): JsonResponse
    {
        $ctx         = app(AuthorizationContextService::class);
        $scopePcnuId = $ctx->hasRole('pcnu') ? $ctx->getScopeId() : null;

        try {
            // Cek apakah model logistik tersedia
            if (!class_exists(\App\Models\LogistikStok::class)) {
                return response()->json(['data' => [], 'note' => 'modul logistik belum aktif']);
            }

            $stok = \App\Models\LogistikStok::query()
                ->where('jumlah_tersedia', '<', \Illuminate\Support\Facades\DB::raw('threshold_minimum'))
                ->orWhere('jumlah_tersedia', 0)
                ->when($scopePcnuId, fn($q) => $q->whereHas('posaju.insiden', fn($i) => $i->where('id_pcnu', $scopePcnuId)))
                ->with(['barang:id_barang,nama_barang,satuan', 'posaju:id_posaju,nama_posaju'])
                ->limit(10)
                ->get()
                ->map(fn($s) => [
                    'nama_barang'    => $s->barang?->nama_barang ?? 'Unknown',
                    'satuan'         => $s->barang?->satuan ?? 'unit',
                    'tersedia'       => $s->jumlah_tersedia,
                    'minimum'        => $s->threshold_minimum ?? 0,
                    'posaju'         => $s->posaju?->nama_posaju,
                ]);

            return response()->json(['data' => $stok]);
        } catch (\Exception $e) {
            return response()->json(['data' => [], 'error' => 'query_failed']);
        }
    }

    /**
     * GET /api/command-center/jurnal-terbaru
     */
    public function jurnalTerbaru(Request $request): JsonResponse
    {
        $ctx         = app(AuthorizationContextService::class);
        $scopePcnuId = $ctx->hasRole('pcnu') ? $ctx->getScopeId() : null;

        try {
            if (!class_exists(\App\Models\OperasiJurnal::class)) {
                return response()->json(['data' => []]);
            }

            $jurnal = \App\Models\OperasiJurnal::query()
                ->with(['pengguna.profil:id_pengguna,nama_lengkap', 'insiden:id_insiden,kode_kejadian'])
                ->whereHas('insiden', function ($q) use ($scopePcnuId) {
                    $q->whereIn('status_insiden', ['respon', 'pemulihan']);
                    if ($scopePcnuId) $q->where('id_pcnu', $scopePcnuId);
                })
                ->latest('dibuat_pada')
                ->limit(10)
                ->get()
                ->map(fn($j) => [
                    'waktu'         => $j->dibuat_pada?->format('H:i'),
                    'kategori'      => $j->kategori_event ?? 'umum',
                    'judul'         => $j->judul_event,
                    'kode_insiden'  => $j->insiden?->kode_kejadian,
                    'oleh'          => $j->pengguna?->profil?->nama_lengkap ?? 'System',
                ]);

            return response()->json(['data' => $jurnal]);
        } catch (\Exception $e) {
            return response()->json(['data' => []]);
        }
    }
}
