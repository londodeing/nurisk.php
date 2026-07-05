<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use App\Models\OperasiSitrep;
use App\Models\SyncTombstone;
use App\Models\SyncCursor;
use Illuminate\Support\Str;

class SyncTombstoneTest extends TestCase
{
    use DatabaseTransactions;

    private function createSuperAdmin(): \App\Models\AuthUser
    {
        $role = \App\Models\AuthRole::firstOrCreate(['nama_peran' => 'super_admin'], ['deskripsi' => 'Super Admin', 'level_otoritas' => 99]);
        return \App\Models\AuthUser::factory()->aktif()->create(['id_peran' => $role->id_peran]);
    }

    public function test_it_creates_tombstone_on_delete()
    {
        $user = $this->createSuperAdmin();
        $uuid = (string) Str::uuid();
        $insiden = \App\Models\OperasiInsiden::factory()->create(['status_insiden' => 'respon']);
        $assessment = \App\Models\AssessmentUtama::factory()->create(['id_insiden' => $insiden->id_insiden]);

        $sitrep = OperasiSitrep::create([
            'uuid_sitrep' => $uuid,
            'id_insiden' => $insiden->id_insiden,
            'id_assessment_basis' => $assessment->id_assessment_utama,
            'id_pembuat' => $user->id_pengguna,
            'nomor_sitrep' => 1,
            'periode_sitrep' => 'Harian',
            'waktu_sitrep' => now(),
            'catatan' => 'Test Sitrep',
            'sync_version' => 1,
        ]);

        $uuid = $sitrep->uuid_sitrep;
        $sitrep->delete();

        $this->assertDatabaseHas('sync_tombstones', [
            'entity_type' => 'sitrep',
            'uuid_entity' => $uuid,
        ]);

        $this->assertDatabaseHas('sync_cursors', [
            'entity_type' => 'sitrep',
            'uuid_entity' => $uuid,
            'action' => 'delete',
        ]);
    }
}
