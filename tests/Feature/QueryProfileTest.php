<?php

namespace Tests\Feature;

use App\Models\AuthRole;
use App\Models\AuthUser;
use App\Models\OperasiInsiden;
use App\Models\OperasiKlaster;
use App\Models\OperasiPenugasan;
use App\Models\SyncCursor;
use App\Models\SyncTombstone;
use Database\Seeders\MasterKlasterSeeder;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class QueryProfileTest extends TestCase
{
    use DatabaseTransactions;

    private string $token;
    private int $insidenId;
    private string $insidenUuid;
    private array $results = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(MasterKlasterSeeder::class);

        $role = AuthRole::firstOrCreate(
            ['nama_peran' => 'super_admin'],
            ['deskripsi' => 'Super Admin', 'level_otoritas' => 100]
        );
        $user = AuthUser::factory()->aktif()->create(['id_peran' => $role->id_peran]);
        $this->token = $user->createToken('profile-token')->plainTextToken;

        $insiden = OperasiInsiden::factory()->create();
        $this->insidenId = $insiden->id_insiden;
        $this->insidenUuid = $insiden->uuid_insiden;

        for ($i = 0; $i < 3; $i++) {
            $klaster = OperasiKlaster::create([
                'id_insiden' => $insiden->id_insiden,
                'id_master_klaster' => 1,
                'status_klaster' => 'aktif',
            ]);
            OperasiPenugasan::create([
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

        foreach (['assessment', 'sitrep', 'klaster', 'penugasan', 'mobilisasi'] as $entity) {
            for ($j = 0; $j < 3; $j++) {
                SyncCursor::create([
                    'entity_type' => $entity,
                    'uuid_entity' => (string) Str::uuid(),
                    'cursor_value' => $j + 1 + ($entity === 'klaster' ? 10 : 0),
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
    }

    /** @test */
    public function profile_sync_endpoint(): void
    {
        DB::enableQueryLog();

        $payload = [
            'request_id' => (string) Str::uuid(),
            'device_uuid' => 'profiler-dev-1',
            'cursors' => ['assessment' => 0, 'sitrep' => 0, 'klaster' => 10, 'penugasan' => 0, 'mobilisasi' => 0],
            'changes' => [
                [
                    'table' => 'penugasan',
                    'action' => 'upsert',
                    'data' => [
                        'uuid_penugasan' => (string) Str::uuid(),
                        'id_insiden' => $this->insidenId,
                        'id_pengguna' => 1,
                        'peran_otoritas' => 'trc',
                        'status_penugasan' => 'aktif',
                        'waktu_mulai' => now()->format('Y-m-d H:i:s'),
                        'ditugaskan_oleh' => 1,
                        'sync_version' => 1,
                    ],
                ],
            ],
        ];

        $server = ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->token];
        $response = $this->postJson('/api/v1/sync', $payload, $server);
        $queries = DB::getQueryLog();
        DB::disableQueryLog();

        $response->assertStatus(200);
        $this->recordResult('POST /api/v1/sync', count($queries), $queries);
    }

    /** @test */
    public function profile_status_endpoint(): void
    {
        DB::enableQueryLog();
        $server = ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->token];
        $response = $this->getJson('/api/v1/sync/status', $server);
        $queries = DB::getQueryLog();
        DB::disableQueryLog();

        $response->assertStatus(200);
        $this->recordResult('GET /api/v1/sync/status', count($queries));
    }

    /** @test */
    public function profile_metrics_endpoint(): void
    {
        DB::enableQueryLog();
        $server = ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->token];
        $response = $this->getJson('/api/v1/sync/metrics', $server);
        $queries = DB::getQueryLog();
        DB::disableQueryLog();

        $response->assertStatus(200);
        $this->recordResult('GET /api/v1/sync/metrics', count($queries));
    }

    /** @test */
    public function profile_bootstrap_endpoint(): void
    {
        DB::enableQueryLog();
        $server = ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->token];
        $response = $this->postJson('/api/v1/bootstrap', [], $server);
        $queries = DB::getQueryLog();
        DB::disableQueryLog();

        $response->assertStatus(200);
        $this->recordResult('POST /api/v1/bootstrap', count($queries));
    }

    /** @test */
    public function profile_assessment_endpoint(): void
    {
        DB::enableQueryLog();
        $server = ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->token];
        $response = $this->getJson('/api/v1/assessment?uuid_insiden=' . $this->insidenUuid, $server);
        $queries = DB::getQueryLog();
        DB::disableQueryLog();

        $response->assertStatus(200);
        $this->recordResult('GET /api/v1/assessment', count($queries));
    }

    /** @test */
    public function profile_penugasan_endpoint(): void
    {
        DB::enableQueryLog();
        $server = ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->token];
        $response = $this->getJson('/api/v1/penugasan?uuid_insiden=' . $this->insidenUuid, $server);
        $queries = DB::getQueryLog();
        DB::disableQueryLog();

        $response->assertStatus(200);
        $this->recordResult('GET /api/v1/penugasan', count($queries));
    }

    private function recordResult(string $endpoint, int $queryCount, ?array $queryLog = null): void
    {
        $this->results[$endpoint] = $queryCount;

        if ($endpoint === 'POST /api/v1/sync' && $queryLog) {
            echo "\n=== SYNC QUERY LOG ===\n";
            foreach ($queryLog as $i => $q) {
                $sql = $q['query'] ?? $q['sql'] ?? '(unknown)';
                $truncated = strlen($sql) > 130 ? substr($sql, 0, 130) . '...' : $sql;
                printf("  %2d: [%5.2fms] %s\n", $i, $q['time'] ?? 0, $truncated);
            }
            echo "=====================\n";
        }
    }

    protected function tearDown(): void
    {
        if (!empty($this->results)) {
            echo "\n\n=== QUERY PROFILE RESULTS ===\n";
            printf("%-35s %12s\n", 'Endpoint', 'Queries');
            echo str_repeat('-', 50) . "\n";
            foreach ($this->results as $endpoint => $count) {
                printf("%-35s %12d\n", $endpoint, $count);
                if ($count >= 30) {
                    printf("  ⚠  %-35s %12s\n", '', 'EXCEEDS TARGET (30)');
                }
            }
            echo "\n";
        }
        parent::tearDown();
    }
}
