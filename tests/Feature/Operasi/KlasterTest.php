<?php

namespace Tests\Feature\Operasi;

use App\Models\AuthRole;
use App\Models\AuthUser;
use App\Models\OperasiInsiden;
use App\Models\MasterKlaster;
use App\Models\OperasiKlaster;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class KlasterTest extends TestCase
{
    use DatabaseTransactions;

    private function createAuthUserWithRole(string $roleName, ?int $scopeId = null): AuthUser
    {
        $role = AuthRole::firstOrCreate(['nama_peran' => $roleName], ['deskripsi' => 'Role', 'level_otoritas' => 1]);

        return AuthUser::factory()->aktif()->create([
            'id_peran' => $role->id_peran,
            'default_scope_id' => $scopeId
        ]);
    }

    public function test_api_store_klaster()
    {
        $admin = $this->createAuthUserWithRole('super_admin');
        $insiden = OperasiInsiden::factory()->create(['status_insiden' => 'respon'])->refresh();
        
        $master = MasterKlaster::create([
            'nama_klaster' => 'Kesehatan',
            'is_aktif' => true,
        ]);

        $payload = [
            'uuid_insiden' => $insiden->uuid_insiden,
            'id_master_klaster' => $master->id_master_klaster,
            'prioritas' => 'tinggi',
            'target_cakupan' => 'Seluruh korban luka',
        ];

        $response = $this->actingAs($admin)
            ->postJson(route('api.v1.klaster.store'), $payload);

        $response->assertStatus(201)
                 ->assertJsonPath('success', true)
                 ->assertJsonPath('data.prioritas', 'tinggi')
                 ->assertJsonPath('data.master_klaster.nama_klaster', 'Kesehatan');

        $this->assertDatabaseHas('operasi_klaster', [
            'id_insiden' => $insiden->id_insiden,
            'id_master_klaster' => $master->id_master_klaster,
            'status_klaster' => 'aktif',
        ]);
    }

    public function test_cannot_activate_same_klaster_twice()
    {
        $admin = $this->createAuthUserWithRole('super_admin');
        $insiden = OperasiInsiden::factory()->create(['status_insiden' => 'respon'])->refresh();
        
        $master = MasterKlaster::create([
            'nama_klaster' => 'Kesehatan',
            'is_aktif' => true,
        ]);

        OperasiKlaster::create([
            'id_insiden' => $insiden->id_insiden,
            'id_master_klaster' => $master->id_master_klaster,
            'status_klaster' => 'aktif',
            'waktu_aktivasi' => now(),
        ]);

        $payload = [
            'uuid_insiden' => $insiden->uuid_insiden,
            'id_master_klaster' => $master->id_master_klaster,
            'prioritas' => 'tinggi',
        ];

        $response = $this->actingAs($admin)
            ->postJson(route('api.v1.klaster.store'), $payload);

        $response->assertStatus(500); // Exception thrown
    }

    public function test_api_show_klaster_by_uuid()
    {
        $admin = $this->createAuthUserWithRole('super_admin');
        $insiden = OperasiInsiden::factory()->create(['status_insiden' => 'respon'])->refresh();
        $master = MasterKlaster::create([
            'nama_klaster' => 'Kesehatan',
            'is_aktif' => true,
        ]);

        $klaster = OperasiKlaster::create([
            'id_insiden' => $insiden->id_insiden,
            'id_master_klaster' => $master->id_master_klaster,
            'status_klaster' => 'aktif',
            'waktu_aktivasi' => now(),
        ]);

        $response = $this->actingAs($admin)
            ->getJson(route('api.v1.klaster.show', ['uuid' => $klaster->uuid_klaster_operasi]));

        $response->assertStatus(200)
                 ->assertJsonPath('success', true)
                 ->assertJsonPath('data.id', $klaster->uuid_klaster_operasi);
    }

    public function test_api_update_klaster()
    {
        $admin = $this->createAuthUserWithRole('super_admin');
        $insiden = OperasiInsiden::factory()->create(['status_insiden' => 'respon'])->refresh();
        $master = MasterKlaster::create([
            'nama_klaster' => 'Kesehatan',
            'is_aktif' => true,
        ]);

        $klaster = OperasiKlaster::create([
            'id_insiden' => $insiden->id_insiden,
            'id_master_klaster' => $master->id_master_klaster,
            'status_klaster' => 'aktif',
            'waktu_aktivasi' => now(),
        ]);

        $response = $this->actingAs($admin)
            ->patchJson(route('api.v1.klaster.update', ['uuid' => $klaster->uuid_klaster_operasi]), [
                'prioritas' => 'kritis',
                'catatan' => 'catatan baru'
            ]);

        $response->assertStatus(200)
                 ->assertJsonPath('success', true)
                 ->assertJsonPath('data.prioritas', 'kritis')
                 ->assertJsonPath('data.catatan', 'catatan baru');
    }

    public function test_api_delete_klaster()
    {
        $admin = $this->createAuthUserWithRole('super_admin');
        $insiden = OperasiInsiden::factory()->create(['status_insiden' => 'respon'])->refresh();
        $master = MasterKlaster::create([
            'nama_klaster' => 'Kesehatan',
            'is_aktif' => true,
        ]);

        $klaster = OperasiKlaster::create([
            'id_insiden' => $insiden->id_insiden,
            'id_master_klaster' => $master->id_master_klaster,
            'status_klaster' => 'aktif',
            'waktu_aktivasi' => now(),
        ]);

        $response = $this->actingAs($admin)
            ->deleteJson(route('api.v1.klaster.destroy', ['uuid' => $klaster->uuid_klaster_operasi]));

        $response->assertStatus(200)
                 ->assertJsonPath('success', true);

        $this->assertSoftDeleted($klaster);
    }
}
