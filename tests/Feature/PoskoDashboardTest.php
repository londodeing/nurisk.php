<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\AuthUser;
use App\Models\OperasiPosaju;
use App\Models\OperasiPenugasan;
use App\Models\OperasiSitrep;
use App\Models\LogistikStok;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PoskoDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Setup initial user
        $this->user = AuthUser::factory()->aktif()->create(['id_peran' => \App\Models\AuthRole::factory()->create(['nama_peran' => 'pcnu'])->id_peran, 'default_scope_type' => 'pcnu', 'default_scope_id' => 1]);
    }

    public function test_dashboard_posko_can_be_rendered()
    {
        $response = $this->actingAs($this->user)->get(route('dashboard.posko'));
        $response->assertStatus(200);
        $response->assertSee('POSKO Dashboard');
        $response->assertSee('Decision Queue');
        $response->assertSee('Posko Aktif');
    }

    public function test_dashboard_polling_endpoint_returns_json_with_metrics()
    {
        // Seed some data
        $insiden = \App\Models\OperasiInsiden::factory()->create();
        $posaju = OperasiPosaju::create(['id_insiden' => $insiden->id_insiden, 'nama_posaju' => 'Posko A', 'alamat_lokasi' => 'Jl. Test', 'pj_posaju' => $this->user->id_pengguna, 'status_alur' => 'aktif']);
        LogistikStok::create(['id_posaju' => $posaju->id_posaju, 'id_gudang' => 1, 'id_katalog' => 1, 'jumlah_tersedia' => 10, 'satuan' => 'kg']); // Kritis

        $response = $this->actingAs($this->user)->getJson(route('dashboard.posko.polling'));
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'timestamp',
            'metrics' => ['posko_aktif', 'relawan_aktif', 'tugas_terbuka', 'sitrep_hari_ini', 'kebutuhan_mendesak'],
            'decision_queue',
            'feed',
            'debug' => ['response_time_ms']
        ]);

        $data = $response->json();
        $this->assertEquals(1, $data['metrics']['posko_aktif']);
        $this->assertEquals(1, $data['metrics']['kebutuhan_mendesak']);
    }

    public function test_decision_queue_limits_to_5_items()
    {
        // Decision Queue service will always return max 5
        $response = $this->actingAs($this->user)->getJson(route('dashboard.posko.polling'));
        $this->assertLessThanOrEqual(5, count($response->json('decision_queue')));
    }

}
