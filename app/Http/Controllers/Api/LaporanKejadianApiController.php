<?php

namespace App\Http\Controllers\Api;

use App\Application\Media\Commands\UploadMediaCommand;
use App\Application\Media\Handlers\UploadMediaHandler;
use App\Http\Controllers\Controller;
use App\Models\AuthUser;
use App\Models\LaporanKejadian;
use App\Models\MasterSuratJenis;
use App\Models\OperasiInsiden;
use App\Models\OperasiPenugasan;
use App\Models\OperasiPenugasanHistory;
use App\Services\LocationService;
use App\Services\SuratService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LaporanKejadianApiController extends Controller
{
    public function __construct(
        private LocationService $locationService,
        private UploadMediaHandler $uploadMediaHandler,
        private SuratService $suratService,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', LaporanKejadian::class);

        $user = $request->user();
        
        $items = LaporanKejadian::query()
            ->with(['jenisBencana:id_jenis,nama_bencana', 'pengguna.profil:id_pengguna,nama_lengkap', 'pcnu:id_pcnu,nama_pcnu'])
            ->when($request->is_valid, fn($q, $v) => $q->where('is_valid', $v))
            ->when($request->id_jenis_bencana, fn($q, $v) => $q->where('id_jenis_bencana', $v))
            ->when($request->tanggal_mulai, fn($q, $v) => $q->whereDate('dibuat_pada', '>=', $v))
            ->when($request->tanggal_selesai, fn($q, $v) => $q->whereDate('dibuat_pada', '<=', $v))
            ->when($user && $user->default_scope_type === 'pcnu' && $user->hasRole('pcnu'), fn($q) => $q->where('id_pcnu', $user->default_scope_id))
            ->latest('dibuat_pada')
            ->paginate($request->get('per_page', 15));

        return response()->json([
            'data' => $items->map(fn($l) => [
                'id'                => $l->id_laporan_kejadian,
                'kode_kejadian'     => $l->kode_kejadian,
                'nama_pelapor'      => $l->nama_pelapor,
                'hp_pelapor'        => $l->hp_pelapor,
                'jenis_bencana'     => $l->jenisBencana?->nama_bencana,
                'keterangan'        => $l->keterangan_situasi,
                'titik_kenal'       => $l->titik_kenal,
                'alamat_lengkap'    => $l->alamat_lengkap,
                'pcnu'              => $l->pcnu?->nama_pcnu,
                'id_pcnu'           => $l->id_pcnu,
                'waktu'             => $l->waktu_kejadian?->toIso8601String(),
                'latitude'          => $l->latitude,
                'longitude'         => $l->longitude,
                'is_valid'          => $l->is_valid,
                'alasan_tolak'      => $l->alasan_tolak,
                'photo'             => $l->photo_path,
                'media_url'         => media_url($l->photo_path),
                'dibuat_pada'       => $l->dibuat_pada?->toIso8601String(),
            ]),
            'meta' => ['total' => $items->total(), 'current_page' => $items->currentPage()],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'id_jenis_bencana' => 'required|exists:bencana_master_jenis,id_jenis',
            'nama_pelapor'     => 'required|string|max:100',
            'hp_pelapor'       => 'required|string|max:20',
            'keterangan_situasi' => 'required|string',
            'titik_kenal'      => 'nullable|string|max:255',
            'waktu_kejadian'   => 'required|date',
            'id_kab'           => 'nullable|string|exists:wilayah_kabupaten,id_kab',
            'id_kec'           => 'nullable|string|exists:wilayah_kecamatan,id_kec',
            'id_desa'          => 'nullable|string|exists:wilayah_desa,id_desa',
            'latitude'         => 'nullable|numeric',
            'longitude'        => 'nullable|numeric',
            'foto'             => ['nullable', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
        ]);

        if ($request->files->has('foto') && !$request->hasFile('foto')) {
            throw ValidationException::withMessages([
                'foto' => ['File foto gagal diupload. Ukuran file mungkin melebihi batas maksimal.'],
            ]);
        }

        $validated['kode_kejadian'] = LaporanKejadian::generateKodeKejadian();
        $validated['is_valid'] = 'menunggu';

        if ($request->user()) {
            $validated['id_pengguna'] = $request->user()->id_pengguna;
        }

        $lat = (float) ($validated['latitude'] ?? 0);
        $lng = (float) ($validated['longitude'] ?? 0);

        $validated['latitude'] = $lat;
        $validated['longitude'] = $lng;

        // Prioritaskan id_kab (dipilih user) untuk menentukan PCNU, bukan latlong
        // karena latlong di daerah sulit sinyal akurasinya lemah
        $idPcnu = null;
        if (!empty($validated['id_kab'])) {
            $idPcnu = $this->locationService->findPcnuByIdKab($validated['id_kab']);
        }

        if (empty($validated['id_kab']) && $lat && $lng) {
            // Fallback: tentukan PCNU dari latlong jika id_kab tidak tersedia
            $generated = $this->locationService->reverseGeocode($lat, $lng);
            $alamatLengkap = $generated['alamat_lengkap'] ?? null;
            $idPcnu = $this->locationService->findPcnuByCoordinates($lat, $lng);

            if ($alamatLengkap) {
                $validated['alamat_lengkap'] = $alamatLengkap;
                if (empty($validated['titik_kenal'])) {
                    $validated['titik_kenal'] = $alamatLengkap;
                }
            }
        } elseif (!empty($validated['id_kab']) && $lat && $lng) {
            // Jika id_kab dan latlong sama-sama ada, tetap pakai id_kab untuk PCNU,
            // tapi gunakan latlong untuk reverse geocode alamat saja
            $generated = $this->locationService->reverseGeocode($lat, $lng);
            $alamatLengkap = $generated['alamat_lengkap'] ?? null;
            if ($alamatLengkap) {
                $validated['alamat_lengkap'] = $alamatLengkap;
            }
        }

        if ($idPcnu) {
            $validated['id_pcnu'] = $idPcnu;
        }

        unset($validated['foto']);

        $laporan = LaporanKejadian::create($validated);

        if ($request->hasFile('foto')) {
            $mediaResult = $this->uploadMediaHandler->handle(new UploadMediaCommand(
                entityType: 'laporan',
                entityId: $laporan->id_laporan_kejadian,
                file: $request->file('foto'),
                visibility: 'PUBLIC',
            ));
            $laporan->update(['photo_path' => $mediaResult->path]);
        }

        return response()->json([
            'data' => [
                'id' => $laporan->id_laporan_kejadian,
                'kode_kejadian' => $laporan->kode_kejadian,
                'photo_path' => $laporan->photo_path,
                'media_url' => media_url($laporan->photo_path),
                'message' => 'Laporan berhasil dikirim. Tim akan melakukan verifikasi.',
            ],
        ], 201);
    }

    public function show(LaporanKejadian $laporan): JsonResponse
    {
        $this->authorize('view', $laporan);

        $laporan->load(['jenisBencana', 'pengguna.profil', 'insiden']);

        return response()->json([
            'data' => [
                'id'                => $laporan->id_laporan_kejadian,
                'kode_kejadian'     => $laporan->kode_kejadian,
                'nama_pelapor'      => $laporan->nama_pelapor,
                'hp_pelapor'        => $laporan->hp_pelapor,
                'jenis_bencana'     => [
                    'id'   => $laporan->jenisBencana?->id_jenis,
                    'nama' => $laporan->jenisBencana?->nama_bencana,
                ],
                'keterangan_situasi' => $laporan->keterangan_situasi,
                'titik_kenal'       => $laporan->titik_kenal,
                'alamat_lengkap'    => $laporan->alamat_lengkap,
                'id_pcnu'           => $laporan->id_pcnu,
                'pcnu'              => $laporan->pcnu?->nama_pcnu,
                'waktu_kejadian'    => $laporan->waktu_kejadian?->toIso8601String(),
                'latitude'          => $laporan->latitude,
                'longitude'         => $laporan->longitude,
                'photo_path'        => $laporan->photo_path,
                'media_url'         => media_url($laporan->photo_path),
                'is_valid'          => $laporan->is_valid,
                'alasan_tolak'      => $laporan->alasan_tolak,
                'catatan_validasi'  => $laporan->catatan_validasi,
                'pelapor'           => $laporan->pengguna?->profil?->nama_lengkap,
                'insiden_terkait'   => $laporan->insiden ? [
                    'id'   => $laporan->insiden->id_insiden,
                    'kode' => $laporan->insiden->kode_kejadian,
                ] : null,
                'dibuat_pada'       => $laporan->dibuat_pada?->toIso8601String(),
            ],
        ]);
    }

    public function validasi(Request $request, LaporanKejadian $laporan): JsonResponse
    {
        $this->authorize('validasi', $laporan);

        $validated = $request->validate([
            'is_valid'         => 'required|in:ya,tidak',
            'alasan_tolak'     => 'nullable|string|in:hoax,duplikat',
            'catatan_validasi' => 'nullable|string|max:500',
            'latitude'         => 'nullable|numeric',
            'longitude'        => 'nullable|numeric',
            'alamat_lengkap'   => 'nullable|string|max:255',
            'id_pcnu'          => 'nullable|integer|exists:organisasi_pcnu,id_pcnu',
        ]);

        $current = $laporan->is_valid;
        $target  = $validated['is_valid'];

        if ($current === $target) {
            return response()->json(['message' => 'Status laporan sudah ' . $target . '.'], 422);
        }

        if ($current === 'ya' && $target === 'tidak') {
            return response()->json(['message' => 'Laporan sudah diverifikasi dengan insiden terkait. Tidak bisa diubah menjadi ditolak.'], 422);
        }

        if ($current === 'tidak' && $target === 'ya') {
            return response()->json(['message' => 'Laporan yang ditolak tidak bisa langsung divalidasi. Gunakan endpoint eskalasi.'], 422);
        }

        if ($target === 'ya' && $laporan->insiden) {
            return response()->json(['message' => 'Laporan ini sudah memiliki insiden terkait.'], 409);
        }

        if ($target === 'tidak' && $laporan->insiden) {
            return response()->json(['message' => 'Tidak bisa menolak laporan yang sudah memiliki insiden.'], 422);
        }

        // Tetapkan id_pcnu: prioritaskan yang dikirim dari request, fallback ke auto-detect dari koordinat
        if ($validated['is_valid'] === 'ya' && is_null($laporan->id_pcnu)) {
            if (!empty($validated['id_pcnu'])) {
                // id_pcnu dikirim eksplisit dari admin
            } else {
                // Fallback: auto-detect dari koordinat
                $checkLat = $validated['latitude'] ?? $laporan->latitude;
                $checkLng = $validated['longitude'] ?? $laporan->longitude;
                if ($checkLat && $checkLng) {
                    $idPcnu = $this->locationService->findPcnuByCoordinates($checkLat, $checkLng);
                    if ($idPcnu) {
                        $validated['id_pcnu'] = $idPcnu;
                    }
                }
            }
        }

        $laporan->update($validated);

        return response()->json(['message' => 'Laporan berhasil divalidasi.', 'data' => [
            'id'       => $laporan->id_laporan_kejadian,
            'is_valid' => $laporan->is_valid,
        ]]);
    }

    public function eskalasiInsiden(Request $request, LaporanKejadian $laporan): JsonResponse
    {
        $this->authorize('eskalasi', $laporan);

        if ($laporan->is_valid !== 'ya') {
            return response()->json(['message' => 'Hanya laporan valid yang bisa dieskalasi.'], 422);
        }

        if ($laporan->insiden) {
            return response()->json(['message' => 'Laporan ini sudah memiliki insiden terkait.', 'data' => [
                'id_insiden' => $laporan->insiden->id_insiden,
            ]], 409);
        }

        $idPcnu = $laporan->id_pcnu;

        $validated = $request->validate([
            'petugas_trc_ids'   => ['nullable', 'array'],
            'petugas_trc_ids.*' => ['integer', 'exists:auth_users,id_pengguna'],
            'prioritas'         => ['nullable', 'string', 'in:rendah,sedang,tinggi,kritis'],
            'status_insiden'    => ['nullable', 'string', 'in:draft,terverifikasi,respon,pemulihan'],
        ]);

        if (is_null($idPcnu)) {
            $pcnuValidated = $request->validate([
                'id_pcnu' => 'required|integer|exists:organisasi_pcnu,id_pcnu',
            ]);
            $idPcnu = $pcnuValidated['id_pcnu'];
        }

        // Prioritaskan id_kab untuk validasi PCNU (bukan latlong, karena akurasi lemah di daerah sulit sinyal)
        if ($laporan->id_kab) {
            $expectedPcnu = $this->locationService->findPcnuByIdKab($laporan->id_kab);
            if ($expectedPcnu && (int) $idPcnu !== (int) $expectedPcnu) {
                return response()->json([
                    'message' => 'PCNU tidak sesuai dengan kabupaten laporan.',
                ], 422);
            }
        } elseif ($laporan->latitude && $laporan->longitude) {
            // Fallback via latlong jika id_kab tidak tersedia
            $expectedPcnu = $this->locationService->findPcnuByCoordinates(
                $laporan->latitude, $laporan->longitude
            );
            if ($expectedPcnu && (int) $idPcnu !== (int) $expectedPcnu) {
                return response()->json([
                    'message' => 'PCNU tidak sesuai dengan lokasi koordinat laporan.',
                ], 422);
            }
        }

        $insiden = DB::transaction(function () use ($laporan, $idPcnu, $validated) {
            $insiden = OperasiInsiden::create([
                'kode_kejadian'    => $laporan->kode_kejadian,
                'id_laporan_asal'  => $laporan->id_laporan_kejadian,
                'id_jenis_bencana' => $laporan->id_jenis_bencana,
                'id_pcnu'          => $idPcnu,
                'status_insiden'   => $validated['status_insiden'] ?? 'draft',
                'prioritas'        => $validated['prioritas'] ?? 'sedang',
                'waktu_mulai'      => $laporan->waktu_kejadian,
            ]);

            if (!empty($validated['petugas_trc_ids'])) {
                $jenisSurat = MasterSuratJenis::where('kode_jenis', 'ST')
                    ->orWhere('nama_jenis', 'like', '%tugas%')
                    ->first();

                if ($jenisSurat) {
                    $trcNames = [];
                    foreach ($validated['petugas_trc_ids'] as $trcId) {
                        $penerima = AuthUser::with('profil')->find($trcId);
                        $trcNames[] = $penerima?->profil?->nama_lengkap ?? $penerima?->no_hp ?? 'Anggota TRC';
                    }

                    $namaPenerimaList = implode("\n- ", $trcNames);

                    $isiSnapshot = "Memberikan tugas kepada:\n- " . $namaPenerimaList . "\n"
                                 . "\nPeran: TRC\n"
                                 . "Untuk melaksanakan tugas assessment atas insiden " . $insiden->kode_kejadian . ".\n"
                                 . "Berdasarkan eskalasi laporan: " . $laporan->kode_kejadian . ".";

                    $surat = $this->suratService->buatSurat([
                        'id_insiden'         => $insiden->id_insiden,
                        'id_jenis_surat'     => $jenisSurat->id_jenis_surat,
                        'perihal'            => 'Surat Perintah Tugas Assessment (SPK)',
                        'tgl_terbit'         => now(),
                        'id_pengguna_ttd'    => Auth::id(),
                        'status_surat'       => 'siap_tanda_tangan', // Menunggu persetujuan Ketua M5
                        'isi_surat_snapshot' => $isiSnapshot,
                    ]);

                    $insiden->update([
                        'no_spk_assesment'  => $surat->nomor_surat_resmi,
                        'tgl_spk_assesment' => now(),
                        'id_pemberi_spk'    => Auth::id(),
                    ]);

                    foreach ($validated['petugas_trc_ids'] as $trcId) {
                        $alreadyAssigned = OperasiPenugasan::where('id_insiden', $insiden->id_insiden)
                            ->where('id_pengguna', $trcId)
                            ->whereNotIn('status_penugasan', ['completed', 'cancelled', 'rejected'])
                            ->exists();

                        if (!$alreadyAssigned) {
                            $penugasan = OperasiPenugasan::create([
                                'uuid_penugasan'   => (string) Str::uuid(),
                                'id_insiden'       => $insiden->id_insiden,
                                'id_pengguna'      => $trcId,
                                'peran_otoritas'   => 'trc',
                                'status_penugasan' => 'draft', // Menunggu SPK disetujui
                                'waktu_mulai'      => now(),
                                'ditugaskan_oleh'  => Auth::id(),
                                'catatan'          => 'Auto-created dari eskalasi laporan: ' . $laporan->kode_kejadian,
                            ]);

                            OperasiPenugasanHistory::create([
                                'id_penugasan'      => $penugasan->id_penugasan,
                                'status_sebelumnya'  => null,
                                'status_baru'        => 'draft',
                                'waktu_perubahan'    => now(),
                                'diubah_oleh'        => Auth::id(),
                            ]);
                        }
                    }
                }
            }

            // Untuk kompatibilitas field lama, simpan id TRC pertama (jika ada) ke id_petugas_trc
            $laporan->update([
                'id_petugas_trc' => !empty($validated['petugas_trc_ids']) ? $validated['petugas_trc_ids'][0] : null,
            ]);

            return $insiden;
        });

        return response()->json([
            'message' => 'Insiden berhasil dibuat dari laporan.',
            'data'    => ['id_insiden' => $insiden->id_insiden, 'kode' => $insiden->kode_kejadian],
        ], 201);
    }

    public function peta(Request $request): JsonResponse
    {
        $items = LaporanKejadian::valid()
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->with('jenisBencana:id_jenis,nama_bencana')
            ->get()
            ->map(fn($l) => [
                'type' => 'Feature',
                'geometry' => [
                    'type'        => 'Point',
                    'coordinates' => [(float) $l->longitude, (float) $l->latitude],
                ],
                'properties' => [
                    'id'            => $l->id_laporan_kejadian,
                    'kode'          => $l->kode_kejadian,
                    'jenis_bencana' => $l->jenisBencana?->nama_bencana,
                    'waktu'         => $l->waktu_kejadian?->toIso8601String(),
                ],
            ]);

        return response()->json([
            'type'     => 'FeatureCollection',
            'features' => $items,
        ]);
    }

    public function tracking(Request $request, string $kodeKejadian): JsonResponse
    {
        // Public API to track report status
        $laporan = LaporanKejadian::with(['insiden.riwayatStatus'])->where('kode_kejadian', $kodeKejadian)->first();

        if (!$laporan) {
            return response()->json(['message' => 'Laporan tidak ditemukan.'], 404);
        }

        $tracking = [];
        
        // 1. DITERIMA
        $tracking[] = [
            'status' => 'DITERIMA',
            'time' => $laporan->dibuat_pada?->toIso8601String(),
            'description' => 'Laporan berhasil diterima oleh sistem.'
        ];

        // 2. VERIFIED
        if ($laporan->is_valid === 'ya') {
            $tracking[] = [
                'status' => 'VERIFIED',
                'time' => $laporan->diperbarui_pada?->toIso8601String(),
                'description' => 'Laporan valid dan telah diverifikasi oleh Pusdalops.'
            ];
        } elseif ($laporan->is_valid === 'tidak') {
            $tracking[] = [
                'status' => 'REJECTED',
                'time' => $laporan->diperbarui_pada?->toIso8601String(),
                'description' => 'Laporan ditolak. Alasan: ' . ($laporan->alasan_tolak ?? 'Tidak valid')
            ];
            return response()->json(['data' => $tracking]);
        }

        // Jika memiliki insiden, kita ambil riwayat status insiden
        if ($laporan->insiden) {
            $riwayat = collect($laporan->insiden->riwayatStatus ?? [])->sortBy('waktu_perubahan');
            
            // 3. TRC DEPLOYED / ASSESSMENT
            $hasAssessment = $riwayat->contains(fn($r) => in_array($r->status_baru, ['assessment', 'trc_deployed']));
            if ($hasAssessment) {
                $assessmentLog = $riwayat->first(fn($r) => in_array($r->status_baru, ['assessment', 'trc_deployed']));
                $tracking[] = [
                    'status' => 'ASSESSMENT',
                    'time' => $assessmentLog->waktu_perubahan,
                    'description' => 'Tim Reaksi Cepat (TRC) menuju lokasi untuk kaji cepat.'
                ];
            }

            // 4. RESPONSE
            $hasResponse = $riwayat->contains(fn($r) => $r->status_baru === 'response');
            if ($hasResponse) {
                $responseLog = $riwayat->first(fn($r) => $r->status_baru === 'response');
                $tracking[] = [
                    'status' => 'RESPONSE',
                    'time' => $responseLog->waktu_perubahan,
                    'description' => 'Operasi tanggap darurat sedang berlangsung.'
                ];
            }

            // 5. RECOVERY / CLOSED
            $hasClosed = $riwayat->contains(fn($r) => in_array($r->status_baru, ['closed', 'recovery']));
            if ($hasClosed) {
                $closedLog = $riwayat->first(fn($r) => in_array($r->status_baru, ['closed', 'recovery']));
                $tracking[] = [
                    'status' => 'RECOVERY',
                    'time' => $closedLog->waktu_perubahan,
                    'description' => 'Penanganan selesai atau masuk masa pemulihan.'
                ];
            }
        }

        return response()->json([
            'status' => 'success',
            'data' => $tracking
        ]);
    }
}
