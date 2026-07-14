<?php

namespace App\Jobs;

use App\Models\DokumenSuratUtama;
use App\Models\OperasiJurnal;
use App\Services\SuratPdfService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class GenerateSuratPdfJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 120;
    public int $tries = 3;
    public array $backoff = [10, 30, 60];

    public function __construct(
        public readonly int $idSurat,
        public readonly string $isiSnapshot,
    ) {
        $this->onQueue('pdf-generation');
    }

    public function handle(SuratPdfService $pdfService): void
    {
        $surat = DokumenSuratUtama::findOrFail($this->idSurat);

        if ($surat->file_pdf_path) {
            Log::warning('GenerateSuratPdfJob skipped: PDF already exists', [
                'id_surat' => $this->idSurat,
                'path' => $surat->file_pdf_path,
            ]);
            return;
        }

        $pdfPath = $pdfService->generate($surat);

        $surat->update([
            'file_pdf_path' => $pdfPath,
        ]);

        if (Schema::hasTable('operasi_jurnal')) {
            OperasiJurnal::create([
                'id_insiden' => $surat->id_insiden,
                'id_pengguna' => 0,
                'kategori_event' => 'aktivasi',
                'judul_event' => 'PDF Surat Generated',
                'deskripsi_event' => 'Surat: ' . $surat->nomor_surat_resmi . ' | PDF: ' . $pdfPath,
                'id_referensi' => $surat->id_surat,
                'tabel_referensi' => 'operasi_surat_keluar',
            ]);
        }

        Log::info('PDF surat berhasil dibuat', [
            'id_surat' => $this->idSurat,
            'path' => $pdfPath,
        ]);
    }

    public function failed(\Throwable $e): void
    {
        Log::error('GenerateSuratPdfJob gagal setelah ' . $this->attempts() . ' percobaan', [
            'id_surat' => $this->idSurat,
            'error' => $e->getMessage(),
        ]);

        try {
            $surat = DokumenSuratUtama::find($this->idSurat);
            if ($surat && Schema::hasTable('operasi_jurnal')) {
                OperasiJurnal::create([
                    'id_insiden' => $surat->id_insiden,
                    'id_pengguna' => 0,
                    'kategori_event' => 'error',
                    'judul_event' => 'PDF Surat Gagal',
                    'deskripsi_event' => 'PDF gagal dibuat setelah ' . $this->attempts() . 'x: ' . $e->getMessage(),
                    'id_referensi' => $surat->id_surat,
                    'tabel_referensi' => 'operasi_surat_keluar',
                ]);
            }
        } catch (\Throwable $logError) {
            Log::warning('Gagal mencatat jurnal kegagalan PDF', ['error' => $logError->getMessage()]);
        }
    }
}
