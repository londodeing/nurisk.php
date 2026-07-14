<?php

namespace App\Http\Controllers\Operasi;

use App\Http\Controllers\Controller;
use App\Models\OperasiDistribusi;
use App\Models\OperasiFeedbackDistribusi;
use App\Models\OperasiPosaju;
use App\Models\OperasiInsiden;
use App\Models\OperasiKlaster;
use App\Models\LogistikBarangKatalog;
use App\Services\Operasi\PosajuJurnalService;
use App\Http\Requests\Operasi\StoreDistribusiRequest;
use App\Http\Requests\Operasi\StoreFeedbackDistribusiRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DistribusiWebController extends Controller
{
    public function __construct(
        private PosajuJurnalService $jurnal
    ) {}

    public function index(Request $request, OperasiInsiden $insiden, OperasiPosaju $posaju)
    {
        $this->authorize('view', $posaju);

        $distribusis = OperasiDistribusi::with(['klasterOperasi.masterKlaster', 'feedback.pengguna.profil'])
            ->where('id_posaju', $posaju->id_posaju)
            ->when($request->status, fn($q, $v) => $q->where('status_distribusi', $v))
            ->when($request->klaster, fn($q, $v) => $q->where('id_klaster_operasi', $v))
            ->orderBy('dibuat_pada', 'desc')
            ->paginate(15);

        $klasterList = OperasiKlaster::with('masterKlaster')
            ->where('id_insiden', $insiden->id_insiden)
            ->orderBy('dibuat_pada', 'desc')
            ->get();

        return view('operasi.distribusi.index', compact('distribusis', 'insiden', 'posaju', 'klasterList'));
    }

    public function create(OperasiInsiden $insiden, OperasiPosaju $posaju)
    {
        $this->authorize('create', [OperasiDistribusi::class, $posaju]);

        $klasterList = OperasiKlaster::with('masterKlaster')
            ->where('id_insiden', $insiden->id_insiden)
            ->orderBy('dibuat_pada', 'desc')
            ->get();

        $barangKatalog = LogistikBarangKatalog::orderBy('nama_barang_standar')->get();

        return view('operasi.distribusi.create', compact('insiden', 'posaju', 'klasterList', 'barangKatalog'));
    }

    public function store(StoreDistribusiRequest $request, OperasiInsiden $insiden, OperasiPosaju $posaju)
    {
        $this->authorize('create', [OperasiDistribusi::class, $posaju]);

        $distribusi = DB::transaction(function () use ($request, $posaju) {
            return OperasiDistribusi::create([
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
        });

        $this->jurnal->catat('distribusi_dibuat', $posaju);

        return redirect()
            ->route('insiden.posaju.distribusi.index', [$insiden, $posaju])
            ->with('success', 'Distribusi bantuan berhasil direncanakan.');
    }

    public function distribusikan(Request $request, OperasiInsiden $insiden, OperasiPosaju $posaju, OperasiDistribusi $distribusi)
    {
        $this->authorize('update', $distribusi);

        if ($distribusi->status_distribusi !== 'direncanakan') {
            return back()->with('error', 'Hanya distribusi berstatus Direncanakan yang bisa didistribusikan.');
        }

        $distribusi->update([
            'status_distribusi' => 'didistribusikan',
            'waktu_distribusi' => now(),
        ]);

        $this->jurnal->catat('distribusi_dikirim', $posaju);

        return back()->with('success', 'Distribusi ditandai sudah didistribusikan.');
    }

    public function terima(Request $request, OperasiInsiden $insiden, OperasiPosaju $posaju, OperasiDistribusi $distribusi)
    {
        $this->authorize('update', $distribusi);

        if ($distribusi->status_distribusi !== 'didistribusikan') {
            return back()->with('error', 'Hanya distribusi berstatus Didistribusikan yang bisa diterima.');
        }

        $distribusi->update([
            'status_distribusi' => 'diterima',
        ]);

        return back()->with('success', 'Distribusi ditandai sudah diterima. Silakan isi feedback.');
    }

    public function feedback(StoreFeedbackDistribusiRequest $request, OperasiInsiden $insiden, OperasiPosaju $posaju, OperasiDistribusi $distribusi)
    {
        $this->authorize('update', $distribusi);

        if ($distribusi->status_distribusi !== 'diterima') {
            return back()->with('error', 'Feedback hanya bisa diisi untuk distribusi yang sudah Diterima.');
        }

        if ($distribusi->feedback) {
            return back()->with('error', 'Feedback sudah pernah diisi untuk distribusi ini.');
        }

        DB::transaction(function () use ($request, $distribusi) {
            OperasiFeedbackDistribusi::create([
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
        });

        $distribusi->update([
            'status_distribusi' => 'direview',
        ]);

        $this->jurnal->catat('distribusi_direview', $posaju);

        return redirect()
            ->route('insiden.posaju.distribusi.index', [$insiden, $posaju])
            ->with('success', 'Feedback berhasil dikirim dan terkunci.');
    }

    public function show(OperasiInsiden $insiden, OperasiPosaju $posaju, OperasiDistribusi $distribusi)
    {
        $this->authorize('view', $posaju);

        $distribusi->load(['klasterOperasi.masterKlaster', 'penugasan.pengguna.profil', 'feedback.pengguna.profil', 'barangKatalog']);

        return view('operasi.distribusi.show', compact('insiden', 'posaju', 'distribusi'));
    }
}