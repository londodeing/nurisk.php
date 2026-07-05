<?php

namespace Tests\Feature\Relawan;

use App\Models\AuthRole;
use App\Models\AuthUser;
use App\Models\AuthPenggunaProfil;
use App\Models\AuthKeahlianMaster;
use App\Http\Resources\Relawan\RelawanProfilResource;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Support\CreatesRelawanSchema;
use Tests\TestCase;

class RelawanProfilResourceTest extends TestCase
{
    use DatabaseTransactions, CreatesRelawanSchema, \Tests\Support\CreatesOperasiSchema;

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

    public function test_profil_resource_json_format()
    {
        $relawan = $this->mockUser('relawan');
        $profil = AuthPenggunaProfil::create([
            'id_pengguna' => $relawan->id_pengguna,
            'nik' => '3201010101010001',
            'nama_lengkap' => 'Ahmad Relawan',
            'email' => 'ahmad@relawan.nu',
            'id_desa_domisili' => '3201010001',
        ]);

        $skill = AuthKeahlianMaster::create([
            'nama_keahlian' => 'Navigasi',
            'deskripsi' => 'Nav darat',
        ]);

        $relawan->keahlian()->sync([$skill->id_keahlian]);
        $profil->load(['pengguna.keahlian']);

        $resource = new RelawanProfilResource($profil);
        $responseArray = $resource->toArray(request());

        $this->assertEquals($profil->id_pengguna, $responseArray['id']);
        $this->assertEquals('3201010101010001', $responseArray['nik']);
        $this->assertEquals('Ahmad Relawan', $responseArray['nama']);
        $this->assertEquals('ahmad@relawan.nu', $responseArray['email']);
        $this->assertEquals('3201010001', $responseArray['id_desa_domisili']);

        // Assert anti-corruption layer hiding database field names
        $this->assertArrayNotHasKey('nama_lengkap', $responseArray);
    }
}
