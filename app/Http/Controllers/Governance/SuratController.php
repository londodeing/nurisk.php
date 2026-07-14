<?php

namespace App\Http\Controllers\Governance;

use App\Http\Controllers\Controller;
use App\Http\Requests\Governance\StoreSuratRequest;
use App\Models\DokumenSuratUtama;
use App\Infrastructure\Media\Storage\Contracts\StorageProvider;
use App\Models\MasterJabatanPenandatangan;
use App\Models\MasterSuratJenis;
use App\Models\OperasiInsiden;
use App\Models\AuthUser;
use App\Services\SuratService;
use Illuminate\Http\Request;

class SuratController extends Controller
{
    public function __construct(
        private SuratService $suratService,
        private StorageProvider $storage,
    ) {}

    public function index(Request $request)
    {
        $this->authorize('viewAny', DokumenSuratUtama::class);

        $query = DokumenSuratUtama::with(['jenisSurat', 'penandatangan.profil'])->latest('dibuat_pada');

        if ($request->filled('status')) {
            $query->where('status_surat', $request->status);
        }

        $suratList = $query->paginate(15)->withQueryString();

        return view('governance.surat.index', compact('suratList'));
    }

    public function create()
    {
        $this->authorize('create', DokumenSuratUtama::class);

        $jenisSurat = MasterSuratJenis::aktif()->get();
        $jabatan = MasterJabatanPenandatangan::aktif()->orderByHierarki()->get();
        $pengguna = AuthUser::with('profil')->where('status_akun', 'aktif')->orderBy('no_hp')->get();
        $insidenList = OperasiInsiden::aktif()->latest('dibuat_pada')->get();

        return view('governance.surat.create', compact('jenisSurat', 'jabatan', 'pengguna', 'insidenList'));
    }

    public function store(StoreSuratRequest $request)
    {
        try {
            $surat = $this->suratService->buatSurat($request->validated());
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal membuat surat: ' . $e->getMessage())
                ->withInput();
        }

        return redirect()->route('surat.show', $surat)
            ->with('success', 'Surat berhasil dibuat.');
    }

    public function show(DokumenSuratUtama $surat)
    {
        $this->authorize('view', $surat);

        $surat->load(['jenisSurat', 'penandatangan.profil', 'jabatanTtd', 'paraf.pengguna.profil', 'tembusan', 'insiden']);

        $pengguna = AuthUser::with('profil')->where('status_akun', 'aktif')->orderBy('no_hp')->get();

        return view('governance.surat.show', compact('surat', 'pengguna'));
    }

    public function edit(DokumenSuratUtama $surat)
    {
        $this->authorize('update', $surat);

        if (!$surat->isDraft()) {
            return back()->with('error', 'Hanya surat berstatus draft yang dapat diedit.');
        }

        $jenisSurat = MasterSuratJenis::aktif()->get();
        $jabatan = MasterJabatanPenandatangan::aktif()->orderByHierarki()->get();
        $pengguna = AuthUser::with('profil')->where('status_akun', 'aktif')->orderBy('no_hp')->get();
        $insidenList = OperasiInsiden::aktif()->latest('dibuat_pada')->get();

        return view('governance.surat.edit', compact('surat', 'jenisSurat', 'jabatan', 'pengguna', 'insidenList'));
    }

    public function update(Request $request, DokumenSuratUtama $surat)
    {
        $this->authorize('update', $surat);

        $validated = $request->validate([
            'id_insiden' => ['nullable', 'integer', 'exists:operasi_insiden,id_insiden'],
            'id_jenis_surat' => ['required', 'integer', 'exists:master_surat_jenis,id_jenis_surat'],
            'perihal' => ['required', 'string', 'max:255'],
            'tgl_terbit' => ['required', 'date'],
            'id_pengguna_ttd' => ['required', 'integer', 'exists:auth_users,id_pengguna'],
            'id_jabatan_ttd' => ['nullable', 'integer', 'exists:master_jabatan_penandatangan,id_jabatan'],
        ]);

        $surat->update($validated);

        return redirect()->route('surat.show', $surat)
            ->with('success', 'Surat berhasil diperbarui.');
    }

    public function finalisasi(Request $request, DokumenSuratUtama $surat)
    {
        $this->authorize('finalisasi', $surat);

        try {
            $isiSnapshot = $request->input('isi_surat_snapshot');
            $this->suratService->finalisasi($surat, $request->user(), $isiSnapshot);
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Surat berhasil ditandatangani.');
    }

    public function downloadPdf(DokumenSuratUtama $surat)
    {
        $this->authorize('view', $surat);

        if (!$surat->file_pdf_path) {
            return back()->with('error', 'File PDF belum tersedia.');
        }

        if (!$this->storage->exists($surat->file_pdf_path)) {
            return back()->with('error', 'File PDF tidak ditemukan.');
        }

        $url = $this->storage->url($surat->file_pdf_path);

        if ($url) {
            return redirect($url);
        }

        return back()->with('error', 'File PDF tidak dapat diakses.');
    }

    public function kirimReview(DokumenSuratUtama $surat)
    {
        $this->authorize('update', $surat);

        try {
            $this->suratService->kirimKeReview($surat);
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Surat berhasil dikirim ke review.');
    }
}
