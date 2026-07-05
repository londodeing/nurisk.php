<?php

namespace Tests\Feature;

use App\Models\AuthRole;
use App\Models\AuthUser;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use App\Models\MobileDevice;
use App\Models\OperasiSitrep;
use Illuminate\Support\Str;

class SyncConflictTest extends TestCase
{
    use DatabaseTransactions;

    private function createSuperAdmin(): AuthUser
    {
        $role = AuthRole::firstOrCreate(['nama_peran' => 'super_admin'], ['deskripsi' => 'Super Admin', 'level_otoritas' => 99]);
        return AuthUser::factory()->aktif()->create(['id_peran' => $role->id_peran]);
    }

    public function test_it_handles_conflict_and_saves_to_db()
    {
        $user = $this->createSuperAdmin();
        $device = MobileDevice::create([
            'uuid_device' => 'device-123',
            'id_pengguna' => $user->id_pengguna,
            'platform' => 'android',
            'app_version' => '1.0.0',
            'status' => 'active',
            'trust_score' => 100,
        ]);

        $uuid = (string) Str::uuid();
        $insiden = \App\Models\OperasiInsiden::factory()->create(['status_insiden' => 'respon']);
        $assessment = \App\Models\AssessmentUtama::factory()->create(['id_insiden' => $insiden->id_insiden]);

        // Server has version 3
        $sitrep = OperasiSitrep::create([
            'uuid_sitrep' => $uuid,
            'id_insiden' => $insiden->id_insiden,
            'id_assessment_basis' => $assessment->id_assessment_utama,
            'id_pembuat' => $user->id_pengguna,
            'nomor_sitrep' => 1,
            'periode_sitrep' => 'Harian',
            'waktu_sitrep' => now(),
            'catatan' => 'Test Sitrep',
            'sync_version' => 3,
        ]);
        
        $uuid = $sitrep->uuid_sitrep;

        $requestId = (string) Str::uuid();

        // Client tries to push version 2 (stale — last-write-wins)
        $payload = [
            'request_id' => $requestId,
            'device_uuid' => 'device-123',
            'cursors' => [
                'sitrep' => 0
            ],
            'changes' => [
                [
                    'table' => 'sitrep',
                    'action' => 'upsert',
                    'data' => [
                        'uuid_sitrep' => $uuid,
                        'uuid_insiden' => $insiden->uuid_insiden,
                        'id_assessment_basis' => $assessment->id_assessment_utama,
                        'id_pembuat' => 1,
                        'nomor_sitrep' => 'SIT-001-Edit',
                        'periode_sitrep' => 'Harian',
                        'waktu_sitrep' => now()->toIso8601String(),
                        'catatan' => 'Test Sitrep Edit',
                        'sync_version' => 2,
                    ]
                ]
            ]
        ];

        // Last-write-wins: server accepts client change
        $response = $this->actingAs($user)->postJson('/api/v1/sync', $payload);
        $response->assertStatus(200);

        // Conflict is still returned and recorded
        $this->assertCount(1, $response->json('data.conflicts'));
        $this->assertEquals($uuid, $response->json('data.conflicts.0.uuid_entity'));

        // Check if conflict is saved in DB
        $this->assertDatabaseHas('sync_conflicts', [
            'uuid_entity' => $uuid,
            'entity_type' => 'sitrep',
            'client_version' => 2,
            'server_version' => 3,
        ]);

        // Last-write-wins: record IS updated by client
        $this->assertDatabaseHas('operasi_sitrep', [
            'uuid_sitrep' => $uuid,
            'nomor_sitrep' => 'SIT-001-Edit',
            'sync_version' => 4,
        ]);
    }
}
