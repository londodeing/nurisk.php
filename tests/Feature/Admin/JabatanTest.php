<?php

namespace Tests\Feature\Admin;

use Tests\TestCase;
use App\Models\AuthUser;
use App\Models\AuthRole;
use App\Models\JabatanPosisi;
use App\Models\PenggunaJabatan;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Schema;

class JabatanTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        // Siapkan tabel yang dibutuhkan








        // Seeding roles
        AuthRole::insertOrIgnore([
            ['id_peran' => 1, 'nama_peran' => 'super_admin', 'level_otoritas' => 1],
            ['id_peran' => 2, 'nama_peran' => 'pwnu', 'level_otoritas' => 2],
            ['id_peran' => 3, 'nama_peran' => 'pcnu', 'level_otoritas' => 3],
            ['id_peran' => 4, 'nama_peran' => 'relawan', 'level_otoritas' => 4],
            ['id_peran' => 5, 'nama_peran' => 'publik', 'level_otoritas' => 5],
        ]);
    }

    /**
     * Helper untuk membuat user dengan role tertentu.
     */
    private function buatUserDenganRole(string $namaPeran): AuthUser
    {
        $peran = AuthRole::where('nama_peran', $namaPeran)->first();
        return AuthUser::factory()->create([
            'id_peran'    => $peran->id_peran,
            'status_akun' => 'aktif',
        ]);
    }

    // === INDEX ===

    public function test_super_admin_dapat_mengakses_halaman_index_jabatan(): void
    {
        $user = $this->buatUserDenganRole('super_admin');
        $response = $this->actingAs($user)->get(route('admin.jabatan.index'));
        $response->assertStatus(200);
    }

    public function test_pwnu_dapat_mengakses_halaman_index_jabatan(): void
    {
        $user = $this->buatUserDenganRole('pwnu');
        $response = $this->actingAs($user)->get(route('admin.jabatan.index'));
        $response->assertStatus(200);
    }

    public function test_pcnu_dapat_mengakses_halaman_index_jabatan(): void
    {
        $user = $this->buatUserDenganRole('pcnu');
        $response = $this->actingAs($user)->get(route('admin.jabatan.index'));
        $response->assertStatus(200);
    }

    public function test_relawan_diblokir_dari_halaman_index_jabatan(): void
    {
        $user = $this->buatUserDenganRole('relawan');
        $response = $this->actingAs($user)->get(route('admin.jabatan.index'));
        $response->assertStatus(403);
    }

    public function test_publik_diblokir_dari_halaman_index_jabatan(): void
    {
        $user = $this->buatUserDenganRole('publik');
        $response = $this->actingAs($user)->get(route('admin.jabatan.index'));
        $response->assertStatus(403);
    }

    public function test_tamu_diredirect_ke_login_dari_halaman_index(): void
    {
        $response = $this->get(route('admin.jabatan.index'));
        $response->assertRedirect('/login');
    }

    // === CREATE & STORE ===

    public function test_super_admin_dapat_mengakses_halaman_create(): void
    {
        $user = $this->buatUserDenganRole('super_admin');
        $response = $this->actingAs($user)->get(route('admin.jabatan.create'));
        $response->assertStatus(200);
    }

    public function test_pwnu_diblokir_dari_halaman_create(): void
    {
        $user = $this->buatUserDenganRole('pwnu');
        $response = $this->actingAs($user)->get(route('admin.jabatan.create'));
        $response->assertStatus(403);
    }

    public function test_pcnu_diblokir_dari_halaman_create(): void
    {
        $user = $this->buatUserDenganRole('pcnu');
        $response = $this->actingAs($user)->get(route('admin.jabatan.create'));
        $response->assertStatus(403);
    }

    public function test_super_admin_berhasil_store_jabatan_baru(): void
    {
        $user = $this->buatUserDenganRole('super_admin');
        $response = $this->actingAs($user)->post(route('admin.jabatan.store'), [
            'nama_jabatan' => 'Wakil Ketua PWNU',
            'slug'         => 'wakil-ketua-pwnu',
            'deskripsi'    => 'Wakil Ketua Pengurus Wilayah NU',
        ]);

        $response->assertRedirect(route('admin.jabatan.index'));
        $this->assertDatabaseHas('master_jabatan', ['slug' => 'wakil-ketua-pwnu']);
    }

    public function test_store_gagal_validasi_jika_nama_jabatan_kosong(): void
    {
        $user = $this->buatUserDenganRole('super_admin');
        $response = $this->actingAs($user)->post(route('admin.jabatan.store'), [
            'nama_jabatan' => '',
            'slug'         => 'slug-test',
        ]);

        $response->assertSessionHasErrors('nama_jabatan');
    }

    public function test_store_gagal_jika_slug_sudah_ada(): void
    {
        JabatanPosisi::factory()->create(['slug' => 'slug-unik']);

        $user = $this->buatUserDenganRole('super_admin');
        $response = $this->actingAs($user)->post(route('admin.jabatan.store'), [
            'nama_jabatan' => 'Jabatan Test',
            'slug'         => 'slug-unik',
        ]);

        $response->assertSessionHasErrors('slug');
    }

    // === EDIT & UPDATE ===

    public function test_super_admin_dapat_mengakses_halaman_edit(): void
    {
        $jabatan = JabatanPosisi::factory()->create();
        $user = $this->buatUserDenganRole('super_admin');

        $response = $this->actingAs($user)->get(route('admin.jabatan.edit', $jabatan));
        $response->assertStatus(200);
    }

    public function test_pwnu_diblokir_dari_halaman_edit(): void
    {
        $jabatan = JabatanPosisi::factory()->create();
        $user = $this->buatUserDenganRole('pwnu');

        $response = $this->actingAs($user)->get(route('admin.jabatan.edit', $jabatan));
        $response->assertStatus(403);
    }

    public function test_super_admin_berhasil_update_jabatan(): void
    {
        $jabatan = JabatanPosisi::factory()->create(['nama_jabatan' => 'Nama Lama']);
        $user = $this->buatUserDenganRole('super_admin');

        $response = $this->actingAs($user)->put(route('admin.jabatan.update', $jabatan), [
            'nama_jabatan' => 'Nama Baru',
            'slug'         => $jabatan->slug,
        ]);

        $response->assertRedirect(route('admin.jabatan.index'));
        $this->assertDatabaseHas('master_jabatan', [
            'id_jabatan_posisi' => $jabatan->id_jabatan_posisi,
            'nama_jabatan' => 'Nama Baru',
        ]);
    }

    public function test_update_gagal_validasi_jika_nama_jabatan_kosong(): void
    {
        $jabatan = JabatanPosisi::factory()->create();
        $user = $this->buatUserDenganRole('super_admin');

        $response = $this->actingAs($user)->put(route('admin.jabatan.update', $jabatan), [
            'nama_jabatan' => '',
            'slug'         => 'slug-test',
        ]);

        $response->assertSessionHasErrors('nama_jabatan');
    }

    public function test_update_dengan_slug_milik_diri_sendiri_tetap_berhasil(): void
    {
        $jabatan = JabatanPosisi::factory()->create(['slug' => 'slug-sendiri']);
        $user = $this->buatUserDenganRole('super_admin');

        $response = $this->actingAs($user)->put(route('admin.jabatan.update', $jabatan), [
            'nama_jabatan' => 'Nama Baru',
            'slug'         => 'slug-sendiri',
        ]);

        $response->assertRedirect(route('admin.jabatan.index'));
    }

    // === DELETE ===

    public function test_super_admin_berhasil_menghapus_jabatan_yang_tidak_dipakai(): void
    {
        $jabatan = JabatanPosisi::factory()->create();
        $user = $this->buatUserDenganRole('super_admin');

        $response = $this->actingAs($user)->delete(route('admin.jabatan.destroy', $jabatan));

        $response->assertRedirect(route('admin.jabatan.index'));
        $this->assertDatabaseMissing('master_jabatan', ['id_jabatan_posisi' => $jabatan->id_jabatan_posisi]);
    }

    public function test_super_admin_gagal_menghapus_jabatan_yang_masih_dipakai_pengguna(): void
    {
        $jabatan = JabatanPosisi::factory()->create();
        $user = $this->buatUserDenganRole('super_admin');

        // Bikin pengguna terasosiasi dengan jabatan ini
        PenggunaJabatan::factory()->create([
            'id_pengguna' => $user->id_pengguna,
            'id_jabatan_posisi' => $jabatan->id_jabatan_posisi,
        ]);

        $response = $this->actingAs($user)->delete(route('admin.jabatan.destroy', $jabatan));

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('master_jabatan', ['id_jabatan_posisi' => $jabatan->id_jabatan_posisi]);
    }

    public function test_pwnu_diblokir_dari_delete(): void
    {
        $jabatan = JabatanPosisi::factory()->create();
        $user = $this->buatUserDenganRole('pwnu');

        $response = $this->actingAs($user)->delete(route('admin.jabatan.destroy', $jabatan));

        $response->assertStatus(403);
    }
}
