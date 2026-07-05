<?php

namespace Tests\Feature\Operasi;

use Tests\TestCase;
use Tests\Support\CreatesOperasiSchema;
use App\Models\OperasiTugas;
use App\Models\OperasiKlaster;
use App\Models\OperasiInsiden;
use App\Models\OperasiPosaju;
use App\Models\AuthUser;
use App\Models\AuthRole;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;

class TugasResourceTest extends TestCase
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
            'kode_kejadian' => 'INS-TG-001',
            'id_jenis_bencana' => 1,
            'waktu_mulai' => now(),
        ]);
    }

    private function createFixtures(AuthUser $user): array
    {
        $insiden = $this->createInsiden(1);

        $posaju = OperasiPosaju::create(['alamat_lokasi' => 'Alamat Default', 'id_insiden' => $insiden->id_insiden,
            'nama_posaju' => 'Pos Aju Pusat',
            'pj_posaju' => $user->id_pengguna,
        ]);

        $klaster = OperasiKlaster::create([
            'id_insiden' => $insiden->id_insiden,
            'id_master_klaster' => 1,
            'status_klaster' => 'aktif',
            'prioritas' => 'tinggi',
        ]);

        return compact('insiden', 'posaju', 'klaster');
    }

    // ─────────────────────────────────────────────────────────
    // 1. Struktur JSON — field yang HARUS tampil
    // ─────────────────────────────────────────────────────────

    public function test_show_returns_expected_json_structure()
    {
        $user = $this->mockUser('pcnu', 'pcnu', 1);
        ['klaster' => $klaster, 'posaju' => $posaju] = $this->createFixtures($user);

        $tugas = OperasiTugas::create([
            'id_operasi_klaster' => $klaster->id_klaster_operasi,
            'id_posaju' => $posaju->id_posaju,
            'ditugaskan_ke' => $user->id_pengguna,
            'judul_tugas' => 'Distribusi Air Bersih',
            'status_tugas' => 'rencana',
            'target_indikator' => '100 KK terlayani',
            'progres_persen' => 0,
        ]);

        $response = $this->actingAs($user)->getJson(route('api.operasi.tugas.show', $tugas));

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'data' => [
                         'id',
                         'judul',
                         'status',
                         'target_indikator',
                         'progress',
                         'klaster' => ['id', 'status', 'prioritas'],
                         'posaju' => ['id', 'nama'],
                         'pelaksana' => ['id'],
                         'dibuat_pada',
                     ],
                 ]);
    }

    public function test_show_returns_correct_field_values()
    {
        $user = $this->mockUser('pcnu', 'pcnu', 1);
        ['klaster' => $klaster, 'posaju' => $posaju] = $this->createFixtures($user);

        $tugas = OperasiTugas::create([
            'id_operasi_klaster' => $klaster->id_klaster_operasi,
            'id_posaju' => $posaju->id_posaju,
            'ditugaskan_ke' => $user->id_pengguna,
            'judul_tugas' => 'Evakuasi Warga',
            'status_tugas' => 'berjalan',
            'target_indikator' => '50 warga dievakuasi',
            'progres_persen' => 35.5,
        ]);

        $response = $this->actingAs($user)->getJson(route('api.operasi.tugas.show', $tugas));

        $response->assertStatus(200)
                 ->assertJsonPath('data.id', $tugas->id_tugas)
                 ->assertJsonPath('data.judul', 'Evakuasi Warga')
                 ->assertJsonPath('data.status', 'berjalan')
                 ->assertJsonPath('data.target_indikator', '50 warga dievakuasi')
                 ->assertJsonPath('data.progress', 35.5)
                 ->assertJsonPath('data.klaster.id', $klaster->id_klaster_operasi)
                 ->assertJsonPath('data.klaster.status', 'aktif')
                 ->assertJsonPath('data.klaster.prioritas', 'tinggi')
                 ->assertJsonPath('data.posaju.id', $posaju->id_posaju)
                 ->assertJsonPath('data.posaju.nama', 'Pos Aju Pusat')
                 ->assertJsonPath('data.pelaksana.id', $user->id_pengguna);
    }

    // ─────────────────────────────────────────────────────────
    // 2. Kolom sensitif — TIDAK BOLEH tampil
    // ─────────────────────────────────────────────────────────

    public function test_show_does_not_expose_internal_fields()
    {
        $user = $this->mockUser('pcnu', 'pcnu', 1);
        ['klaster' => $klaster, 'posaju' => $posaju] = $this->createFixtures($user);

        $tugas = OperasiTugas::create([
            'id_operasi_klaster' => $klaster->id_klaster_operasi,
            'id_posaju' => $posaju->id_posaju,
            'ditugaskan_ke' => $user->id_pengguna,
            'judul_tugas' => 'Tugas Rahasia',
            'status_tugas' => 'rencana',
        ]);

        $response = $this->actingAs($user)->getJson(route('api.operasi.tugas.show', $tugas));
        $data = $response->json('data');

        // Kolom internal yang harus disembunyikan
        $this->assertArrayNotHasKey('id_tugas', $data);
        $this->assertArrayNotHasKey('id_operasi_klaster', $data);
        $this->assertArrayNotHasKey('id_posaju', $data);
        $this->assertArrayNotHasKey('ditugaskan_ke', $data);
        $this->assertArrayNotHasKey('id_surat_perintah', $data);
        $this->assertArrayNotHasKey('judul_tugas', $data);
        $this->assertArrayNotHasKey('status_tugas', $data);
        $this->assertArrayNotHasKey('progres_persen', $data);
        $this->assertArrayNotHasKey('dihapus_pada', $data);
    }

    // ─────────────────────────────────────────────────────────
    // 3. Collection — daftar & meta
    // ─────────────────────────────────────────────────────────

    public function test_index_returns_collection_with_meta()
    {
        $user = $this->mockUser('pcnu', 'pcnu', 1);
        ['klaster' => $klaster] = $this->createFixtures($user);

        OperasiTugas::create(['id_operasi_klaster' => $klaster->id_klaster_operasi, 'judul_tugas' => 'Tugas A', 'status_tugas' => 'rencana']);
        OperasiTugas::create(['id_operasi_klaster' => $klaster->id_klaster_operasi, 'judul_tugas' => 'Tugas B', 'status_tugas' => 'berjalan']);
        OperasiTugas::create(['id_operasi_klaster' => $klaster->id_klaster_operasi, 'judul_tugas' => 'Tugas C', 'status_tugas' => 'selesai']);

        $response = $this->actingAs($user)->getJson(route('api.operasi.tugas.index'));

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'data' => [
                         '*' => ['id', 'judul', 'status', 'progress'],
                     ],
                     'meta' => ['total'],
                 ])
                 ->assertJsonPath('meta.total', 3)
                 ->assertJsonCount(3, 'data');
    }

    // ─────────────────────────────────────────────────────────
    // 4. Nested relation opsional — tanpa eager load
    // ─────────────────────────────────────────────────────────

    public function test_tugas_without_posaju_omits_posaju_field()
    {
        $user = $this->mockUser('pcnu', 'pcnu', 1);
        ['klaster' => $klaster] = $this->createFixtures($user);

        // Tugas tanpa posaju (nullable)
        $tugas = OperasiTugas::create([
            'id_operasi_klaster' => $klaster->id_klaster_operasi,
            'id_posaju' => null,
            'judul_tugas' => 'Tugas Tanpa Posaju',
            'status_tugas' => 'rencana',
        ]);

        $response = $this->actingAs($user)->getJson(route('api.operasi.tugas.show', $tugas));
        $data = $response->json('data');

        // posaju harus ada di response tapi nilainya null/absent karena relasi null
        $this->assertArrayNotHasKey('posaju', $data);
    }
}
