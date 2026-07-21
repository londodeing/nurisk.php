<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\LaporanKejadian;
use App\Models\OperasiInsiden;
use App\Models\AssessmentUtama;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class PwnuDashboardController extends Controller
{
    public function index()
    {
        $data = $this->buildDashboardData();
        return view('dashboard.pwnu', $data);
    }

    public function polling(): JsonResponse
    {
        return response()->json($this->buildDashboardData());
    }

    private function buildDashboardData(): array
    {
        // === KPI CARDS ===
        $laporanMenunggu   = LaporanKejadian::where('is_valid', 'menunggu')->count();
        $insidenAktif      = OperasiInsiden::whereIn('status_insiden', ['terverifikasi', 'respon', 'pemulihan'])->count();
        $spkBelumTerbit    = OperasiInsiden::whereIn('status_insiden', ['terverifikasi', 'respon'])
                                ->whereNull('no_spk_assesment')->count();
        $plenoMenunggu     = DB::table('operasi_pleno')
                                ->whereNotIn('status_pleno', ['selesai', 'dibatalkan'])->count();

        // === PIPELINE: insiden per tahap lifecycle ===
        $pipelineCounts = [
            'laporan'    => $laporanMenunggu,
            'terverifikasi_no_spk' => OperasiInsiden::where('status_insiden', 'terverifikasi')
                                        ->whereNull('no_spk_assesment')->count(),
            'assessment' => OperasiInsiden::where('status_insiden', 'terverifikasi')
                                        ->whereNotNull('no_spk_assesment')
                                        ->whereDoesntHave('assessments')->count(),
            'pleno'      => DB::table('operasi_pleno')
                                ->whereNotIn('status_pleno', ['selesai', 'dibatalkan'])->count(),
            'respon'     => OperasiInsiden::where('status_insiden', 'respon')->count(),
            'pemulihan'  => OperasiInsiden::where('status_insiden', 'pemulihan')->count(),
        ];

        // === INSIDEN AKTIF: untuk tabel pipeline utama ===
        $insidenList = OperasiInsiden::with(['jenisBencana', 'pcnu'])
            ->whereIn('status_insiden', ['terverifikasi', 'respon', 'pemulihan'])
            ->orderByRaw("FIELD(prioritas, 'kritis', 'tinggi', 'sedang', 'rendah')")
            ->orderByRaw("FIELD(status_insiden, 'respon', 'terverifikasi', 'pemulihan')")
            ->take(20)
            ->get()
            ->map(fn ($i) => [
                'id'            => $i->id_insiden,
                'kode'          => $i->kode_kejadian,
                'jenis'         => $i->jenisBencana?->nama_bencana ?? '-',
                'pcnu'          => $i->pcnu?->nama_pcnu ?? '-',
                'status'        => $i->status_insiden,
                'prioritas'     => $i->prioritas,
                'spk'           => $i->no_spk_assesment,
                'tgl_spk'       => $i->tgl_spk_assesment,
                'waktu_mulai'   => $i->waktu_mulai?->format('d/m H:i'),
                'url_show'      => route('insiden.show', $i->id_insiden),
                'url_assessment'=> $i->no_spk_assesment
                    ? route('insiden.assessment.create', $i->id_insiden)
                    : null,
                'url_pleno'     => route('insiden.pleno.index', $i->id_insiden),
            ]);

        // === LAPORAN MENUNGGU VERIFIKASI ===
        $laporanList = LaporanKejadian::with(['jenisBencana'])
            ->where('is_valid', 'menunggu')
            ->latest('dibuat_pada')
            ->take(5)
            ->get()
            ->map(fn ($l) => [
                'id'      => $l->id_laporan_kejadian,
                'kode'    => $l->kode_kejadian,
                'jenis'   => $l->jenisBencana?->nama_bencana ?? '-',
                'pelapor' => $l->nama_pelapor,
                'waktu'   => $l->dibuat_pada?->diffForHumans(),
                'url'     => route('dashboard.laporan.show', $l->id_laporan_kejadian),
            ]);

        // === TINDAKAN MENDESAK ===
        $tindakanMenunggu = [];
        if ($laporanMenunggu > 0) {
            $tindakanMenunggu[] = [
                'label'    => "Verifikasi $laporanMenunggu Laporan Masuk",
                'icon'     => 'bi-inbox-fill',
                'badge'    => 'danger',
                'url'      => route('dashboard.laporan.index'),
            ];
        }
        if ($spkBelumTerbit > 0) {
            $tindakanMenunggu[] = [
                'label'    => "Terbitkan SPK untuk $spkBelumTerbit Insiden",
                'icon'     => 'bi-file-earmark-check',
                'badge'    => 'warning',
                'url'      => route('insiden.index', ['status' => 'terverifikasi']),
            ];
        }
        if ($plenoMenunggu > 0) {
            $tindakanMenunggu[] = [
                'label'    => "$plenoMenunggu Pleno Menunggu Finalisasi",
                'icon'     => 'bi-people-fill',
                'badge'    => 'warning',
                'url'      => route('insiden.index'),
            ];
        }

        return compact(
            'laporanMenunggu',
            'insidenAktif',
            'spkBelumTerbit',
            'plenoMenunggu',
            'pipelineCounts',
            'insidenList',
            'laporanList',
            'tindakanMenunggu'
        );
    }
}


