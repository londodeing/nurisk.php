<?php

namespace Tests\Feature\Operasi;

use App\Models\AuthRole;
use App\Models\AuthUser;
use App\Models\OperasiInsiden;
use App\Models\OperasiPenugasan;
use App\Models\SyncCursor;
use App\Models\SyncTombstone;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class TombstoneSyncTest extends TestCase
{
    use DatabaseTransactions;

    private function createAuthUserWithRole(string $roleName): AuthUser
    {
        $role = AuthRole::firstOrCreate(['nama_peran' => $roleName], ['deskripsi' => 'Role', 'level_otoritas' => 1]);
        return AuthUser::factory()->aktif()->create([
            'id_peran' => $role->id_peran
        ]);
    }

    public function test_deleting_entity_creates_tombstone_and_propagates_in_sync()
    {
        $user = $this->createAuthUserWithRole('super_admin');
        $insiden = OperasiInsiden::factory()->create(['status_insiden' => 'respon'])->refresh();
        $relawan = $this->createAuthUserWithRole('relawan');

        // 1. Create Penugasan
        $penugasan = OperasiPenugasan::create([
            'id_insiden' => $insiden->id_insiden,
            'id_pengguna' => $relawan->id_pengguna,
            'peran_otoritas' => 'trc',
            'status_penugasan' => 'draft',
            'waktu_mulai' => now(),
            'ditugaskan_oleh' => $user->id_pengguna,
        ]);

        $uuid = $penugasan->uuid_penugasan;
        $cursorBeforeDelete = SyncCursor::max('cursor_value') ?? 0;

        // 2. Delete Penugasan
        $penugasan->alasan_hapus = 'Salah entri data';
        $penugasan->deleted_by = $user->id_pengguna;
        $penugasan->save();
        $penugasan->delete();

        // Check if tombstone was created
        $this->assertDatabaseHas('sync_tombstones', [
            'uuid_entity' => $uuid,
            'entity_type' => 'penugasan',
            'alasan_hapus' => 'Salah entri data',
            'deleted_by' => $user->id_pengguna,
        ]);

        // 3. Sync API call with cursor before delete should return this tombstone
        $response = $this->actingAs($user)->postJson(route('api.v1.sync'), [
            'request_id' => \Illuminate\Support\Str::uuid()->toString(),
            'device_uuid' => 'client-device-001',
            'cursors' => ['penugasan' => $cursorBeforeDelete],
            'changes' => []
        ]);

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data.tombstones')
            ->assertJsonPath('data.tombstones.0.uuid_entity', $uuid)
            ->assertJsonPath('data.tombstones.0.entity_type', 'penugasan');
    }
}
