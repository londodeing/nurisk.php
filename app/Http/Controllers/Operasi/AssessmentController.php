<?php

namespace App\Http\Controllers\Operasi;

use App\Http\Controllers\Controller;
use App\Http\Requests\Operasi\StoreAssessmentRequest;
use App\Models\OperasiInsiden;
use App\Models\AssessmentUtama;
use App\Models\WilayahKecamatan;
use App\Models\WilayahDesa;
use App\Services\Operasi\AssessmentService;
use Illuminate\Http\Request;

class AssessmentController extends Controller
{
    private AssessmentService $service;

    public function __construct(AssessmentService $service)
    {
        $this->service = $service;
    }

    public function create(OperasiInsiden $insiden)
    {
        $this->authorize('create', [AssessmentUtama::class, $insiden]);
        $kecamatanList = WilayahKecamatan::orderBy('nama_kec')->get(['id_kec', 'nama_kec']);
        return view('operasi.assessment.create', compact('insiden', 'kecamatanList'));
    }

    public function store(StoreAssessmentRequest $request, OperasiInsiden $insiden)
    {
        $this->authorize('create', [AssessmentUtama::class, $insiden]);

        $data = $request->validated();
        $data['id_insiden'] = $insiden->id_insiden;
        $data['id_petugas_assessment'] = auth()->id();

        $assessment = $this->service->createAssessment($data);



        return redirect()->route('insiden.assessment.show', [$insiden->id_insiden, $assessment->id_assessment_utama])
            ->with('success', 'Assessment berhasil disimpan.');
    }

    public function show(OperasiInsiden $insiden, AssessmentUtama $assessment)
    {
        $this->authorize('view', $assessment);
        $assessment->load([
            'dampakManusia', 'dampakManusiaV2',
            'kebutuhanMendesak', 'kebutuhanLanjutan', 'kebutuhanNumerik.item',
            'dampakInfrastruktur', 'dampakRumah', 'dampakFasum', 'dampakVital',
            'dampakLingkungan', 'dampakEkonomi',
            'biodataKejadian', 'narasiKejadian', 'narasiDetail',
            'lokasiDetail.kecamatan', 'lokasiDetail.desa',
            'ringkasanSkor', 'petugas.profil',
        ]);
        return view('operasi.assessment.show', compact('insiden', 'assessment'));
    }

    public function edit(OperasiInsiden $insiden, AssessmentUtama $assessment)
    {
        $this->authorize('update', $assessment);
        $assessment->load([
            'dampakManusia', 'dampakManusiaV2', 'dampakInfrastruktur', 'dampakLingkungan',
            'dampakEkonomi', 'biodataKejadian', 'narasiKejadian', 'kebutuhanMendesak',
        ]);
        $kecamatanList = WilayahKecamatan::orderBy('nama_kec')->get(['id_kec', 'nama_kec']);
        return view('operasi.assessment.edit', compact('insiden', 'assessment', 'kecamatanList'));
    }

    public function update(StoreAssessmentRequest $request, OperasiInsiden $insiden, AssessmentUtama $assessment)
    {
        $this->authorize('update', $assessment);

        $data = $request->validated();
        $this->service->updateAssessment($assessment, $data);

        return redirect()->route('insiden.assessment.show', [$insiden->id_insiden, $assessment->id_assessment_utama])
            ->with('success', 'Assessment berhasil diperbarui.');
    }

    public function skor(OperasiInsiden $insiden, AssessmentUtama $assessment)
    {
        $assessment->load(['skorItem.indikator', 'ringkasanSkor']);
        return view('operasi.assessment.skor', compact('insiden', 'assessment'));
    }

    public function cetak(OperasiInsiden $insiden, AssessmentUtama $assessment)
    {
        $this->authorize('view', $assessment);

        $assessment->loadMissing([
            'petugas.profil',
            'ringkasanSkor',
            'dampakManusiaV2', 'dampakManusia',
            'dampakInfrastruktur', 'dampakLingkungan', 'dampakEkonomi',
            'dampakRumah', 'dampakFasum', 'dampakVital',
            'biodataKejadian', 'kebutuhanLanjutan', 'kebutuhanMendesak',
            'kebutuhanNumerik.itemMaster', 'lokasiDetail.kecamatan', 'lokasiDetail.desa',
            'narasiDetail', 'narasiKejadian',
        ]);

        return view('operasi.assessment.laporan', compact('insiden', 'assessment'));
    }
}
