<?php

namespace Tests\Feature\Jobs;

use App\Jobs\GenerateSuratPdfJob;
use App\Models\AuthUser;
use App\Models\DokumenSuratUtama;
use App\Models\MasterSuratJenis;
use App\Models\OperasiInsiden;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class GenerateSuratPdfJobTest extends TestCase
{
    use DatabaseTransactions;

    public function test_job_dispatched_on_finalisasi(): void
    {
        Queue::fake();

        $user = AuthUser::factory()->aktif()->create();
        $jenisSurat = MasterSuratJenis::create([
            'kode_jenis' => 'TEST-' . now()->timestamp,
            'nama_jenis' => 'Test',
            'kategori' => 'UMUM',
            'format_nomor' => '{SEQ}/TEST/{TAHUN}',
        ]);

        $surat = DokumenSuratUtama::create([
            'id_jenis_surat' => $jenisSurat->id_jenis_surat,
            'nomor_surat_resmi' => 'TEST/' . now()->timestamp,
            'perihal' => 'Test dispatch',
            'tgl_terbit' => now(),
            'id_pengguna_ttd' => $user->id_pengguna,
            'status_surat' => 'siap_tanda_tangan',
        ]);

        app(\App\Services\SuratService::class)->finalisasi($surat, $user, 'Snapshot test');

        Queue::assertPushed(GenerateSuratPdfJob::class, function ($job) use ($surat) {
            return $job->idSurat === $surat->id_surat
                && $job->isiSnapshot === 'Snapshot test'
                && $job->queue === 'pdf-generation';
        });
    }

    public function test_job_has_correct_retry_config(): void
    {
        $job = new GenerateSuratPdfJob(1, 'test');

        $this->assertEquals(3, $job->tries);
        $this->assertEquals(120, $job->timeout);
        $this->assertEquals([10, 30, 60], $job->backoff);
        $this->assertEquals('pdf-generation', $job->queue);
    }

    public function test_job_skips_if_surat_already_finalized(): void
    {
        $user = AuthUser::factory()->aktif()->create();
        $jenisSurat = MasterSuratJenis::create([
            'kode_jenis' => 'TEST2-' . now()->timestamp,
            'nama_jenis' => 'Test 2',
            'kategori' => 'UMUM',
            'format_nomor' => '{SEQ}/TEST2/{TAHUN}',
        ]);

        $surat = DokumenSuratUtama::create([
            'id_jenis_surat' => $jenisSurat->id_jenis_surat,
            'nomor_surat_resmi' => 'TEST2/' . now()->timestamp,
            'perihal' => 'Test skip',
            'tgl_terbit' => now(),
            'id_pengguna_ttd' => $user->id_pengguna,
            'status_surat' => 'ditandatangani',
        ]);

        $job = new GenerateSuratPdfJob($surat->id_surat, 'test');
        $job->handle(app(\App\Services\SuratPdfService::class));

        // Status should remain unchanged
        $this->assertEquals('ditandatangani', $surat->fresh()->status_surat);
    }
}
