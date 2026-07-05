<?php

namespace Tests\Feature\Operasi;

use App\Models\AssessmentUtama;
use App\Models\AssessmentDampakManusia;
use App\Models\AssessmentKebutuhanMendesak;
use App\Models\AuthUser;
use App\Models\AuthRole;
use App\Models\OperasiInsiden;
use App\Models\OperasiPenugasan;
use App\Models\OperasiSitrep;
use App\Models\OrganisasiPcnu;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class SitrepTest extends TestCase
{
    use DatabaseTransactions;

    private function createAuthUserWithRole(string $roleName, ?int $scopeId = null): AuthUser
    {
        $role = AuthRole::firstOrCreate(['nama_peran' => $roleName], ['deskripsi' => 'Role', 'level_otoritas' => 1]);

        return AuthUser::factory()->aktif()->create([
            'id_peran' => $role->id_peran,
            'default_scope_id' => $scopeId
        ]);
    }

    public function test_api_store_sitrep_generates_immutable_snapshot()
    {
        $this->withoutExceptionHandling();
        $admin = $this->createAuthUserWithRole('super_admin');
        
        $insiden = OperasiInsiden::factory()->create(['status_insiden' => 'respon'])->refresh();
        
        // Buat Assessment Utama yang akan menjadi basis
        $assessment = AssessmentUtama::create([
            'id_insiden' => $insiden->id_insiden,
            'jenis_laporan' => 'kaji_cepat',
            'cakupan_wilayah_deskripsi' => 'Seluruh desa',
            'is_latest' => true,
            'waktu_assesment' => now(),
        ]);

        AssessmentDampakManusia::create([
            'id_assessment_utama' => $assessment->id_assessment_utama,
            'meninggal' => 5,
            'hilang' => 1,
            'luka_berat' => 10,
            'luka_ringan' => 50,
            'menderita_mengungsi' => 100,
        ]);

        AssessmentKebutuhanMendesak::create([
            'id_assessment_utama' => $assessment->id_assessment_utama,
            'nama_kebutuhan' => 'Tenda',
            'jumlah' => 20,
            'satuan' => 'Unit',
        ]);

        // Generate Sitrep
        $payload = [
            'uuid_insiden' => $insiden->uuid_insiden,
            'periode_sitrep' => 'Sore',
            'catatan' => 'Laporan sore'
        ];

        $response = $this->actingAs($admin)
            ->postJson(route('api.v1.sitrep.store'), $payload);

        $response->assertStatus(201)
                 ->assertJsonPath('data.periode_sitrep', 'Sore')
                 ->assertJsonPath('data.dampak.meninggal', 5)
                 ->assertJsonPath('data.kebutuhan.0.nama_kebutuhan', 'Tenda');

        $sitrep = OperasiSitrep::where('id_insiden', $insiden->id_insiden)->first();
        $this->assertNotNull($sitrep);
        $this->assertEquals(5, $sitrep->dampak->meninggal);
        $this->assertEquals('Tenda', $sitrep->kebutuhan->first()->nama_kebutuhan);

        // Uji Sifat Immutable Snapshot
        // Jika assessment awal diubah, data di sitrep tidak boleh berubah
        $assessment->dampakManusia->update(['meninggal' => 99]);

        $sitrep->refresh();
        $this->assertEquals(5, $sitrep->dampak->meninggal); // Harus tetap 5
    }

    public function test_lapis_4_auth_relawan_komandan_insiden_allowed_to_create_sitrep()
    {
        $insiden = OperasiInsiden::factory()->create(['status_insiden' => 'respon'])->refresh();
        $relawan = $this->createAuthUserWithRole('relawan');
        
        OperasiPenugasan::create([
            'id_insiden' => $insiden->id_insiden,
            'id_pengguna' => $relawan->id_pengguna,
            'peran_otoritas' => 'komandan_insiden',
            'waktu_mulai' => now(),
            'waktu_selesai' => null, // Masih aktif
            'ditugaskan_oleh' => $relawan->id_pengguna,
        ]);

        // Setup assessment
        $assessment = AssessmentUtama::create([
            'id_insiden' => $insiden->id_insiden,
            'jenis_laporan' => 'kaji_cepat',
            'cakupan_wilayah_deskripsi' => 'Desa A',
            'is_latest' => true,
            'waktu_assesment' => now(),
        ]);

        $response = $this->actingAs($relawan)
            ->postJson(route('api.v1.sitrep.store'), [
                'uuid_insiden' => $insiden->uuid_insiden,
                'periode_sitrep' => 'Malam'
            ]);

        $response->assertStatus(201);
    }

    public function test_lapis_4_auth_relawan_trc_denied_to_create_sitrep()
    {
        $insiden = OperasiInsiden::factory()->create(['status_insiden' => 'respon'])->refresh();
        $relawan = $this->createAuthUserWithRole('relawan');
        
        OperasiPenugasan::create([
            'id_insiden' => $insiden->id_insiden,
            'id_pengguna' => $relawan->id_pengguna,
            'peran_otoritas' => 'trc',
            'waktu_mulai' => now(),
            'waktu_selesai' => null,
            'ditugaskan_oleh' => $relawan->id_pengguna,
        ]);

        $assessment = AssessmentUtama::create([
            'id_insiden' => $insiden->id_insiden,
            'jenis_laporan' => 'kaji_cepat',
            'cakupan_wilayah_deskripsi' => 'Desa B',
            'is_latest' => true,
            'waktu_assesment' => now(),
        ]);

        $response = $this->actingAs($relawan)
            ->postJson(route('api.v1.sitrep.store'), [
                'uuid_insiden' => $insiden->uuid_insiden
            ]);

        $response->assertStatus(403);
    }
}
