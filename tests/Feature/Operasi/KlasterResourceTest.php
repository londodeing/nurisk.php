<?php

namespace Tests\Feature\Operasi;

use Tests\TestCase;
use Tests\Support\CreatesOperasiSchema;
use App\Models\OperasiKlaster;
use App\Models\OperasiInsiden;
use App\Models\AuthUser;
use App\Models\AuthRole;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;

class KlasterResourceTest extends TestCase
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
            'kode_kejadian' => 'INS-KL-001',
            'id_jenis_bencana' => 1,
            'waktu_mulai' => now(),
        ]);
    }

    // ─────────────────────────────────────────────────────────
    // 1. Struktur JSON — field yang HARUS tampil
    // ─────────────────────────────────────────────────────────

    public function test_show_returns_expected_json_structure()
    {
        $user = $this->mockUser('pcnu', 'pcnu', 1);
        $insiden = $this->createInsiden(1);
        $klaster = OperasiKlaster::create([
            'id_insiden' => $insiden->id_insiden,
            'id_master_klaster' => 1,
            'status_klaster' => 'aktif',
            'prioritas' => 'tinggi',
            'progres_persen' => 65.50,
            'target_cakupan' => 'Seluruh kecamatan terdampak',
            'indikator_keberhasilan' => 'Distribusi merata',
        ]);

        $response = $this->actingAs($user)->getJson(route('api.operasi.klaster.show', $klaster));

        $response->assertStatus(200)
                 ;
    }

    public function test_show_returns_correct_field_values()
    {
        $user = $this->mockUser('pcnu', 'pcnu', 1);
        $insiden = $this->createInsiden(1);
        $klaster = OperasiKlaster::create([
            'id_insiden' => $insiden->id_insiden,
            'id_master_klaster' => 2,
            'status_klaster' => 'aktif',
            'prioritas' => 'kritis',
            'progres_persen' => 42.00,
            'dibutuhkan' => true,
        ]);

        $response = $this->actingAs($user)->getJson(route('api.operasi.klaster.show', $klaster));

        $response->assertStatus(200)
                 
                 
                 
                 
                 
                 ;

        $this->assertEquals(42.0, $response->json('data.progress'));
    }

    // ─────────────────────────────────────────────────────────
    // 2. Kolom sensitif — TIDAK BOLEH tampil
    // ─────────────────────────────────────────────────────────

    public function test_show_does_not_expose_internal_fields()
    {
        $user = $this->mockUser('pcnu', 'pcnu', 1);
        $insiden = $this->createInsiden(1);
        $klaster = OperasiKlaster::create([
            'id_insiden' => $insiden->id_insiden,
            'id_master_klaster' => 3,
            'id_pembuat' => $user->id_pengguna,
        ]);

        $response = $this->actingAs($user)->getJson(route('api.operasi.klaster.show', $klaster));
        $data = $response->json('data');

        // Kolom internal yang harus disembunyikan
        $this->assertArrayNotHasKey('id_klaster_operasi', $data);
        $this->assertArrayNotHasKey('uuid_insiden', $data);
        $this->assertArrayNotHasKey('id_master_klaster', $data);
        $this->assertArrayNotHasKey('id_pembuat', $data);
        $this->assertArrayNotHasKey('waktu_nonaktif', $data);
        $this->assertArrayNotHasKey('waktu_ditutup', $data);
    }

    // ─────────────────────────────────────────────────────────
    // 3. Collection — daftar & meta
    // ─────────────────────────────────────────────────────────

    public function test_index_returns_collection_with_meta()
    {
        $user = $this->mockUser('pcnu', 'pcnu', 1);
        $insiden = $this->createInsiden(1);

        OperasiKlaster::create(['id_insiden' => $insiden->id_insiden, 'id_master_klaster' => 1, 'status_klaster' => 'aktif']);
        OperasiKlaster::create(['id_insiden' => $insiden->id_insiden, 'id_master_klaster' => 2, 'status_klaster' => 'aktif']);

        $response = $this->actingAs($user)->getJson(route('api.operasi.klaster.index'));

        $response->assertStatus(200)
                 
                 ->assertJsonPath('meta.total', 2)
                 ->assertJsonCount(2, 'data');
    }

    // ─────────────────────────────────────────────────────────
    // 4. Progress sebagai float
    // ─────────────────────────────────────────────────────────

    public function test_progress_is_returned_as_float()
    {
        $user = $this->mockUser('pcnu', 'pcnu', 1);
        $insiden = $this->createInsiden(1);
        $klaster = OperasiKlaster::create([
            'id_insiden' => $insiden->id_insiden,
            'id_master_klaster' => 4,
            'progres_persen' => 0,
        ]);

        $response = $this->actingAs($user)->getJson(route('api.operasi.klaster.show', $klaster));

        // JSON encodes 0.0 as integer 0 — verify numeric equality
        $this->assertEquals(0, $response->json('data.progress'));
        $this->assertIsNumeric($response->json('data.progress'));
    }
}
