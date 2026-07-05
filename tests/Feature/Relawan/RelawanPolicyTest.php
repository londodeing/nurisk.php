<?php

namespace Tests\Feature\Relawan;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Schema;
use Tests\Support\CreatesRelawanSchema;
use App\Models\AuthUser;
use App\Models\AuthRole;
use App\Models\RelawanKebutuhan;
use App\Models\RelawanPendaftaran;
use App\Models\AuthPenggunaProfil;
use App\Models\OperasiInsiden;
use App\Models\OrganisasiPcnu;
use App\Models\OrganisasiUnit;

class RelawanPolicyTest extends TestCase
{
    use DatabaseTransactions;
    use CreatesRelawanSchema;

    protected function setUp(): void
    {
        parent::setUp();

        Schema::disableForeignKeyConstraints();








        $this->createRelawanSchema();

        AuthRole::insertOrIgnore([
            ['id_peran' => 1, 'nama_peran' => 'super_admin', 'level_otoritas' => 1],
            ['id_peran' => 2, 'nama_peran' => 'pwnu', 'level_otoritas' => 2],
            ['id_peran' => 3, 'nama_peran' => 'pcnu', 'level_otoritas' => 3],
            ['id_peran' => 4, 'nama_peran' => 'relawan', 'level_otoritas' => 4],
            ['id_peran' => 6, 'nama_peran' => 'mwc', 'level_otoritas' => 5],
        ]);
    }

    private function createUser(string $role, ?string $scopeType = null, ?int $scopeId = null): AuthUser
    {
        $roleRecord = AuthRole::where('nama_peran', $role)->first();
        return AuthUser::create([
            'no_hp' => '08' . rand(100000000, 999999999), 'kata_sandi' => 'hash', 'id_peran' => $roleRecord->id_peran,
            'no_hp' => '0812' . rand(1000, 9999),
            'kata_sandi' => bcrypt('password'),
            'status_akun' => 'aktif',
            'default_scope_type' => $scopeType,
            'default_scope_id' => $scopeId,
        ]);
    }

    public function test_viewAnyKebutuhan()
    {
        $superAdmin = $this->createUser('super_admin');
        $pwnu = $this->createUser('pwnu');
        $pcnu = $this->createUser('pcnu');
        $mwc = $this->createUser('mwc');
        $relawan = $this->createUser('relawan');

        $this->actingAs($superAdmin);
        $this->assertTrue($superAdmin->can('viewAnyKebutuhan', RelawanKebutuhan::class));
        $this->actingAs($pwnu);
        $this->assertTrue($pwnu->can('viewAnyKebutuhan', RelawanKebutuhan::class));
        $this->actingAs($pcnu);
        $this->assertTrue($pcnu->can('viewAnyKebutuhan', RelawanKebutuhan::class));
        $this->actingAs($mwc);
        $this->assertTrue($mwc->can('viewAnyKebutuhan', RelawanKebutuhan::class));
        $this->actingAs($relawan);
        $this->assertTrue($relawan->can('viewAnyKebutuhan', RelawanKebutuhan::class));
    }

    public function test_createKebutuhan()
    {
        $superAdmin = $this->createUser('super_admin');
        $pwnu = $this->createUser('pwnu');
        $pcnu = $this->createUser('pcnu');
        $mwc = $this->createUser('mwc');
        $relawan = $this->createUser('relawan');

        $this->actingAs($superAdmin);
        $this->assertTrue($superAdmin->can('createKebutuhan', RelawanKebutuhan::class));
        $this->actingAs($pwnu);
        $this->assertTrue($pwnu->can('createKebutuhan', RelawanKebutuhan::class));
        $this->actingAs($pcnu);
        $this->assertTrue($pcnu->can('createKebutuhan', RelawanKebutuhan::class));
        $this->actingAs($mwc);
        $this->assertFalse($mwc->can('createKebutuhan', RelawanKebutuhan::class));
        $this->actingAs($relawan);
        $this->assertFalse($relawan->can('createKebutuhan', RelawanKebutuhan::class));
    }

