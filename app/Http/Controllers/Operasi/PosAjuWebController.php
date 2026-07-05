<?php

namespace App\Http\Controllers\Operasi;

use App\Http\Controllers\Controller;
use App\Models\OperasiPosaju;
use App\Models\OperasiInsiden;
use App\Models\AuthUser;
use App\Services\Operasi\OperasiPosajuService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PosAjuWebController extends Controller
{
    public function __construct(
        private OperasiPosajuService $posajuService
    ) {}

    public function index(Request $request)
    {
        $this->authorize('viewAny', OperasiPosaju::class);

        $posajus = OperasiPosaju::with(['insiden', 'pj'])
            ->when($request->status_alur, fn($q, $v) => $q->where('status_alur', $v))
            ->orderBy('dibuat_pada', 'desc')
            ->paginate(15);

        return view('operasi.posaju.index', compact('posajus'));
    }

    public function create()
    {
        $this->authorize('create', OperasiPosaju::class);

        $insidenList = OperasiInsiden::where('is_locked', false)
            ->orderBy('dibuat_pada', 'desc')
            ->get(['id_insiden', 'kode_kejadian']);

        $penggunaList = AuthUser::with('profil')
            ->where('status_akun', 'aktif')
            ->orderBy('id_pengguna')
            ->get(['id_pengguna']);

        return view('operasi.posaju.create', compact('insidenList', 'penggunaList'));
    }

    public function store(Request $request)
    {
        $this->authorize('create', OperasiPosaju::class);

        $validated = $request->validate([
            'id_insiden'   => ['required', 'integer', 'exists:operasi_insiden,id_insiden'],
            'nama_posaju'  => ['required', 'string', 'max:150'],
            'alamat_lokasi' => ['nullable', 'string'],
            'pj_posaju'    => ['nullable', 'integer', 'exists:auth_users,id_pengguna'],
        ]);

        $posaju = DB::transaction(function () use ($validated) {
            return OperasiPosaju::create([
                'id_insiden'   => $validated['id_insiden'],
                'nama_posaju'  => $validated['nama_posaju'],
                'alamat_lokasi' => $validated['alamat_lokasi'] ?? null,
                'pj_posaju'    => $validated['pj_posaju'] ?? null,
                'status_alur'  => 'direncanakan',
            ]);
        });

        return redirect()->route('posaju.show', $posaju)
            ->with('success', 'Pos Aju berhasil dibuat.');
    }

    public function show(OperasiPosaju $posaju)
    {
        $this->authorize('view', $posaju);

        $posaju->load(['insiden', 'pj', 'stok.katalog', 'personel.pengguna.profil']);

        return view('operasi.posaju.show', compact('posaju'));
    }

    public function activate(Request $request, OperasiPosaju $posaju)
    {
        $this->authorize('activate', $posaju);

        try {
            $this->posajuService->activate($posaju);
            return redirect()->route('posaju.show', $posaju)
                ->with('success', 'Pos Aju berhasil diaktifkan.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal mengaktifkan Pos Aju: ' . $e->getMessage());
        }
    }

    public function close(Request $request, OperasiPosaju $posaju)
    {
        $this->authorize('close', $posaju);

        $validated = $request->validate([
            'alasan_penutupan' => ['nullable', 'string', 'max:500'],
        ]);

        try {
            $this->posajuService->close($posaju, $validated['alasan_penutupan'] ?? null);
            return redirect()->route('posaju.show', $posaju)
                ->with('success', 'Pos Aju berhasil ditutup.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menutup Pos Aju: ' . $e->getMessage());
        }
    }

    public function extend(Request $request, OperasiPosaju $posaju)
    {
        $this->authorize('extend', $posaju);

        $validated = $request->validate([
            'diperpanjang_hingga' => ['required', 'date', 'after:today'],
            'alasan_penutupan' => ['nullable', 'string', 'max:500'],
        ]);

        try {
            $this->posajuService->extend(
                $posaju,
                new \DateTime($validated['diperpanjang_hingga']),
                $validated['alasan_penutupan'] ?? null
            );
            return redirect()->route('posaju.show', $posaju)
                ->with('success', 'Pos Aju berhasil diperpanjang.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memperpanjang Pos Aju: ' . $e->getMessage());
        }
    }
}
