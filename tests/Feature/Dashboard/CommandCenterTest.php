<?php

namespace Tests\Feature\Dashboard;

use Tests\TestCase;
use App\Models\AuthUser;
use App\Models\AuthRole;
use App\Models\OperasiInsiden;
use App\Models\OperasiPenugasan;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class CommandCenterTest extends TestCase
{
    use DatabaseTransactions;

    // === AKSES ===

    public function test_super_admin_dapat_akses_command_center()
    {
        $role = AuthRole::factory()->create(['nama_peran' => 'super_admin']);
        $user = AuthUser::factory()->create(['id_peran' => $role->id_peran, 'status_akun' => 'aktif']);
        $this->actingAs($user)->get('/command-center')->assertStatus(200);
    }

    public function test_pwnu_dapat_akses_command_center()
    {
        $role = AuthRole::factory()->create(['nama_peran' => 'pwnu']);
        $user = AuthUser::factory()->create(['id_peran' => $role->id_peran, 'status_akun' => 'aktif']);
        $this->actingAs($user)->get('/command-center')->assertStatus(200);
    }

    public function test_pcnu_dapat_akses_command_center()
    {
        $role = AuthRole::factory()->create(['nama_peran' => 'pcnu']);
        $user = AuthUser::factory()->create(['id_peran' => $role->id_peran, 'status_akun' => 'aktif']);
        $this->actingAs($user)->get('/command-center')->assertStatus(200);
    }

    public function test_relawan_tanpa_penugasan_tidak_dapat_akses()
    {
        $role = AuthRole::factory()->create(['nama_peran' => 'relawan']);
        $user = AuthUser::factory()->create(['id_peran' => $role->id_peran, 'status_akun' => 'aktif']);
        $this->actingAs($user)->get('/command-center')->assertStatus(403);
    }

    public function test_relawan_dengan_penugasan_aktif_dapat_akses()
    {
        $role = AuthRole::factory()->create(['nama_peran' => 'relawan']);
        $user = AuthUser::factory()->create(['id_peran' => $role->id_peran, 'status_akun' => 'aktif']);
        OperasiPenugasan::factory()->create([
            'id_pengguna' => $user->id_pengguna,
            'status_penugasan' => 'aktif',
        ]);
        $this->actingAs($user)->get('/command-center')->assertStatus(200);
    }

    public function test_publik_tanpa_login_diredirect()
    {
        $this->get('/command-center')->assertStatus(302); // Redirect ke login
    }

    // === SCOPE FILTER ===

    public function test_pcnu_hanya_melihat_insiden_scope_sendiri()
    {
        $role = AuthRole::factory()->create(['nama_peran' => 'pcnu']);
        $pcnu1 = \App\Models\OrganisasiPcnu::factory()->create();
        $pcnu2 = \App\Models\OrganisasiPcnu::factory()->create();
        
        $userPcnuA = AuthUser::factory()->create(['id_peran' => $role->id_peran, 'default_scope_type' => 'pcnu', 'default_scope_id' => $pcnu1->id_pcnu, 'status_akun' => 'aktif']);
        
        OperasiInsiden::factory()->create(['id_pcnu' => $pcnu1->id_pcnu, 'status_insiden' => 'respon']);
        OperasiInsiden::factory()->create(['id_pcnu' => $pcnu2->id_pcnu, 'status_insiden' => 'respon']);

        $response = $this->actingAs($userPcnuA)->getJson('/api/command-center/insiden-aktif');
        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
    }

    public function test_pwnu_melihat_semua_insiden_lintas_pcnu()
    {
        $role = AuthRole::factory()->create(['nama_peran' => 'pwnu']);
        $userPwnu = AuthUser::factory()->create(['id_peran' => $role->id_peran, 'status_akun' => 'aktif']);
        
        $pcnu1 = \App\Models\OrganisasiPcnu::factory()->create();
        $pcnu2 = \App\Models\OrganisasiPcnu::factory()->create();
        
        OperasiInsiden::factory()->create(['id_pcnu' => $pcnu1->id_pcnu, 'status_insiden' => 'respon']);
        OperasiInsiden::factory()->create(['id_pcnu' => $pcnu2->id_pcnu, 'status_insiden' => 'respon']);

        $response = $this->actingAs($userPwnu)->getJson('/api/command-center/insiden-aktif');
        $response->assertStatus(200);
        $this->assertCount(2, $response->json('data'));
    }

    // === API ENDPOINTS ===

    public function test_api_insiden_aktif_hanya_return_respon_dan_pemulihan()
    {
        $role = AuthRole::factory()->create(['nama_peran' => 'super_admin']);
        $user = AuthUser::factory()->create(['id_peran' => $role->id_peran, 'status_akun' => 'aktif']);
        
        OperasiInsiden::factory()->create(['status_insiden' => 'draft']);
        OperasiInsiden::factory()->create(['status_insiden' => 'respon']);
        OperasiInsiden::factory()->create(['status_insiden' => 'selesai']);

        $response = $this->actingAs($user)->getJson('/api/command-center/insiden-aktif');
        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
        $this->assertEquals('respon', $response->json('data.0.status'));
        $response->assertJsonStructure(['data' => [['posaju']]]);
    }

    public function test_api_statistik_return_struktur_benar()
    {
        $role = AuthRole::factory()->create(['nama_peran' => 'super_admin']);
        $user = AuthUser::factory()->create(['id_peran' => $role->id_peran, 'status_akun' => 'aktif']);
        $response = $this->actingAs($user)->getJson('/api/command-center/statistik');
        
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'total_insiden',
            'total_personel',
            'total_posaju',
            'korban_terdampak',
            'updated_at',
        ]);
    }

    public function test_api_stok_kritis_return_200_meski_model_tidak_ada()
    {
        $role = AuthRole::factory()->create(['nama_peran' => 'super_admin']);
        $user = AuthUser::factory()->create(['id_peran' => $role->id_peran, 'status_akun' => 'aktif']);
        $response = $this->actingAs($user)->getJson('/api/command-center/stok-kritis');
        $response->assertStatus(200);
    }

    public function test_api_jurnal_terbaru_return_200_meski_model_tidak_ada()
    {
        $role = AuthRole::factory()->create(['nama_peran' => 'super_admin']);
        $user = AuthUser::factory()->create(['id_peran' => $role->id_peran, 'status_akun' => 'aktif']);
        $response = $this->actingAs($user)->getJson('/api/command-center/jurnal-terbaru');
        $response->assertStatus(200);
    }

    public function test_api_endpoints_require_auth()
    {
        $this->getJson('/api/command-center/insiden-aktif')->assertStatus(401);
    }
}
