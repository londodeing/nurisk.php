<?php

namespace Tests\Feature\Operasi\Mobilisasi;

use App\Models\AuthRole;
use App\Models\AuthUser;
use App\Models\OperasiInsiden;
use App\Models\OperasiMobilisasi;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Str;
use Tests\TestCase;

class MobilisasiApiTest extends TestCase
{
    use DatabaseTransactions;

    private function createAuthUserWithRole(string $roleName, ?int $scopeId = null): AuthUser
    {
        $role = AuthRole::firstOrCreate(['nama_peran' => $roleName], ['deskripsi' => 'Role', 'level_otoritas' => 1], null, 'dihapus_pada');

        return AuthUser::factory()->aktif()->create([
            'id_peran' => $role->id_peran,
            'default_scope_id' => $scopeId
        ], null, 'dihapus_pada');
    }

    // 1. Authorization: viewAny
    public function test_api_index_unauthorized_access()
    {
        $relawan = $this->createAuthUserWithRole('relawan');
        $response = $this->actingAs($relawan)->getJson(route('api.v1.mobilisasi.index'));
        $response->assertStatus(403);
    }

    public function test_api_index_authorized_access()
    {
        $admin = $this->createAuthUserWithRole('super_admin');
        $response = $this->actingAs($admin)->getJson(route('api.v1.mobilisasi.index'));
        $response->assertStatus(200);
    }

    // 2. Authorization: create (Cross Scope Access)
    public function test_api_store_cross_scope_boundary_forbidden()
    {
        $pcnu1 = \App\Models\OrganisasiPcnu::factory()->create();
        $pcnu = $this->createAuthUserWithRole('pcnu', $pcnu1->id_pcnu);
        
        $pcnu2 = \App\Models\OrganisasiPcnu::factory()->create();
        // incident belongs to scopeId 2
        $insiden = OperasiInsiden::factory()->create(['status_insiden' => 'respon', 'id_pcnu' => $pcnu2->id_pcnu], null, 'dihapus_pada');

        $payload = [
            'uuid_insiden' => $insiden->uuid_insiden,
            'id_pengguna' => $pcnu->id_pengguna,
            'jenis_mobilisasi' => 'relawan',
            'lokasi_asal' => 'PCNU 1',
            'lokasi_tujuan' => 'Posko Utama',
        ];

        // This should fail because pcnu scope 1 cannot manage incident in scope 2
        $response = $this->actingAs($pcnu)->postJson(route('api.v1.mobilisasi.store'), $payload);
        $response->assertStatus(403);
    }

    public function test_api_store_authorized_scope()
    {
        $pcnu1 = \App\Models\OrganisasiPcnu::factory()->create();
        $pcnu = $this->createAuthUserWithRole('pcnu', $pcnu1->id_pcnu);
        $insiden = OperasiInsiden::factory()->create(['status_insiden' => 'respon', 'id_pcnu' => $pcnu1->id_pcnu], null, 'dihapus_pada'); // Match scope

        $payload = [
            'uuid_insiden' => $insiden->uuid_insiden,
            'id_pengguna' => $pcnu->id_pengguna,
            'jenis_mobilisasi' => 'relawan',
            'lokasi_asal' => 'PCNU 1',
            'lokasi_tujuan' => 'Posko Utama',
        ];

        $response = $this->actingAs($pcnu)->postJson(route('api.v1.mobilisasi.store'), $payload);
        $response->assertStatus(201);
    }

    // 3. State Machine flows
    public function test_api_state_machine_full_flow()
    {
        $admin = $this->createAuthUserWithRole('super_admin');
        $insiden = OperasiInsiden::factory()->create(['status_insiden' => 'respon'], null, 'dihapus_pada');

        $mobilisasi = OperasiMobilisasi::create([
            'id_insiden' => $insiden->id_insiden,
            'id_pengguna' => $admin->id_pengguna,
            'jenis_mobilisasi' => 'relawan',
            'status_mobilisasi' => 'draft',
            'created_by' => $admin->id_pengguna,
        ], null, 'dihapus_pada');

        $uuid = $mobilisasi->uuid_mobilisasi;

        // draft -> disetujui
        $this->actingAs($admin)->postJson(route('api.v1.mobilisasi.approve', ['uuid' => $uuid]))->assertStatus(200);
        $this->assertEquals('disetujui', $mobilisasi->refresh()->status_mobilisasi);

        // disetujui -> berangkat
        $this->actingAs($admin)->postJson(route('api.v1.mobilisasi.depart', ['uuid' => $uuid]))->assertStatus(200);
        $this->assertEquals('berangkat', $mobilisasi->refresh()->status_mobilisasi);
        $this->assertNotNull($mobilisasi->refresh()->waktu_berangkat);

        // berangkat -> tiba
        $this->actingAs($admin)->postJson(route('api.v1.mobilisasi.arrive', ['uuid' => $uuid]))->assertStatus(200);
        $this->assertEquals('tiba', $mobilisasi->refresh()->status_mobilisasi);
        $this->assertNotNull($mobilisasi->refresh()->waktu_tiba);

        // tiba -> selesai
        $this->actingAs($admin)->postJson(route('api.v1.mobilisasi.finish', ['uuid' => $uuid]))->assertStatus(200);
        $this->assertEquals('selesai', $mobilisasi->refresh()->status_mobilisasi);
    }

