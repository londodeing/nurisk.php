<?php

namespace Benchmarks;

use App\Models\AuthRole;
use App\Models\AuthUser;
use App\Models\OperasiInsiden;
use App\Models\OperasiPenugasan;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Illuminate\Support\Str;

class SyncBenchmarkTest extends TestCase
{
    use DatabaseTransactions;

    private AuthUser $user;
    private array $results = [];

    protected function setUp(): void
    {
        parent::setUp();
        $role = AuthRole::firstOrCreate(['nama_peran' => 'super_admin'], ['deskripsi' => 'Super Admin', 'level_otoritas' => 99]);
        $this->user = AuthUser::factory()->aktif()->create(['id_peran' => $role->id_peran]);
    }

    public function test_sync_benchmark_1000(): void
    {
        $this->runSyncBenchmark(1000);
    }

    public function test_sync_benchmark_10000(): void
    {
        $this->runSyncBenchmark(10000);
    }

    public function test_sync_benchmark_50000(): void
    {
        $this->runSyncBenchmark(50000);
    }

    private function runSyncBenchmark(int $changeCount): void
    {
        $insiden = OperasiInsiden::factory()->create(['status_insiden' => 'respon'])->refresh();
        $relawan = AuthUser::factory()->aktif()->create();
        $deviceUuid = 'bench-dev-' . Str::random(8);

        // ──────────────────────────────────────────────
        // 1. CREATE phase: sync N new penugasan records
        // ──────────────────────────────────────────────
        fwrite(STDERR, PHP_EOL . "[{$changeCount}] Generating {$changeCount} changes... ");
        $createChanges = [];
        for ($i = 0; $i < $changeCount; $i++) {
            $createChanges[] = [
                'table' => 'penugasan',
                'action' => 'upsert',
                'data' => [
                    'uuid_penugasan' => (string) Str::uuid(),
                    'uuid_insiden' => $insiden->uuid_insiden,
                    'id_pengguna' => $relawan->id_pengguna,
                    'peran_otoritas' => 'trc',
                    'status_penugasan' => 'aktif',
                    'waktu_mulai' => now()->format('Y-m-d H:i:s'),
                    'ditugaskan_oleh' => $this->user->id_pengguna,
                    'sync_version' => 1,
                ],
            ];
        }
        fwrite(STDERR, "done." . PHP_EOL);

        gc_collect_cycles();
        fwrite(STDERR, "[{$changeCount}] Sending create sync... ");

        $createPayload = [
            'request_id' => (string) Str::uuid(),
            'device_uuid' => $deviceUuid,
            'cursors' => ['penugasan' => 0],
            'changes' => $createChanges,
        ];

        $createStart = microtime(true);

        $response = $this->actingAs($this->user)->postJson('/api/v1/sync', $createPayload);

        $createDuration = (microtime(true) - $createStart) * 1000;
        $createPeakMem = memory_get_peak_usage(true);

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
        $this->assertCount(0, $response->json('data.conflicts'));

        fwrite(STDERR, "done in {$createDuration}ms" . PHP_EOL);

        unset($createChanges, $createPayload);
        gc_collect_cycles();

        // ──────────────────────────────────────────────
        // 2. CONFLICT phase: measure conflict resolution
        // ──────────────────────────────────────────────
        fwrite(STDERR, "[{$changeCount}] Generating conflicts for " . min(200, $changeCount) . " records... ");

        $conflictCount = min(200, $changeCount);
        $penugasans = OperasiPenugasan::where('id_insiden', $insiden->id_insiden)
            ->take($conflictCount)
            ->get();

        foreach ($penugasans as $p) {
            $p->timestamps = false;
            $p->updateQuietly(['catatan' => 'Server pre-update']);
        }

        $conflictChanges = [];
        foreach ($penugasans as $p) {
            $conflictChanges[] = [
                'table' => 'penugasan',
                'action' => 'upsert',
                'data' => [
                    'uuid_penugasan' => $p->uuid_penugasan,
                    'uuid_insiden' => $insiden->uuid_insiden,
                    'id_pengguna' => $relawan->id_pengguna,
                    'peran_otoritas' => 'medis',
                    'status_penugasan' => 'aktif',
                    'waktu_mulai' => now()->format('Y-m-d H:i:s'),
                    'ditugaskan_oleh' => $this->user->id_pengguna,
                    'catatan' => 'Client override with stale version',
                    'sync_version' => 1,
                ],
            ];
        }

        fwrite(STDERR, "done." . PHP_EOL);

        gc_collect_cycles();
        fwrite(STDERR, "[{$changeCount}] Sending conflict sync... ");

        $conflictPayload = [
            'request_id' => (string) Str::uuid(),
            'device_uuid' => $deviceUuid,
            'cursors' => ['penugasan' => 0],
            'changes' => $conflictChanges,
        ];

        $conflictStart = microtime(true);
        $response2 = $this->actingAs($this->user)->postJson('/api/v1/sync', $conflictPayload);
        $conflictDuration = (microtime(true) - $conflictStart) * 1000;

        $response2->assertStatus(200);
        $response2->assertJsonPath('success', true);

        $detectedConflicts = count($response2->json('data.conflicts'));
        $this->assertGreaterThan(0, $detectedConflicts);

        fwrite(STDERR, "done in {$conflictDuration}ms ({$detectedConflicts} conflicts detected)" . PHP_EOL);

        unset($conflictChanges, $conflictPayload);
        gc_collect_cycles();

        // ──────────────────────────────────────────────
        // 3. FETCH phase: cursor processing time
        // ──────────────────────────────────────────────
        fwrite(STDERR, "[{$changeCount}] Sending fetch-only sync (catching up)... ");

        $serverCursors = $response2->json('data.server_cursors');
        $fetchPayload = [
            'request_id' => (string) Str::uuid(),
            'device_uuid' => $deviceUuid,
            'cursors' => $serverCursors,
            'changes' => [],
        ];

        $fetchStart = microtime(true);
        $response3 = $this->actingAs($this->user)->postJson('/api/v1/sync', $fetchPayload);
        $fetchDuration = (microtime(true) - $fetchStart) * 1000;

        $response3->assertStatus(200);
        $response3->assertJsonPath('success', true);
        $this->assertEquals(0, count($response3->json('data.changes')));

        fwrite(STDERR, "done in {$fetchDuration}ms" . PHP_EOL);

        unset($fetchPayload);
        gc_collect_cycles();

        // ──────────────────────────────────────────────
        // 4. Compute metrics
        // ──────────────────────────────────────────────
        $totalChanges = $changeCount + $conflictCount;
        $totalDuration = $createDuration + $conflictDuration + $fetchDuration;
        $avgPerChange = $totalDuration / max($totalChanges, 1);
        $throughput = $totalChanges / ($totalDuration / 1000);

        $this->results[$changeCount] = [
            'change_count' => $changeCount,
            'create_duration_ms' => round($createDuration, 2),
            'conflict_duration_ms' => round($conflictDuration, 2),
            'fetch_cursor_duration_ms' => round($fetchDuration, 2),
            'total_duration_ms' => round($totalDuration, 2),
            'peak_memory_bytes' => $createPeakMem,
            'peak_memory_mb' => round($createPeakMem / 1024 / 1024, 2),
            'per_change_avg_ms' => round($avgPerChange, 4),
            'throughput_changes_per_sec' => round($throughput, 2),
            'conflicts_detected' => $detectedConflicts,
        ];

        fwrite(STDERR, PHP_EOL . str_repeat('=', 65) . PHP_EOL);
        fwrite(STDERR, "  Sync Benchmark Results: {$changeCount} changes" . PHP_EOL);
        fwrite(STDERR, str_repeat('=', 65) . PHP_EOL);
        foreach ($this->results[$changeCount] as $key => $value) {
            fwrite(STDERR, "  {$key}: {$value}" . PHP_EOL);
        }
        fwrite(STDERR, str_repeat('=', 65) . PHP_EOL . PHP_EOL);
    }

    public function getResults(): array
    {
        return $this->results;
    }
}
