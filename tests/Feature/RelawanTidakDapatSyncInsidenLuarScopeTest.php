<?php

namespace Tests\Feature;

use App\Models\AuthRole;
use App\Models\AuthUser;
use App\Models\OperasiInsiden;
use App\Models\OperasiPenugasan;
use App\Models\OrganisasiPcnu;
use App\Models\OrganisasiUnit;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Illuminate\Support\Str;

class RelawanTidakDapatSyncInsidenLuarScopeTest extends TestCase
{
    use DatabaseTransactions;

    private function createRelawanWithScope(int $pcnuId): AuthUser
    {
        $role = AuthRole::firstOrCreate(['nama_peran' => 'relawan'], ['deskripsi' => 'Relawan', 'level_otoritas' => 1]);
        $user = AuthUser::factory()->aktif()->create(['id_peran' => $role->id_peran]);
        
        $user->default_scope_type = 'pcnu';
        $user->default_scope_id = $pcnuId;
        $user->save();
        
        return $user;
    }

    public function test_relawan_tidak_dapat_sync_insiden_luar_scope()
    {
        // 1. Buat dua PCNU yang berbeda
        $unit = OrganisasiUnit::create(['nama_unit' => 'Unit Test', 'parent_id' => null, 'tipe_unit' => 'pcnu']);
        $pcnu1 = OrganisasiPcnu::create(['id_unit' => $unit->id_unit, 'nama_pcnu' => 'PCNU 1']);
        $pcnu2 = OrganisasiPcnu::create(['id_unit' => $unit->id_unit, 'nama_pcnu' => 'PCNU 2']);

        // 2. Buat relawan yang hanya memiliki akses ke PCNU 1
        $relawan = $this->createRelawanWithScope($pcnu1->id_pcnu);

        // 3. Buat insiden di PCNU 1 (dalam scope)
        $insiden1 = OperasiInsiden::factory()->create(['id_pcnu' => $pcnu1->id_pcnu]);

        // 4. Buat insiden di PCNU 2 (LUAR scope)
        $insiden2 = OperasiInsiden::factory()->create(['id_pcnu' => $pcnu2->id_pcnu]);

        // 5. Client mencoba sync insiden di PCNU 2 (harus ditolak)
        $uuidPenugasan = (string) Str::uuid();
        $payload = [
            'request_id' => (string) Str::uuid(),
            'device_uuid' => 'client-device-001',
            'cursors' => ['penugasan' => 0],
            'changes' => [
                [
                    'table' => 'operasi_penugasan',
                    'action' => 'upsert',
                    'data' => [
                        'uuid_penugasan' => $uuidPenugasan,
                        'uuid_insiden' => $insiden2->uuid_insiden, // insiden di PCNU 2 (LUAR scope)
                        'id_pengguna' => $relawan->id_pengguna,
                        'peran_otoritas' => 'trc',
                        'status_penugasan' => 'aktif',
                        'waktu_mulai' => now()->format('Y-m-d H:i:s'),
                        'ditugaskan_oleh' => $relawan->id_pengguna,
                        'sync_version' => 1
                    ]
                ]
            ]
        ];

        // 6. Sync harus ditolak dengan HTTP 403
        $response = $this->actingAs($relawan)->postJson('/api/v1/sync', $payload);
        $response->assertStatus(403);
        $response->assertJson([
            'success' => false,
            'message' => 'Forbidden: you do not have access to this incident',
        ]);

        // 7. Verifikasi tidak ada data yang ditulis
        $this->assertDatabaseMissing('operasi_penugasan', ['uuid_penugasan' => $uuidPenugasan]);
        
        // 8. Verifikasi tidak ada konflik yang dibuat
        $this->assertDatabaseCount('sync_conflicts', 0);

        // 9. Verifikasi insiden di PCNU 1 masih ada (tidak terpengaruh)
        $this->assertDatabaseHas('operasi_insiden', [
            'id_insiden' => $insiden1->id_insiden,
        ]);
    }

    public function test_relawan_tidak_dapat_sync_insiden_luar_scope_with_id_pcnu()
    {
        // 1. Buat dua PCNU yang berbeda
        $unit = OrganisasiUnit::create(['nama_unit' => 'Unit Test', 'parent_id' => null, 'tipe_unit' => 'pcnu']);
        $pcnu1 = OrganisasiPcnu::create(['id_unit' => $unit->id_unit, 'nama_pcnu' => 'PCNU 1']);
        $pcnu2 = OrganisasiPcnu::create(['id_unit' => $unit->id_unit, 'nama_pcnu' => 'PCNU 2']);

        // 2. Buat relawan yang hanya memiliki akses ke PCNU 1
        $relawan = $this->createRelawanWithScope($pcnu1->id_pcnu);

        // 3. Client mencoba sync dengan id_pcnu yang langsung diset (harus ditolak)
        $uuidPenugasan = (string) Str::uuid();
        $payload = [
            'request_id' => (string) Str::uuid(),
            'device_uuid' => 'client-device-001',
            'cursors' => ['penugasan' => 0],
            'changes' => [
                [
                    'table' => 'operasi_penugasan',
                    'action' => 'upsert',
                    'data' => [
                        'uuid_penugasan' => $uuidPenugasan,
                        'id_insiden' => null, // tidak ada insiden
                        'id_pcnu' => $pcnu2->id_pcnu, // PCNU 2 (LUAR scope)
                        'id_pengguna' => $relawan->id_pengguna,
                        'peran_otoritas' => 'trc',
                        'status_penugasan' => 'aktif',
                        'waktu_mulai' => now()->format('Y-m-d H:i:s'),
                        'ditugaskan_oleh' => $relawan->id_pengguna,
                        'sync_version' => 1
                    ]
                ]
            ]
        ];

        // 4. Sync harus ditolak dengan HTTP 403
        $response = $this->actingAs($relawan)->postJson('/api/v1/sync', $payload);
        $response->assertStatus(403);
        $response->assertJson([
            'success' => false,
            'message' => 'Forbidden: you do not have access to this PCNU',
        ]);

        // 5. Verifikasi tidak ada data yang ditulis
        $this->assertDatabaseMissing('operasi_penugasan', ['uuid_penugasan' => $uuidPenugasan]);
    }
}