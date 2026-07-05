<?php

namespace Tests\Feature\Operasi;

use Tests\TestCase;
use Tests\Support\CreatesOperasiSchema;
use App\Models\OperasiPosaju;
use App\Models\OperasiInsiden;
use App\Models\AuthUser;
use App\Models\AuthRole;
use App\Services\Auth\AuthorizationContextService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Gate;

class OperasiPosajuPolicyTest extends TestCase
{
    use DatabaseTransactions, CreatesOperasiSchema;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createOperasiSchema();
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

    public function test_super_admin_can_create()
    {
        $user = $this->mockUser('super_admin', 'pwnu', 0);
        $this->actingAs($user);
        $this->assertTrue($user->can('create', OperasiPosaju::class));
    }

    public function test_pwnu_and_pcnu_can_create()
    {
        $pwnu = $this->mockUser('pwnu', 'pwnu', 1);
        $pcnu = $this->mockUser('pcnu', 'pcnu', 1);

        $this->actingAs($pwnu);
        $this->assertTrue($pwnu->can('create', OperasiPosaju::class));
        $this->actingAs($pcnu);
        $this->assertTrue($pcnu->can('create', OperasiPosaju::class));
    }

    public function test_mwc_ranting_relawan_cannot_create()
    {
        $mwc = $this->mockUser('mwc', 'mwc', 1);
        $ranting = $this->mockUser('ranting', 'ranting', 1);
        $relawan = $this->mockUser('relawan', 'pcnu', 1);

        $this->actingAs($mwc);
        $this->assertFalse($mwc->can('create', OperasiPosaju::class));
        $this->actingAs($ranting);
        $this->assertFalse($ranting->can('create', OperasiPosaju::class));
        $this->actingAs($relawan);
        $this->assertFalse($relawan->can('create', OperasiPosaju::class));
    }

    public function test_pcnu_cannot_update_out_of_scope_posaju()
    {
        $userPcnu1 = $this->mockUser('pcnu', 'pcnu', 1);
        $insidenPcnu2 = $this->createInsiden(2);
        $posaju = OperasiPosaju::create(['alamat_lokasi' => 'Alamat Default', 'id_insiden' => $insidenPcnu2->id_insiden, 'pj_posaju' => $userPcnu1->id_pengguna, 'status_alur' => 'aktif', 'nama_posaju' => 'Pos Aju Test']);

        $this->actingAs($userPcnu1);
        $this->assertFalse($userPcnu1->can('update', $posaju));
    }

    public function test_pcnu_can_update_in_scope_posaju()
    {
        $userPcnu1 = $this->mockUser('pcnu', 'pcnu', 1);
        $insidenPcnu1 = $this->createInsiden(1);
        $posaju = OperasiPosaju::create(['alamat_lokasi' => 'Alamat Default', 'id_insiden' => $insidenPcnu1->id_insiden, 'pj_posaju' => $userPcnu1->id_pengguna, 'status_alur' => 'aktif', 'nama_posaju' => 'Pos Aju Test']);

        $this->actingAs($userPcnu1);
        $this->assertTrue($userPcnu1->can('update', $posaju));
    }

    public function test_activate_only_when_direncanakan()
    {
        $user = $this->mockUser('pcnu', 'pcnu', 1);
        $insiden = $this->createInsiden(1);
        
        $posajuRencana = OperasiPosaju::create(['alamat_lokasi' => 'Alamat Default', 'id_insiden' => $insiden->id_insiden, 'pj_posaju' => $user->id_pengguna, 'status_alur' => 'direncanakan', 'nama_posaju' => 'Pos Aju Rencana']);
        $posajuAktif = OperasiPosaju::create(['alamat_lokasi' => 'Alamat Default', 'id_insiden' => $insiden->id_insiden, 'pj_posaju' => $user->id_pengguna, 'status_alur' => 'aktif', 'nama_posaju' => 'Pos Aju Aktif']);

        $this->actingAs($user);
        $this->assertTrue($user->can('activate', $posajuRencana));
        $this->assertFalse($user->can('activate', $posajuAktif)); // Tidak boleh karena sudah aktif
    }

    public function test_extend_only_when_aktif()
    {
        $user = $this->mockUser('super_admin');
        $insiden = $this->createInsiden(1);
        
        $posajuRencana = OperasiPosaju::create(['alamat_lokasi' => 'Alamat Default', 'id_insiden' => $insiden->id_insiden, 'pj_posaju' => $user->id_pengguna, 'status_alur' => 'direncanakan', 'nama_posaju' => 'Pos Aju Rencana']);
        $posajuAktif = OperasiPosaju::create(['alamat_lokasi' => 'Alamat Default', 'id_insiden' => $insiden->id_insiden, 'pj_posaju' => $user->id_pengguna, 'status_alur' => 'aktif', 'nama_posaju' => 'Pos Aju Aktif']);

        $this->actingAs($user);
        $this->assertFalse($user->can('extend', $posajuRencana));
        $this->assertTrue($user->can('extend', $posajuAktif));
    }

    public function test_close_only_when_aktif_or_diperpanjang()
    {
        $user = $this->mockUser('pcnu', 'pcnu', 1);
        $insiden = $this->createInsiden(1);
        
        $posajuRencana = OperasiPosaju::create(['alamat_lokasi' => 'Alamat Default', 'id_insiden' => $insiden->id_insiden, 'pj_posaju' => $user->id_pengguna, 'status_alur' => 'direncanakan', 'nama_posaju' => 'Pos Aju Rencana']);
        $posajuAktif = OperasiPosaju::create(['alamat_lokasi' => 'Alamat Default', 'id_insiden' => $insiden->id_insiden, 'pj_posaju' => $user->id_pengguna, 'status_alur' => 'aktif', 'nama_posaju' => 'Pos Aju Aktif']);
        $posajuDiperpanjang = OperasiPosaju::create(['alamat_lokasi' => 'Alamat Default', 'id_insiden' => $insiden->id_insiden, 'pj_posaju' => $user->id_pengguna, 'status_alur' => 'diperpanjang', 'nama_posaju' => 'Pos Aju Diperpanjang']);

        $this->actingAs($user);
        $this->assertFalse($user->can('close', $posajuRencana));
        $this->assertTrue($user->can('close', $posajuAktif));
        $this->assertTrue($user->can('close', $posajuDiperpanjang));
    }
}
