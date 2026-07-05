<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\AuthUser;
use App\Models\AuthRole;
use App\Models\JabatanPosisi;
use App\Models\OrganisasiUnit;
use App\Models\OrganisasiPcnu;
use App\Models\WilayahKabupaten;
use App\Models\WilayahKecamatan;
use App\Models\WilayahDesa;

class IdentityLifecycleTest extends TestCase
{
    use RefreshDatabase;

    public function test_new_user_registration_as_relawan()
    {
        AuthRole::insert([
            ['id_peran' => 4, 'nama_peran' => 'relawan', 'level_otoritas' => 4],
        ]);

        JabatanPosisi::insert([
            ['id_jabatan_posisi' => 15, 'nama_jabatan' => 'Relawan Umum', 'slug' => 'relawan-umum'],
        ]);

        OrganisasiUnit::insert([
            ['id_unit' => 1, 'nama_unit' => 'Unit Test', 'tipe_unit' => 'pcnu'],
        ]);

        OrganisasiPcnu::insert([
            ['id_pcnu' => 123, 'nama_pcnu' => 'PCNU Test', 'id_unit' => 1],
        ]);

        WilayahKabupaten::insertOrIgnore([
            ['id_kab' => '3301', 'nama_kab' => 'Kab Test', 'tipe' => 'Kabupaten']
        ]);

        WilayahKecamatan::insertOrIgnore([
            ['id_kec' => '330101', 'id_kab' => '3301', 'nama_kec' => 'Kec Test']
        ]);

        WilayahDesa::insertOrIgnore([
            ['id_desa' => '3301012001', 'id_kec' => '330101', 'nama_desa' => 'Desa Test']
        ]);

        $response = $this->post('/daftar/relawan', [
            'no_hp'               => '081234567890',
            'kata_sandi'          => 'password123',
            'kata_sandi_confirmation' => 'password123',
            'nama_lengkap'        => 'Budi Santoso',
            'nik'                 => '3301234567890001',
            'email'               => 'budi@example.com',
            'id_kabupaten'        => '3301',
            'id_kecamatan'        => '330101',
            'id_desa'             => '3301012001',
            'alamat_deskriptif'   => 'RT 01 RW 02, Dusun Krajan',
        ]);

        $response->assertRedirect('/dashboard');

        $this->assertDatabaseHas('auth_users', [
            'no_hp' => '081234567890',
            'id_peran' => 4,
            'status_akun' => AuthUser::STATUS_AKTIF,
        ]);

        $this->assertDatabaseHas('auth_pengguna_profil', [
            'nama_lengkap' => 'Budi Santoso',
            'nik' => '3301234567890001',
        ]);
    }
}
