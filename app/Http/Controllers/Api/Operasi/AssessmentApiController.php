<?php

namespace App\Http\Controllers\Api\Operasi;

use App\Http\Controllers\Controller;
use App\Http\Requests\Operasi\StoreAssessmentRequest;
use App\Models\OperasiInsiden;
use App\Models\AssessmentUtama;
use App\Models\AssessmentDampakManusia;
use App\Models\AssessmentKebutuhanMendesak;
use App\Services\Operasi\AssessmentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Resources\Operasi\AssessmentResource;

class AssessmentApiController extends Controller
{
    private AssessmentService $service;

    public function __construct(AssessmentService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request): JsonResponse
    {
        $request->validate(['uuid_insiden' => 'required|exists:operasi_insiden,uuid_insiden']);
        $insiden = OperasiInsiden::where('uuid_insiden', $request->query('uuid_insiden'))->firstOrFail();

        $this->authorize('viewAny', [AssessmentUtama::class, $insiden]);

        $query = AssessmentUtama::with(['dampakManusiaV2', 'kebutuhanMendesak', 'kebutuhanNumerik'])
            ->where('id_insiden', $insiden->id_insiden);

        // Incremental sync
        if ($request->has('updated_since')) {
            $query->where('diperbarui_pada', '>', $request->query('updated_since'));
        }

        // Filtering standard
        $filterable = ['jenis_laporan'];
        foreach ($filterable as $field) {
            if ($request->has($field)) {
                $query->where($field, $request->query($field));
            }
        }

        // Sorting standard
        $sortBy = $request->query('sort_by', 'waktu_assesment');
        $sortOrder = $request->query('sort_order', 'desc');
        $allowedSortColumns = ['waktu_assesment', 'dibuat_pada', 'diperbarui_pada', 'jenis_laporan'];
        
        if (!in_array($sortBy, $allowedSortColumns)) {
            $sortBy = 'waktu_assesment';
        }
        if (!in_array(strtolower($sortOrder), ['asc', 'desc'])) {
            $sortOrder = 'desc';
        }

        $assessments = $query->orderBy($sortBy, $sortOrder)->paginate(15);

        return $this->apiPaginatedResponse($assessments, AssessmentResource::class);
    }

    public function store(StoreAssessmentRequest $request): JsonResponse
    {
        $data = $request->validated();
        $insiden = OperasiInsiden::where('uuid_insiden', $data['uuid_insiden'])->firstOrFail();
        $data['id_insiden'] = $insiden->id_insiden;

        $this->authorize('create', [AssessmentUtama::class, $insiden]);

        $assessment = $this->service->createAssessment($data);
        $assessment->load(['dampakManusiaV2', 'kebutuhanMendesak', 'kebutuhanNumerik']);

        return $this->apiResponse(new AssessmentResource($assessment), 'Data berhasil disimpan', 201);
    }

    public function show(AssessmentUtama $assessment, Request $request): JsonResponse
    {
        $this->authorize('view', $assessment);
        $assessment->loadMissing([
            'insiden', 'dampakManusiaV2', 'kebutuhanMendesak', 'kebutuhanNumerik', 
            'petugas.profil', 'dampakRumah', 'dampakFasum', 'dampakVital', 
            'dampakLingkungan', 'dampakEkonomi', 'lokasiDetail', 'narasiDetail', 
            'kebutuhanLanjutan', 'narasiKejadian', 'biodataKejadian'
        ]);

        $resource = new AssessmentResource($assessment);
        $formData = $this->buildFormData($assessment);
        
        return response()->json([
            'success' => true,
            'message' => 'Data berhasil diambil',
            'data' => $resource,
            'form_data' => $formData
        ]);
    }

