<?php

namespace App\Jobs;

use App\Models\DokumenSuratUtama;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class BenchmarkPdfJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 120;
    public int $tries = 3;
    public array $backoff = [10, 30, 60];

    public function __construct(
        public readonly int $idSurat,
    ) {
        $this->onQueue('pdf-generation');
    }

    public function handle(): void
    {
        $surat = DokumenSuratUtama::findOrFail($this->idSurat);

        if ($surat->status_surat !== 'siap_tanda_tangan') {
            return;
        }

        $html = view('pdf.benchmark', ['surat' => $surat])->render();

        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', false);
        $options->set('defaultPaperSize', 'A4');

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $path = 'benchmark/surat-' . $surat->id_surat . '.pdf';

        Storage::disk('local')->put($path, $dompdf->output());

        $surat->update([
            'file_pdf_path' => $path,
            'status_surat' => 'ditandatangani',
        ]);
    }

    public function failed(\Throwable $e): void
    {
        Log::error('BenchmarkPdfJob gagal', [
            'id_surat' => $this->idSurat,
            'error' => $e->getMessage(),
        ]);
    }
}