    public function test_update_dan_delete_kebutuhan()
    {
        Schema::disableForeignKeyConstraints();
        \Illuminate\Support\Facades\DB::table('bencana_master_jenis')->insertOrIgnore(['id_jenis' => 1, 'nama_bencana' => 'Bencana', 'slug' => 'bencana']);
        $unit = OrganisasiUnit::create(['nama_unit' => 'Unit PCNU 1', 'tipe_unit' => 'pcnu']);
        $pcnuObj = OrganisasiPcnu::create(['id_pcnu' => 1, 'id_unit' => $unit->id_unit, 'nama_pcnu' => 'PCNU 1']);
        $insiden = OperasiInsiden::create(['kode_kejadian' => 'KODE-1', 'id_jenis_bencana' => 1, 'id_pcnu' => 1, 'waktu_mulai' => now()]);
        Schema::enableForeignKeyConstraints();

        $kebutuhan = RelawanKebutuhan::create(['deskripsi_tugas' => 'Deskripsi Default', 'judul_posisi' => 'Default Posisi', 'id_insiden' => $insiden->id_insiden,
            'jumlah_dibutuhkan' => 5,
        ]);

        $superAdmin = $this->createUser('super_admin');
        $pwnu = $this->createUser('pwnu');
        $pcnuOwner = $this->createUser('pcnu', 'pcnu', 1);
        $pcnuOther = $this->createUser('pcnu', 'pcnu', 2);
        $relawan = $this->createUser('relawan');

        $this->actingAs($superAdmin);
        $this->assertTrue($superAdmin->can('updateKebutuhan', $kebutuhan));
        $this->actingAs($pwnu);
        $this->assertTrue($pwnu->can('updateKebutuhan', $kebutuhan));
        $this->actingAs($pcnuOwner);
        $this->assertTrue($pcnuOwner->can('updateKebutuhan', $kebutuhan));
        $this->actingAs($pcnuOther);
        $this->assertFalse($pcnuOther->can('updateKebutuhan', $kebutuhan));
        $this->actingAs($relawan);
        $this->assertFalse($relawan->can('updateKebutuhan', $kebutuhan));
    }

    public function test_approve_reject_assign_relawan()
    {
        Schema::disableForeignKeyConstraints();
        \Illuminate\Support\Facades\DB::table('bencana_master_jenis')->insertOrIgnore(['id_jenis' => 1, 'nama_bencana' => 'Bencana', 'slug' => 'bencana']);
        $unit = OrganisasiUnit::create(['nama_unit' => 'Unit PCNU 1', 'tipe_unit' => 'pcnu']);
        OrganisasiPcnu::create(['id_pcnu' => 1, 'id_unit' => $unit->id_unit, 'nama_pcnu' => 'PCNU 1']);
        $insiden = OperasiInsiden::create(['kode_kejadian' => 'KODE-1', 'id_jenis_bencana' => 1, 'id_pcnu' => 1, 'waktu_mulai' => now()]);
        Schema::enableForeignKeyConstraints();
        $kebutuhan = RelawanKebutuhan::create(['deskripsi_tugas' => 'Deskripsi Default', 'judul_posisi' => 'Default Posisi', 'id_insiden' => $insiden->id_insiden, 'jumlah_dibutuhkan' => 5]);
        
        $user = $this->createUser('relawan');
        $pendaftaran = RelawanPendaftaran::create([
            'id_relawan_kebutuhan' => $kebutuhan->id_relawan_kebutuhan,
            'id_pengguna' => $user->id_pengguna,
            'status_pendaftaran' => 'seleksi'
        ]);

        $superAdmin = $this->createUser('super_admin');
        $pcnuOwner = $this->createUser('pcnu', 'pcnu', 1);
        $pcnuOther = $this->createUser('pcnu', 'pcnu', 2);

        $this->actingAs($superAdmin);
        $this->assertTrue($superAdmin->can('approveRelawan', $pendaftaran));
        $this->actingAs($pcnuOwner);
        $this->assertTrue($pcnuOwner->can('approveRelawan', $pendaftaran));
        $this->actingAs($pcnuOther);
        $this->assertFalse($pcnuOther->can('approveRelawan', $pendaftaran));
        
        $this->actingAs($superAdmin);
        $this->assertTrue($superAdmin->can('rejectRelawan', $pendaftaran));
        $this->assertTrue($superAdmin->can('assignRelawan', $pendaftaran));
    }

    public function test_view_dan_update_profil()
    {
        $relawan = $this->createUser('relawan');
        $profil = AuthPenggunaProfil::create([
            'id_pengguna' => $relawan->id_pengguna,
            'nik' => '123',
            'nama_lengkap' => 'Nama',
            'email' => 'email@test.com'
        ]);

        $relawanLain = $this->createUser('relawan');
        $admin = $this->createUser('super_admin');

        $this->actingAs($relawan);
        $this->assertTrue($relawan->can('viewProfil', $profil));
        $this->assertTrue($relawan->can('updateProfil', $profil));

        $this->actingAs($relawanLain);
        $this->assertFalse($relawanLain->can('viewProfil', $profil));
        $this->assertFalse($relawanLain->can('updateProfil', $profil));

        $this->actingAs($admin);
        $this->assertTrue($admin->can('viewProfil', $profil));
        $this->assertFalse($admin->can('updateProfil', $profil)); // Admin tidak bisa edit profil relawan
    }
}
