<?php

namespace Tests\Feature\Relawan;

use App\Models\AuthRole;
use App\Models\AuthUser;
use App\Models\OperasiInsiden;
use App\Models\OperasiPosaju;
use App\Models\RelawanKebutuhan;
use App\Models\RelawanPendaftaran;
use App\Models\RelawanPenugasan;
use App\Http\Resources\Relawan\RelawanPenugasanResource;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\Support\CreatesOperasiSchema;
use Tests\Support\CreatesRelawanSchema;
use Tests\TestCase;

class RelawanPenugasanResourceTest extends TestCase
{
    use DatabaseTransactions, CreatesOperasiSchema, CreatesRelawanSchema;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createOperasiSchema();
        $this->createRelawanSchema();
    }

    private function mockUser(string $role): AuthUser
    {
        $roleModel = AuthRole::create(['nama_peran' => $role, 'level_otoritas' => 4]);
        return AuthUser::forceCreate([
            'no_hp' => '08' . rand(100000000, 999999999), 'kata_sandi' => 'hash', 'id_peran' => $roleModel->id_peran,
            'default_scope_type' => 'pcnu',
            'default_scope_id' => 1,
            'status_akun' => 'aktif',
        ]);
    }

    public function test_penugasan_resource_json_format()
    {
        $admin = $this->mockUser('pcnu');
        $relawan = $this->mockUser('relawan');
        
        DB::table('organisasi_unit')->insertOrIgnore(['id_unit' => 1, 'parent_id' => 99, 'nama_unit' => 'PCNU 1', 'tipe_unit' => 'pcnu']);
        DB::table('organisasi_pcnu')->insertOrIgnore(['id_pcnu' => 1, 'id_unit' => 1, 'nama_pcnu' => 'PCNU 1']);
        DB::table('bencana_master_jenis')->insertOrIgnore(['id_jenis' => 1, 'nama_bencana' => 'Banjir', 'slug' => 'banjir']);
        
        $insiden = OperasiInsiden::forceCreate([
            'id_pcnu' => 1,
            'kode_kejadian' => 'INS-REP-2',
            'id_jenis_bencana' => 1,
            'waktu_mulai' => now(),
        ]);

        $posaju = OperasiPosaju::create(['alamat_lokasi' => 'Alamat Default', 'id_insiden' => $insiden->id_insiden,
            'pj_posaju' => $admin->id_pengguna,
            'nama_posaju' => 'Pos Aju TRC',
            'status_alur' => 'aktif',
        ]);

        $kebutuhan = RelawanKebutuhan::create(['deskripsi_tugas' => 'Deskripsi Default', 'judul_posisi' => 'Default Posisi', 'id_insiden' => $insiden->id_insiden,
            'judul_posisi' => 'Evakuasi',
            'jumlah_dibutuhkan' => 5,
        ]);

        $pendaftaran = RelawanPendaftaran::create([
            'id_relawan_kebutuhan' => $kebutuhan->id_relawan_kebutuhan,
            'id_pengguna' => $relawan->id_pengguna,
            'status_pendaftaran' => 'ditugaskan',
        ]);

        $penugasan = RelawanPenugasan::create([
            'id_pendaftaran' => $pendaftaran->id_pendaftaran,
            'id_posaju' => $posaju->id_posaju,
            'peran_lapangan' => 'Rescue 1',
            'status_aktif' => true,
            'tgl_mulai_aktif' => '2026-06-17',
        ]);

        $penugasan->load(['posaju']);

        $resource = new RelawanPenugasanResource($penugasan);
        $responseArray = $resource->toArray(request());

        $this->assertEquals($penugasan->id_penugasan_relawan, $responseArray['id']);
        $this->assertEquals('Rescue 1', $responseArray['peran']);
        $this->assertTrue($responseArray['status_aktif']);
        $this->assertEquals('2026-06-17', $responseArray['tgl_mulai']);
        
        // Assert anti-corruption layer hiding database field names
        $this->assertArrayNotHasKey('id_penugasan_relawan', $responseArray);
        $this->assertArrayNotHasKey('peran_lapangan', $responseArray);
        $this->assertArrayNotHasKey('tgl_mulai_aktif', $responseArray);
        $this->assertArrayNotHasKey('dihapus_pada', $responseArray);
    }
}
