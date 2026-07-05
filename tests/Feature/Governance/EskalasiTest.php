<?php

namespace Tests\Feature\Governance;

use Tests\TestCase;
use App\Models\AuthUser;
use App\Models\AuthRole;
use App\Models\OrganisasiPcnu;
use App\Models\OrganisasiUnit;
use App\Models\OperasiInsiden;
use App\Models\OperasiPleno;
use Database\Seeders\BencanaMasterJenisSeeder;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Schema;

class EskalasiTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        Schema::disableForeignKeyConstraints();
        Schema::enableForeignKeyConstraints();

        AuthRole::insertOrIgnore([
            ['id_peran' => 1, 'nama_peran' => 'super_admin', 'level_otoritas' => 1],
            ['id_peran' => 2, 'nama_peran' => 'pwnu', 'level_otoritas' => 2],
            ['id_peran' => 3, 'nama_peran' => 'pcnu', 'level_otoritas' => 3],
            ['id_peran' => 4, 'nama_peran' => 'relawan', 'level_otoritas' => 4],
            ['id_peran' => 5, 'nama_peran' => 'publik', 'level_otoritas' => 5],
        ]);

        \Illuminate\Support\Facades\DB::table('organisasi_unit')->insertOrIgnore([
            ['id_unit' => 1, 'nama_unit' => 'Unit 1', 'tipe_unit' => 'pcnu'],
            ['id_unit' => 2, 'nama_unit' => 'Unit 2', 'tipe_unit' => 'pcnu'],
        ]);

        $this->seed(BencanaMasterJenisSeeder::class);
    }

    private function buatUserDenganRole(string $namaPeran, ?string $scopeType = null, ?int $scopeId = null): AuthUser
    {
        $peran = AuthRole::where('nama_peran', $namaPeran)->first();
        return AuthUser::factory()->create([
            'id_peran' => $peran->id_peran,
            'status_akun' => 'aktif',
            'default_scope_type' => $scopeType,
            'default_scope_id' => $scopeId,
        ]);
    }

    public function test_pwnu_dapat_eskalasi_insiden_dengan_pleno_valid(): void
    {
        $pcnu = OrganisasiPcnu::create(['id_pcnu' => 10, 'id_unit' => 1, 'nama_pcnu' => 'PCNU Cilacap']);
        $insiden = OperasiInsiden::factory()->create([
            'id_pcnu' => $pcnu->id_pcnu,
            'id_jenis_bencana' => 1,
            'is_locked' => false,
        ]);
        $user = $this->buatUserDenganRole('pwnu');
        $pleno = OperasiPleno::factory()->create([
            'id_insiden' => $insiden->id_insiden,
            'pimpinan_pleno' => $user->id_pengguna,
            'notulis_pleno' => $user->id_pengguna,
        ]);

        $response = $this->actingAs($user)->post(route('insiden.eskalasi.store', $insiden), [
            'id_pleno' => $pleno->id_pleno,
            'level_sebelumnya' => 'pcnu',
            'level_baru' => 'pwnu',
            'alasan_eskalasi' => 'Bencana meluas ke lintas kabupaten, perlu koordinasi PWNU.',
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('operasi_eskalasi', [
            'id_insiden' => $insiden->id_insiden,
            'level_sebelumnya' => 'pcnu',
            'level_baru' => 'pwnu',
        ]);
    }

    public function test_pcnu_tidak_dapat_eskalasi(): void
    {
        $pcnu = OrganisasiPcnu::create(['id_pcnu' => 10, 'id_unit' => 1, 'nama_pcnu' => 'PCNU Cilacap']);
        $insiden = OperasiInsiden::factory()->create([
            'id_pcnu' => $pcnu->id_pcnu,
            'id_jenis_bencana' => 1,
            'is_locked' => false,
        ]);
        $user = $this->buatUserDenganRole('pcnu', 'pcnu', $pcnu->id_pcnu);
        $pleno = OperasiPleno::factory()->create([
            'id_insiden' => $insiden->id_insiden,
            'pimpinan_pleno' => $user->id_pengguna,
            'notulis_pleno' => $user->id_pengguna,
        ]);

        $response = $this->actingAs($user)->post(route('insiden.eskalasi.store', $insiden), [
            'id_pleno' => $pleno->id_pleno,
            'level_sebelumnya' => 'lokal',
            'level_baru' => 'pcnu',
            'alasan_eskalasi' => 'Test eskalasi oleh PCNU.',
        ]);

        $response->assertStatus(403);
    }

    public function test_eskalasi_level_turun_ditolak(): void
    {
        $pcnu = OrganisasiPcnu::create(['id_pcnu' => 10, 'id_unit' => 1, 'nama_pcnu' => 'PCNU Cilacap']);
        $insiden = OperasiInsiden::factory()->create([
            'id_pcnu' => $pcnu->id_pcnu,
            'id_jenis_bencana' => 1,
            'is_locked' => false,
        ]);
        $user = $this->buatUserDenganRole('pwnu');
        $pleno = OperasiPleno::factory()->create([
            'id_insiden' => $insiden->id_insiden,
            'pimpinan_pleno' => $user->id_pengguna,
            'notulis_pleno' => $user->id_pengguna,
        ]);

        $response = $this->actingAs($user)->post(route('insiden.eskalasi.store', $insiden), [
            'id_pleno' => $pleno->id_pleno,
            'level_sebelumnya' => 'pwnu',
            'level_baru' => 'pcnu',
            'alasan_eskalasi' => 'Test eskalasi level turun.',
        ]);

        $response->assertSessionHas('error');
    }

    public function test_eskalasi_tanpa_pleno_insiden_yang_sama_ditolak(): void
    {
        $pcnu = OrganisasiPcnu::create(['id_pcnu' => 10, 'id_unit' => 1, 'nama_pcnu' => 'PCNU Cilacap']);
        $insiden1 = OperasiInsiden::factory()->create([
            'id_pcnu' => $pcnu->id_pcnu,
            'id_jenis_bencana' => 1,
            'is_locked' => false,
        ]);
        $insiden2 = OperasiInsiden::factory()->create([
            'id_pcnu' => $pcnu->id_pcnu,
            'id_jenis_bencana' => 1,
            'is_locked' => false,
        ]);
        $user = $this->buatUserDenganRole('pwnu');
        $pleno = OperasiPleno::factory()->create([
            'id_insiden' => $insiden2->id_insiden,
            'pimpinan_pleno' => $user->id_pengguna,
            'notulis_pleno' => $user->id_pengguna,
        ]);

        $response = $this->actingAs($user)->post(route('insiden.eskalasi.store', $insiden1), [
            'id_pleno' => $pleno->id_pleno,
            'level_sebelumnya' => 'pcnu',
            'level_baru' => 'pwnu',
            'alasan_eskalasi' => 'Test eskalasi dengan pleno insiden lain.',
        ]);

        $response->assertStatus(404);
    }

    public function test_eskalasi_level_sama_ditolak(): void
    {
        $pcnu = OrganisasiPcnu::create(['id_pcnu' => 10, 'id_unit' => 1, 'nama_pcnu' => 'PCNU Cilacap']);
        $insiden = OperasiInsiden::factory()->create([
            'id_pcnu' => $pcnu->id_pcnu,
            'id_jenis_bencana' => 1,
            'is_locked' => false,
        ]);
        $user = $this->buatUserDenganRole('pwnu');
        $pleno = OperasiPleno::factory()->create([
            'id_insiden' => $insiden->id_insiden,
            'pimpinan_pleno' => $user->id_pengguna,
            'notulis_pleno' => $user->id_pengguna,
        ]);

        $response = $this->actingAs($user)->post(route('insiden.eskalasi.store', $insiden), [
            'id_pleno' => $pleno->id_pleno,
            'level_sebelumnya' => 'pcnu',
            'level_baru' => 'pcnu',
            'alasan_eskalasi' => 'Test eskalasi level sama.',
        ]);

        $response->assertSessionHasErrors('level_baru');
    }

    public function test_validation_level_sama_fail(): void
    {
        $request = new \App\Http\Requests\Governance\StoreEskalasiRequest();
        $rules = $request->rules();

        $this->assertArrayHasKey('level_baru', $rules);
        $levelBaruRules = is_array($rules['level_baru']) ? $rules['level_baru'] : explode('|', $rules['level_baru']);
        $hasDifferent = false;
        foreach ($levelBaruRules as $rule) {
            $ruleStr = is_string($rule) ? $rule : (method_exists($rule, '__toString') ? $rule->__toString() : '');
            if (str_contains($ruleStr, 'different') || str_contains($ruleStr, 'different:level_sebelumnya')) {
                $hasDifferent = true;
                break;
            }
        }
        $this->assertTrue($hasDifferent, 'level_baru harus memiliki rule different:level_sebelumnya');
    }

    public function test_eskalasi_menggunakan_kategori_sistem(): void
    {
        if (!\Illuminate\Support\Facades\Schema::hasTable('operasi_jurnal')) {
            $this->markTestSkipped('Tabel operasi_jurnal belum tersedia.');
        }

        $pcnu = OrganisasiPcnu::create(['id_pcnu' => 10, 'id_unit' => 1, 'nama_pcnu' => 'PCNU Cilacap']);
        $insiden = OperasiInsiden::factory()->create([
            'id_pcnu' => $pcnu->id_pcnu,
            'id_jenis_bencana' => 1,
            'is_locked' => false,
        ]);
        $user = $this->buatUserDenganRole('pwnu');
        $pleno = OperasiPleno::factory()->create([
            'id_insiden' => $insiden->id_insiden,
            'pimpinan_pleno' => $user->id_pengguna,
            'notulis_pleno' => $user->id_pengguna,
        ]);

        $this->actingAs($user)->post(route('insiden.eskalasi.store', $insiden), [
            'id_pleno' => $pleno->id_pleno,
            'level_sebelumnya' => 'pcnu',
            'level_baru' => 'pwnu',
            'alasan_eskalasi' => 'Bencana meluas ke lintas kabupaten, perlu koordinasi PWNU.',
        ]);

        $this->assertDatabaseHas('operasi_jurnal', [
            'id_insiden' => $insiden->id_insiden,
            'id_pengguna' => $user->id_pengguna,
            'kategori_event' => 'sistem',
            'tabel_referensi' => 'operasi_pleno',
        ]);
    }
}
