<?php

namespace Tests\Feature\Relawan;

use App\Models\AuthRole;
use App\Models\AuthUser;
use App\Models\OperasiInsiden;
use App\Models\RelawanKebutuhan;
use App\Models\RelawanPendaftaran;
use App\Http\Resources\Relawan\RelawanPendaftaranResource;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\Support\CreatesOperasiSchema;
use Tests\Support\CreatesRelawanSchema;
use Tests\TestCase;

class RelawanPendaftaranResourceTest extends TestCase
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

    public function test_pendaftaran_resource_json_format()
    {
        $user = $this->mockUser('pcnu');
        $relawan = $this->mockUser('relawan');
        
        DB::table('organisasi_unit')->insertOrIgnore(['id_unit' => 1, 'parent_id' => 99, 'nama_unit' => 'PCNU 1', 'tipe_unit' => 'pcnu']);
        DB::table('organisasi_pcnu')->insertOrIgnore(['id_pcnu' => 1, 'id_unit' => 1, 'nama_pcnu' => 'PCNU 1']);
        DB::table('bencana_master_jenis')->insertOrIgnore(['id_jenis' => 1, 'nama_bencana' => 'Banjir', 'slug' => 'banjir']);
        
        $insiden = OperasiInsiden::forceCreate([
            'id_pcnu' => 1,
            'kode_kejadian' => 'INS-REP-1',
            'id_jenis_bencana' => 1,
            'waktu_mulai' => now(),
        ]);

        $kebutuhan = RelawanKebutuhan::create(['deskripsi_tugas' => 'Deskripsi Default', 'judul_posisi' => 'Default Posisi', 'id_insiden' => $insiden->id_insiden,
            'judul_posisi' => 'Evakuasi',
            'jumlah_dibutuhkan' => 5,
        ]);

        $pendaftaran = RelawanPendaftaran::create([
            'id_relawan_kebutuhan' => $kebutuhan->id_relawan_kebutuhan,
            'id_pengguna' => $relawan->id_pengguna,
            'motivasi_singkat' => 'Bantu sesama',
            'status_pendaftaran' => 'seleksi',
        ]);

        // Eager load relations
        $pendaftaran->load(['kebutuhan', 'relawan.profil']);

        $resource = new RelawanPendaftaranResource($pendaftaran);
        $responseArray = $resource->toArray(request());

        $this->assertEquals($pendaftaran->id_pendaftaran, $responseArray['id']);
        $this->assertEquals('seleksi', $responseArray['status']);
        $this->assertEquals('Bantu sesama', $responseArray['motivasi']);
        
        // Assert anti-corruption layer hiding database field names
        $this->assertArrayNotHasKey('id_pendaftaran', $responseArray);
        $this->assertArrayNotHasKey('status_pendaftaran', $responseArray);
        $this->assertArrayNotHasKey('motivasi_singkat', $responseArray);
        $this->assertArrayNotHasKey('dihapus_pada', $responseArray);
    }
}
