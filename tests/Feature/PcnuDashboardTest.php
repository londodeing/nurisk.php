<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\AuthUser;
use App\Models\OperasiPosaju;
use App\Models\LogistikStok;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PcnuDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Setup initial user
        $this->user = AuthUser::factory()->aktif()->create(['id_peran' => \App\Models\AuthRole::factory()->create(['nama_peran' => 'pcnu'])->id_peran]);
    }

    public function test_dashboard_pcnu_can_be_rendered()
    {
        $response = $this->actingAs($this->user)->get(route('dashboard.pcnu'));
        $response->assertStatus(200);
        $response->assertSee('PCNU Mission Coordination Center');
        $response->assertSee('Tactical KPI');
        $response->assertSee('Posko Health Matrix');
    }

    public function test_dashboard_pcnu_polling_endpoint_returns_json_structure()
    {
        // Seed some data
        $insiden = \App\Models\OperasiInsiden::factory()->create();
        $posaju = OperasiPosaju::create(['id_insiden' => $insiden->id_insiden, 'nama_posaju' => 'Posko A', 'alamat_lokasi' => 'Jl. Test', 'pj_posaju' => $this->user->id_pengguna, 'status_alur' => 'aktif']);
        LogistikStok::create(['id_posaju' => $posaju->id_posaju, 'id_gudang' => 1, 'id_katalog' => 1, 'jumlah_tersedia' => 5, 'satuan' => 'kg']); // Kritis

        $response = $this->actingAs($this->user)->getJson(route('dashboard.pcnu.polling'));
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'timestamp',
            'kpi' => ['posko_aktif', 'posko_kritis', 'relawan_aktif', 'relawan_tersedia', 'stok_kritis', 'sitrep_terlambat'],
            'decision_queue',
            'health_matrix',
            'resource_distribution',
            'escalation_queue'
        ]);

        $data = $response->json();
        $this->assertEquals(1, $data['kpi']['posko_aktif']);
        $this->assertEquals(1, $data['kpi']['stok_kritis']);
    }

    public function test_decision_queue_limits_to_5_items()
    {
        $response = $this->actingAs($this->user)->getJson(route('dashboard.pcnu.polling'));
        $this->assertLessThanOrEqual(5, count($response->json('decision_queue')));
    }

    public function test_quick_actions_are_visible()
    {
        $response = $this->actingAs($this->user)->get(route('dashboard.pcnu'));
        $response->assertSee('Penugasan');
        $response->assertSee('Kirim Relawan');
        $response->assertSee('Logistik');
        $response->assertSee('Eskalasi');
        $response->assertSee('Broadcast');
    }
}
