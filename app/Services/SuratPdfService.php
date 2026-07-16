<?php

namespace App\Services;

use App\Infrastructure\Media\Storage\Contracts\StorageProvider;
use App\Models\AssessmentUtama;
use App\Models\DokumenSuratUtama;
use Dompdf\Dompdf;
use Dompdf\Options;

class SuratPdfService
{
    public function __construct(
        private readonly StorageProvider $storage,
    ) {}

    public function generate(DokumenSuratUtama $surat): string
    {
        $surat->load([
            'jenisSurat', 'penandatangan', 'jabatanTtd', 'paraf.pengguna', 'tembusan',
            'insiden.laporanAsal.jenisBencana', 'insiden.laporanAsal.kabupaten',
            'insiden.laporanAsal.kecamatan', 'insiden.laporanAsal.desa',
            'insiden.pcnu'
        ]);

        $html = view('pdf.surat', compact('surat'))->render();

        $dompdf = $this->createDompdf($html);
        $path = 'surat/' . now()->format('Y/m') . '/surat-' . $surat->id_surat . '.pdf';

        $this->storage->storeContents($path, $dompdf->output());

        return $path;
    }

    public function generateWithLampiran(DokumenSuratUtama $surat, ?AssessmentUtama $assessment = null): string
    {
        $surat->loadMissing([
            'jenisSurat', 'penandatangan.profil', 'jabatanTtd',
            'tembusan', 'insiden.jenisBencana', 'insiden.pcnu', 'insiden.pemberiSpk.profil', 
            'insiden.laporanAsal.jenisBencana', 'insiden.laporanAsal.kabupaten',
            'insiden.laporanAsal.kecamatan', 'insiden.laporanAsal.desa'
        ]);

        if ($assessment) {
            $assessment->loadMissing([
                'petugas.profil', 'lokasiDetail.kecamatan', 'lokasiDetail.desa',
                'biodataKejadian', 'ringkasanSkor', 'dampakManusiaV2', 'dampakManusia',
                'dampakInfrastruktur', 'dampakLingkungan', 'dampakEkonomi',
                'kebutuhanMendesak', 'kebutuhanLanjutan',
            ]);
        }

        $html = view('pdf.surat_tugas_lampiran', compact('surat', 'assessment'))->render();

        $dompdf = $this->createDompdf($html);
        $path = 'surat/' . now()->format('Y/m') . '/surat-' . $surat->id_surat . '.pdf';

        $this->storage->storeContents($path, $dompdf->output());

        return $path;
    }

    public function generatePlenoPdf(\App\Models\OperasiPleno $pleno): string
    {
        $pleno->loadMissing([
            'pimpinan.profil', 'notulis.profil', 'keputusan',
            'insiden.pcnu', 'insiden.laporanAsal.jenisBencana',
            'insiden.laporanAsal.kabupaten', 'insiden.laporanAsal.kecamatan',
            'insiden.laporanAsal.desa'
        ]);

        $html = view('pdf.pleno', compact('pleno'))->render();

        $dompdf = $this->createDompdf($html);
        $path = 'pleno/' . now()->format('Y/m') . '/pleno-' . $pleno->id_pleno . '.pdf';

        $this->storage->storeContents($path, $dompdf->output());

        return $path;
    }

    public function generateAssessmentOnlyPdf(AssessmentUtama $assessment): string
    {
        $assessment->loadMissing([
            'petugas.profil', 'lokasiDetail.kecamatan', 'lokasiDetail.desa',
            'biodataKejadian', 'ringkasanSkor', 'dampakManusiaV2', 'dampakManusia',
            'dampakInfrastruktur', 'dampakLingkungan', 'dampakEkonomi',
            'kebutuhanMendesak', 'kebutuhanLanjutan', 'insiden.pcnu'
        ]);

        $html = view('pdf.assessment', compact('assessment'))->render();

        $dompdf = $this->createDompdf($html);
        $path = 'assessment/' . now()->format('Y/m') . '/assessment-' . $assessment->id_assessment_utama . '.pdf';

        $this->storage->storeContents($path, $dompdf->output());

        return $path;
    }

    private function createDompdf(string $html): Dompdf
    {
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', false);
        $options->set('defaultPaperSize', 'A4');

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return $dompdf;
    }
}
