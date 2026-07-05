<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Histori\HistoriBencanaWilayah;
use Illuminate\Http\Request;

class HistoriBencanaController extends Controller
{
    public function index(Request $request)
    {
        $query = HistoriBencanaWilayah::terverifikasi()
            ->with(['kabupaten:id_kab,nama_kab', 'jenisBencana:id_jenis,nama_bencana']);

        if ($request->filled('region') && $request->region !== 'all') {
            $query->byKabupaten($request->region);
        }

        if ($request->filled('disaster_type') && $request->disaster_type !== 'all') {
            $ids = \App\Models\BencanaMasterJenis::where('nama_bencana', $request->disaster_type)
                ->orWhere('slug', $request->disaster_type)
                ->pluck('id_jenis');
            if ($ids->isNotEmpty()) {
                $query->whereIn('id_jenis_bencana', $ids);
            }
        }

        if ($request->filled('start_date')) {
            $query->where('tanggal_mulai', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->where('tanggal_mulai', '<=', $request->end_date);
        }

        $records = $query->latest('tanggal_mulai')->get();

        $data = $records->map(function ($h) {
            $intensity = 0.2;
            if ($h->korban_terdampak > 0) {
                $intensity = min($h->korban_terdampak / 5000, 1);
            } elseif ($h->rumah_rusak_total > 0) {
                $intensity = min($h->rumah_rusak_total / 1000, 0.8);
            } elseif ($h->pengungsi > 0) {
                $intensity = min($h->pengungsi / 2000, 0.6);
            }

            $lat = null;
            $lng = null;
            if ($h->kabupaten) {
                $coords = self::KAB_COORDS[$h->id_kab] ?? self::KAB_COORDS_BY_NAME[$h->kabupaten->nama_kab] ?? null;
                if ($coords) {
                    $lat = $coords[0];
                    $lng = $coords[1];
                }
            }

            return [
                'id' => $h->id_histori,
                'latitude' => $lat,
                'longitude' => $lng,
                'fixed_intensity' => round($intensity, 4),
                'nama_kejadian' => $h->nama_kejadian,
                'jenis_bencana' => $h->jenisBencana?->nama_bencana,
                'kabupaten' => $h->kabupaten?->nama_kab,
                'id_kab' => $h->id_kab,
                'tahun' => $h->tahun,
                'bulan' => $h->bulan,
                'tanggal_mulai' => $h->tanggal_mulai?->toDateString(),
                'korban_terdampak' => $h->korban_terdampak,
                'rumah_rusak_total' => $h->rumah_rusak_total,
                'pengungsi' => $h->pengungsi,
                'kerugian_estimasi_juta' => $h->kerugian_estimasi_juta,
            ];
        });

        return response()->json([
            'data' => $data,
            'total' => $data->count(),
        ]);
    }

    private const KAB_COORDS = [
        '3301' => [-7.4833, 108.8500],
        '3302' => [-7.4167, 109.2333],
        '3303' => [-7.4833, 108.6500],
        '3304' => [-7.3500, 109.5000],
        '3305' => [-7.6333, 109.5167],
        '3306' => [-7.6000, 110.1167],
        '3307' => [-7.3333, 109.8667],
        '3308' => [-7.5500, 110.2167],
        '3309' => [-7.4167, 110.3333],
        '3310' => [-7.7500, 110.5000],
        '3311' => [-7.1333, 111.1000],
        '3312' => [-7.6833, 111.0333],
        '3313' => [-7.6000, 110.8833],
        '3314' => [-7.1500, 110.8000],
        '3315' => [-7.0833, 110.6000],
        '3316' => [-7.1000, 111.4167],
        '3317' => [-6.7000, 111.3333],
        '3318' => [-6.7333, 111.0333],
        '3319' => [-6.8000, 110.8333],
        '3320' => [-6.7000, 110.6667],
        '3321' => [-6.9667, 110.4167],
        '3322' => [-7.0167, 110.5167],
        '3323' => [-7.1000, 110.1500],
        '3324' => [-6.9833, 110.1167],
        '3325' => [-6.8833, 109.6667],
        '3326' => [-6.8833, 109.7500],
        '3327' => [-6.9000, 109.1333],
        '3328' => [-6.8667, 109.1167],
        '3329' => [-6.7167, 108.8667],
        '3330' => [-6.7000, 108.5667],
        '3371' => [-6.7333, 110.6667],
        '3372' => [-7.5667, 110.8167],
        '3373' => [-7.4667, 110.2167],
        '3374' => [-6.9833, 110.4167],
        '3375' => [-6.9000, 109.7500],
        '3376' => [-7.1333, 109.8833],
    ];

    private const KAB_COORDS_BY_NAME = [
        'Cilacap' => [-7.7333, 109.0000],
        'Banyumas' => [-7.4500, 109.1667],
        'Purbalingga' => [-7.3833, 109.3500],
        'Banjarnegara' => [-7.3667, 109.6833],
        'Kebumen' => [-7.6667, 109.6500],
        'Purworejo' => [-7.7000, 110.0167],
        'Wonosobo' => [-7.3667, 109.9000],
        'Magelang' => [-7.4333, 110.1667],
        'Boyolali' => [-7.5333, 110.6000],
        'Klaten' => [-7.7000, 110.6000],
        'Sukoharjo' => [-7.6833, 110.8167],
        'Wonogiri' => [-7.8167, 110.9167],
        'Karanganyar' => [-7.6000, 111.0667],
        'Sragen' => [-7.4167, 111.0167],
        'Grobogan' => [-7.0167, 110.9167],
        'Blora' => [-6.9667, 111.4167],
        'Rembang' => [-6.7000, 111.3500],
        'Pati' => [-6.7500, 111.0333],
        'Kudus' => [-6.8000, 110.8333],
        'Jepara' => [-6.5833, 110.6667],
        'Demak' => [-6.8833, 110.6333],
        'Semarang' => [-7.0500, 110.4333],
        'Temanggung' => [-7.3333, 110.1667],
        'Kendal' => [-6.9167, 110.2000],
        'Batang' => [-6.9000, 109.7333],
        'Pekalongan' => [-6.8833, 109.6667],
        'Pemalang' => [-6.8833, 109.3833],
        'Tegal' => [-6.8667, 109.1333],
        'Brebes' => [-6.8667, 109.0500],
        'Magelang Kota' => [-7.4667, 110.2167],
        'Surakarta' => [-7.5667, 110.8167],
        'Salatiga' => [-7.3333, 110.5000],
        'Semarang Kota' => [-6.9667, 110.4167],
        'Pekalongan Kota' => [-6.8833, 109.6667],
        'Tegal Kota' => [-6.8667, 109.1333],
    ];
}
