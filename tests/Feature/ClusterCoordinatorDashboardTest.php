<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\AuthUser;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ClusterCoordinatorDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_cluster_coordinator_can_access_dashboard()
    {
        $role = \App\Models\AuthRole::firstOrCreate(['id_peran' => 1], ['nama_peran' => 'super_admin', 'level_otoritas' => 1]);
        $user = AuthUser::factory()->aktif()->create(['id_peran' => $role->id_peran]);
        $response = $this->actingAs($user)->get(route('dashboard.cluster_coordinator'));
        $response->assertStatus(200);
        $response->assertSee('Gap Management Center');
        $response->assertSee('Gap Analysis Matrix');
        $response->assertSee('Resource Redistribution');
    }

    public function test_operator_cannot_access_cluster_dashboard()
    {
        $role = \App\Models\AuthRole::firstOrCreate(['id_peran' => 5], ['nama_peran' => 'publik', 'level_otoritas' => 99]);
        $user = AuthUser::factory()->aktif()->create(['id_peran' => $role->id_peran]);
        $response = $this->actingAs($user)->get(route('dashboard.cluster_coordinator'));
        $response->assertStatus(403);
    }

    public function test_cluster_polling_endpoint_returns_json()
    {
        $role = \App\Models\AuthRole::firstOrCreate(['id_peran' => 1], ['nama_peran' => 'super_admin', 'level_otoritas' => 1]);
        $user = AuthUser::factory()->aktif()->create(['id_peran' => $role->id_peran]);
        $response = $this->actingAs($user)->getJson(route('dashboard.cluster_coordinator.polling'));
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'timestamp',
            'stats' => ['total_kebutuhan', 'posko_butuh_bantuan', 'area_unserved', 'permintaan_menunggu'],
            'decision_queue',
            'gap_matrix',
            'unserved_area',
            'redistribution',
            'escalations'
        ]);
    }
}
