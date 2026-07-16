<?php

namespace App\Http\Controllers\Operasi;

use App\Http\Controllers\Controller;
use App\Models\OperasiPosaju;
use App\Models\OperasiPosajuKomandan;
use App\Models\OperasiInsiden;
use App\Models\AuthUser;
use App\Services\Operasi\OperasiPosajuService;
use App\Services\Operasi\PosajuJurnalService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PosAjuWebController extends Controller
{
    public function __construct(
        private OperasiPosajuService $posajuService,
        private PosajuJurnalService $jurnal
    ) {}

    public function index(Request $request, ?OperasiInsiden $insiden = null)
    {
        $this->authorize('viewAny', OperasiPosaju::class);

        $posajus = OperasiPosaju::with(['insiden', 'pj.profil', 'komandanAktif.pengguna.profil'])
            ->when($insiden, fn($q) => $q->where('id_insiden', $insiden->id_insiden))
            ->when($request->status_alur, fn($q, $v) => $q->where('status_alur', $v))
            ->orderBy('dibuat_pada', 'desc')
            ->paginate(15);

        if ($insiden) {
            return view('operasi.posaju.index', compact('posajus', 'insiden'));
        }

        return view('operasi.posaju.index', compact('posajus'));
    }

    public function create(?OperasiInsiden $insiden = null)
    {
        $this->authorize('create', [OperasiPosaju::class, $insiden]);

        $insidenList = OperasiInsiden::where('is_locked', false)
            ->orderBy('dibuat_pada', 'desc')
            ->get(['id_insiden', 'kode_kejadian']);

        $penggunaList = AuthUser::with('profil')
            ->where('status_akun', 'aktif')
            ->orderBy('id_pengguna')
            ->get(['id_pengguna']);

        $plenoKeputusanList = \App\Models\OperasiPlenoKeputusan::with('pleno.insiden')
            ->where('kategori_objek', 'aktivasi_posko')
            ->whereNull('referensi_tabel')
            ->whereIn('status_pelaksanaan', ['pending', 'dijadwalkan'])
            ->when($insiden, fn($q) => $q->whereHas('pleno', fn($p) => $p->where('id_insiden', $insiden->id_insiden)))
            ->orderBy('dibuat_pada', 'desc')
            ->get(['id_keputusan', 'nama_keputusan', 'id_pleno']);

        return view('operasi.posaju.create', compact('insidenList', 'penggunaList', 'plenoKeputusanList', 'insiden'));
    }

    public function store(Request $request, ?OperasiInsiden $insiden = null)
    {
        $this->authorize('create', [OperasiPosaju::class, $insiden]);

        $rules = [
            'nama_posaju'  => ['required', 'string', 'max:150'],
            'alamat_lokasi' => ['nullable', 'string'],
            'latitude'     => ['required', 'numeric', 'between:-90,90'],
            'longitude'    => ['required', 'numeric', 'between:-180,180'],
            'pj_posaju'    => ['nullable', 'integer', 'exists:auth_users,id_pengguna'],
            'id_pleno_keputusan' => ['required', 'integer', 'exists:operasi_pleno_keputusan,id_keputusan'],
        ];

        if (!$insiden) {
            $rules['id_insiden'] = ['required', 'integer', 'exists:operasi_insiden,id_insiden'];
        }

        $validated = $request->validate($rules);

$posaju = DB::transaction(function () use ($validated, $insiden) {
            $posaju = OperasiPosaju::create([
                'id_insiden'   => $insiden ? $insiden->id_insiden : $validated['id_insiden'],
                'nama_posaju'  => $validated['nama_posaju'],
                'alamat_lokasi' => $validated['alamat_lokasi'] ?? null,
                'latitude'     => $validated['latitude'],
                'longitude'    => $validated['longitude'],
                'pj_posaju'    => $validated['pj_posaju'] ?? null,
                'id_pleno_keputusan' => $validated['id_pleno_keputusan'],
                'status_alur'  => 'direncanakan',
            ]);

            return $posaju;
        });

        $this->jurnal->catat('posaju_dibuat', $posaju);

        $route = $insiden
            ? route('insiden.posaju.show', [$insiden, $posaju])
            : route('posaju.show', $posaju);

        return redirect($route)->with('success', 'Pos Aju berhasil dibuat.');
    }

    public function show(OperasiInsiden $insiden, OperasiPosaju $posaju)
    {
        $this->authorize('view', $posaju);

        $posaju->load([
            'insiden', 'pj',
            'stok.katalog',
            'stok.gudang',
            'personel.pengguna.profil',
            'penugasan.klasterOperasi.masterKlaster',
            'permintaanLogistik.klaster.masterKlaster',
            'komandan.pengguna.profil',
            'komandanAktif',
            'distribusi.klasterOperasi.masterKlaster',
            'distribusi.feedback.pengguna.profil',
        ]);

        if ($insiden) {
            return view('operasi.posaju.show', compact('posaju', 'insiden'));
        }

        return view('operasi.posaju.show', compact('posaju'));
    }

    public function activate(Request $request, OperasiInsiden $insiden, OperasiPosaju $posaju)
    {
        $this->authorize('activate', $posaju);

        try {
            $posaju = $this->posajuService->activate($posaju);
            $this->jurnal->catat('posaju_diaktifkan', $posaju);

            return redirect()->route('insiden.posaju.show', [$insiden, $posaju])
                ->with('success', 'Pos Aju berhasil diaktifkan.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal mengaktifkan Pos Aju: ' . $e->getMessage());
        }
    }

    public function close(Request $request, OperasiInsiden $insiden, OperasiPosaju $posaju)
    {
        $this->authorize('close', $posaju);

        $validated = $request->validate([
            'alasan_penutupan' => ['nullable', 'string', 'max:500'],
        ]);

        try {
            $this->posajuService->close($posaju, $validated['alasan_penutupan'] ?? null);
            $this->jurnal->catat('posaju_ditutup', $posaju);

            $route = $insiden
                ? route('insiden.posaju.show', [$insiden, $posaju])
                : route('posaju.show', $posaju);

            return redirect($route)->with('success', 'Pos Aju berhasil ditutup.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menutup Pos Aju: ' . $e->getMessage());
        }
    }

    public function tutup(Request $request, OperasiInsiden $insiden, OperasiPosaju $posaju)
    {
        $this->authorize('close', $posaju);

        $validated = $request->validate([
            'alasan_penutupan' => ['nullable', 'string', 'max:500'],
        ]);

        try {
            $this->posajuService->close($posaju, $validated['alasan_penutupan'] ?? null);
            $this->jurnal->catat('posaju_ditutup', $posaju);

            return redirect()->route('insiden.posaju.show', [$insiden, $posaju])
                ->with('success', 'Pos Aju berhasil ditutup.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menutup Pos Aju: ' . $e->getMessage());
        }
    }

    public function extend(Request $request, OperasiInsiden $insiden, OperasiPosaju $posaju)
    {
        $this->authorize('extend', $posaju);

        $validated = $request->validate([
            'diperpanjang_hingga' => ['required', 'date', 'after:today'],
            'alasan_perpanjangan' => ['nullable', 'string', 'max:500'],
        ]);

        try {
            $this->posajuService->extend(
                $posaju,
                new \DateTime($validated['diperpanjang_hingga']),
                $validated['alasan_perpanjangan'] ?? null
            );
            $this->jurnal->catat('posaju_diperpanjang', $posaju);

            return redirect()->route('insiden.posaju.show', [$insiden, $posaju])
                ->with('success', 'Pos Aju berhasil diperpanjang.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memperpanjang Pos Aju: ' . $e->getMessage());
        }
    }
}
