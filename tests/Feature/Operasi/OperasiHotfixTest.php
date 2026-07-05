<?php

namespace Tests\Feature\Operasi;

use Tests\TestCase;
use Tests\Support\CreatesOperasiSchema;
use Tests\Support\CreatesRelawanSchema;
use App\Models\OperasiPosaju;
use App\Models\OperasiKlaster;
use App\Models\OperasiInsiden;
use App\Models\AuthUser;
use App\Models\AuthRole;
use App\Services\Auth\AuthorizationContextService;
use App\Services\Operasi\OperasiPosajuService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class OperasiHotfixTest extends TestCase
{
    use DatabaseTransactions, CreatesOperasiSchema, CreatesRelawanSchema;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createOperasiSchema();
        $this->createRelawanSchema();
    }

    private function mockUser(string $role, string $scopeType = 'pcnu', int $scopeId = 1): AuthUser
    {
        $roleModel = AuthRole::create(['nama_peran' => $role, 'level_otoritas' => 1]);
        return AuthUser::forceCreate([
            'no_hp' => '08' . rand(100000000, 999999999), 'kata_sandi' => 'hash', 'id_peran' => $roleModel->id_peran,
            'default_scope_type' => $scopeType,
            'default_scope_id' => $scopeId,
            'status_akun' => 'aktif',
        ]);
    }

    private function createInsiden(int $pcnuId): OperasiInsiden
    {
        DB::table('organisasi_unit')->insertOrIgnore(['id_unit' => $pcnuId, 'parent_id' => 99, 'nama_unit' => 'PCNU ' . $pcnuId, 'tipe_unit' => 'pcnu']);
        DB::table('organisasi_unit')->insertOrIgnore(['id_unit' => 99, 'nama_unit' => 'PWNU Jatim', 'tipe_unit' => 'pwnu']);
        
        DB::table('organisasi_pcnu')->insertOrIgnore(['id_pcnu' => $pcnuId, 'id_unit' => $pcnuId, 'nama_pcnu' => 'PCNU ' . $pcnuId]);
        DB::table('bencana_master_jenis')->insertOrIgnore(['id_jenis' => 1, 'nama_bencana' => 'Banjir', 'slug' => 'banjir']);
        
        return OperasiInsiden::forceCreate([
            'id_pcnu' => $pcnuId,
            'kode_kejadian' => 'INS-' . uniqid(),
            'id_jenis_bencana' => 1,
            'waktu_mulai' => now()
        ]);
    }

    public function test_pwnu_boundary_authorization()
    {
        // PWNU dengan id_unit 99
        $userPwnu = $this->mockUser('pwnu', 'pwnu', 99);
        $userPwnuJabar = $this->mockUser('pwnu', 'pwnu', 88); // Beda id_unit

        $insiden = $this->createInsiden(1); // PCNU dengan id_unit 1, parent_id 99
        
        $posaju = OperasiPosaju::create(['alamat_lokasi' => 'Alamat Default', 'id_insiden' => $insiden->id_insiden, 
            'pj_posaju' => $userPwnu->id_pengguna, 
            'status_alur' => 'direncanakan',
            'nama_posaju' => 'Pos Aju Test'
        ]);

        $authContext = app(AuthorizationContextService::class);
        
        $this->actingAs($userPwnu);
        $this->assertTrue($authContext->canManageInsiden($userPwnu, $posaju->insiden));
        $this->assertTrue($userPwnu->can('update', $posaju));

        $this->actingAs($userPwnuJabar);
        $this->assertFalse($authContext->canManageInsiden($userPwnuJabar, $posaju->insiden));
        $this->assertFalse($userPwnuJabar->can('update', $posaju));
    }

    public function test_posaju_illegal_state_transition()
    {
        $user = $this->mockUser('super_admin');
        $insiden = $this->createInsiden(1);
        
        $posaju = OperasiPosaju::create(['alamat_lokasi' => 'Alamat Default', 'id_insiden' => $insiden->id_insiden, 
            'pj_posaju' => $user->id_pengguna, 
            'status_alur' => 'ditutup',
            'nama_posaju' => 'Pos Aju Test'
        ]);

        $service = app(OperasiPosajuService::class);

        // Tidak boleh mengaktifkan yang sudah ditutup
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Hanya posaju berstatus direncanakan yang bisa diaktifkan.");
        
        $service->activate($posaju);
    }

    public function test_relawan_assignment_capacity_lock()
    {
        \App\Models\AuthRole::insertOrIgnore([
            ['id_peran' => 4, 'nama_peran' => 'relawan', 'level_otoritas' => 4]
        ]);

        $user1 = \App\Models\AuthUser::create([
            'no_hp' => '08' . rand(100000000, 999999999), 'kata_sandi' => 'hash', 'id_pengguna' => 101,
            'id_peran' => 4,
            'no_hp' => '081299991111',
            'kata_sandi' => bcrypt('password'),
            'status_akun' => 'aktif',
        ]);
        $user2 = \App\Models\AuthUser::create([
            'no_hp' => '08' . rand(100000000, 999999999), 'kata_sandi' => 'hash', 'id_pengguna' => 102,
            'id_peran' => 4,
            'no_hp' => '081299992222',
            'kata_sandi' => bcrypt('password'),
            'status_akun' => 'aktif',
        ]);

        $insiden = $this->createInsiden(1);
        $posaju = \App\Models\OperasiPosaju::create(['alamat_lokasi' => 'Alamat Default', 'id_insiden' => $insiden->id_insiden,
            'pj_posaju' => $user1->id_pengguna,
            'nama_posaju' => 'Pos Aju Test',
            'status_alur' => 'aktif',
        ]);

        // Simulasi kita butuh 1 orang relawan
        $kebutuhan = \App\Models\RelawanKebutuhan::create([
            'id_insiden' => $insiden->id_insiden,
            'id_posaju' => $posaju->id_posaju,
            'jumlah_dibutuhkan' => 1,
            'status_rekrutmen' => 'dibuka',
        ]);

        $pendaftaran1 = \App\Models\RelawanPendaftaran::create([
            'id_pengguna' => $user1->id_pengguna,
            'id_relawan_kebutuhan' => $kebutuhan->id_relawan_kebutuhan,
            'status_pendaftaran' => 'diterima',
        ]);

        $pendaftaran2 = \App\Models\RelawanPendaftaran::create([
            'id_pengguna' => $user2->id_pengguna,
            'id_relawan_kebutuhan' => $kebutuhan->id_relawan_kebutuhan,
            'status_pendaftaran' => 'diterima',
        ]);

        $service = app(\App\Services\Relawan\RelawanService::class);

        // Tugaskan pendaftar 1
        $service->assignVolunteer($pendaftaran1->id_pendaftaran, $posaju->id_posaju);

        // Tugaskan pendaftar 2 harus gagal karena kuota sudah penuh
        $this->expectException(\Illuminate\Validation\ValidationException::class);
        $service->assignVolunteer($pendaftaran2->id_pendaftaran, $posaju->id_posaju);
    }
}
