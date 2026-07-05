<?php

namespace Benchmarks;

use App\Models\AuthRole;
use App\Models\AuthUser;
use App\Models\OperasiInsiden;
use App\Models\OperasiPenugasan;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class BootstrapBenchmarkTest extends TestCase
{
    use DatabaseTransactions;

    private AuthUser $user;
    private array $metrics = [];

    protected function setUp(): void
    {
        parent::setUp();
        $role = AuthRole::firstOrCreate(['nama_peran' => 'super_admin'], ['deskripsi' => 'Super Admin', 'level_otoritas' => 99]);
        $this->user = AuthUser::factory()->aktif()->create(['id_peran' => $role->id_peran]);
    }

    public function test_bootstrap_benchmark_1000_requests()
    {
        $iterations = 1000;
        $this->runBenchmark($iterations);
    }

    public function test_bootstrap_benchmark_5000_requests()
    {
        $iterations = 5000;
        $this->runBenchmark($iterations);
    }

    private function runBenchmark(int $iterations): void
    {
        $insiden = OperasiInsiden::factory()->create(['status_insiden' => 'respon'])->refresh();
        $relawan = AuthUser::factory()->aktif()->create();

        // Seed some data so snapshot is non-trivial
        for ($i = 0; $i < 10; $i++) {
            OperasiPenugasan::create([
                'id_insiden' => $insiden->id_insiden,
                'id_pengguna' => $relawan->id_pengguna,
                'peran_otoritas' => 'trc',
                'status_penugasan' => 'aktif',
                'waktu_mulai' => now(),
                'ditugaskan_oleh' => $this->user->id_pengguna,
            ]);
        }

        $latencies = [];
        $startTime = microtime(true);
        $peakMem = 0;

        for ($i = 0; $i < $iterations; $i++) {
            $t0 = microtime(true);
            $response = $this->actingAs($this->user)->postJson('/api/v1/bootstrap');
            $t1 = microtime(true);

            $latencies[] = ($t1 - $t0) * 1000;
            $peakMem = max($peakMem, memory_get_peak_usage(true));

            if ($i % 100 === 0) {
                gc_collect_cycles();
            }

            $response->assertStatus(200);
        }

        $totalTime = (microtime(true) - $startTime) * 1000;
        $avgLatency = array_sum($latencies) / count($latencies);
        $p95Latency = $this->percentile($latencies, 95);
        $p99Latency = $this->percentile($latencies, 99);

        $this->metrics = [
            'iterations' => $iterations,
            'total_time_ms' => round($totalTime, 2),
            'avg_latency_ms' => round($avgLatency, 2),
            'p95_latency_ms' => round($p95Latency, 2),
            'p99_latency_ms' => round($p99Latency, 2),
            'peak_memory_bytes' => $peakMem,
            'peak_memory_mb' => round($peakMem / 1024 / 1024, 2),
            'throughput_rps' => round($iterations / ($totalTime / 1000), 2),
        ];

        $this->assertTrue($avgLatency < 5000, "Average latency {$avgLatency}ms exceeds 5000ms target");
    }

    private function percentile(array $data, int $percentile): float
    {
        sort($data);
        $index = (int) ceil($percentile / 100 * count($data));
        return $data[min($index, count($data) - 1)];
    }

    public function getMetrics(): array
    {
        return $this->metrics;
    }
}
