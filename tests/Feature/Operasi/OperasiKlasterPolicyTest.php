<?php

namespace Tests\Feature\Operasi;

use Tests\TestCase;
use Tests\Support\CreatesOperasiSchema;
use App\Models\OperasiKlaster;
use App\Models\OperasiInsiden;
use App\Models\OperasiTugas;
use App\Models\AuthUser;
use App\Models\AuthRole;
use App\Services\Auth\AuthorizationContextService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Gate;

class OperasiKlasterPolicyTest extends TestCase
{
    use DatabaseTransactions, CreatesOperasiSchema;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createOperasiSchema();
        $this->seed(\Database\Seeders\MasterKlasterSeeder::class);
    }

    private function mockUser(string $role, string $scopeType = 'pcnu', int $scopeId = 1): AuthUser
    {
        $roleModel = AuthRole::create(['nama_peran' => $role, 'level_otoritas' => 1]);
        $user = AuthUser::forceCreate([
            'no_hp' => '08' . rand(100000000, 999999999), 'kata_sandi' => 'hash', 'id_peran' => $roleModel->id_peran,
            'default_scope_type' => $scopeType,
            'default_scope_id' => $scopeId,
            'status_akun' => 'aktif',
        ]);
        return $user;
    }

    private function createInsiden(int $pcnuId): OperasiInsiden
    {
        \Illuminate\Support\Facades\DB::table('organisasi_unit')->insertOrIgnore(['id_unit' => $pcnuId, 'nama_unit' => 'Unit PCNU ' . $pcnuId, 'tipe_unit' => 'pcnu']);
        \Illuminate\Support\Facades\DB::table('organisasi_pcnu')->insertOrIgnore(['id_pcnu' => $pcnuId, 'id_unit' => $pcnuId, 'nama_pcnu' => 'PCNU ' . $pcnuId]);
        \Illuminate\Support\Facades\DB::table('bencana_master_jenis')->insertOrIgnore(['id_jenis' => 1, 'nama_bencana' => 'Banjir', 'slug' => 'banjir']);
        
        return OperasiInsiden::forceCreate([
            'id_pcnu' => $pcnuId,
            'kode_kejadian' => 'INS-' . uniqid(),
            'id_jenis_bencana' => 1,
            'waktu_mulai' => now()
        ]);
    }

    public function test_mwc_ranting_cannot_update_progress()
    {
        $mwc = $this->mockUser('mwc', 'mwc', 1);
        $insiden = $this->createInsiden(1);
        $klaster = OperasiKlaster::create(['id_insiden' => $insiden->id_insiden, 'id_master_klaster' => 1, 'status_klaster' => 'aktif']);

        $this->actingAs($mwc);
        $this->assertFalse($mwc->can('updateProgress', $klaster));
    }

    public function test_pcnu_cannot_complete_out_of_scope()
    {
        $pcnu1 = $this->mockUser('pcnu', 'pcnu', 1);
        $insidenPcnu2 = $this->createInsiden(2);
        $klaster = OperasiKlaster::create(['id_insiden' => $insidenPcnu2->id_insiden, 'id_master_klaster' => 1, 'status_klaster' => 'aktif']);

        $this->actingAs($pcnu1);
        $this->assertFalse($pcnu1->can('complete', $klaster));
    }

    public function test_pcnu_can_complete_if_no_active_tasks()
    {
        $pcnu1 = $this->mockUser('pcnu', 'pcnu', 1);
        $insidenPcnu1 = $this->createInsiden(1);
        $klaster = OperasiKlaster::create(['id_insiden' => $insidenPcnu1->id_insiden, 'id_master_klaster' => 1, 'status_klaster' => 'aktif']);

        $this->actingAs($pcnu1);
        $this->assertTrue($pcnu1->can('complete', $klaster));
    }

    public function test_cannot_complete_if_has_active_tasks()
    {
        $superAdmin = $this->mockUser('super_admin');
        $insiden = $this->createInsiden(1);
        $klaster = OperasiKlaster::create(['id_insiden' => $insiden->id_insiden, 'id_master_klaster' => 1, 'status_klaster' => 'aktif']);

        // Tambah task aktif
        OperasiTugas::create([
            'id_operasi_klaster' => $klaster->id_klaster_operasi,
            'judul_tugas' => 'Bantu Logistik',
            'status_tugas' => 'berjalan'
        ]);

        $this->actingAs($superAdmin);
        $this->assertFalse($superAdmin->can('complete', $klaster));
    }
}
