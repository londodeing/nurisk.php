<?php

namespace Tests\Feature\Operasi;

use App\Models\AuthRole;
use App\Models\AuthUser;
use App\Models\OperasiInsiden;
use App\Models\OperasiPenugasan;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ConflictResolutionTest extends TestCase
{
    use DatabaseTransactions;

    private function createAuthUserWithRole(string $roleName): AuthUser
    {
        $role = AuthRole::firstOrCreate(['nama_peran' => $roleName], ['deskripsi' => 'Role', 'level_otoritas' => 1]);
        return AuthUser::factory()->aktif()->create([
            'id_peran' => $role->id_peran
        ]);
    }

    public function test_conflict_detected_when_client_version_is_stale()
    {
        $user = $this->createAuthUserWithRole('super_admin');
        $insiden = OperasiInsiden::factory()->create(['status_insiden' => 'respon'])->refresh();
        $relawan = $this->createAuthUserWithRole('relawan');

        // 1. Create a record on the server, which initializes sync_version=1
        $penugasan = OperasiPenugasan::create([
            'id_insiden' => $insiden->id_insiden,
            'id_pengguna' => $relawan->id_pengguna,
            'peran_otoritas' => 'trc',
            'status_penugasan' => 'aktif',
            'waktu_mulai' => now(),
            'ditugaskan_oleh' => $user->id_pengguna,
        ]);

        $this->assertEquals(1, $penugasan->sync_version);

        // 2. Perform an update via standard endpoint, raising sync_version to 2
        $penugasan->catatan = 'Diperbarui pertama';
        $penugasan->save();
        $penugasan = \App\Models\OperasiPenugasan::find($penugasan->id_penugasan);
        $this->assertEquals(2, $penugasan->sync_version);

        // 3. Client tries to sync an update using a stale version = 1
        $response = $this->actingAs($user)->postJson(route('api.v1.sync'), [
            'request_id' => \Illuminate\Support\Str::uuid()->toString(),
            'device_uuid' => 'client-device-001',
            'cursors' => ['penugasan' => 0],
            'changes' => [
                [
                    'table' => 'operasi_penugasan',
                    'action' => 'upsert',
                    'data' => [
                        'uuid_penugasan' => $penugasan->uuid_penugasan,
                        'uuid_insiden' => $insiden->uuid_insiden,
                        'id_pengguna' => $relawan->id_pengguna,
                        'peran_otoritas' => 'medis', // Client tries to change roles
                        'catatan' => 'Mencoba menimpa',
                        'sync_version' => 1 // Stale client version
                    ]
                ]
            ]
        ]);

        // Last-write-wins: server returns 200 and conflict recorded
        $response->assertStatus(200)
            ->assertJsonPath('success', true);

        // Conflict is returned in data
        $this->assertCount(1, $response->json('data.conflicts'));
        $this->assertEquals(2, $response->json('data.conflicts.0.server_version'));
        $this->assertEquals(1, $response->json('data.conflicts.0.client_version'));

        // Last-write-wins: changes ARE applied
        $penugasan = \App\Models\OperasiPenugasan::find($penugasan->id_penugasan);
        $this->assertEquals('medis', $penugasan->peran_otoritas);
        $this->assertEquals('Mencoba menimpa', $penugasan->catatan);
    }
}
