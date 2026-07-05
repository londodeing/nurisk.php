<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\AuthUser;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TrcDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_trc_can_access_dashboard()
    {
        $user = AuthUser::factory()->aktif()->create(['id_peran' => \App\Models\AuthRole::factory()->create(['nama_peran' => 'trc'])->id_peran]);
        $response = $this->actingAs($user)->get(route('dashboard.trc'));
        $response->assertStatus(200);
        $response->assertSee('TRC: ACTIVE');
        $response->assertSee('TUGAS ASESMEN SAAT INI');
        $response->assertSee('Quick Actions'); // Walau tidak persis stringnya tapi terlihat dari tombol
    }

    public function test_relawan_biasa_cannot_access_trc_dashboard()
    {
        $user = AuthUser::factory()->aktif()->create(['id_peran' => \App\Models\AuthRole::factory()->create(['nama_peran' => 'relawan'])->id_peran]);
        $response = $this->actingAs($user)->get(route('dashboard.trc'));
        $response->assertStatus(403); // Forbidden
    }

    public function test_trc_polling_endpoint_returns_json_structure()
    {
        $user = AuthUser::factory()->aktif()->create(['id_peran' => \App\Models\AuthRole::factory()->create(['nama_peran' => 'trc'])->id_peran]);
        $response = $this->actingAs($user)->getJson(route('dashboard.trc.polling'));
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'timestamp',
            'assignment' => ['id', 'title', 'distance', 'priority', 'status'],
            'incident' => ['level', 'korban_terdampak', 'cuaca'],
            'decision_queue',
            'contacts'
        ]);
    }

    public function test_decision_queue_does_not_exceed_5_items()
    {
        $user = AuthUser::factory()->aktif()->create(['id_peran' => \App\Models\AuthRole::factory()->create(['nama_peran' => 'trc'])->id_peran]);
        $response = $this->actingAs($user)->getJson(route('dashboard.trc.polling'));
        $this->assertLessThanOrEqual(5, count($response->json('decision_queue')));
    }
}
