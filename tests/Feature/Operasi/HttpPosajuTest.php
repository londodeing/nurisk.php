<?php

namespace Tests\Feature\Operasi;

use Tests\TestCase;
use Tests\Support\CreatesOperasiSchema;
use App\Models\OperasiPosaju;
use App\Models\OperasiInsiden;
use App\Models\AuthUser;
use App\Models\AuthRole;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;

class HttpPosajuTest extends TestCase
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

    public function test_can_create_posaju_success()
    {
        $user = $this->mockUser('pcnu', 'pcnu', 1);
        $insiden = $this->createInsiden(1);

        $response = $this->actingAs($user)->postJson(route('api.operasi.posaju.store'), [
            'uuid_insiden' => $insiden->uuid_insiden,
            'nama_posaju' => 'Pos Aju Utama',
            'alamat_lokasi' => 'Alun-alun Kota',
            'pj_posaju' => $user->id_pengguna,
            'latitude' => -7.123,
            'longitude' => 112.123,
        ]);

        $response->assertStatus(201)
                 ->assertJsonPath('data.nama', 'Pos Aju Utama')
                 ->assertJsonMissingPath('data.nama_posaju')
                 ->assertJsonMissingPath('data.status_alur');

        $this->assertDatabaseHas('operasi_posaju', [
            'nama_posaju' => 'Pos Aju Utama',
            'status_alur' => 'direncanakan' // Default db schema
        ]);
    }

    public function test_validation_error_when_missing_required_fields()
    {
        $user = $this->mockUser('pcnu', 'pcnu', 1);

        $response = $this->actingAs($user)->postJson(route('api.operasi.posaju.store'), []);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['uuid_insiden', 'nama_posaju', 'pj_posaju']);
    }

    public function test_pwnu_cross_jurisdiction_forbidden()
    {
        $userPwnu = $this->mockUser('pwnu', 'pwnu', 88); // PWNU wilayah lain
        $insiden = $this->createInsiden(1); // PCNU dengan id_unit 1, parent_id 99
        $posaju = OperasiPosaju::create(['alamat_lokasi' => 'Alamat Default', 'id_insiden' => $insiden->id_insiden,
            'nama_posaju' => 'Pos Aju Lama',
            'pj_posaju' => $this->mockUser('relawan')->id_pengguna,
            'status_alur' => 'direncanakan',
        ]);

        $response = $this->actingAs($userPwnu)->putJson(route('api.operasi.posaju.update', $posaju), [
            'nama_posaju' => 'Pos Aju Diubah'
        ]);

        $response->assertStatus(403);
    }

    public function test_activate_state_transition_success()
    {
        $user = $this->mockUser('pcnu', 'pcnu', 1);
        $insiden = $this->createInsiden(1);
        $posaju = OperasiPosaju::create(['alamat_lokasi' => 'Alamat Default', 'id_insiden' => $insiden->id_insiden,
            'nama_posaju' => 'Pos Aju Lama',
            'pj_posaju' => $user->id_pengguna,
            'status_alur' => 'direncanakan',
        ]);

        $response = $this->actingAs($user)->postJson(route('api.operasi.posaju.activate', $posaju));

        $response->assertStatus(200)
                 ->assertJsonPath('message', 'Pos Aju berhasil diaktifkan')
                 ->assertJsonPath('data.status', 'aktif')
                 ->assertJsonMissingPath('data.status_alur');
                 
        $this->assertEquals('aktif', $posaju->fresh()->status_alur);
    }

    public function test_activate_illegal_state_transition_denied()
    {
        $user = $this->mockUser('pcnu', 'pcnu', 1);
        $insiden = $this->createInsiden(1);
        $posaju = OperasiPosaju::create(['alamat_lokasi' => 'Alamat Default', 'id_insiden' => $insiden->id_insiden,
            'nama_posaju' => 'Pos Aju Lama',
            'pj_posaju' => $user->id_pengguna,
            'status_alur' => 'ditutup', // Sudah ditutup
        ]);

        $response = $this->actingAs($user)->postJson(route('api.operasi.posaju.activate', $posaju));

        // Karena Policy memblokir state transition yang tidak valid dengan 403
        $response->assertStatus(403);
        $this->assertEquals('ditutup', $posaju->fresh()->status_alur);
    }

    public function test_extend_posaju_success()
    {
        $user = $this->mockUser('pcnu', 'pcnu', 1);
        $insiden = $this->createInsiden(1);
        $posaju = OperasiPosaju::create(['alamat_lokasi' => 'Alamat Default', 'id_insiden' => $insiden->id_insiden,
            'nama_posaju' => 'Pos Aju Lama',
            'pj_posaju' => $user->id_pengguna,
            'status_alur' => 'aktif',
        ]);

        $response = $this->actingAs($user)->postJson(route('api.operasi.posaju.extend', $posaju), [
            'diperpanjang_hingga' => now()->addDays(7)->toDateString(),
            'alasan_penutupan' => 'Masih butuh pos'
        ]);

        $response->assertStatus(200);
        $this->assertEquals('diperpanjang', $posaju->fresh()->status_alur);
    }
}
