<?php

namespace Tests\Feature\Operasi;

use Tests\TestCase;
use App\Models\OperasiInsiden;
use App\Models\AssessmentUtama;
use App\Models\AuthUser;
use App\Models\AuthRole;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class AssessmentLogicTest extends TestCase
{
    use DatabaseTransactions;

    private function createAuthUserWithRole($roleName)
    {
        $user = AuthUser::factory()->aktif()->create();
        $role = AuthRole::firstOrCreate(['nama_peran' => $roleName], ['deskripsi' => 'Role', 'level_otoritas' => 1]);
        $user->id_peran = $role->id_peran;
        $user->save();
        return $user;
    }

    public function test_assessment_default_values_and_non_decreasing_meninggal()
    {
        $insiden = OperasiInsiden::factory()->create(['status_insiden' => 'respon', 'no_spk_assesment' => 'SPK-TEST']);
        $user = $this->createAuthUserWithRole('super_admin');
        
        // First assessment: provide some data
        $payload1 = [
            'event_date' => now()->format('Y-m-d'),
            'event_time' => now()->format('H:i'),
            'jenis_laporan' => 'kaji_cepat',
            'alamat_spesifik' => 'Test Location',
            'dampak_manusia' => [
                'meninggal' => 5,
                'luka_berat' => 2,
            ],
            'dampak_rumah' => [
                'berat' => 10
            ]
        ];

        $response1 = $this->actingAs($user, 'sanctum')->postJson("/api/insiden/{$insiden->uuid_insiden}/assessment", $payload1);
        $response1->assertStatus(201);

        $assessment1 = AssessmentUtama::where('id_insiden', $insiden->id_insiden)->latest('id_assessment_utama')->first();
        $this->assertEquals(5, $assessment1->dampakManusiaV2->meninggal);
        $this->assertEquals(2, $assessment1->dampakManusiaV2->luka_berat);
        $this->assertEquals(0, $assessment1->dampakManusiaV2->luka_ringan); // default 0
        $this->assertEquals(10, $assessment1->dampakRumah->rusak_berat);
        $this->assertEquals(0, $assessment1->dampakRumah->rusak_ringan); // default 0

        // Second assessment: missing some fields, try to decrease meninggal
        $payload2 = [
            'event_date' => now()->format('Y-m-d'),
            'event_time' => now()->format('H:i'),
            'jenis_laporan' => 'kaji_cepat',
            'alamat_spesifik' => 'Test Location Updated',
            'dampak_manusia' => [
                'meninggal' => 2, // Should not decrease, should remain 5
                // luka_berat missing, should inherit 2
                'luka_ringan' => 3 // should update to 3
            ],
            // dampak_rumah completely missing, should inherit all from prev
        ];

        $response2 = $this->actingAs($user, 'sanctum')->postJson("/api/insiden/{$insiden->uuid_insiden}/assessment", $payload2);
        $response2->assertStatus(201);

        $assessment2 = AssessmentUtama::where('id_insiden', $insiden->id_insiden)->latest('id_assessment_utama')->first();
        $this->assertNotEquals($assessment1->id_assessment_utama, $assessment2->id_assessment_utama);
        
        $this->assertEquals(5, $assessment2->dampakManusiaV2->meninggal, 'Meninggal should not decrease');
        $this->assertEquals(2, $assessment2->dampakManusiaV2->luka_berat, 'Luka berat should inherit from previous');
        $this->assertEquals(3, $assessment2->dampakManusiaV2->luka_ringan, 'Luka ringan should be updated');
        
        $this->assertEquals(10, $assessment2->dampakRumah->rusak_berat, 'Rumah berat should inherit from previous');
        $this->assertEquals(0, $assessment2->dampakRumah->rusak_ringan, 'Rumah ringan should inherit from previous');
    }
}
