<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\AuthUser;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PwnuDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_pwnu_can_access_dashboard()
    {
        $user = AuthUser::factory()->aktif()->create(['id_peran' => \App\Models\AuthRole::factory()->create(['nama_peran' => 'pwnu'])->id_peran]);
        $response = $this->actingAs($user)->get(route('dashboard.pwnu'));
        $response->assertStatus(200);
        $response->assertSee('PWNU Executive Dashboard');
        $response->assertSee('Critical Areas');
    }

    public function test_pcnu_cannot_access_pwnu_dashboard()
    {
        $user = AuthUser::factory()->aktif()->create(['id_peran' => \App\Models\AuthRole::factory()->create(['nama_peran' => 'pcnu'])->id_peran]);
        $response = $this->actingAs($user)->get(route('dashboard.pwnu'));
        $response->assertStatus(403); // Forbidden based on role middleware
    }

    public function test_pwnu_polling_endpoint_returns_json_structure()
    {
        $user = AuthUser::factory()->aktif()->create(['id_peran' => \App\Models\AuthRole::factory()->create(['nama_peran' => 'pwnu'])->id_peran]);
        $response = $this->actingAs($user)->getJson(route('dashboard.pwnu.polling'));
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'timestamp',
            'kpi' => ['total_insiden', 'total_posko', 'total_relawan', 'cabang_terdampak'],
            'decision_queue',
            'critical_areas',
            'trends' => ['insiden_baru', 'sitrep_masuk', 'relawan_aktif'],
            'resources' => ['relawan', 'logistik', 'posko']
        ]);
    }
}