    private function buildFormData(AssessmentUtama $assessment): array
    {
        $data = [
            'uuid_insiden' => $assessment->insiden->uuid_insiden ?? null,
            'jenis_laporan' => $assessment->jenis_laporan,
            'cakupan_wilayah_deskripsi' => $assessment->cakupan_wilayah_deskripsi,
            'latitude' => $assessment->latitude,
            'longitude' => $assessment->longitude,
            'waktu_assesment' => $assessment->waktu_assesment?->format('Y-m-d H:i:s'),
        ];
        
        if ($assessment->dampakManusiaV2) {
            $data['dampak_manusia'] = $assessment->dampakManusiaV2->toArray();
            unset($data['dampak_manusia']['id_assessment']);
        }
        
        $di = [];
        if ($assessment->dampakRumah) {
            $di['rumah_rusak_berat'] = $assessment->dampakRumah->rusak_berat;
            $di['rumah_rusak_sedang'] = $assessment->dampakRumah->rusak_sedang;
            $di['rumah_rusak_ringan'] = $assessment->dampakRumah->rusak_ringan;
            $di['rumah_terendam'] = $assessment->dampakRumah->terendam;
            $di['rumah_terancam'] = $assessment->dampakRumah->terancam;
        }
        if ($assessment->dampakFasum) {
            $di['fasilitas_kesehatan_rusak'] = $assessment->dampakFasum->kesehatan;
            $di['fasilitas_pendidikan_rusak'] = $assessment->dampakFasum->pendidikan;
            $di['tempat_ibadah_rusak'] = $assessment->dampakFasum->ibadah;
            $di['kantor_pemerintah_rusak'] = $assessment->dampakFasum->kantor;
            $di['sanitasi'] = $assessment->dampakFasum->sanitasi;
            $di['pasar'] = $assessment->dampakFasum->pasar;
            $di['spbu'] = $assessment->dampakFasum->spbu;
            $di['jembatan_putus'] = $assessment->dampakFasum->jembatan;
        }
        if ($assessment->dampakVital) {
            $di['jalan_rusak_km'] = $assessment->dampakVital->jalan;
            $di['sarana_air_bersih_rusak'] = $assessment->dampakVital->air_bersih;
            $di['jaringan_listrik_padam_kk'] = $assessment->dampakVital->listrik;
            $di['jaringan_komunikasi_putus'] = $assessment->dampakVital->telekomunikasi;
            $di['irigasi'] = $assessment->dampakVital->irigasi;
            $di['sawah_ha'] = $assessment->dampakVital->sawah_ha;
            $di['ternak_ekor'] = $assessment->dampakVital->ternak_ekor;
            $di['hutan_ha'] = $assessment->dampakVital->hutan_ha;
            $di['catatan_infrastruktur'] = $assessment->dampakVital->catatan_vital ?? ($assessment->dampakFasum->catatan_fasum ?? null);
            if (!isset($di['spbu']) && $assessment->dampakVital->spbu > 0) {
                $di['spbu'] = $assessment->dampakVital->spbu;
            }
        }
        if (!empty($di)) {
            $data['dampak_infrastruktur'] = $di;
        }

        if ($assessment->dampakLingkungan) {
            $dl = $assessment->dampakLingkungan->toArray();
            $dl['unggas'] = $assessment->dampakLingkungan->ternak_unggas_ekor;
            $dl['kaki_empat'] = $assessment->dampakLingkungan->ternak_kaki_empat_ekor;
            $dl['perikanan_kolam'] = $assessment->dampakLingkungan->perikanan_kolam_ha;
            $dl['perikanan_nelayan'] = $assessment->dampakLingkungan->perikanan_nelayan_unit;
            unset($dl['id_assessment_lingkungan'], $dl['id_assessment'], $dl['ternak_unggas_ekor'], $dl['ternak_kaki_empat_ekor'], $dl['perikanan_kolam_ha'], $dl['perikanan_nelayan_unit'], $dl['tingkat_kerusakan_lingkungan'], $dl['butuh_rehabilitasi_lahan']);
            $data['dampak_lingkungan'] = $dl;
        }

        if ($assessment->dampakEkonomi) {
            $de = $assessment->dampakEkonomi->toArray();
            unset($de['id_assessment_ekonomi'], $de['id_assessment']);
            $data['dampak_ekonomi'] = $de;
        }

        if ($assessment->biodataKejadian) {
            $bk = $assessment->biodataKejadian->toArray();
            unset($bk['id_assessment_biodata'], $bk['id_assessment']);
            $data['biodata_kejadian'] = $bk;
        }

        if ($assessment->narasiKejadian) {
            $nk = $assessment->narasiKejadian->toArray();
            unset($nk['id_assessment_narasi'], $nk['id_assessment']);
            $data['narasi_kejadian'] = $nk;
        }

        if ($assessment->lokasiDetail) {
            $ld = $assessment->lokasiDetail->toArray();
            unset($ld['id_lokasi_detail'], $ld['id_assessment']);
            $data['lokasi_detail'] = $ld;
        }

        if ($assessment->narasiDetail) {
            $nd = $assessment->narasiDetail->toArray();
            unset($nd['id_narasi_detail'], $nd['id_assessment']);
            $data['narasi_detail'] = $nd;
        }

        if ($assessment->kebutuhanLanjutan) {
            $kl = $assessment->kebutuhanLanjutan->toArray();
            unset($kl['id_kebutuhan_lanjutan'], $kl['id_assessment']);
            $data['kebutuhan_lanjutan'] = $kl;
        }
        
        if ($assessment->kebutuhanNumerik) {
            $data['kebutuhan_numerik'] = $assessment->kebutuhanNumerik->map(function($kn) {
                return [
                    'id_item' => $kn->id_item,
                    'jumlah_dibutuhkan' => $kn->jumlah_dibutuhkan,
                    'jumlah_tersedia' => $kn->jumlah_tersedia,
                    'satuan' => $kn->satuan,
                    'prioritas' => $kn->prioritas,
                    'keterangan' => $kn->keterangan
                ];
            })->toArray();
        }

        return $data;
    }

