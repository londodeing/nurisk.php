<?php

namespace Tests\Feature\Operasi;

use App\Models\AuthRole;
use App\Models\AuthUser;
use App\Models\OperasiInsiden;
use App\Models\OperasiPenugasan;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Illuminate\Support\Str;

class PenugasanTest extends TestCase
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

    public function test_api_store_penugasan_flat_endpoint()
    {
        $admin = $this->createAuthUserWithRole('super_admin');
        $insiden = OperasiInsiden::factory()->create(['status_insiden' => 'respon'])->refresh();
        $trcUser = $this->createAuthUserWithRole('trc');

        $payload = [
            'uuid_insiden' => $insiden->uuid_insiden,
            'id_pengguna' => $trcUser->id_pengguna,
            'peran_otoritas' => 'trc',
            'catatan' => 'Tugas awal',
        ];

        $response = $this->actingAs($admin)
            ->postJson(route('api.v1.penugasan.store'), $payload);

        $response->assertStatus(201)
                 ->assertJsonPath('success', true)
                 ->assertJsonPath('data.peran_otoritas', 'trc')
                 ->assertJsonPath('data.status_penugasan', 'draft');

        $this->assertDatabaseHas('operasi_penugasan', [
            'id_insiden' => $insiden->id_insiden,
            'id_pengguna' => $trcUser->id_pengguna,
            'peran_otoritas' => 'trc',
            'status_penugasan' => 'draft',
        ]);
    }

    public function test_api_update_status_penugasan()
    {
        $admin = $this->createAuthUserWithRole('super_admin');
        $insiden = OperasiInsiden::factory()->create(['status_insiden' => 'respon'])->refresh();
        $relawan = $this->createAuthUserWithRole('relawan');

        $penugasan = OperasiPenugasan::create([
            'id_insiden' => $insiden->id_insiden,
            'id_pengguna' => $relawan->id_pengguna,
            'peran_otoritas' => 'trc',
            'status_penugasan' => 'aktif',
            'waktu_mulai' => now(),
            'ditugaskan_oleh' => $admin->id_pengguna,
        ]);

        $response = $this->actingAs($admin)
            ->patchJson(route('api.v1.penugasan.status', ['uuid' => $penugasan->uuid_penugasan]), [
                'status_penugasan' => 'selesai',
                'catatan' => 'Selesai tugas',
            ]);

        $response->assertStatus(200)
                 ->assertJsonPath('data.status_penugasan', 'selesai');

        $penugasan->refresh();
        $this->assertEquals('selesai', $penugasan->status_penugasan);
        $this->assertNotNull($penugasan->waktu_selesai);
    }

    public function test_lapis_4_auth_relawan_biasa_cannot_assign()
    {
        $relawan = $this->createAuthUserWithRole('relawan');
        $insiden = OperasiInsiden::factory()->create(['status_insiden' => 'respon'])->refresh();
        $relawanTarget = $this->createAuthUserWithRole('relawan');

        $payload = [
            'uuid_insiden' => $insiden->uuid_insiden,
            'id_pengguna' => $relawanTarget->id_pengguna,
            'peran_otoritas' => 'trc',
        ];

        $response = $this->actingAs($relawan)
            ->postJson(route('api.v1.penugasan.store'), $payload);

        $response->assertStatus(403);
    }

    public function test_lapis_4_auth_komandan_insiden_can_assign()
    {
        $komandan = $this->createAuthUserWithRole('relawan');
        $insiden = OperasiInsiden::factory()->create(['status_insiden' => 'respon'])->refresh();
        
        OperasiPenugasan::create([
            'id_insiden' => $insiden->id_insiden,
            'id_pengguna' => $komandan->id_pengguna,
            'peran_otoritas' => 'komandan_insiden',
            'status_penugasan' => 'aktif',
            'waktu_mulai' => now(),
            'ditugaskan_oleh' => 1,
        ]);

        $relawanTarget = $this->createAuthUserWithRole('relawan');

        $payload = [
            'uuid_insiden' => $insiden->uuid_insiden,
            'id_pengguna' => $relawanTarget->id_pengguna,
            'peran_otoritas' => 'medis',
        ];

        $response = $this->actingAs($komandan)
            ->postJson(route('api.v1.penugasan.store'), $payload);

        $response->assertStatus(201);
    }

    public function test_cannot_assign_double_active_role_same_incident()
    {
        $admin = $this->createAuthUserWithRole('super_admin');
        $insiden = OperasiInsiden::factory()->create(['status_insiden' => 'respon'])->refresh();
        $relawan = $this->createAuthUserWithRole('relawan');

        OperasiPenugasan::create([
            'id_insiden' => $insiden->id_insiden,
            'id_pengguna' => $relawan->id_pengguna,
            'peran_otoritas' => 'trc',
            'status_penugasan' => 'aktif',
            'waktu_mulai' => now(),
            'ditugaskan_oleh' => $admin->id_pengguna,
        ]);

        $payload = [
            'uuid_insiden' => $insiden->uuid_insiden,
            'id_pengguna' => $relawan->id_pengguna,
            'peran_otoritas' => 'logistik',
        ];

        $response = $this->actingAs($admin)
            ->postJson(route('api.v1.penugasan.store'), $payload);

        $response->assertStatus(500); // Because of exception thrown in service
    }
}
