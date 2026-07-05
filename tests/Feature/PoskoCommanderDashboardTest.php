<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\AuthUser;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PoskoCommanderDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_posko_commander_can_access_dashboard()
    {
        // Asumsi ada role posko_commander
        $user = AuthUser::factory()->aktif()->create(['id_peran' => \App\Models\AuthRole::factory()->create(['nama_peran' => 'posko_commander'])->id_peran]);
        $response = $this->actingAs($user)->get(route('dashboard.posko_commander'));
        $response->assertStatus(200);
        $response->assertSee('Posko Commander Dashboard');
        $response->assertSee('Decision Queue');
        $response->assertSee('Escalation Center');
    }

    public function test_operator_posko_cannot_access_commander_dashboard()
    {
        // Asumsi operator posko biasa tidak punya peran posko_commander
        $user = AuthUser::factory()->aktif()->create(['id_peran' => \App\Models\AuthRole::factory()->create(['nama_peran' => 'posko'])->id_peran]);
        $response = $this->actingAs($user)->get(route('dashboard.posko_commander'));
        $response->assertStatus(403); // Forbidden
    }

    public function test_commander_polling_endpoint_returns_json_structure()
    {
        $user = AuthUser::factory()->aktif()->create(['id_peran' => \App\Models\AuthRole::factory()->create(['nama_peran' => 'posko_commander'])->id_peran]);
        $response = $this->actingAs($user)->getJson(route('dashboard.posko_commander.polling'));
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'timestamp',
            'kpi' => ['posko_aktif', 'personel_bertugas', 'logistik_kritis', 'eskalasi_aktif'],
            'alerts',
            'decision_queue',
            'resources' => ['personel', 'logistik', 'posko'],
            'escalations'
        ]);
    }
}
