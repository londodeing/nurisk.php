<?php

namespace Tests\Feature;

use App\Models\AuthUser;
use App\Models\OrganisasiPcnu;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class EndToEndAuthTest extends TestCase
{
    use DatabaseTransactions;
    public function test_e2e_register_approve_login_ui()
    {
        // 1. Setup Data: Pastikan PCNU Kudus ada
        $kab = DB::table('wilayah_kabupaten')->where('nama_kab', 'like', '%Kudus%')->first();
        $this->assertNotNull($kab, "Kabupaten Kudus tidak ditemukan di database.");

        $kec = DB::table('wilayah_kecamatan')->where('id_kab', $kab->id_kab)->first();
        $desa = DB::table('wilayah_desa')->where('id_kec', $kec->id_kec)->first();

        // Pastikan ada PCNU
        $unitId = DB::table('organisasi_unit')->where('id_wilayah', $kab->id_kab)->value('id_unit');
        if (!$unitId) {
            $unitId = DB::table('organisasi_unit')->insertGetId([
                'nama_unit' => 'PCNU Kabupaten Kudus',
                'tipe_unit' => 'pcnu',
                'id_wilayah' => $kab->id_kab
            ]);
            DB::table('organisasi_pcnu')->insert([
                'id_unit' => $unitId,
                'nama_pcnu' => 'PCNU Kabupaten Kudus'
            ]);
        }

        // Setup PWNU Admin
        $pwnuAdmin = AuthUser::factory()->create([
            'no_hp' => '08111111111',
            'kata_sandi' => Hash::make('password'),
            'default_scope_type' => 'pwnu',
            'default_scope_id' => 1,
            'status_akun' => 'aktif'
        ]);
        // Beri role
        $rolePwnu = DB::table('auth_peran')->where('nama_peran', 'admin_pwnu')->value('id_peran');
        if (!$rolePwnu) {
            $rolePwnu = DB::table('auth_peran')->insertGetId([
                'nama_peran' => 'admin_pwnu',
                'deskripsi' => 'Admin PWNU'
            ]);
        }
        $pwnuAdmin->id_peran = $rolePwnu;
        $pwnuAdmin->save();

        // 2. Simulasikan Register dari form Web UI
        $response = $this->post('/register/admin_pcnu', [
            'no_hp' => '08333333333',
            'kata_sandi' => 'password123',
            'kata_sandi_confirmation' => 'password123',
            'nama_lengkap' => 'Admin Kudus',
            'id_kabupaten' => $kab->id_kab,
            'id_kecamatan' => $kec->id_kec,
            'id_desa' => $desa->id_desa,
            'alamat_deskriptif' => 'Jl. Kudus No 1'
        ]);

        $response->assertRedirect(route('register.menunggu'));

        // Cek bahwa user tersimpan dengan status menunggu dan mendapat scope Kudus
        $adminKudus = AuthUser::where('no_hp', '08333333333')->first();
        $this->assertNotNull($adminKudus, 'Admin Kudus gagal tersimpan di database');
        $this->assertEquals('menunggu', $adminKudus->status_akun);
        $this->assertEquals('pcnu', $adminKudus->default_scope_type);
        
        $pcnuKudusId = DB::table('organisasi_pcnu')->where('id_unit', $unitId)->value('id_pcnu');
        $this->assertEquals($pcnuKudusId, $adminKudus->default_scope_id);

        // 3. Login PWNU
        $response = $this->post('/login', [
            'no_hp' => '08111111111',
            'kata_sandi' => 'password'
        ]);
        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($pwnuAdmin);

        // 4. PWNU Approve
        $response = $this->patch("/dashboard/admin/pengguna/{$adminKudus->id_pengguna}/setujui");
        $response->assertSessionHas('success');

        $adminKudus->refresh();
        $this->assertEquals('aktif', $adminKudus->status_akun);

        // 5. Logout PWNU
        $this->post('/logout');
        $this->assertGuest();

        // 6. Login Admin Kudus
        $response = $this->post('/login', [
            'no_hp' => '08333333333',
            'kata_sandi' => 'password123'
        ]);
        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($adminKudus);

        // Success!
        $this->assertTrue(true);
    }
}
