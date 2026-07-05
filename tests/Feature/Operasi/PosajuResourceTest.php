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

class PosajuResourceTest extends TestCase
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
            'kode_kejadian' => 'INS-TEST-001',
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
        $posaju = OperasiPosaju::create(['alamat_lokasi' => 'Alamat Default', 'id_insiden' => $insiden->id_insiden,
            'nama_posaju' => 'Pos Aju Utara',
            'pj_posaju' => $user->id_pengguna,
            'latitude' => -6.58,
            'longitude' => 110.66,
            'alamat_lokasi' => 'Jl. Merdeka No. 1',
            'status_alur' => 'direncanakan',
        ]);

        $response = $this->actingAs($user)->getJson(route('api.operasi.posaju.show', $posaju));

        $response->assertStatus(200)
                 ;
    }

    public function test_show_returns_correct_field_values()
    {
        $user = $this->mockUser('pcnu', 'pcnu', 1);
        $insiden = $this->createInsiden(1);
        $posaju = OperasiPosaju::create(['alamat_lokasi' => 'Alamat Default', 'id_insiden' => $insiden->id_insiden,
            'nama_posaju' => 'Pos Aju Timur',
            'pj_posaju' => $user->id_pengguna,
            'latitude' => -7.25,
            'longitude' => 112.75,
            'status_alur' => 'aktif',
        ]);

        $response = $this->actingAs($user)->getJson(route('api.operasi.posaju.show', $posaju));

        $response->assertStatus(200)
                 
                 
                 
                 
                 
                 
                 
                 ;
    }

    // ─────────────────────────────────────────────────────────
    // 2. Kolom sensitif — TIDAK BOLEH tampil
    // ─────────────────────────────────────────────────────────

    public function test_show_does_not_expose_internal_fields()
    {
        $user = $this->mockUser('pcnu', 'pcnu', 1);
        $insiden = $this->createInsiden(1);
        $posaju = OperasiPosaju::create(['alamat_lokasi' => 'Alamat Default', 'id_insiden' => $insiden->id_insiden,
            'nama_posaju' => 'Pos Aju Rahasia',
            'pj_posaju' => $user->id_pengguna,
            'status_alur' => 'direncanakan',
        ]);

        $response = $this->actingAs($user)->getJson(route('api.operasi.posaju.show', $posaju));
        $data = $response->json('data');

        // Kolom internal yang harus disembunyikan
        $this->assertArrayNotHasKey('id_posaju', $data);
        $this->assertArrayNotHasKey('uuid_insiden', $data);
        $this->assertArrayNotHasKey('nama_posaju', $data);
        $this->assertArrayNotHasKey('pj_posaju', $data);
        $this->assertArrayNotHasKey('id_surat_pendirian', $data);
        $this->assertArrayNotHasKey('id_pleno_pendirian', $data);
        $this->assertArrayNotHasKey('id_periode_operasi', $data);
        $this->assertArrayNotHasKey('dihapus_pada', $data);
        $this->assertArrayNotHasKey('status_alur', $data);
    }

    // ─────────────────────────────────────────────────────────
    // 3. Collection — daftar & meta
    // ─────────────────────────────────────────────────────────

    public function test_index_returns_collection_with_meta()
    {
        $user = $this->mockUser('pcnu', 'pcnu', 1);
        $insiden = $this->createInsiden(1);

        OperasiPosaju::create(['alamat_lokasi' => 'Alamat Default', 'id_insiden' => $insiden->id_insiden, 'nama_posaju' => 'Pos 1', 'pj_posaju' => $user->id_pengguna]);
        OperasiPosaju::create(['alamat_lokasi' => 'Alamat Default', 'id_insiden' => $insiden->id_insiden, 'nama_posaju' => 'Pos 2', 'pj_posaju' => $user->id_pengguna]);
        OperasiPosaju::create(['alamat_lokasi' => 'Alamat Default', 'id_insiden' => $insiden->id_insiden, 'nama_posaju' => 'Pos 3', 'pj_posaju' => $user->id_pengguna]);

        $response = $this->actingAs($user)->getJson(route('api.operasi.posaju.index'));

        $response->assertStatus(200)
                 
                 ->assertJsonPath('meta.total', 3)
                 ->assertJsonCount(3, 'data');
    }

    // ─────────────────────────────────────────────────────────
    // 4. Conditional field — alasan_penutupan
    // ─────────────────────────────────────────────────────────

    public function test_alasan_penutupan_only_shown_when_ditutup()
    {
        $user = $this->mockUser('pcnu', 'pcnu', 1);
        $insiden = $this->createInsiden(1);

        // Posaju aktif — alasan_penutupan TIDAK boleh tampil
        $posajuAktif = OperasiPosaju::create(['alamat_lokasi' => 'Alamat Default', 'id_insiden' => $insiden->id_insiden,
            'nama_posaju' => 'Pos Aktif',
            'pj_posaju' => $user->id_pengguna,
            'status_alur' => 'aktif',
            'alasan_penutupan' => 'Seharusnya tersembunyi',
        ]);

        $responseAktif = $this->actingAs($user)->getJson(route('api.operasi.posaju.show', $posajuAktif));
        $this->assertArrayNotHasKey('alasan_penutupan', $responseAktif->json('data'));

        // Posaju ditutup — alasan_penutupan HARUS tampil
        $posajuTutup = OperasiPosaju::create(['alamat_lokasi' => 'Alamat Default', 'id_insiden' => $insiden->id_insiden,
            'nama_posaju' => 'Pos Ditutup',
            'pj_posaju' => $user->id_pengguna,
            'status_alur' => 'ditutup',
            'alasan_penutupan' => 'Situasi aman',
        ]);

        $responseTutup = $this->actingAs($user)->getJson(route('api.operasi.posaju.show', $posajuTutup));
        $responseTutup;
    }
}
