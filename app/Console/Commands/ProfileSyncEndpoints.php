<?php

namespace App\Console\Commands;

use App\Models\AuthRole;
use App\Models\AuthUser;
use App\Models\OperasiInsiden;
use App\Models\OperasiKlaster;
use App\Models\OperasiMobilisasi;
use App\Models\OperasiPenugasan;
use App\Models\OperasiSitrep;
use App\Models\SyncCursor;
use App\Models\SyncTombstone;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProfileSyncEndpoints extends Command
{
    protected $signature = 'nurisk:profile-sync
        {--iterations=5 : Number of iterations per endpoint}
        {--changes=3 : Number of changes to send in sync payload}';

    protected $description = 'Profile sync endpoint query counts and latencies';

    private array $results = [];

    public function handle(): int
    {
        $iterations = (int) $this->option('iterations');
        $numChanges = (int) $this->option('changes');

        $this->info('=== NURISK Sync Endpoint Profiling ===');
        $this->line("Iterations: {$iterations}, Changes per sync: {$numChanges}");
        $this->newLine();

        // Seed test data
        $this->seedTestData();

        // Profile endpoints
        $this->profileEndpoint('POST /api/v1/sync', fn () => $this->callSync($numChanges), $iterations);
        $this->profileEndpoint('GET /api/v1/sync/status', fn () => $this->callStatus(), $iterations);
        $this->profileEndpoint('GET /api/v1/sync/metrics', fn () => $this->callMetrics(), $iterations);
        $this->profileEndpoint('POST /api/v1/bootstrap', fn () => $this->callBootstrap(), $iterations);
        $this->profileEndpoint('GET /api/v1/assessment', fn () => $this->callAssessment(), $iterations);
        $this->profileEndpoint('GET /api/v1/penugasan', fn () => $this->callPenugasan(), $iterations);

        // Summary
        $this->newLine();
        $this->table(
            ['Endpoint', 'Avg Queries', 'Min (ms)', 'Avg (ms)', 'P50 (ms)', 'P95 (ms)', 'P99 (ms)'],
            array_map(fn ($r) => [
                $r['name'],
                round($r['avg_queries'], 1),
                round($r['min_ms'], 1),
                round($r['avg_ms'], 1),
                round($r['p50_ms'], 1),
                round($r['p95_ms'], 1),
                round($r['p99_ms'], 1),
            ], $this->results)
        );

        return Command::SUCCESS;
    }

    private function profileEndpoint(string $name, callable $fn, int $iterations): void
    {
        $this->info("Profiling: {$name}");

        $queryCounts = [];
        $durations = [];

        for ($i = 0; $i < $iterations; $i++) {
            DB::enableQueryLog();

            $start = microtime(true);
            $fn();
            $end = microtime(true);

            $queries = count(DB::getQueryLog());
            $duration = ($end - $start) * 1000;

            $queryCounts[] = $queries;
            $durations[] = $duration;

            DB::disableQueryLog();
        }

        sort($durations);
        $n = count($durations);

        $this->results[] = [
            'name' => $name,
            'avg_queries' => array_sum($queryCounts) / $n,
            'min_ms' => min($durations),
            'avg_ms' => array_sum($durations) / $n,
            'p50_ms' => $durations[(int) ($n * 0.5)],
            'p95_ms' => $durations[(int) ($n * 0.95)],
            'p99_ms' => $durations[(int) ($n * 0.99)],
        ];

        $this->line("  Queries: " . json_encode($queryCounts) . " | Times (ms): " . json_encode(array_map(fn ($v) => round($v, 1), $durations)));
    }

    private function seedTestData(): void
    {
        $role = AuthRole::firstOrCreate(
            ['nama_peran' => 'super_admin'],
            ['deskripsi' => 'Super Admin', 'level_otoritas' => 100]
        );
        $user = AuthUser::factory()->aktif()->create(['id_peran' => $role->id_peran]);
        $this->laravel->make('auth')->guard('web')->login($user);
        $token = $user->createToken('profile-test-token');
        $this->outputToken = $token->plainTextToken;

        $insiden = OperasiInsiden::factory()->create();

        // Seed cursors
        foreach (['assessment', 'sitrep', 'klaster', 'penugasan', 'mobilisasi'] as $entity) {
            for ($j = 0; $j < 3; $j++) {
                SyncCursor::create([
                    'entity_type' => $entity,
                    'uuid_entity' => (string) Str::uuid(),
                    'cursor_value' => $j + 1,
                    'action' => 'upsert',
                    'scope_type' => 'pcnu',
                    'scope_id' => $insiden->id_pcnu,
                ]);
                SyncTombstone::create([
                    'entity_type' => $entity,
                    'uuid_entity' => (string) Str::uuid(),
                    'cursor_value' => $j + 100,
                    'deleted_at' => now(),
                    'scope_type' => 'pcnu',
                    'scope_id' => $insiden->id_pcnu,
                ]);
            }
        }

        // Seed model data
        $klaster = OperasiKlaster::create([
            'uuid_klaster_operasi' => (string) Str::uuid(),
            'id_insiden' => $insiden->id_insiden,
            'id_master_klaster' => 1,
            'status_klaster' => 'aktif',
        ]);
        OperasiPenugasan::create([
            'uuid_penugasan' => (string) Str::uuid(),
            'id_insiden' => $insiden->id_insiden,
            'id_klaster_operasi' => $klaster->id_klaster_operasi,
            'id_pengguna' => $user->id_pengguna,
            'peran_otoritas' => 'trc',
            'status_penugasan' => 'aktif',
            'waktu_mulai' => now(),
            'ditugaskan_oleh' => $user->id_pengguna,
            'sync_version' => 1,
        ]);
    }

    private function callSync(int $numChanges): void
    {
        $changes = [];
        for ($i = 0; $i < $numChanges; $i++) {
            $changes[] = [
                'table' => 'penugasan',
                'action' => 'upsert',
                'data' => [
                    'uuid_penugasan' => (string) Str::uuid(),
                    'id_insiden' => 1,
                    'id_pengguna' => 1,
                    'peran_otoritas' => 'trc',
                    'status_penugasan' => 'aktif',
                    'waktu_mulai' => now()->format('Y-m-d H:i:s'),
                    'ditugaskan_oleh' => 1,
                    'sync_version' => 1,
                ],
            ];
        }

        $this->makeRequest('POST', '/api/v1/sync', [
            'request_id' => (string) Str::uuid(),
            'device_uuid' => 'profiler-device-' . Str::random(8),
            'cursors' => ['assessment' => 0, 'sitrep' => 0, 'klaster' => 0, 'penugasan' => 0, 'mobilisasi' => 0],
            'changes' => $changes,
        ]);
    }

    private function callStatus(): void
    {
        $this->makeRequest('GET', '/api/v1/sync/status');
    }

    private function callMetrics(): void
    {
        $this->makeRequest('GET', '/api/v1/sync/metrics');
    }

    private function callBootstrap(): void
    {
        $this->makeRequest('POST', '/api/v1/bootstrap');
    }

    private function callAssessment(): void
    {
        $this->makeRequest('GET', '/api/v1/assessment?uuid_insiden=' . $this->getInsidenUuid());
    }

    private function callPenugasan(): void
    {
        $this->makeRequest('GET', '/api/v1/penugasan');
    }

    private string $outputToken = '';

    private function getInsidenUuid(): string
    {
        return OperasiInsiden::first()->uuid_insiden;
    }

    private function makeRequest(string $method, string $uri, array $data = []): void
    {
        $headers = ['Authorization' => 'Bearer ' . $this->outputToken];
        $server = ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->outputToken, 'CONTENT_TYPE' => 'application/json'];

        if ($method === 'GET' && !empty($data)) {
            $uri .= '?' . http_build_query($data);
            $this->call($method, $uri, [], $server);
        } else {
            $this->call($method, $uri, $data, $server);
        }
    }
}
