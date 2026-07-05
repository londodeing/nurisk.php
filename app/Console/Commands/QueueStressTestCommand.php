<?php

namespace App\Console\Commands;

use App\Jobs\BenchmarkPdfJob;
use App\Models\AuthUser;
use App\Models\DokumenSuratUtama;
use App\Models\MasterJabatanPenandatangan;
use App\Models\MasterSuratJenis;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class QueueStressTestCommand extends Command
{
    protected $signature = 'queue:stress-test
        {--jobs=100 : Number of PDF jobs to dispatch}
        {--batch= : Jobs per batch (default: same as --jobs)}
        {--retain-data : Keep test data after benchmark}';

    protected $description = 'Benchmark queue performance with PDF generation jobs';

    public function handle(): int
    {
        $totalJobs = max(1, (int) $this->option('jobs'));
        $retain = (bool) $this->option('retain-data');

        $this->info('Queue Stress Test — PDF Generation Benchmark');
        $this->line(str_repeat('=', 60));
        $this->line("  Jobs: {$totalJobs}");
        $this->line("  Driver: " . config('queue.default'));
        $this->line("  DB: " . config('database.default'));
        $this->line(str_repeat('=', 60));
        $this->newLine();

        $this->line('Seeding test data...');
        $suratIds = $this->seedTestData($totalJobs);
        $this->line('  Created ' . count($suratIds) . ' surat records.');
        $this->newLine();

        $this->line('Dispatching jobs...');
        $startMemory = memory_get_peak_usage(true);
        $startTime = hrtime(true);

        $failedJobs = 0;
        $dispatched = 0;

        $bar = $this->output->createProgressBar($totalJobs);
        $bar->start();

        foreach ($suratIds as $idSurat) {
            try {
                BenchmarkPdfJob::dispatch($idSurat);
                $dispatched++;
            } catch (\Throwable $e) {
                $failedJobs++;
            }
            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $endTime = hrtime(true);
        $endMemory = memory_get_peak_usage(true);

        $totalTimeNs = $endTime - $startTime;
        $totalTimeMs = $totalTimeNs / 1_000_000;
        $avgTimeMs = $dispatched > 0 ? $totalTimeMs / $dispatched : 0;
        $jobsPerSec = $totalTimeMs > 0 ? ($dispatched / ($totalTimeMs / 1000)) : 0;
        $peakMemoryMb = ($endMemory - $startMemory) / 1_048_576;

        $processedCount = DokumenSuratUtama::whereIn('id_surat', $suratIds)
            ->where('status_surat', 'ditandatangani')
            ->count();

        $pdfCount = DokumenSuratUtama::whereIn('id_surat', $suratIds)
            ->whereNotNull('file_pdf_path')
            ->count();

        $failedDbCount = DokumenSuratUtama::whereIn('id_surat', $suratIds)
            ->where('status_surat', 'siap_tanda_tangan')
            ->count();

        // Results table
        $this->info('=== RESULTS ===');
        $this->newLine();

        $headers = ['Metric', 'Value'];
        $rows = [
            ['Total Jobs', (string) $totalJobs],
            ['Dispatched', (string) $dispatched],
            ['Failed (exception)', (string) $failedJobs],
            ['Completed (ditandatangani)', (string) $processedCount],
            ['PDF Files Created', (string) $pdfCount],
            ['Still in queue (unprocessed)', (string) $failedDbCount],
            ['Total Time (ms)', number_format($totalTimeMs, 2)],
            ['Avg Time per Job (ms)', number_format($avgTimeMs, 2)],
            ['Throughput (jobs/sec)', number_format($jobsPerSec, 2)],
            ['Peak Memory Delta (MB)', number_format($peakMemoryMb, 2)],
            ['Queue Driver', config('queue.default')],
            ['Max Retries', '3 (backoff: 10, 30, 60s)'],
        ];

        $this->table($headers, $rows);

        $this->newLine();
        $this->info('=== VERIFICATION ===');
        if ($failedJobs === 0 && $processedCount === $totalJobs) {
            $this->info('  ✓ TARGET MET: 0 failed jobs, all records processed.');
        } else {
            $this->warn("  ✗ TARGET: 0 failed jobs | Failed: {$failedJobs}, Completed: {$processedCount}/{$totalJobs}");
        }

        if (!$retain) {
            $this->cleanupTestData($suratIds);
            $this->line('  Test data cleaned up.');
        }

        $this->newLine();
        return self::SUCCESS;
    }

    private function seedTestData(int $count): array
    {
        $ids = [];

        DB::statement('PRAGMA synchronous = OFF');
        DB::statement('PRAGMA journal_mode = MEMORY');

        $idJenis = MasterSuratJenis::first()?->id_jenis_surat;
        if (!$idJenis) {
            $idJenis = MasterSuratJenis::create([
                'kode_jenis' => 'BENCH',
                'nama_jenis' => 'Benchmark Surat',
                'kategori' => 'UMUM',
                'format_nomor' => 'BENCH/{no}/VI/2026',
                'aktif' => true,
            ])->id_jenis_surat;
        }

        $idPengguna = AuthUser::first()?->id_pengguna;
        if (!$idPengguna) {
            $idPengguna = AuthUser::factory()->aktif()->create()->id_pengguna;
        }

        $idJabatan = MasterJabatanPenandatangan::first()?->id_jabatan;
        if (!$idJabatan) {
            $idJabatan = MasterJabatanPenandatangan::create([
                'kode_jabatan' => 'BENCH',
                'nama_jabatan' => 'Benchmark Testing',
                'urutan_hierarki' => 1,
                'aktif' => true,
            ])->id_jabatan;
        }

        $now = now();
        $chunkSize = 50;

        for ($i = 0; $i < $count; $i += $chunkSize) {
            $records = [];
            $batchEnd = min($i + $chunkSize, $count);

            for ($j = $i; $j < $batchEnd; $j++) {
                $records[] = [
                    'id_insiden' => null,
                    'id_jenis_surat' => $idJenis,
                    'nomor_surat_resmi' => 'BENCH/' . Str::random(8) . '/VI/2026',
                    'perihal' => 'Benchmark Surat #' . ($j + 1),
                    'tgl_terbit' => $now->toDateString(),
                    'id_pengguna_ttd' => $idPengguna,
                    'id_jabatan_ttd' => $idJabatan,
                    'isi_surat_snapshot' => 'Isi surat benchmark untuk pengujian antrean PDF. Nomor urut: ' . ($j + 1),
                    'status_surat' => 'siap_tanda_tangan',
                    'dibuat_pada' => $now,
                ];
            }

            DB::table('operasi_surat_keluar')->insert($records);
        }

        $suratCollection = DokumenSuratUtama::where('status_surat', 'siap_tanda_tangan')
            ->where('perihal', 'like', 'Benchmark Surat #%')
            ->orderBy('id_surat', 'desc')
            ->take($count)
            ->pluck('id_surat')
            ->toArray();

        return array_reverse($suratCollection);
    }

    private function cleanupTestData(array $ids): void
    {
        if (empty($ids)) {
            return;
        }

        DB::table('operasi_surat_keluar')->whereIn('id_surat', $ids)->delete();
    }
}
