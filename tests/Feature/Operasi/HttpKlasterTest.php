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

class HttpKlasterTest extends TestCase
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
            'kode_kejadian' => 'INS-' . uniqid(),
            'id_jenis_bencana' => 1,
            'waktu_mulai' => now()
        ]);
    }

    public function test_can_create_klaster_success()
    {
        $user = $this->mockUser('pcnu', 'pcnu', 1);
        $insiden = $this->createInsiden(1);
        $master = \App\Models\MasterKlaster::firstOrCreate(['nama_klaster' => 'Test Klaster', 'deskripsi' => 'Test']);

        
    $response = $this->actingAs($user)->postJson(route('api.operasi.klaster.store'), [
            'uuid_insiden' => $insiden->uuid_insiden,
            'id_master_klaster' => $master->id_master_klaster,
            'prioritas' => 'tinggi',
        ]);

        $response->assertStatus(201)
                 ->assertJsonPath('data.id', fn($id) => is_string($id))
                 ->assertJsonMissingPath('data.id_klaster')
                 ->assertJsonMissingPath('data.status_klaster');

        $this->assertDatabaseHas('operasi_klaster', [
            'id_insiden' => $insiden->id_insiden,
            'id_master_klaster' => $master->id_master_klaster,
            'status_klaster' => 'aktif' // Default db
        ]);
    }

    public function test_update_progress_success()
    {
        $user = $this->mockUser('pcnu', 'pcnu', 1);
        $insiden = $this->createInsiden(1);
        $klaster = OperasiKlaster::create([
            'id_insiden' => $insiden->id_insiden,
            'id_master_klaster' => 1,
            'prioritas' => 'tinggi',
            'status_klaster' => 'aktif',
            'progres_persen' => 0
        ]);

        $response = $this->actingAs($user)->postJson(route('api.operasi.klaster.progress', $klaster), [
            'progres_persen' => 50.5
        ]);

        $response->assertStatus(200)
                 ->assertJsonPath('data.progress', 50.5)
                 ->assertJsonMissingPath('data.progres_persen');

        $this->assertEquals(50.5, $klaster->fresh()->progres_persen);
    }

    public function test_complete_klaster_success()
    {
        $user = $this->mockUser('pcnu', 'pcnu', 1);
        $insiden = $this->createInsiden(1);
        $klaster = OperasiKlaster::create([
            'id_insiden' => $insiden->id_insiden,
            'id_master_klaster' => 1,
            'prioritas' => 'tinggi',
            'status_klaster' => 'aktif',
            'progres_persen' => 0
        ]);

        $response = $this->actingAs($user)->postJson(route('api.operasi.klaster.complete', $klaster));

        $response->assertStatus(200)
                 ->assertJsonPath('data.status', 'selesai')
                 ->assertJsonMissingPath('data.status_klaster');

        $this->assertEquals('selesai', $klaster->fresh()->status_klaster);
    }
}
