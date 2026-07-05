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

class OperasiTugasPolicyTest extends TestCase
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

    private function createTugas(int $pcnuId, string $status): OperasiTugas
    {
        $insiden = $this->createInsiden($pcnuId);
        $klaster = OperasiKlaster::create(['id_insiden' => $insiden->id_insiden, 'id_master_klaster' => 1, 'status_klaster' => 'aktif']);
        return OperasiTugas::create([
            'id_operasi_klaster' => $klaster->id_klaster_operasi,
            'judul_tugas' => 'Tugas A',
            'status_tugas' => $status
        ]);
    }

    public function test_start_tugas_only_when_rencana_or_tertunda()
    {
        $user = $this->mockUser('pcnu', 'pcnu', 1);
        $tugasRencana = $this->createTugas(1, 'rencana');
        $tugasTertunda = $this->createTugas(1, 'tertunda');
        $tugasBerjalan = $this->createTugas(1, 'berjalan');

        $this->actingAs($user);
        $this->assertTrue($user->can('start', $tugasRencana));
        $this->assertTrue($user->can('start', $tugasTertunda));
        $this->assertFalse($user->can('start', $tugasBerjalan));
    }

    public function test_pause_tugas_only_when_berjalan()
    {
        $user = $this->mockUser('super_admin');
        $tugasRencana = $this->createTugas(1, 'rencana');
        $tugasBerjalan = $this->createTugas(1, 'berjalan');

        $this->actingAs($user);
        $this->assertFalse($user->can('pause', $tugasRencana));
        $this->assertTrue($user->can('pause', $tugasBerjalan));
    }

    public function test_complete_tugas_only_when_berjalan()
    {
        $user = $this->mockUser('pcnu', 'pcnu', 1);
        $tugasSelesai = $this->createTugas(1, 'selesai');
        $tugasBerjalan = $this->createTugas(1, 'berjalan');

        $this->actingAs($user);
        $this->assertFalse($user->can('complete', $tugasSelesai));
        $this->assertTrue($user->can('complete', $tugasBerjalan));
    }

    public function test_out_of_scope_denied()
    {
        $userPcnu1 = $this->mockUser('pcnu', 'pcnu', 1);
        $tugasPcnu2 = $this->createTugas(2, 'rencana');

        $this->actingAs($userPcnu1);
        $this->assertFalse($userPcnu1->can('start', $tugasPcnu2));
    }

    public function test_relawan_denied_from_changing_tugas()
    {
        $relawan = $this->mockUser('relawan', 'pcnu', 1);
        $tugas = $this->createTugas(1, 'rencana');

        $this->actingAs($relawan);
        $this->assertFalse($relawan->can('start', $tugas));
    }
}
