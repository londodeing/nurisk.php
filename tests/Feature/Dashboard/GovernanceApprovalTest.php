<?php

namespace Tests\Feature\Dashboard;

use Tests\TestCase;
use App\Models\AuthUser;
use App\Models\DokumenSuratUtama;
use App\Models\DokumenSuratParaf;
use App\Models\OperasiPleno;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class GovernanceApprovalTest extends TestCase
{
    use DatabaseTransactions; // Tidak pakai RefreshDatabase

    public function test_super_admin_dapat_mengakses_halaman()
    {
        $role = \App\Models\AuthRole::factory()->create(['nama_peran' => 'super_admin']);
        $user = AuthUser::factory()->create(['id_peran' => $role->id_peran, 'status_akun' => 'aktif']);
        $response = $this->actingAs($user)->get(route('governance.approval.index'));
        $response->assertStatus(200);
        $response->assertSee('Governance Approval Center');
    }

    public function test_pcnu_dapat_mengakses_halaman()
    {
        $role = \App\Models\AuthRole::factory()->create(['nama_peran' => 'pcnu']);
        $user = AuthUser::factory()->create(['id_peran' => $role->id_peran, 'status_akun' => 'aktif']);
        $response = $this->actingAs($user)->get(route('governance.approval.index'));
        $response->assertStatus(200);
        $response->assertSee('Governance Approval Center');
    }

    public function test_relawan_diblokir()
    {
        $role = \App\Models\AuthRole::factory()->create(['nama_peran' => 'relawan']);
        $user = AuthUser::factory()->create(['id_peran' => $role->id_peran, 'status_akun' => 'aktif']);
        $response = $this->actingAs($user)->get(route('governance.approval.index'));
        $response->assertStatus(403);
    }

    public function test_guest_diredirect_login()
    {
        $response = $this->get(route('governance.approval.index'));
        $response->assertStatus(302);
    }

    public function test_request_json_mengembalikan_counter_saja()
    {
        $role = \App\Models\AuthRole::factory()->create(['nama_peran' => 'pcnu']);
        $user = AuthUser::factory()->create(['id_peran' => $role->id_peran, 'status_akun' => 'aktif']);
        $response = $this->actingAs($user)->getJson(route('governance.approval.index'));
        
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'total_pending',
            'paraf_count',
            'pleno_count',
            'surat_ttd_count'
        ]);
    }
}
