<?php

namespace Tests\Feature\CommandCenter;

use App\Models\AuthRole;
use App\Models\AuthUser;
use App\Models\AuthPenggunaProfil;
use App\Models\OrganisasiPcnu;
use App\Models\OrganisasiUnit;
use App\Models\OperasiInsiden;
use App\Models\OperasiPenugasan;
use App\Models\OperasiPleno;
use App\Models\OperasiPosaju;
use App\Models\OperasiSuratKeluar;
use App\Models\OperasiTugas;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DecisionQueueTest extends TestCase
{
    use RefreshDatabase;

    public function test_decision_queue_api_returns_empty_for_relawan_without_tasks(): void
    {
        $roleRelawan = AuthRole::factory()->create(['nama_peran' => 'relawan', 'level_otoritas' => 40]);
        $user = AuthUser::factory()->create([
            'id_peran' => $roleRelawan->id_peran,
            'status_akun' => 'aktif',
        ]);

        $response = $this->actingAs($user)->getJson('/dashboard/api/decision-queue');
        $response->assertStatus(200);
        $response->assertJsonStructure(['queue', 'timestamp']);
    }

    public function test_pwnu_sees_surat_in_decision_queue(): void
    {
        $rolePwnu = AuthRole::factory()->create(['nama_peran' => 'pwnu', 'level_otoritas' => 80]);
        $user = AuthUser::factory()->create([
            'id_peran' => $rolePwnu->id_peran,
            'default_scope_type' => 'pwnu',
            'status_akun' => 'aktif',
        ]);

        $surat = OperasiSuratKeluar::factory()->create([
            'status_surat' => 'siap_tanda_tangan',
        ]);

        $response = $this->actingAs($user)->getJson('/dashboard/api/decision-queue');
        $response->assertStatus(200);
    }

    public function test_pcnu_sees_sitrep_overdue_in_decision_queue(): void
    {
        $pcnuUnit = OrganisasiUnit::factory()->create(['tipe_unit' => 'pcnu']);
        $pcnu = OrganisasiPcnu::factory()->create(['id_unit' => $pcnuUnit->id_unit]);
        $rolePcnu = AuthRole::factory()->create(['nama_peran' => 'pcnu', 'level_otoritas' => 60]);
        $user = AuthUser::factory()->create([
            'id_peran' => $rolePcnu->id_peran,
            'default_scope_type' => 'pcnu',
            'default_scope_id' => $pcnu->id_pcnu,
            'status_akun' => 'aktif',
        ]);

        OperasiInsiden::factory()->create([
            'id_pcnu' => $pcnu->id_pcnu,
            'status_insiden' => 'respon',
        ]);

        $response = $this->actingAs($user)->getJson('/dashboard/api/decision-queue');
        $response->assertStatus(200);
    }

    public function test_relawan_sees_new_task_in_decision_queue(): void
    {
        $roleRelawan = AuthRole::factory()->create(['nama_peran' => 'relawan', 'level_otoritas' => 40]);
        $user = AuthUser::factory()->create([
            'id_peran' => $roleRelawan->id_peran,
            'status_akun' => 'aktif',
        ]);

        OperasiTugas::factory()->create([
            'ditugaskan_ke' => $user->id_pengguna,
            'status_tugas' => 'rencana',
            'judul_tugas' => 'Tugas Baru Untuk Saya',
        ]);

        $response = $this->actingAs($user)->getJson('/dashboard/api/decision-queue');
        $response->assertStatus(200);
        $response->assertJsonFragment(['judul' => 'Tugas baru: Tugas Baru Untuk Saya']);
    }
}
