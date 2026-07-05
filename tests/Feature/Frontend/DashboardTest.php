<?php

namespace Tests\Feature\Frontend;

use Tests\TestCase;
use App\Models\AuthUser;
use Illuminate\Foundation\Testing\RefreshDatabase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login()
    {
        $response = $this->get('/dashboard');
        $response->assertRedirect('/login');
    }

    public function test_pwnu_redirects_to_pwnu_dashboard()
    {
        $role = \App\Models\AuthRole::factory()->create(['nama_peran' => 'pwnu']);
        $user = AuthUser::factory()->create(['id_peran' => $role->id_peran, 'status_akun' => 'aktif']);
        
        $response = $this->actingAs($user)->get('/dashboard');
        $response->assertRedirect('/dashboard/pwnu');

        $dashboardResponse = $this->actingAs($user)->get('/dashboard/pwnu');
        $dashboardResponse->assertStatus(200);
        $dashboardResponse->assertSee('PWNU Executive Dashboard');
    }

    public function test_pcnu_redirects_to_pcnu_dashboard()
    {
        $role = \App\Models\AuthRole::factory()->create(['nama_peran' => 'pcnu']);
        $user = AuthUser::factory()->create(['id_peran' => $role->id_peran, 'status_akun' => 'aktif']);
        
        // Debugging removed
        
        $response = $this->actingAs($user)->get('/dashboard');
        $response->assertRedirect('/dashboard/pcnu');

        $dashboardResponse = $this->actingAs($user)->get('/dashboard/pcnu');
        $dashboardResponse->assertStatus(200);
        $dashboardResponse->assertSee('PCNU Dashboard');
    }

    public function test_relawan_redirects_to_relawan_dashboard()
    {
        $role = \App\Models\AuthRole::factory()->create(['nama_peran' => 'relawan']);
        $user = AuthUser::factory()->create(['id_peran' => $role->id_peran, 'status_akun' => 'aktif']);
        
        $response = $this->actingAs($user)->get('/dashboard');
        $response->assertRedirect('/dashboard/relawan');

        $dashboardResponse = $this->actingAs($user)->get('/dashboard/relawan');
        $dashboardResponse->assertStatus(200);
        $dashboardResponse->assertSee('Relawan Home');
    }

    public function test_command_center_access()
    {
        // Asumsi command center public atau butuh auth
        $role = \App\Models\AuthRole::factory()->create(['nama_peran' => 'pcnu']);
        $user = AuthUser::factory()->create(['id_peran' => $role->id_peran, 'status_akun' => 'aktif']);
        
        $response = $this->actingAs($user)->get('/command-center');
        // Command center existing route is /command-center
        $response->assertStatus(200);
    }
}
