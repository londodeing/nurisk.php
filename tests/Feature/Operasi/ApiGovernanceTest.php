<?php

namespace Tests\Feature\Operasi;

use App\Models\AuthRole;
use App\Models\AuthUser;
use App\Models\OperasiInsiden;
use App\Models\OperasiPenugasan;
use App\Models\OperasiKlaster;
use App\Models\MasterKlaster;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ApiGovernanceTest extends TestCase
{
    use DatabaseTransactions;

    private function createAuthUserWithRole(string $roleName, ?int $scopeId = null): AuthUser
    {
        $role = AuthRole::firstOrCreate(['nama_peran' => $roleName], ['deskripsi' => 'Role', 'level_otoritas' => 1]);

        return AuthUser::factory()->aktif()->create([
            'id_peran' => $role->id_peran,
            'default_scope_id' => $scopeId
        ]);
    }

    public function test_global_json_envelope_and_pagination_format()
    {
        $admin = $this->createAuthUserWithRole('super_admin');
        $insiden = OperasiInsiden::factory()->create(['status_insiden' => 'respon'])->refresh();
        $relawan = $this->createAuthUserWithRole('relawan');

        // Create a penugasan
        OperasiPenugasan::create([
            'id_insiden' => $insiden->id_insiden,
            'id_pengguna' => $relawan->id_pengguna,
            'peran_otoritas' => 'trc',
            'status_penugasan' => 'aktif',
            'waktu_mulai' => now(),
            'ditugaskan_oleh' => $admin->id_pengguna,
        ]);

        $response = $this->actingAs($admin)
            ->getJson(route('api.v1.penugasan.index', ['uuid_insiden' => $insiden->uuid_insiden]));

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data',
                'meta' => [
                    'page',
                    'per_page',
                    'total'
                ]
            ])
            ->assertJsonPath('success', true)
            ->assertJsonCount(1, 'data');
    }

    public function test_standardized_error_validation_422()
    {
        $admin = $this->createAuthUserWithRole('super_admin');

        $response = $this->actingAs($admin)
            ->postJson(route('api.v1.penugasan.store'), []); // Empty payload

        $response->assertStatus(422)
            ->assertJsonStructure([
                'success',
                'message',
                'errors'
            ])
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Validasi gagal');
    }

    public function test_standardized_error_not_found_404()
    {
        $admin = $this->createAuthUserWithRole('super_admin');

        $response = $this->actingAs($admin)
            ->getJson('/api/v1/penugasan/non-existent-uuid');

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Resource tidak ditemukan'
            ]);
    }

    public function test_standardized_error_forbidden_403()
    {
        $relawan = $this->createAuthUserWithRole('relawan');
        $insiden = OperasiInsiden::factory()->create(['status_insiden' => 'respon'])->refresh();
        $relawanTarget = $this->createAuthUserWithRole('relawan');

        $payload = [
            'uuid_insiden' => $insiden->uuid_insiden,
            'id_pengguna' => $relawanTarget->id_pengguna,
            'peran_otoritas' => 'trc',
        ];

        // Normal relawan cannot assign roles
        $response = $this->actingAs($relawan)
            ->postJson(route('api.v1.penugasan.store'), $payload);

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'Forbidden'
            ]);
    }

    public function test_standardized_error_unauthenticated_401()
    {
        $insiden = OperasiInsiden::factory()->create(['status_insiden' => 'respon'])->refresh();

        // Unauthenticated access — auth middleware now returns 401
        $response = $this->getJson(route('api.v1.penugasan.index', ['uuid_insiden' => $insiden->uuid_insiden]));

        $response->assertStatus(401);
    }

    public function test_incremental_sync_updated_since()
    {
        $admin = $this->createAuthUserWithRole('super_admin');
        $insiden = OperasiInsiden::factory()->create(['status_insiden' => 'respon'])->refresh();
        $relawan = $this->createAuthUserWithRole('relawan');

        // Create two penugasan
        $p1 = OperasiPenugasan::create([
            'id_insiden' => $insiden->id_insiden,
            'id_pengguna' => $relawan->id_pengguna,
            'peran_otoritas' => 'trc',
            'status_penugasan' => 'aktif',
            'waktu_mulai' => now()->subHours(2),
            'ditugaskan_oleh' => $admin->id_pengguna,
        ]);
        $p1->diperbarui_pada = now()->subHours(2);
        $p1->save();

        $p2 = OperasiPenugasan::create([
            'id_insiden' => $insiden->id_insiden,
            'id_pengguna' => $relawan->id_pengguna,
            'peran_otoritas' => 'medis',
            'status_penugasan' => 'aktif',
            'waktu_mulai' => now(),
            'ditugaskan_oleh' => $admin->id_pengguna,
        ]);
        $p2->diperbarui_pada = now();
        $p2->save();

        // Get updated_since 1 hour ago
        $response = $this->actingAs($admin)
            ->getJson(route('api.v1.penugasan.index', [
                'uuid_insiden' => $insiden->uuid_insiden,
                'updated_since' => now()->subHour()->format('Y-m-d H:i:s')
            ]));

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.uuid_penugasan', $p2->uuid_penugasan);
    }

    public function test_filtering_and_sorting()
    {
        $admin = $this->createAuthUserWithRole('super_admin');
        $insiden = OperasiInsiden::factory()->create(['status_insiden' => 'respon'])->refresh();
        $relawan = $this->createAuthUserWithRole('relawan');

        // Create penugasan with different status
        $p1 = OperasiPenugasan::create([
            'id_insiden' => $insiden->id_insiden,
            'id_pengguna' => $relawan->id_pengguna,
            'peran_otoritas' => 'trc',
            'status_penugasan' => 'draft',
            'waktu_mulai' => now()->subHours(2),
            'ditugaskan_oleh' => $admin->id_pengguna,
        ]);

        $p2 = OperasiPenugasan::create([
            'id_insiden' => $insiden->id_insiden,
            'id_pengguna' => $relawan->id_pengguna,
            'peran_otoritas' => 'medis',
            'status_penugasan' => 'aktif',
            'waktu_mulai' => now()->subHour(),
            'ditugaskan_oleh' => $admin->id_pengguna,
        ]);

        // Filter status_penugasan=aktif
        $responseFilter = $this->actingAs($admin)
            ->getJson(route('api.v1.penugasan.index', [
                'uuid_insiden' => $insiden->uuid_insiden,
                'status_penugasan' => 'aktif'
            ]));

        $responseFilter->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.uuid_penugasan', $p2->uuid_penugasan);

        // Sorting asc
        $responseSortAsc = $this->actingAs($admin)
            ->getJson(route('api.v1.penugasan.index', [
                'uuid_insiden' => $insiden->uuid_insiden,
                'sort_by' => 'waktu_mulai',
                'sort_order' => 'asc'
            ]));

        $responseSortAsc->assertStatus(200)
            ->assertJsonPath('data.0.uuid_penugasan', $p1->uuid_penugasan)
            ->assertJsonPath('data.1.uuid_penugasan', $p2->uuid_penugasan);
    }
}
