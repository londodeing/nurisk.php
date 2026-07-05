<?php

namespace App\Http\Controllers\Operasi;

use App\Http\Controllers\Controller;
use App\Models\BencanaMasterJenis;
use App\Models\LaporanKejadian;
use App\Models\MasterSuratJenis;
use App\Models\OperasiPenugasan;
use App\Models\OperasiPenugasanHistory;
use App\Models\OrganisasiPcnu;
use App\Services\Auth\AuthorizationContextService;
use App\Services\InsidenService;
use App\Services\LocationService;
use App\Services\SuratService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class LaporanKejadianController extends Controller
{
    public function __construct(
        private InsidenService $insidenService,
        private LocationService $locationService,
        private SuratService $suratService,
    ) {}

    /**
     * Tampilkan daftar laporan masuk yang menunggu verifikasi.
     */
    public function index(Request $request)
    {
        $query = LaporanKejadian::with(['jenisBencana', 'pcnu']);

        // Filter status (default: menunggu)
        $status = $request->input('status', 'menunggu');
        if (in_array($status, ['menunggu', 'ya', 'tidak'])) {
            $query->where('is_valid', $status);
        }

        // Filter jenis bencana
        if ($request->filled('id_jenis_bencana')) {
            $query->where('id_jenis_bencana', $request->input('id_jenis_bencana'));
        }

        // Filter tanggal
        if ($request->filled('dari')) {
            $query->whereDate('waktu_kejadian', '>=', $request->input('dari'));
        }
        if ($request->filled('sampai')) {
            $query->whereDate('waktu_kejadian', '<=', $request->input('sampai'));
        }

        $user = auth()->user();
        if ($user && $user->default_scope_type === 'pcnu' && $user->hasRole('pcnu')) {
            $query->where('id_pcnu', $user->default_scope_id);
        }

        $laporanList = $query->latest('dibuat_pada')->paginate(15);
        $jenisBencanaList = BencanaMasterJenis::orderBy('nama_bencana')->get();

        return view('operasi.laporan.index', compact('laporanList', 'jenisBencanaList'));
    }

    /**
     * Detail dari sebuah laporan kejadian
     */
    public function show(LaporanKejadian $laporan)
    {
        $this->authorize('validasi', $laporan);

        $laporan->loadMissing(['jenisBencana', 'insiden']);

        $authCtx = app(AuthorizationContextService::class);
        $accessibleIds = $authCtx->getAccessiblePcnuIds();
        $pcnuList = OrganisasiPcnu::orderBy('nama_pcnu')
            ->when($accessibleIds !== null, fn($q) => $q->whereIn('id_pcnu', $accessibleIds))
            ->get();

        $trcList = \App\Models\AuthUser::where('status_akun', 'aktif')
            ->whereHas('peran', fn($q) => $q->where('nama_peran', 'like', '%trc%'))
            ->with('profil')
            ->orderBy('id_pengguna')
            ->get();

        return view('operasi.laporan.show', compact('laporan', 'pcnuList', 'trcList'));
    }

    public function edit(LaporanKejadian $laporan)
    {
        $this->authorize('validasi', $laporan);

        if ($laporan->is_valid !== 'menunggu') {
            return back()->with('error', 'Hanya laporan yang berstatus menunggu yang dapat direvisi.');
        }

        $jenisBencanaList = BencanaMasterJenis::orderBy('nama_bencana')->get();
        $kabupatenList = \App\Models\WilayahKabupaten::orderBy('nama_kab')->get();

        return view('operasi.laporan.edit', compact('laporan', 'jenisBencanaList', 'kabupatenList'));
    }

    public function update(Request $request, LaporanKejadian $laporan)
    {
        $this->authorize('validasi', $laporan);

        if ($laporan->is_valid !== 'menunggu') {
            return back()->with('error', 'Hanya laporan yang berstatus menunggu yang dapat direvisi.');
        }

        $validated = $request->validate([
            'id_jenis_bencana' => ['required', 'integer', 'exists:bencana_master_jenis,id_jenis'],
            'keterangan_situasi' => ['required', 'string'],
            'titik_kenal' => ['nullable', 'string'],
            'id_kab' => ['nullable', 'string', 'exists:wilayah_kabupaten,id_kab'],
            'id_kec' => ['nullable', 'string', 'exists:wilayah_kecamatan,id_kec'],
            'id_desa' => ['nullable', 'string', 'exists:wilayah_desa,id_desa'],
            'latitude' => ['required', 'numeric'],
            'longitude' => ['required', 'numeric'],
        ]);

        // Reassign PCNU jika kabupaten berubah (prioritas id_kab, fallback latlong)
        $idKab = $validated['id_kab'] ?? null;
        if ($idKab) {
            $newPcnuId = $this->locationService->findPcnuByIdKab($idKab);
            if ($newPcnuId) {
                $validated['id_pcnu'] = $newPcnuId;
            }
        } elseif ($validated['latitude'] && $validated['longitude']) {
            $newPcnuId = $this->locationService->findPcnuByCoordinates(
                (float) $validated['latitude'], (float) $validated['longitude']
            );
            if ($newPcnuId) {
                $validated['id_pcnu'] = $newPcnuId;
            }
        }

        // Bangun alamat_lengkap dari wilayah yang dipilih
        $idKec = $validated['id_kec'] ?? null;
        $idDesa = $validated['id_desa'] ?? null;
        if ($idKab || $idKec || $idDesa) {
            $alamatBagian = [];
            if ($idDesa) {
                $desa = \App\Models\WilayahDesa::find($idDesa);
                if ($desa) $alamatBagian[] = $desa->nama_desa;
            }
            if ($idKec) {
                $kec = \App\Models\WilayahKecamatan::find($idKec);
                if ($kec) $alamatBagian[] = 'Kec. ' . $kec->nama_kec;
            }
            if ($idKab) {
                $kab = \App\Models\WilayahKabupaten::find($idKab);
                if ($kab) $alamatBagian[] = $kab->nama_kab;
            }
            if ($alamatBagian) {
                $validated['alamat_lengkap'] = implode(', ', $alamatBagian);
            }
        }

        $oldPcnuId = $laporan->id_pcnu;
        $laporan->update($validated);

        $redirect = redirect()->route('dashboard.laporan.show', $laporan)
                         ->with('success', 'Laporan berhasil direvisi.');

        if (isset($validated['id_pcnu']) && (int) $validated['id_pcnu'] !== (int) $oldPcnuId) {
            $pcnuBaru = \App\Models\OrganisasiPcnu::find($validated['id_pcnu']);
            if ($pcnuBaru) {
                $redirect->with('info', 'Laporan dialihkan ke PCNU ' . $pcnuBaru->nama_pcnu . ' sesuai wilayah yang diperbarui.');
            }
        }

        return $redirect;
    }

    /**
     * Proses verifikasi laporan menjadi Operasi Insiden
     */
    public function verify(Request $request, LaporanKejadian $laporan)
    {
        $this->authorize('validasi', $laporan);

        if ($laporan->is_valid === 'ya' && $laporan->insiden) {
            return back()->with('error', 'Laporan ini sudah memiliki insiden terkait.');
        }

        if ($laporan->is_valid === 'tidak') {
            return back()->with('error', 'Laporan yang sudah ditolak tidak bisa diverifikasi. Gunakan tombol validasi jika ingin membatalkan penolakan.');
        }

        $validated = $request->validate([
            'id_pcnu'        => ['required', 'integer', 'exists:organisasi_pcnu,id_pcnu'],
            'prioritas'      => ['required', 'string', 'in:rendah,sedang,tinggi,kritis'],
            'status_insiden' => ['required', 'string', 'in:terverifikasi,respon'],
        ]);

        // Boundary check: PCNU hanya boleh assign ke PCNU sendiri
        $user = $request->user();
        if ($user->hasRole('pcnu') && (int) $validated['id_pcnu'] !== (int) $user->default_scope_id) {
            return back()->with('error', 'Anda hanya dapat memverifikasi laporan ke PCNU yurisdiksi Anda sendiri.');
        }

        // Pastikan PCNU yang dipilih sesuai dengan kabupaten laporan (bukan latlong,
        // karena latlong di daerah sulit sinyal akurasinya lemah)
        if ($laporan->id_kab) {
            // Prioritas utama: cocokkan PCNU dengan id_kab (pilihan user)
            $expectedPcnuId = $this->locationService->findPcnuByIdKab($laporan->id_kab);
            if ($expectedPcnuId && (int) $validated['id_pcnu'] !== (int) $expectedPcnuId) {
                return back()->with('error', 'PCNU yang dipilih tidak sesuai dengan kabupaten laporan.');
            }
        } elseif ($laporan->latitude && $laporan->longitude) {
            // Fallback: cek via latlong jika id_kab tidak tersedia
            $expectedPcnuId = $this->locationService->findPcnuByCoordinates(
                $laporan->latitude, $laporan->longitude
            );
            if ($expectedPcnuId && (int) $validated['id_pcnu'] !== (int) $expectedPcnuId) {
                return back()->with('error', 'PCNU yang dipilih tidak sesuai dengan lokasi laporan.');
            }
        }

        try {
            DB::beginTransaction();

            // Buat insiden baru menggunakan InsidenService
            $insiden = $this->insidenService->buatInsiden([
                'id_laporan_asal' => $laporan->id_laporan_kejadian,
                'id_jenis_bencana' => $laporan->id_jenis_bencana,
                'id_pcnu' => $validated['id_pcnu'],
                'prioritas' => $validated['prioritas'],
                'status_insiden' => 'draft',
            ]);

            $this->insidenService->ubahStatus(
                $insiden,
                $validated['status_insiden'],
                $request->user(),
                'Verifikasi dari Laporan Kejadian Publik'
            );



            $laporan->update([
                'is_valid'        => 'ya',
                'catatan_validasi' => 'Diverifikasi menjadi insiden: ' . $insiden->kode_kejadian,
            ]);

            DB::commit();

            return redirect()->route('insiden.show', $insiden)
                             ->with('success', 'Laporan berhasil diverifikasi menjadi Operasi Insiden.' . ($surat ?? false ? ' SPK: ' . $surat->nomor_surat_resmi : ''));

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal memverifikasi laporan: ' . $e->getMessage());
        }
    }

    /**
     * Tolak laporan kejadian (spam/hoaks)
     */
    public function reject(Request $request, LaporanKejadian $laporan)
    {
        $this->authorize('validasi', $laporan);

        if ($laporan->insiden) {
            return back()->with('error', 'Laporan ini sudah memiliki insiden terkait. Batalkan insiden terlebih dahulu.');
        }

        if ($laporan->is_valid === 'tidak') {
            return back()->with('error', 'Laporan ini sudah ditolak sebelumnya.');
        }

        $validated = $request->validate([
            'alasan'  => ['nullable', 'string', 'in:hoax,duplikat'],
            'catatan' => ['required', 'string', 'max:500'],
        ]);

        $laporan->update([
            'is_valid'      => 'tidak',
            'alasan_tolak'  => $validated['alasan'] ?? null,
            'catatan_validasi' => $validated['catatan'],
        ]);

        return redirect()->route('dashboard.laporan.index')
                         ->with('success', 'Laporan berhasil ditolak dan diarsipkan.');
    }
}
