<?php

namespace App\Http\Controllers\Api\Governance;

use App\Http\Controllers\Controller;
use App\Models\OperasiInsiden;
use App\Models\OperasiPleno;
use App\Models\OperasiPlenoKeputusan;
use App\Models\OperasiPlenoPeserta;
use App\Models\AuthUser;
use App\Models\MasterSuratJenis;
use App\Models\OperasiKlaster;
use App\Models\OperasiPenugasan;
use App\Models\OperasiPosaju;
use App\Models\OperasiPosajuKomandan;
use App\Services\NomorSuratService;
use App\Services\SuratPdfService;
use App\Services\PlanoService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PlanoApiController extends Controller
{
    public function index(Request $request, OperasiInsiden $insiden): JsonResponse
    {
        $items = OperasiPleno::where('id_insiden', $insiden->id_insiden)
            ->with(['keputusan', 'peserta.pengguna.profil', 'pimpinan.profil', 'notulis.profil'])
            ->orderBy('waktu_pleno', 'desc')
            ->paginate($request->get('per_page', 15));

        return response()->json([
            'data' => $items,
            'meta' => ['total' => $items->total()],
        ]);
    }

    public function show(OperasiInsiden $insiden, OperasiPleno $pleno): JsonResponse
    {
        if ($pleno->id_insiden !== $insiden->id_insiden) {
            return response()->json(['message' => 'Not found'], 404);
        }

        $pleno->load(['keputusan', 'peserta.pengguna.profil', 'pimpinan.profil', 'notulis.profil']);

        return response()->json(['data' => $pleno]);
    }

    public function store(Request $request, OperasiInsiden $insiden): JsonResponse
    {
        $this->authorize('create', [OperasiPleno::class, $insiden]);

        $validated = $request->validate([
            'nomor_pleno' => 'nullable|string|max:100',
            'waktu_pleno' => 'required|date',
            'jenis_pleno' => 'required|string|max:50',
            'pimpinan_pleno' => 'required|exists:auth_users,id_pengguna',
            'notulis_pleno' => 'required|exists:auth_users,id_pengguna',
            'lokasi_pleno' => 'nullable|string|max:255',
        ]);

        $validated['id_insiden'] = $insiden->id_insiden;
        $validated['status_pleno'] = 'draft';

        $pleno = app(PlanoService::class)->buatPlano($validated);

        return response()->json(['message' => 'Pleno dibuat.', 'data' => $pleno], 201);
    }

    public function update(Request $request, OperasiInsiden $insiden, OperasiPleno $pleno): JsonResponse
    {
        $this->authorize('update', $pleno);

        if ($pleno->id_insiden !== $insiden->id_insiden) {
            return response()->json(['message' => 'Not found'], 404);
        }

        $validated = $request->validate([
            'nomor_pleno' => 'nullable|string|max:100',
            'waktu_pleno' => 'sometimes|date',
            'jenis_pleno' => 'sometimes|string|max:50',
            'pimpinan_pleno' => 'sometimes|exists:auth_users,id_pengguna',
            'notulis_pleno' => 'sometimes|exists:auth_users,id_pengguna',
            'lokasi_pleno' => 'nullable|string|max:255',
            'hasil_umum' => 'nullable|string',
        ]);

        $pleno->update($validated);

        return response()->json(['message' => 'Pleno diperbarui.', 'data' => $pleno]);
    }

    public function destroy(OperasiInsiden $insiden, OperasiPleno $pleno): JsonResponse
    {
        $this->authorize('delete', $pleno);

        if ($pleno->id_insiden !== $insiden->id_insiden) {
            return response()->json(['message' => 'Not found'], 404);
        }

        $pleno->delete();
        return response()->json(['message' => 'Pleno dihapus.']);
    }

    public function finalisasi(OperasiInsiden $insiden, OperasiPleno $pleno): JsonResponse
    {
        $this->authorize('finalisasi', $pleno);

        if ($pleno->id_insiden !== $insiden->id_insiden) {
            return response()->json(['message' => 'Not found'], 404);
        }

        $pleno->update([
            'status_pleno' => 'final',
            'disetujui_oleh' => Auth::id() ?? 1,
            'waktu_disetujui' => now(),
            'waktu_difinalisasi' => now(),
        ]);

        event(new \App\Events\Operasi\PlenoFinalized($pleno));

        return response()->json(['message' => 'Pleno difinalisasi.']);
    }

    public function tambahKeputusan(Request $request, OperasiInsiden $insiden, OperasiPleno $pleno): JsonResponse
    {
        $this->authorize('tambahKeputusan', $pleno);

        if ($pleno->id_insiden !== $insiden->id_insiden) {
            return response()->json(['message' => 'Not found'], 404);
        }

        $validated = $request->validate([
            'kategori_objek' => 'required|string|max:50',
            'jenis_keputusan' => 'required|string|max:50',
            'deskripsi_keputusan' => 'required|string',
            'payload' => 'nullable|array',
        ]);

        $tipeTargetMap = [
            'status_insiden' => 'insiden',
            'aktivasi_posko' => 'pos_aju',
            'aktivasi_klaster' => 'klaster',
            'mobilisasi_relawan' => 'personil',
            'eskalasi_wilayah' => 'insiden',
            'logistik' => 'logistik',
            'lainnya' => 'insiden',
        ];

        $kategoriObjek = $validated['kategori_objek'];
        $tipeTarget = $tipeTargetMap[$kategoriObjek] ?? 'insiden';

        $keputusan = OperasiPlenoKeputusan::create([
            'id_pleno' => $pleno->id_pleno,
            'kategori_objek' => $kategoriObjek,
            'jenis_keputusan' => $validated['jenis_keputusan'],
            'tipe_target_keputusan' => $tipeTarget,
            'deskripsi_keputusan' => $validated['deskripsi_keputusan'],
            'payload_eksekusi' => $validated['payload'] ?? null,
            'status_pelaksanaan' => 'rencana',
        ]);

        try {
            DB::transaction(function () use ($pleno, $keputusan, $validated) {
                $payload = $validated['payload'] ?? [];
                $kategori = $keputusan->kategori_objek;

                if ($kategori === 'aktivasi_posko') {
                    $this->executeAktivasiPosko($pleno, $keputusan, $payload);
                } elseif ($kategori === 'aktivasi_klaster') {
                    $this->executeAktivasiKlaster($pleno, $keputusan, $payload);
                }

                $keputusan->update(['status_pelaksanaan' => 'selesai']);
            });
        } catch (\Exception $e) {
            Log::error("Gagal mengeksekusi keputusan {$keputusan->id_keputusan}: " . $e->getMessage());
            return response()->json(['message' => 'Keputusan tersimpan tetapi gagal dieksekusi: ' . $e->getMessage(), 'data' => $keputusan], 500);
        }

        return response()->json(['message' => 'Keputusan berhasil ditambahkan dan dieksekusi.', 'data' => $keputusan], 201);
    }

    private function executeAktivasiPosko($pleno, $keputusan, array $payload): void
    {
        if (!empty($payload['id_koordinator'])) {
            $this->validasiKoordinatorScope($pleno->id_insiden, $payload['id_koordinator']);
        }

        $posAju = OperasiPosaju::create([
            'id_insiden' => $pleno->id_insiden,
            'id_pleno_pendirian' => $pleno->id_pleno,
            'id_pleno_keputusan' => $keputusan->id_keputusan,
            'nama_posaju' => $payload['nama_posaju'] ?? 'Pos Aju Utama',
            'alamat_lokasi' => $payload['lokasi_posaju'] ?? $pleno->lokasi_pleno,
            'pj_posaju' => $payload['id_koordinator'] ?? null,
            'status_alur' => 'aktif',
            'waktu_diaktifkan' => now(),
        ]);

        $keputusan->update([
            'referensi_tabel' => 'operasi_posaju',
            'referensi_id' => $posAju->id_posaju,
        ]);

        if (!empty($payload['id_koordinator'])) {
            OperasiPosajuKomandan::create([
                'id_posaju' => $posAju->id_posaju,
                'id_pengguna' => $payload['id_koordinator'],
                'id_pleno_keputusan' => $keputusan->id_keputusan,
                'waktu_mulai_tugas' => now(),
            ]);

            $penugasan = OperasiPenugasan::create([
                'id_insiden' => $pleno->id_insiden,
                'id_pengguna' => $payload['id_koordinator'],
                'peran_otoritas' => 'koordinator_pos',
                'status_penugasan' => 'draft',
                'waktu_mulai' => now(),
                'ditugaskan_oleh' => Auth::id() ?? 1,
                'catatan' => 'Auto-assigned by Pleno ID: ' . $pleno->id_pleno . ' | Pos Aju: ' . $posAju->nama_posaju,
            ]);

            $this->buatSuratTugasUntukPenugasan($penugasan, $pleno);
        }
    }

    private function executeAktivasiKlaster($pleno, $keputusan, array $payload): void
    {
        $jenisKlasterArray = $payload['jenis_klaster'] ?? [];
        $idKoordinator = $payload['id_koordinator'] ?? null;

        if ($idKoordinator) {
            $this->validasiKoordinatorScope($pleno->id_insiden, $idKoordinator);
        }

        foreach ($jenisKlasterArray as $idMasterKlaster) {
            $existing = OperasiKlaster::where('id_insiden', $pleno->id_insiden)
                ->where('id_master_klaster', $idMasterKlaster)
                ->whereNull('waktu_ditutup')
                ->first();

            if (!$existing) {
                $klaster = OperasiKlaster::create([
                    'id_insiden' => $pleno->id_insiden,
                    'id_master_klaster' => $idMasterKlaster,
                    'status_klaster' => 'aktif',
                    'waktu_aktivasi' => now(),
                    'id_pembuat' => $pleno->disetujui_oleh ?: Auth::id() ?? 1,
                    'dibutuhkan' => true,
                    'catatan' => 'Activated by Pleno ID: ' . $pleno->id_pleno,
                ]);

                if ($idKoordinator) {
                    $penugasan = OperasiPenugasan::create([
                        'id_insiden' => $pleno->id_insiden,
                        'id_pengguna' => $idKoordinator,
                        'id_klaster_operasi' => $klaster->id_klaster_operasi,
                        'peran_otoritas' => 'koordinator_klaster',
                        'status_penugasan' => 'draft',
                        'waktu_mulai' => now(),
                        'ditugaskan_oleh' => Auth::id() ?? 1,
                        'catatan' => 'Auto-assigned by Pleno ID: ' . $pleno->id_pleno . ' | Klaster ID: ' . $idMasterKlaster,
                    ]);

                    $this->buatSuratTugasUntukPenugasan($penugasan, $pleno);
                }
            }
        }
    }

    private function buatSuratTugasUntukPenugasan($penugasan, $pleno): void
    {
        $insiden = OperasiInsiden::find($pleno->id_insiden);
        if (!$insiden) return;

        $jenisSurat = MasterSuratJenis::where('kode_jenis', 'ST')
            ->orWhere('nama_jenis', 'like', '%tugas%')
            ->first();
        if (!$jenisSurat) return;

        $nomorSurat = app(NomorSuratService::class)->generate($jenisSurat, now()->year, $insiden);

        $penerima = AuthUser::find($penugasan->id_pengguna);
        $namaPenerima = $penerima?->profil?->nama_lengkap ?? $penerima?->no_hp ?? 'Personel';
        $peranLabel = str_replace('_', ' ', $penugasan->peran_otoritas);
        $pcnu = $insiden->pcnu;

        $isiSnapshot = "Memberikan tugas kepada:\n"
                     . "Nama: " . $namaPenerima . "\n"
                     . "Peran: " . ucfirst($peranLabel) . "\n\n"
                     . "Untuk melaksanakan tugas operasi atas insiden " . $insiden->kode_kejadian
                     . " di wilayah PCNU " . optional($pcnu)->nama_pcnu . ".\n\n"
                     . "Rincian Tugas:\n"
                     . ($penugasan->catatan ?? '-') . "\n\n"
                     . "Laporan assessment situasi darurat terlampir dalam dokumen ini untuk bahan review.";

        $surat = \App\Models\DokumenSuratUtama::create([
            'id_insiden' => $insiden->id_insiden,
            'id_jenis_surat' => $jenisSurat->id_jenis_surat,
            'nomor_surat_resmi' => $nomorSurat,
            'perihal' => 'Surat Tugas Operasi — ' . $insiden->kode_kejadian,
            'tgl_terbit' => now(),
            'id_pengguna_ttd' => $pleno->disetujui_oleh ?: Auth::id() ?? 1,
            'status_surat' => 'siap_tanda_tangan',
            'isi_surat_snapshot' => $isiSnapshot,
        ]);

        $pdfService = app(SuratPdfService::class);
        $assessment = \App\Models\AssessmentUtama::where('id_insiden', $insiden->id_insiden)
            ->where('is_latest', true)
            ->first();

        try {
            $pdfPath = $pdfService->generateWithLampiran($surat, $assessment);
            $surat->update([
                'status_surat' => 'ditandatangani',
                'file_pdf_path' => $pdfPath,
            ]);
            $penugasan->update(['id_surat_tugas' => $surat->id_surat]);
        } catch (\Exception $e) {
            Log::warning("Gagal generate PDF Surat Tugas: " . $e->getMessage());
        }
    }

    private function validasiKoordinatorScope(int $idInsiden, int $idPengguna): void
    {
        $insiden = OperasiInsiden::findOrFail($idInsiden);
        $user = AuthUser::find($idPengguna);

        if (!$user) {
            throw new \Exception("User koordinator tidak ditemukan.");
        }

        if ($user->default_scope_type === 'pcnu' && $user->default_scope_id !== $insiden->id_pcnu) {
            throw new \Exception("Koordinator harus berasal dari PCNU setempat.");
        }
    }

    public function hapusKeputusan(OperasiInsiden $insiden, OperasiPleno $pleno, OperasiPlenoKeputusan $keputusan): JsonResponse
    {
        $this->authorize('tambahKeputusan', $pleno);

        if ($pleno->id_insiden !== $insiden->id_insiden || $keputusan->id_pleno !== $pleno->id_pleno) {
            return response()->json(['message' => 'Not found'], 404);
        }

        $keputusan->delete();
        return response()->json(['message' => 'Keputusan dihapus.']);
    }

    public function tambahPeserta(Request $request, OperasiInsiden $insiden, OperasiPleno $pleno): JsonResponse
    {
        $this->authorize('tambahPeserta', $pleno);

        if ($pleno->id_insiden !== $insiden->id_insiden) {
            return response()->json(['message' => 'Not found'], 404);
        }

        $validated = $request->validate([
            'id_pengguna' => 'required|exists:auth_users,id_pengguna',
            'peran_dalam_rapat' => 'nullable|string|max:100',
        ]);

        $peserta = $pleno->peserta()->create($validated);

        return response()->json(['message' => 'Peserta ditambahkan.', 'data' => $peserta], 201);
    }

    public function hapusPeserta(OperasiInsiden $insiden, OperasiPleno $pleno, OperasiPlenoPeserta $peserta): JsonResponse
    {
        $this->authorize('tambahPeserta', $pleno);

        if ($pleno->id_insiden !== $insiden->id_insiden || $peserta->id_pleno !== $pleno->id_pleno) {
            return response()->json(['message' => 'Not found'], 404);
        }

        $peserta->delete();
        return response()->json(['message' => 'Peserta dihapus.']);
    }

    public function downloadPdf(OperasiInsiden $insiden, OperasiPleno $pleno)
    {
        $this->authorize('view', $pleno);

        if ($pleno->id_insiden !== $insiden->id_insiden) {
            return response()->json(['message' => 'Not found'], 404);
        }

        $pdfService = app(\App\Services\SuratPdfService::class);
        $path = $pdfService->generatePlenoPdf($pleno);

        return \Illuminate\Support\Facades\Storage::download($path);
    }
}