    public function test_api_state_machine_cancel_flow()
    {
        $admin = $this->createAuthUserWithRole('super_admin');
        $insiden = OperasiInsiden::factory()->create(['status_insiden' => 'respon'], null, 'dihapus_pada');

        $mobilisasi = OperasiMobilisasi::create([
            'id_insiden' => $insiden->id_insiden,
            'id_pengguna' => $admin->id_pengguna,
            'jenis_mobilisasi' => 'relawan',
            'status_mobilisasi' => 'draft',
            'created_by' => $admin->id_pengguna,
        ], null, 'dihapus_pada');

        $uuid = $mobilisasi->uuid_mobilisasi;

        // draft -> dibatalkan
        $this->actingAs($admin)->postJson(route('api.v1.mobilisasi.cancel', ['uuid' => $uuid]))->assertStatus(200);
        $this->assertEquals('dibatalkan', $mobilisasi->refresh()->status_mobilisasi);
    }

    public function test_api_state_machine_invalid_transition()
    {
        $admin = $this->createAuthUserWithRole('super_admin');
        $insiden = OperasiInsiden::factory()->create(['status_insiden' => 'respon'], null, 'dihapus_pada');

        $mobilisasi = OperasiMobilisasi::create([
            'id_insiden' => $insiden->id_insiden,
            'id_pengguna' => $admin->id_pengguna,
            'jenis_mobilisasi' => 'relawan',
            'status_mobilisasi' => 'berangkat',
            'created_by' => $admin->id_pengguna,
        ], null, 'dihapus_pada');

        $response = $this->actingAs($admin)
            ->postJson(route('api.v1.mobilisasi.approve', ['uuid' => $mobilisasi->uuid_mobilisasi]));

        $response->assertStatus(422)
                 ->assertJsonPath('message', 'Invalid state transition');
    }

    // 4. Update and Delete
    public function test_api_update_mobilisasi()
    {
        $admin = $this->createAuthUserWithRole('super_admin');
        $insiden = OperasiInsiden::factory()->create(['status_insiden' => 'respon'], null, 'dihapus_pada');

        $mobilisasi = OperasiMobilisasi::create([
            'id_insiden' => $insiden->id_insiden,
            'id_pengguna' => $admin->id_pengguna,
            'jenis_mobilisasi' => 'relawan',
            'status_mobilisasi' => 'draft',
            'created_by' => $admin->id_pengguna,
        ], null, 'dihapus_pada');

        $payload = ['jenis_mobilisasi' => 'armada'];

        $response = $this->actingAs($admin)->putJson(route('api.v1.mobilisasi.update', ['uuid' => $mobilisasi->uuid_mobilisasi]), $payload, []);

        $response->assertStatus(200);
        $this->assertEquals('armada', $mobilisasi->refresh()->jenis_mobilisasi);
    }

    public function test_api_delete_mobilisasi()
    {
        $admin = $this->createAuthUserWithRole('super_admin');
        $insiden = OperasiInsiden::factory()->create(['status_insiden' => 'respon'], null, 'dihapus_pada');

        $mobilisasi = OperasiMobilisasi::create([
            'id_insiden' => $insiden->id_insiden,
            'id_pengguna' => $admin->id_pengguna,
            'jenis_mobilisasi' => 'relawan',
            'status_mobilisasi' => 'draft',
            'created_by' => $admin->id_pengguna,
        ], null, 'dihapus_pada');

        $response = $this->actingAs($admin)->deleteJson(route('api.v1.mobilisasi.destroy', ['uuid' => $mobilisasi->uuid_mobilisasi]));
        $response->assertStatus(200);
        
        $this->assertSoftDeleted('operasi_mobilisasi', [
            'id_mobilisasi' => $mobilisasi->id_mobilisasi,
        ], null, 'dihapus_pada');
    }
}
