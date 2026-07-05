<?php

namespace Tests\Feature;

use App\Models\AuthRole;
use App\Models\AuthUser;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class SyncMetricsTest extends TestCase
{
    use DatabaseTransactions;

    private function createSuperAdmin(): AuthUser
    {
        $role = AuthRole::firstOrCreate(['nama_peran' => 'super_admin'], ['deskripsi' => 'Super Admin', 'level_otoritas' => 99]);
        return AuthUser::factory()->aktif()->create(['id_peran' => $role->id_peran]);
    }

    public function test_sync_metrics_endpoint()
    {
        $user = $this->createSuperAdmin();
        $response = $this->actingAs($user)->getJson('/api/v1/sync/metrics');
        
        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'success',
                     'data' => [
                         'total_sync',
                         'success_rate',
                         'average_duration_ms',
                         'total_conflict',
                         'total_device'
                     ]
                 ]);
    }

    public function test_sync_status_endpoint()
    {
        $user = $this->createSuperAdmin();
        $response = $this->actingAs($user)->getJson('/api/v1/sync/status');
        
        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'success',
                     'data' => [
                         'server_time',
                         'latest_cursors',
                         'pending_tombstones',
                         'pending_changes'
                     ]
                 ]);
    }
}
