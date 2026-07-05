<?php

namespace Tests\Feature\Auth;

use Tests\TestCase;
use App\Models\AuthUser;
use App\Models\AuthRole;
use App\Models\JabatanPosisi;
use App\Models\PenggunaJabatan;
use App\Models\OperasiInsiden;
use App\Models\MasterSuratJenis;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class PositionBasedAuthorizationTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        // Seeding roles
        AuthRole::insertOrIgnore([
            ['id_peran' => 1, 'nama_peran' => 'super_admin', 'level_otoritas' => 1],
            ['id_peran' => 3, 'nama_peran' => 'pcnu', 'level_otoritas' => 3],
            ['id_peran' => 4, 'nama_peran' => 'relawan', 'level_otoritas' => 4],
        ]);

        // Seeding master data yurisdiksi dan bencana
        \Illuminate\Support\Facades\DB::table('organisasi_unit')->insertOrIgnore(['id_unit' => 1, 'parent_id' => 99, 'nama_unit' => 'PCNU 1', 'tipe_unit' => 'pcnu']);
        \Illuminate\Support\Facades\DB::table('organisasi_unit')->insertOrIgnore(['id_unit' => 99, 'nama_unit' => 'PWNU Jatim', 'tipe_unit' => 'pwnu']);
        \Illuminate\Support\Facades\DB::table('organisasi_pcnu')->insertOrIgnore(['id_pcnu' => 1, 'id_unit' => 1, 'nama_pcnu' => 'PCNU 1']);
        \Illuminate\Support\Facades\DB::table('bencana_master_jenis')->insertOrIgnore(['id_jenis' => 1, 'nama_bencana' => 'Banjir', 'slug' => 'banjir']);

        // Seeding master surat jenis untuk "ST" (Surat Tugas)
        MasterSuratJenis::firstOrCreate(
            ['kode_jenis' => 'ST'],
            ['nama_jenis' => 'Surat Perintah Tugas', 'singkatan' => 'ST', 'format_nomor' => 'ST/001/PCNU', 'urutan_hierarki' => 1, 'kategori' => 'OPERASI']
        );
    }

    private function buatUserDenganRole(string $namaPeran): AuthUser
    {
        $peran = AuthRole::where('nama_peran', $namaPeran)->first();
        return AuthUser::factory()->create([
            'id_peran'    => $peran->id_peran,
            'status_akun' => 'aktif',
            'default_scope_type' => 'pcnu',
            'default_scope_id' => 1,
        ]);
    }

    public function test_user_with_role_pcnu_but_no_ketua_position_cannot_issue_spk(): void
    {
        $user = $this->buatUserDenganRole('pcnu');
        $trc = $this->buatUserDenganRole('relawan');
        $insiden = OperasiInsiden::factory()->create([
            'id_pcnu' => 1,
            'status_insiden' => 'respon'
        ]);

        $response = $this->actingAs($user)->post(route('insiden.spk.store', $insiden), [
            'id_penerima_spk' => $trc->id_pengguna,
            'catatan_penugasan' => 'Bantu evakuasi'
        ]);

        $response->assertStatus(403);
    }

    public function test_user_with_role_pcnu_and_active_ketua_position_can_issue_spk(): void
    {
        $user = $this->buatUserDenganRole('pcnu');
        $trc = $this->buatUserDenganRole('relawan');
        $insiden = OperasiInsiden::factory()->create([
            'id_pcnu' => 1,
            'status_insiden' => 'respon'
        ]);

        // Create the ketua-pcnu position and assign it to the user
        $jabatan = JabatanPosisi::firstOrCreate(
            ['slug' => 'ketua-pcnu'],
            ['nama_jabatan' => 'Ketua PCNU', 'deskripsi' => 'Ketua Pengurus Cabang NU']
        );

        PenggunaJabatan::create([
            'id_pengguna' => $user->id_pengguna,
            'id_jabatan_posisi' => $jabatan->id_jabatan_posisi,
            'tipe_lingkup' => 'pcnu',
            'id_lingkup' => 1,
            'status_aktif' => 1,
            'ditugaskan_pada' => now()
        ]);

        // Clear auth service context cache so it reads updated relations
        app(\App\Services\Auth\AuthorizationContextService::class)->clearCache();

        $response = $this->actingAs($user)->post(route('insiden.spk.store', $insiden), [
            'id_penerima_spk' => $trc->id_pengguna,
            'catatan_penugasan' => 'Bantu evakuasi'
        ]);

        $response->assertStatus(302); // Redirect back on success
        $this->assertNotNull($insiden->fresh()->no_spk_assesment);
    }

    public function test_super_admin_can_issue_spk_always(): void
    {
        $user = $this->buatUserDenganRole('super_admin');
        $trc = $this->buatUserDenganRole('relawan');
        $insiden = OperasiInsiden::factory()->create([
            'id_pcnu' => 1,
            'status_insiden' => 'respon'
        ]);

        $response = $this->actingAs($user)->post(route('insiden.spk.store', $insiden), [
            'id_penerima_spk' => $trc->id_pengguna,
            'catatan_penugasan' => 'Bantu evakuasi'
        ]);

        $response->assertStatus(302);
        $this->assertNotNull($insiden->fresh()->no_spk_assesment);
    }

    public function test_admin_can_toggle_user_position_status(): void
    {
        $admin = $this->buatUserDenganRole('super_admin');
        $user = $this->buatUserDenganRole('pcnu');
        
        $jabatan = JabatanPosisi::firstOrCreate(
            ['slug' => 'ketua-pcnu'],
            ['nama_jabatan' => 'Ketua PCNU', 'deskripsi' => 'Ketua Pengurus Cabang NU']
        );

        $pj = PenggunaJabatan::create([
            'id_pengguna' => $user->id_pengguna,
            'id_jabatan_posisi' => $jabatan->id_jabatan_posisi,
            'tipe_lingkup' => 'pcnu',
            'id_lingkup' => 1,
            'status_aktif' => 0,
            'ditugaskan_pada' => now()
        ]);

        $response = $this->actingAs($admin)->patch(route('admin.pengguna-jabatan.toggle', $pj));
        $response->assertStatus(302);
        
        $this->assertTrue((bool) $pj->fresh()->status_aktif);
    }
}
