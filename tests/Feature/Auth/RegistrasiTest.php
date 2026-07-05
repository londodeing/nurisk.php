<?php

namespace Tests\Feature\Auth;

use Tests\TestCase;
use App\Models\AuthUser;
use App\Models\AuthRole;
use App\Models\AuthKeahlianMaster;
use App\Models\JabatanPosisi;
use App\Models\OrganisasiPcnu;
use App\Models\OrganisasiUnit;
use App\Models\PenggunaJabatan;
use App\Models\WilayahKabupaten;
use App\Models\WilayahKecamatan;
use App\Models\WilayahDesa;
use App\Services\Auth\RegistrationService;
use App\Services\Auth\ApprovalService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class RegistrasiTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed roles sesuai SQL v37
        AuthRole::insert([
            ['id_peran' => 1, 'nama_peran' => 'super_admin', 'level_otoritas' => 1],
            ['id_peran' => 2, 'nama_peran' => 'pwnu',        'level_otoritas' => 2],
            ['id_peran' => 3, 'nama_peran' => 'pcnu',        'level_otoritas' => 3],
            ['id_peran' => 4, 'nama_peran' => 'relawan',     'level_otoritas' => 4],
            ['id_peran' => 5, 'nama_peran' => 'publik',      'level_otoritas' => 5],
        ]);

        // Seed jabatan yang diperlukan
        JabatanPosisi::insert([
            ['id_jabatan_posisi' => 10, 'nama_jabatan' => 'Anggota TRC PCNU',     'slug' => 'anggota-trc-pcnu'],
            ['id_jabatan_posisi' => 5,  'nama_jabatan' => 'Anggota TRC PWNU',     'slug' => 'anggota-trc-pwnu'],
            ['id_jabatan_posisi' => 8,  'nama_jabatan' => 'Admin PCNU',           'slug' => 'admin-pcnu'],
            ['id_jabatan_posisi' => 3,  'nama_jabatan' => 'Admin PWNU',           'slug' => 'admin-pwnu'],
            ['id_jabatan_posisi' => 15, 'nama_jabatan' => 'Relawan Umum',         'slug' => 'relawan-umum'],
        ]);

        // Seed keahlian master
        if (AuthKeahlianMaster::count() === 0) {
            AuthKeahlianMaster::insert([
                ['id_keahlian' => 1, 'nama_keahlian' => 'Medis'],
                ['id_keahlian' => 2, 'nama_keahlian' => 'Water Rescue'],
                ['id_keahlian' => 3, 'nama_keahlian' => 'Vertical Rescue'],
                ['id_keahlian' => 4, 'nama_keahlian' => 'Logistik'],
                ['id_keahlian' => 5, 'nama_keahlian' => 'Dapur Umum'],
            ]);
        }

        // Seed wilayah
        WilayahKabupaten::insert(['id_kab' => '3374', 'nama_kab' => 'Kota Semarang', 'tipe' => 'Kota']);
        WilayahKecamatan::insert(['id_kec' => '337401', 'id_kab' => '3374', 'nama_kec' => 'Semarang Tengah']);
        WilayahDesa::insert(['id_desa' => '3374011001', 'id_kec' => '337401', 'nama_desa' => 'Pindrikan Kidul']);

        // Seed organisasi
        OrganisasiUnit::insert(['id_unit' => 1, 'nama_unit' => 'PWNU Jateng', 'tipe_unit' => 'pwnu']);
        OrganisasiPcnu::insert(['id_pcnu' => 1, 'nama_pcnu' => 'PCNU Kota Semarang', 'id_unit' => 1]);
    }

    // ===================== RELAWAN =====================

    public function test_relawan_bisa_daftar_dan_langsung_login()
    {
        $response = $this->post(route('register.proses', 'relawan'), [
            'no_hp'               => '081234567890',
            'kata_sandi'          => 'Password123!',
            'kata_sandi_confirmation' => 'Password123!',
            'nama_lengkap'        => 'Budi Santoso',
            'nik'                 => '3374011234567890',
            'email'               => 'budi@example.com',
            'id_kabupaten'        => '3374',
            'id_kecamatan'        => '337401',
            'id_desa'             => '3374011001',
            'alamat_deskriptif'   => 'RT 01 RW 02, Dusun Krajan',
            'alamat_deskriptif'   => 'RT 01 RW 02, Dusun Krajan',
            'keahlian'            => [1, 4],
        ]);

        $response->assertRedirect(route('dashboard'));

        $this->assertDatabaseHas('auth_users', [
            'no_hp'       => '081234567890',
            'status_akun' => AuthUser::STATUS_AKTIF,
        ]);

        $this->assertDatabaseHas('auth_pengguna_profil', [
            'nama_lengkap' => 'Budi Santoso',
        ]);

        $this->assertDatabaseHas('auth_pengguna_keahlian', [
            'id_pengguna' => AuthUser::where('no_hp', '081234567890')->first()->id_pengguna,
            'id_keahlian' => 1,
        ]);

        $this->assertAuthenticated();
    }

    public function test_relawan_gagal_daftar_jika_no_hp_duplikat()
    {
        $this->post(route('register.proses', 'relawan'), [
            'no_hp'               => '081234567890',
            'kata_sandi'          => 'Password123!',
            'kata_sandi_confirmation' => 'Password123!',
            'nama_lengkap'        => 'Budi Santoso',
            'id_kabupaten'        => '3374',
            'id_kecamatan'        => '337401',
            'id_desa'             => '3374011001',
            'alamat_deskriptif'   => 'RT 01 RW 02, Dusun Krajan',
        ]);

        Auth::logout();

        $response = $this->post(route('register.proses', 'relawan'), [
            'no_hp'               => '081234567890',
            'kata_sandi'          => 'Password123!',
            'kata_sandi_confirmation' => 'Password123!',
            'nama_lengkap'        => 'Budi Santoso',
            'id_kabupaten'        => '3374',
            'id_kecamatan'        => '337401',
            'id_desa'             => '3374011001',
            'alamat_deskriptif'   => 'RT 01 RW 02, Dusun Krajan',
        ]);

        $response->assertSessionHasErrors('no_hp');
    }

    public function test_relawan_gagal_daftar_jika_no_hp_format_salah()
    {
        $response = $this->post(route('register.proses', 'relawan'), [
            'no_hp'               => 'abc',
            'kata_sandi'          => 'Password123!',
            'kata_sandi_confirmation' => 'Password123!',
            'nama_lengkap'        => 'Budi Santoso',
            'id_kabupaten'        => '3374',
            'id_kecamatan'        => '337401',
            'id_desa'             => '3374011001',
            'alamat_deskriptif'   => 'RT 01 RW 02, Dusun Krajan',
        ]);

        $response->assertSessionHasErrors('no_hp');
    }

    // ===================== TRC PCNU =====================

    public function test_trc_pcnu_didaftar_dengan_status_menunggu()
    {
        $response = $this->post(route('register.proses', 'trc_pcnu'), [
            'no_hp'               => '081234567891',
            'kata_sandi'          => 'Password123!',
            'kata_sandi_confirmation' => 'Password123!',
            'nama_lengkap'        => 'Siti Rahayu',
            'id_kabupaten'        => '3374',
            'id_kecamatan'        => '337401',
            'id_desa'             => '3374011001',
            'alamat_deskriptif'   => 'RT 01 RW 02, Dusun Krajan',
            'id_pcnu'             => 1,
        ]);

        $response->assertRedirect(route('register.menunggu'));

        $this->assertDatabaseHas('auth_users', [
            'no_hp'       => '081234567891',
            'status_akun' => AuthUser::STATUS_MENUNGGU,
        ]);

        $this->assertGuest();
    }

    public function test_trc_pcnu_wajib_memilih_id_pcnu()
    {
        $response = $this->post(route('register.proses', 'trc_pcnu'), [
            'no_hp'               => '081234567892',
            'kata_sandi'          => 'Password123!',
            'kata_sandi_confirmation' => 'Password123!',
            'nama_lengkap'        => 'Ahmad Fauzi',
            'id_kabupaten'        => '3374',
            'id_kecamatan'        => '337401',
            'id_desa'             => '3374011001',
            'alamat_deskriptif'   => 'RT 01 RW 02, Dusun Krajan',
            // id_pcnu tidak diisi
        ]);

        $response->assertSessionHasErrors('id_pcnu');
    }

    public function test_trc_pcnu_membuat_pengguna_jabatan_dengan_status_aktif_false()
    {
        $user = app(RegistrationService::class)->daftar([
            'no_hp'        => '081234567893',
            'kata_sandi'   => 'Password123!',
            'nama_lengkap' => 'Test TRC PCNU',
            'id_kabupaten' => '3374',
            'id_kecamatan' => '337401',
            'id_desa'      => '3374011001',
            'id_pcnu'      => 1,
        ], RegistrationService::JENIS_TRC_PCNU);

        $this->assertDatabaseHas('pengguna_jabatan', [
            'id_pengguna' => $user->id_pengguna,
            'status_aktif' => 0,
        ]);
    }

    // ===================== ADMIN PCNU =====================

    public function test_admin_pcnu_didaftar_dengan_role_pcnu_status_menunggu()
    {
        $response = $this->post(route('register.proses', 'admin_pcnu'), [
            'no_hp'               => '081234567894',
            'kata_sandi'          => 'Password123!',
            'kata_sandi_confirmation' => 'Password123!',
            'nama_lengkap'        => 'Admin PCNU Test',
            'id_kabupaten'        => '3374',
            'id_kecamatan'        => '337401',
            'id_desa'             => '3374011001',
            'alamat_deskriptif'   => 'RT 01 RW 02, Dusun Krajan',
            'id_pcnu'             => 1,
        ]);

        $adminRoleId = AuthRole::where('nama_peran', 'pcnu')->first()->id_peran;

        $this->assertDatabaseHas('auth_users', [
            'no_hp'       => '081234567894',
            'id_peran'    => $adminRoleId,
            'status_akun' => AuthUser::STATUS_MENUNGGU,
        ]);
    }

    // ===================== APPROVAL =====================

    public function test_admin_pcnu_dapat_lihat_daftar_trc_pcnu_dalam_scope()
    {
        $pcnuAdmin = AuthUser::factory()->aktif()->create([
            'id_peran'           => 3,
            'default_scope_type' => 'pcnu',
            'default_scope_id'   => 1,
        ]);

        // Buat calon TRC PCNU di scope yang sama
        $calon = AuthUser::factory()->menunggu()->create([
            'id_peran'           => 4,
            'default_scope_type' => 'pcnu',
            'default_scope_id'   => 1,
        ]);
        PenggunaJabatan::create([
            'id_pengguna'       => $calon->id_pengguna,
            'id_jabatan_posisi' => 10, // Anggota TRC PCNU
            'tipe_lingkup'      => 'pcnu',
            'id_lingkup'        => 1,
            'status_aktif'      => 0,
        ]);

        $this->actingAs($pcnuAdmin);
        $menunggu = app(ApprovalService::class)->daftarMenungguApproval($pcnuAdmin);

        $this->assertCount(1, $menunggu);
        $this->assertEquals($calon->id_pengguna, $menunggu->first()->id_pengguna);
    }

    public function test_admin_pcnu_setujui_trc_pcnu_status_jadi_aktif()
    {
        $pcnuAdmin = AuthUser::factory()->aktif()->create([
            'id_peran' => 3,
            'default_scope_type' => 'pcnu',
            'default_scope_id'   => 1,
        ]);

        $calon = AuthUser::factory()->menunggu()->create([
            'id_peran'           => 4,
            'default_scope_type' => 'pcnu',
            'default_scope_id'   => 1,
        ]);
        $jabatan = PenggunaJabatan::create([
            'id_pengguna'       => $calon->id_pengguna,
            'id_jabatan_posisi' => 10,
            'tipe_lingkup'      => 'pcnu',
            'id_lingkup'        => 1,
            'status_aktif'      => 0,
        ]);

        $this->actingAs($pcnuAdmin);
        app(ApprovalService::class)->setujui($calon, $pcnuAdmin);

        $this->assertEquals(AuthUser::STATUS_AKTIF, $calon->fresh()->status_akun);
        $this->assertEquals(1, $jabatan->fresh()->status_aktif);
    }

    public function test_relawan_tidak_bisa_akses_halaman_approval()
    {
        $relawan = AuthUser::factory()->aktif()->create(['id_peran' => 4]);

        $response = $this->actingAs($relawan)->get(route('admin.approval.index'));

        $response->assertStatus(403);
    }

    public function test_pwnu_admin_bisa_setujui_admin_pcnu()
    {
        $pwnu = AuthUser::factory()->aktif()->create([
            'id_peran' => 2,
        ]);

        $calon = AuthUser::factory()->menunggu()->create([
            'id_peran'           => 3,
            'default_scope_type' => 'pcnu',
            'default_scope_id'   => 1,
        ]);
        PenggunaJabatan::create([
            'id_pengguna'       => $calon->id_pengguna,
            'id_jabatan_posisi' => 8, // Admin PCNU
            'tipe_lingkup'      => 'pcnu',
            'id_lingkup'        => 1,
            'status_aktif'      => 0,
        ]);

        $this->actingAs($pwnu);
        app(ApprovalService::class)->setujui($calon, $pwnu);

        $this->assertEquals(AuthUser::STATUS_AKTIF, $calon->fresh()->status_akun);
    }

    // ===================== BUG FIXES =====================

    public function test_bug_c1_user_dengan_status_aktif_bisa_login()
    {
        AuthUser::factory()->aktif()->create([
            'id_peran' => 4,
            'no_hp'    => '081111111111',
            'kata_sandi' => Hash::make('Password123!'),
        ]);

        $response = $this->post('/login', [
            'no_hp'      => '081111111111',
            'kata_sandi' => 'Password123!',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticated();
    }

    public function test_bug_c1_user_dengan_status_menunggu_tidak_bisa_login()
    {
        AuthUser::factory()->menunggu()->create([
            'id_peran' => 4,
            'no_hp'    => '081111111112',
            'kata_sandi' => Hash::make('Password123!'),
        ]);

        $response = $this->post('/login', [
            'no_hp'      => '081111111112',
            'kata_sandi' => 'Password123!',
        ]);

        $response->assertSessionHasErrors('no_hp');
        $this->assertGuest();
    }

    public function test_semua_status_menggunakan_konstanta_authuser()
    {
        $user = new AuthUser();
        $user->status_akun = AuthUser::STATUS_AKTIF;
        $this->assertTrue($user->isAktif());

        $user->status_akun = AuthUser::STATUS_MENUNGGU;
        $this->assertTrue($user->isMenunggu());

        $user->status_akun = AuthUser::STATUS_SUSPEND;
        $this->assertTrue($user->isSuspended());
    }
}
