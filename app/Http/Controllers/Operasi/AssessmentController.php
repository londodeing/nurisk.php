<?php

namespace App\Http\Controllers\Operasi;

use App\Http\Controllers\Controller;
use App\Http\Requests\Operasi\StoreAssessmentRequest;
use App\Models\OperasiInsiden;
use App\Models\AssessmentUtama;
use App\Models\WilayahKecamatan;
use App\Models\WilayahDesa;
use App\Models\Assessment\AssessmentKebutuhanNumerikMaster;
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
        $kebutuhanMaster = AssessmentKebutuhanNumerikMaster::where('aktif', 1)->orderBy('urutan')->get();

        $latestAssessment = AssessmentUtama::where('id_insiden', $insiden->id_insiden)
                                ->latest('id_assessment_utama')
                                ->first();

        if ($latestAssessment) {
            $latestAssessment->load([
                'dampakManusia', 'dampakManusiaV2', 'dampakInfrastruktur', 'dampakLingkungan',
                'dampakEkonomi', 'biodataKejadian', 'narasiKejadian', 'kebutuhanMendesak',
                'lokasiDetail', 'kebutuhanLanjutan', 'kebutuhanNumerik', 'dampakRumah', 'dampakFasum', 'dampakVital'
            ]);
            $assessment = $latestAssessment;
            $desaList = $assessment->lokasiDetail?->id_kec ? WilayahDesa::where('id_kec', $assessment->lokasiDetail->id_kec)->orderBy('nama_desa')->get(['id_desa', 'nama_desa']) : [];
        } else {
            $assessment = new AssessmentUtama();
            $laporan = $insiden->laporanAsal;
            
            if ($laporan) {
                // Populate Utama
                $assessment->cakupan_wilayah_deskripsi = $laporan->alamat_lengkap;
                $assessment->latitude = $laporan->latitude;
                $assessment->longitude = $laporan->longitude;

                // Populate Lokasi
                $assessment->setRelation('lokasiDetail', new \App\Models\Assessment\AssessmentLokasiDetail([
                    'id_kec' => $laporan->id_kec,
                    'id_desa' => $laporan->id_desa,
                ]));

                // Populate Biodata
                $assessment->setRelation('biodataKejadian', new \App\Models\Assessment\AssessmentBiodataKejadian([
                    'tanggal_mulai_kejadian' => $laporan->waktu_kejadian ? $laporan->waktu_kejadian->format('Y-m-d') : null,
                    'jam_mulai_kejadian' => $laporan->waktu_kejadian ? $laporan->waktu_kejadian->format('H:i') : null,
                    'kronologi_singkat' => $laporan->keterangan_situasi,
                ]));
                
                $desaList = $laporan->id_kec ? WilayahDesa::where('id_kec', $laporan->id_kec)->orderBy('nama_desa')->get(['id_desa', 'nama_desa']) : [];
            } else {
                $desaList = [];
            }
        }

        return view('operasi.assessment.create', compact('insiden', 'kecamatanList', 'kebutuhanMaster', 'assessment', 'desaList'));
    }

    public function store(StoreAssessmentRequest $request, OperasiInsiden $insiden)
    {
        $this->authorize('create', [AssessmentUtama::class, $insiden]);

        $data = $request->validated();
        $data['id_insiden'] = $insiden->id_insiden;
        $data['id_petugas_assessment'] = auth()->id();

        $assessment = $this->service->createAssessment($data);



        return redirect()->route('insiden.assessment.show', [$insiden, $assessment])
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
        $desaList = $assessment->lokasiDetail?->id_kec ? WilayahDesa::where('id_kec', $assessment->lokasiDetail->id_kec)->orderBy('nama_desa')->get(['id_desa', 'nama_desa']) : [];
        $kebutuhanMaster = AssessmentKebutuhanNumerikMaster::where('aktif', 1)->orderBy('urutan')->get();
        return view('operasi.assessment.edit', compact('insiden', 'assessment', 'kecamatanList', 'desaList', 'kebutuhanMaster'));
    }

    public function update(StoreAssessmentRequest $request, OperasiInsiden $insiden, AssessmentUtama $assessment)
    {
        $this->authorize('update', $assessment);

        $data = $request->validated();
        $this->service->updateAssessment($assessment, $data);

        return redirect()->route('insiden.assessment.show', [$insiden, $assessment])
            ->with('success', 'Assessment berhasil diperbarui.');
    }

    public function skor(OperasiInsiden $insiden, AssessmentUtama $assessment)
    {
        $assessment->load(['skorItem.indikator', 'ringkasanSkor']);
        return view('operasi.assessment.skor', compact('insiden', 'assessment'));
    }

    public function submit(Request $request, OperasiInsiden $insiden, AssessmentUtama $assessment)
    {
        $this->authorize('submit', $assessment);

        if ($assessment->status_review !== 'draft') {
            return redirect()->back()->with('error', 'Assessment sudah di-submit.');
        }

        $assessment->update(['status_review' => 'submitted']);

        return redirect()->back()->with('success', 'Assessment diajukan untuk review.');
    }

    public function review(Request $request, OperasiInsiden $insiden, AssessmentUtama $assessment)
    {
        $validated = $request->validate([
            'action'        => 'required|in:approved,rejected',
            'catatan_review' => 'required_if:action,rejected|nullable|string|max:1000',
        ]);

        if ($assessment->status_review !== 'submitted') {
            return redirect()->back()->with('error', 'Assessment belum di-submit.');
        }

        $this->authorize($validated['action'] === 'approved' ? 'approve' : 'reject', $assessment);

        $assessment->update([
            'status_review'  => $validated['action'] === 'approved' ? 'in_review' : 'rejected',
            'catatan_review' => $validated['catatan_review'] ?? null,
            'id_reviewer'    => auth()->id(),
            'waktu_review'   => now(),
        ]);

        if ($validated['action'] === 'approved') {
            return redirect()->route('insiden.pleno.create', $insiden)
                ->with('success', 'Assessment disetujui! Silakan buat Pleno untuk melanjutkan.');
        }

        return redirect()->back()->with('success', 'Assessment ditolak.');
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
            'kebutuhanNumerik.item', 'lokasiDetail.kecamatan', 'lokasiDetail.desa',
            'narasiDetail', 'narasiKejadian',
        ]);

        return view('operasi.assessment.laporan', compact('insiden', 'assessment'));
    }
}
