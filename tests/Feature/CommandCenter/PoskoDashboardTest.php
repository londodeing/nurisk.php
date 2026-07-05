<?php

namespace Tests\Feature\CommandCenter;

use App\Models\AuthRole;
use App\Models\AuthUser;
use App\Models\AuthPenggunaProfil;
use App\Models\OrganisasiPcnu;
use App\Models\OrganisasiUnit;
use App\Models\OperasiInsiden;
use App\Models\OperasiPenugasan;
use App\Models\OperasiPosaju;
use App\Models\OperasiTugas;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PoskoDashboardTest extends TestCase
{
    use RefreshDatabase;

    private AuthUser $pj;
    private OperasiPosaju $posko;
    private OperasiInsiden $insiden;
    private OperasiPenugasan $penugasan;
    private OperasiTugas $tugas;

    protected function setUp(): void
    {
        parent::setUp();

        $pcnuUnit = OrganisasiUnit::factory()->create(['tipe_unit' => 'pcnu']);
        $pcnu = OrganisasiPcnu::factory()->create(['id_unit' => $pcnuUnit->id_unit]);

        $rolePcnu = AuthRole::factory()->create(['nama_peran' => 'pcnu', 'level_otoritas' => 60]);

        $this->pj = AuthUser::factory()->create([
            'id_peran' => $rolePcnu->id_peran,
            'default_scope_type' => 'pcnu',
            'default_scope_id' => $pcnu->id_pcnu,
            'status_akun' => 'aktif',
        ]);
        AuthPenggunaProfil::factory()->create([
            'id_pengguna' => $this->pj->id_pengguna,
            'nama_lengkap' => 'PJ Posko Test',
        ]);

        $this->insiden = OperasiInsiden::factory()->create([
            'id_pcnu' => $pcnu->id_pcnu,
            'status_insiden' => 'respon',
        ]);

        $this->posko = OperasiPosaju::factory()->create([
            'id_insiden' => $this->insiden->id_insiden,
            'nama_posaju' => 'Posko Test',
            'pj_posaju' => $this->pj->id_pengguna,
        ]);

        $this->penugasan = OperasiPenugasan::factory()->create([
            'id_insiden' => $this->insiden->id_insiden,
            'id_pengguna' => $this->pj->id_pengguna,
            'status_penugasan' => 'aktif',
            'waktu_checkin' => now(),
        ]);

        $this->tugas = OperasiTugas::factory()->create([
            'id_posaju' => $this->posko->id_posaju,
            'ditugaskan_ke' => $this->pj->id_pengguna,
            'judul_tugas' => 'Tugas Test',
            'status_tugas' => 'berjalan',
            'progres_persen' => 50,
        ]);
    }

    public function test_pj_can_view_posko_dashboard(): void
    {
        $response = $this->actingAs($this->pj)->get('/dashboard/posko');

        $response->assertStatus(200);
        $response->assertSee('Posko Test');
        $response->assertSee('POSKO Dashboard');
    }

    // public function test_pj_sees_posko_data_scoped(): void
    // {
    //     $response = $this->actingAs($this->pj)->get('/dashboard/posko');
    //
    //     $response->assertStatus(200);
    //     $response->assertSee('Tugas Test');
    // }

    public function test_summary_api_returns_correct_counts(): void
    {
        $response = $this->actingAs($this->pj)->getJson('/dashboard/api/posko/summary');

        $response->assertStatus(200);
        $response->assertJsonStructure(['personel', 'tugas_aktif', 'kebutuhan', 'timestamp']);
        $this->assertGreaterThanOrEqual(1, $response->json('personel'));
        $this->assertGreaterThanOrEqual(1, $response->json('tugas_aktif'));
    }

    public function test_tugas_api_returns_assigned_tasks(): void
    {
        $response = $this->actingAs($this->pj)->getJson('/dashboard/api/posko/tugas');

        $response->assertStatus(200);
        $response->assertJsonStructure(['tugas', 'timestamp']);
        $this->assertCount(1, $response->json('tugas'));
        $this->assertEquals('Tugas Test', $response->json('tugas.0.judul'));
    }

    public function test_personel_api_returns_checked_in_staff(): void
    {
        $response = $this->actingAs($this->pj)->getJson('/dashboard/api/posko/personel');

        $response->assertStatus(200);
        $response->assertJsonStructure(['personel', 'timestamp']);
        $this->assertCount(1, $response->json('personel'));
        $this->assertTrue($response->json('personel.0.is_hadir'));
    }

    public function test_unauthorized_user_cannot_access_posko_dashboard(): void
    {
        $roleRelawan = AuthRole::factory()->create(['nama_peran' => 'relawan', 'level_otoritas' => 40]);
        $relawan = AuthUser::factory()->create(['id_peran' => $roleRelawan->id_peran, 'status_akun' => 'aktif']);

        $response = $this->actingAs($relawan)->get('/dashboard/posko');
        $response->assertStatus(403);
    }

    public function test_other_pcnu_cannot_see_this_posko(): void
    {
        $otherPcnu = OrganisasiPcnu::factory()->create();
        $rolePcnu = AuthRole::factory()->create(['nama_peran' => 'pcnu', 'level_otoritas' => 60]);
        $otherUser = AuthUser::factory()->create([
            'id_peran' => $rolePcnu->id_peran,
            'default_scope_type' => 'pcnu',
            'default_scope_id' => $otherPcnu->id_pcnu,
            'status_akun' => 'aktif',
        ]);

        $response = $this->actingAs($otherUser)->get('/dashboard/posko');
        $response->assertStatus(200);
        $response->assertDontSee('Posko Test');
    }
}
