<?php

namespace Tests\Feature\Operasi;

use App\Models\AuthRole;
use App\Models\AuthUser;
use App\Models\OperasiInsiden;
use App\Models\OperasiPenugasan;
use App\Models\SyncCursor;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class OfflineSyncTest extends TestCase
{
    use DatabaseTransactions;

    private function createAuthUserWithRole(string $roleName): AuthUser
    {
        $role = AuthRole::firstOrCreate(['nama_peran' => $roleName], ['deskripsi' => 'Role', 'level_otoritas' => 1]);
        return AuthUser::factory()->aktif()->create([
            'id_peran' => $role->id_peran
        ]);
    }

    public function test_sync_upserts_and_returns_newer_server_records()
    {
        $user = $this->createAuthUserWithRole('super_admin');
        $insiden = OperasiInsiden::factory()->create(['status_insiden' => 'respon'])->refresh();
        $relawan = $this->createAuthUserWithRole('relawan');

        // 1. Client uploads a new penugasan
        $uuidPenugasan = \Illuminate\Support\Str::uuid()->toString();
        $payload = [
            'request_id' => \Illuminate\Support\Str::uuid()->toString(),
            'device_uuid' => 'client-device-001',
            'cursors' => ['penugasan' => 0],
            'changes' => [
                [
                    'table' => 'operasi_penugasan',
                    'action' => 'upsert',
                    'data' => [
                        'uuid_penugasan' => $uuidPenugasan,
                        'uuid_insiden' => $insiden->uuid_insiden,
                        'id_pengguna' => $relawan->id_pengguna,
                        'peran_otoritas' => 'trc',
                        'status_penugasan' => 'aktif',
                        'waktu_mulai' => now()->format('Y-m-d H:i:s'),
                        'ditugaskan_oleh' => $user->id_pengguna,
                        'sync_version' => 1
                    ]
                ]
            ]
        ];

        $response = $this->actingAs($user)->postJson(route('api.v1.sync'), $payload);
        $response->dump();

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'server_cursors',
                    'changes',
                    'tombstones',
                    'conflicts'
                ]
            ]);

        $this->assertDatabaseHas('operasi_penugasan', [
            'uuid_penugasan' => $uuidPenugasan,
            'status_penugasan' => 'aktif',
            'sync_version' => 1
        ]);

        // 2. Fetch updates newer than cursor 0
        $serverCursors = $response->json('data.server_cursors');

        // A second sync with cursor matching latest server cursor should return 0 changes
        $response2 = $this->actingAs($user)->postJson(route('api.v1.sync'), [
            'request_id' => \Illuminate\Support\Str::uuid()->toString(),
            'device_uuid' => 'client-device-001',
            'cursors' => $serverCursors,
            'changes' => []
        ]);

        $response2->assertStatus(200)
            ->assertJsonCount(0, 'data.changes');
    }
}
