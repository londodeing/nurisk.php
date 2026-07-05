<?php

namespace Tests\Feature\Operasi\Mobilisasi;

use App\Models\AuthRole;
use App\Models\AuthUser;
use App\Models\OperasiInsiden;
use App\Models\OperasiMobilisasi;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Str;
use Tests\TestCase;
use Database\Seeders\MasterKlasterSeeder;
use Database\Seeders\MasterRoleSeeder;
use Database\Seeders\MasterScopeSeeder;

class MobilisasiSyncTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
    }

    private function createAuthUserWithRole(string $roleName, ?int $scopeId = null): AuthUser
    {
        $role = AuthRole::firstOrCreate(['nama_peran' => $roleName], ['deskripsi' => 'Role', 'level_otoritas' => 1]);

        return AuthUser::factory()->aktif()->create([
            'id_peran' => $role->id_peran,
            'default_scope_id' => $scopeId
        ]);
    }

    public function test_mobilisasi_sync_push_creates_new_record()
    {
        $user = $this->createAuthUserWithRole('super_admin');
        $insiden = OperasiInsiden::factory()->create();

        $uuidMobilisasi = (string) Str::uuid();

        $payload = [
            'device_uuid' => (string) \Illuminate\Support\Str::uuid(),
            'request_id' => (string) \Illuminate\Support\Str::uuid(),
            'cursors' => [
                'mobilisasi' => 0
            ],
            'changes' => [
                [
                    'table' => 'mobilisasi',
                    'action' => 'upsert',
                    'cursor' => 0,
                    'data' => [
                        'uuid_mobilisasi' => $uuidMobilisasi,
                        'uuid_insiden' => $insiden->uuid_insiden,
                        'id_pengguna' => $user->id_pengguna,
                        'jenis_mobilisasi' => 'armada',
                        'status_mobilisasi' => 'draft',
                        'lokasi_asal' => 'Gudang A',
                        'lokasi_tujuan' => 'Lokasi B',
                        'sync_version' => 1,
                    ]
                ]
            ]
        ];

        $response = $this->actingAs($user)->postJson(route('api.v1.sync'), $payload);

        $response->assertStatus(200)
                 ->assertJsonPath('success', true);

        $this->assertDatabaseHas('operasi_mobilisasi', [
            'uuid_mobilisasi' => $uuidMobilisasi,
            'id_insiden' => $insiden->id_insiden,
            'jenis_mobilisasi' => 'armada',
        ]);
    }

    public function test_mobilisasi_sync_conflict_detected()
    {
        $user = $this->createAuthUserWithRole('super_admin');
        $insiden = OperasiInsiden::factory()->create();

        $mobilisasi = OperasiMobilisasi::create([
            'id_insiden' => $insiden->id_insiden,
            'id_pengguna' => $user->id_pengguna,
            'jenis_mobilisasi' => 'armada',
            'status_mobilisasi' => 'disetujui',
            'sync_version' => 2, // Server version is 2
        ]);

        $payload = [
            'device_uuid' => (string) \Illuminate\Support\Str::uuid(),
            'request_id' => (string) \Illuminate\Support\Str::uuid(),
            'cursors' => [
                'mobilisasi' => 0
            ],
            'changes' => [
                [
                    'table' => 'mobilisasi',
                    'action' => 'upsert',
                    'cursor' => 0,
                    'data' => [
                        'uuid_mobilisasi' => $mobilisasi->uuid_mobilisasi,
                        'uuid_insiden' => $insiden->uuid_insiden,
                        'status_mobilisasi' => 'draft',
                        'sync_version' => 1, // Client version is 1 (stale)
                    ]
                ]
            ]
        ];

        $response = $this->actingAs($user)->postJson(route('api.v1.sync'), $payload);

        // Last-write-wins: server accepts stale client change
        $response->assertStatus(200)
                 ->assertJsonPath('success', true);

        $this->assertCount(1, $response->json('data.conflicts'));
        $this->assertEquals($mobilisasi->uuid_mobilisasi, $response->json('data.conflicts.0.uuid_entity'));
        $this->assertEquals('mobilisasi', $response->json('data.conflicts.0.entity_type'));

        // Record IS updated despite conflict (last-write-wins)
        $this->assertDatabaseHas('operasi_mobilisasi', [
            'uuid_mobilisasi' => $mobilisasi->uuid_mobilisasi,
            'status_mobilisasi' => 'draft',
        ]);
    }

    public function test_create_mobilisasi_generates_sync_cursor()
    {
        $admin = $this->createAuthUserWithRole('super_admin');
        $insiden = OperasiInsiden::factory()->create();

        $mobilisasi = OperasiMobilisasi::create([
            'uuid_mobilisasi' => (string) Str::uuid(),
            'id_insiden' => $insiden->id_insiden,
            'id_pengguna' => $admin->id_pengguna,
            'jenis_mobilisasi' => 'relawan',
            'status_mobilisasi' => 'draft',
        ]);

        $this->assertDatabaseHas('sync_cursors', [
            'entity_type' => 'mobilisasi',
            'uuid_entity' => $mobilisasi->uuid_mobilisasi,
            'action' => 'create',
        ]);
    }

    public function test_update_mobilisasi_generates_sync_cursor()
    {
        $admin = $this->createAuthUserWithRole('super_admin');
        $insiden = OperasiInsiden::factory()->create();

        $mobilisasi = OperasiMobilisasi::create([
            'uuid_mobilisasi' => (string) Str::uuid(),
            'id_insiden' => $insiden->id_insiden,
            'id_pengguna' => $admin->id_pengguna,
            'jenis_mobilisasi' => 'relawan',
            'status_mobilisasi' => 'draft',
        ]);

        // clear previous cursor for clean test
        \App\Models\SyncCursor::truncate();

        $mobilisasi->update(['status_mobilisasi' => 'disetujui']);

        $this->assertDatabaseHas('sync_cursors', [
            'entity_type' => 'mobilisasi',
            'uuid_entity' => $mobilisasi->uuid_mobilisasi,
            'action' => 'update',
        ]);
    }

    public function test_delete_mobilisasi_generates_sync_tombstone()
    {
        $admin = $this->createAuthUserWithRole('super_admin');
        $insiden = OperasiInsiden::factory()->create();

        $mobilisasi = OperasiMobilisasi::create([
            'uuid_mobilisasi' => (string) Str::uuid(),
            'id_insiden' => $insiden->id_insiden,
            'id_pengguna' => $admin->id_pengguna,
            'jenis_mobilisasi' => 'relawan',
            'status_mobilisasi' => 'draft',
        ]);

        $uuid = $mobilisasi->uuid_mobilisasi;
        $mobilisasi->delete();

        $this->assertDatabaseHas('sync_tombstones', [
            'entity_type' => 'mobilisasi',
            'uuid_entity' => $uuid,
        ]);
    }

    public function test_pull_sync_receives_mobilisasi_data()
    {
        $admin = $this->createAuthUserWithRole('super_admin');
        $insiden = OperasiInsiden::factory()->create();

        $mobilisasi = OperasiMobilisasi::create([
            'uuid_mobilisasi' => (string) Str::uuid(),
            'id_insiden' => $insiden->id_insiden,
            'id_pengguna' => $admin->id_pengguna,
            'jenis_mobilisasi' => 'relawan',
            'status_mobilisasi' => 'draft',
        ]);

        $payload = [
            'device_uuid' => (string) Str::uuid(),
            'request_id' => (string) Str::uuid(),
            'cursors' => [
                'mobilisasi' => 0
            ],
            'changes' => []
        ];

        $response = $this->actingAs($admin)->postJson(route('api.v1.sync'), $payload);

        $response->assertStatus(200)
                 ->assertJsonPath('success', true);

        $changes = $response->json('data.changes');
        $found = collect($changes)->firstWhere('data.id', $mobilisasi->uuid_mobilisasi);
        
        $this->assertNotNull($found);
        $this->assertEquals('mobilisasi', $found['table']);
        $this->assertEquals('relawan', $found['data']['jenis_mobilisasi']);
    }

    public function test_pull_sync_receives_tombstone_data()
    {
        $admin = $this->createAuthUserWithRole('super_admin');
        $insiden = OperasiInsiden::factory()->create();

        $mobilisasi = OperasiMobilisasi::create([
            'uuid_mobilisasi' => (string) Str::uuid(),
            'id_insiden' => $insiden->id_insiden,
            'id_pengguna' => $admin->id_pengguna,
            'jenis_mobilisasi' => 'relawan',
            'status_mobilisasi' => 'draft',
        ]);

        $uuid = $mobilisasi->uuid_mobilisasi;
        $mobilisasi->delete(); // This generates tombstone via SyncObserver

        $payload = [
            'device_uuid' => (string) Str::uuid(),
            'request_id' => (string) Str::uuid(),
            'cursors' => [
                'mobilisasi' => 0
            ],
            'changes' => []
        ];

        $response = $this->actingAs($admin)->postJson(route('api.v1.sync'), $payload);

        $response->assertStatus(200)
                 ->assertJsonPath('success', true);

        $tombstones = $response->json('data.tombstones');
        $found = collect($tombstones)->firstWhere('uuid_entity', $uuid);
        
        $this->assertNotNull($found);
        $this->assertEquals('mobilisasi', $found['entity_type']);
    }
}
