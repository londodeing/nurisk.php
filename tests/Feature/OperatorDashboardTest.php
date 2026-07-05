<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\AuthUser;
use Illuminate\Foundation\Testing\RefreshDatabase;

class OperatorDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_operator_can_access_dashboard()
    {
        $user = AuthUser::factory()->aktif()->create(['id_peran' => \App\Models\AuthRole::factory()->create(['nama_peran' => 'posko'])->id_peran]); // Role operator posko
        $response = $this->actingAs($user)->get(route('dashboard.operator'));
        $response->assertStatus(200);
        $response->assertSee('Posko Data Entry Center');
        $response->assertSee('Pending Work Queue');
        $response->assertSee('Data Quality Queue');
        $response->assertSee('Submission Queue');
    }

    public function test_operator_cannot_access_commander_dashboard()
    {
        $user = AuthUser::factory()->aktif()->create(['id_peran' => \App\Models\AuthRole::factory()->create(['nama_peran' => 'posko'])->id_peran]);
        $response = $this->actingAs($user)->get(route('dashboard.posko_commander'));
        $response->assertStatus(403);
    }

    public function test_operator_cannot_access_pcnu_dashboard()
    {
        $user = AuthUser::factory()->aktif()->create(['id_peran' => \App\Models\AuthRole::factory()->create(['nama_peran' => 'posko'])->id_peran]);
        $response = $this->actingAs($user)->get(route('dashboard.pcnu'));
        $response->assertStatus(403);
    }

    public function test_operator_cannot_access_pwnu_dashboard()
    {
        $user = AuthUser::factory()->aktif()->create(['id_peran' => \App\Models\AuthRole::factory()->create(['nama_peran' => 'posko'])->id_peran]);
        $response = $this->actingAs($user)->get(route('dashboard.pwnu'));
        $response->assertStatus(403);
    }

    public function test_operator_polling_endpoint_is_protected_and_returns_json()
    {
        $user = AuthUser::factory()->aktif()->create(['id_peran' => \App\Models\AuthRole::factory()->create(['nama_peran' => 'posko'])->id_peran]);
        $response = $this->actingAs($user)->getJson(route('dashboard.operator.polling'));
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'timestamp',
            'shift' => ['operator_name', 'posko_name', 'shift', 'server_time', 'sync_status'],
            'pending_queue',
            'quality_queue',
            'submission_queue',
            'activity_feed'
        ]);
    }
}