    public function update(\App\Http\Requests\Operasi\StoreAssessmentRequest $request, AssessmentUtama $assessment): JsonResponse
    {
        $this->authorize('update', $assessment);

        $data = $request->validated();
        $assessment = $this->service->updateAssessment($assessment, $data);

        return $this->apiResponse(new AssessmentResource($assessment->fresh(['dampakManusiaV2', 'kebutuhanMendesak', 'kebutuhanNumerik'])), 'Assessment diperbarui');
    }

    public function destroy(AssessmentUtama $assessment): JsonResponse
    {
        $this->authorize('delete', $assessment);
        $assessment->delete();
        return response()->json(['message' => 'Assessment dihapus.']);
    }

    public function dampakIndex(AssessmentUtama $assessment): JsonResponse
    {
        $this->authorize('view', $assessment);
        return response()->json(['data' => $assessment->dampakManusia()->get()]);
    }

    public function dampakStore(Request $request, AssessmentUtama $assessment): JsonResponse
    {
        $this->authorize('update', $assessment);
        $validated = $request->validate([
            'meninggal' => 'nullable|integer|min:0',
            'hilang' => 'nullable|integer|min:0',
            'luka_berat' => 'nullable|integer|min:0',
            'luka_ringan' => 'nullable|integer|min:0',
            'menderita_mengungsi' => 'nullable|integer|min:0',
        ]);
        $dampak = $assessment->dampakManusia()->create($validated);
        return response()->json(['message' => 'Data dampak ditambahkan.', 'data' => $dampak], 201);
    }

    public function dampakUpdate(Request $request, AssessmentUtama $assessment, AssessmentDampakManusia $dampak): JsonResponse
    {
        $this->authorize('update', $assessment);
        if ($dampak->id_assessment_utama !== $assessment->id_assessment_utama) {
            return response()->json(['message' => 'Not found'], 404);
        }
        $dampak->update($request->validate([
            'meninggal' => 'nullable|integer|min:0',
            'hilang' => 'nullable|integer|min:0',
            'luka_berat' => 'nullable|integer|min:0',
            'luka_ringan' => 'nullable|integer|min:0',
            'menderita_mengungsi' => 'nullable|integer|min:0',
        ]));
        return response()->json(['message' => 'Data dampak diperbarui.']);
    }

    public function dampakDestroy(AssessmentUtama $assessment, AssessmentDampakManusia $dampak): JsonResponse
    {
        $this->authorize('update', $assessment);
        if ($dampak->id_assessment_utama !== $assessment->id_assessment_utama) {
            return response()->json(['message' => 'Not found'], 404);
        }
        $dampak->delete();
        return response()->json(['message' => 'Data dampak dihapus.']);
    }

    public function kebutuhanIndex(AssessmentUtama $assessment): JsonResponse
    {
        $this->authorize('view', $assessment);
        return response()->json(['data' => $assessment->kebutuhanMendesak()->get()]);
    }

    public function kebutuhanStore(Request $request, AssessmentUtama $assessment): JsonResponse
    {
        $this->authorize('update', $assessment);
        $validated = $request->validate([
            'nama_kebutuhan' => 'required|string|max:200',
            'jumlah' => 'required|integer|min:1',
            'satuan' => 'nullable|string|max:50',
        ]);
        $kebutuhan = $assessment->kebutuhanMendesak()->create($validated);
        return response()->json(['message' => 'Kebutuhan ditambahkan.', 'data' => $kebutuhan], 201);
    }

    public function kebutuhanUpdate(Request $request, AssessmentUtama $assessment, AssessmentKebutuhanMendesak $kebutuhan): JsonResponse
    {
        $this->authorize('update', $assessment);
        if ($kebutuhan->id_assessment_utama !== $assessment->id_assessment_utama) {
            return response()->json(['message' => 'Not found'], 404);
        }
        $kebutuhan->update($request->validate([
            'nama_kebutuhan' => 'sometimes|string|max:200',
            'jumlah' => 'sometimes|integer|min:1',
            'satuan' => 'nullable|string|max:50',
        ]));
        return response()->json(['message' => 'Kebutuhan diperbarui.']);
    }

    public function kebutuhanDestroy(AssessmentUtama $assessment, AssessmentKebutuhanMendesak $kebutuhan): JsonResponse
    {
        $this->authorize('update', $assessment);
        if ($kebutuhan->id_assessment_utama !== $assessment->id_assessment_utama) {
            return response()->json(['message' => 'Not found'], 404);
        }
        $kebutuhan->delete();
        return response()->json(['message' => 'Kebutuhan dihapus.']);
    }

    public function downloadPdf(AssessmentUtama $assessment)
    {
        $this->authorize('view', $assessment);

        $pdfService = app(\App\Services\SuratPdfService::class);
        $path = $pdfService->generateAssessmentOnlyPdf($assessment);

        return \Illuminate\Support\Facades\Storage::download($path);
    }
}
