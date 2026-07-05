<?php

namespace Tests\Feature\Operasi;

use App\Models\AuthRole;
use App\Models\AuthUser;
use App\Models\OperasiInsiden;
use App\Models\OperasiPenugasan;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class BulkSyncTest extends TestCase
{
    use DatabaseTransactions;

    private function createAuthUserWithRole(string $roleName): AuthUser
    {
        $role = AuthRole::firstOrCreate(['nama_peran' => $roleName], ['deskripsi' => 'Role', 'level_otoritas' => 1]);
        return AuthUser::factory()->aktif()->create([
            'id_peran' => $role->id_peran
        ]);
    }

    public function test_penugasan_bulk_processing_with_partial_success()
    {
        $user = $this->createAuthUserWithRole('super_admin');
        $insiden = OperasiInsiden::factory()->create(['status_insiden' => 'respon'])->refresh();
        
        $relawan1 = $this->createAuthUserWithRole('relawan');
        $relawan2 = $this->createAuthUserWithRole('relawan');

        // Create a duplicate condition beforehand
        OperasiPenugasan::create([
            'id_insiden' => $insiden->id_insiden,
            'id_pengguna' => $relawan1->id_pengguna,
            'peran_otoritas' => 'trc',
            'status_penugasan' => 'aktif',
            'waktu_mulai' => now(),
            'ditugaskan_oleh' => $user->id_pengguna,
        ]);

        // Payload: item 1 is duplicate (fails), item 2 is new (succeeds)
        $payload = [
            'items' => [
                [
                    'uuid_insiden' => $insiden->uuid_insiden,
                    'id_pengguna' => $relawan1->id_pengguna,
                    'peran_otoritas' => 'medis',
                    'catatan' => 'Item duplikat'
                ],
                [
                    'uuid_insiden' => $insiden->uuid_insiden,
                    'id_pengguna' => $relawan2->id_pengguna,
                    'peran_otoritas' => 'logistik',
                    'catatan' => 'Item baru'
                ]
            ]
        ];

        $response = $this->actingAs($user)
            ->postJson(route('api.v1.penugasan.bulk'), $payload);

        $response->assertStatus(207) // Multi-Status
            ->assertJsonPath('success', false)
            ->assertJsonPath('data.processed', 2)
            ->assertJsonPath('data.success_count', 1)
            ->assertJsonPath('data.failed_count', 1)
            ->assertJsonCount(1, 'data.failures')
            ->assertJsonCount(1, 'data.successes');
    }

    public function test_logistik_and_mobilisasi_bulk_stubs()
    {
        $user = $this->createAuthUserWithRole('super_admin');

        $responseLogistik = $this->actingAs($user)
            ->postJson(route('api.v1.logistik.bulk'), ['items' => [['barang' => 'beras']]]);

        $responseLogistik->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.success_count', 1);

        $responseMobilisasi = $this->actingAs($user)
            ->postJson(route('api.v1.mobilisasi.bulk'), ['items' => [['personel' => 'budi']]]);

        $responseMobilisasi->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.success_count', 1);
    }
}
