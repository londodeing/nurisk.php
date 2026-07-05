<?php

namespace Tests\Feature\Insiden;

use Tests\TestCase;
use App\Models\AuthUser;
use App\Models\AuthRole;
use App\Models\BencanaMasterJenis;
use App\Models\OrganisasiPcnu;
use App\Models\OrganisasiUnit;
use App\Models\OperasiInsiden;
use Database\Seeders\BencanaMasterJenisSeeder;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Schema;

class InsidenCrudTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Bangun skema dinamis untuk tabel master data & auth agar tidak terkendala foreign key
        Schema::disableForeignKeyConstraints();









        Schema::enableForeignKeyConstraints();

        // 2. Seeding
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

    // === INDEX ===

    public function test_super_admin_dapat_mengakses_index_insiden(): void
    {
        $user = $this->buatUserDenganRole('super_admin');
        $response = $this->actingAs($user)->get(route('insiden.index'));
        $response->assertStatus(200);
    }

    public function test_pcnu_dapat_mengakses_index_insiden(): void
    {
        $user = $this->buatUserDenganRole('pcnu', 'pcnu', 1);
        $response = $this->actingAs($user)->get(route('insiden.index'));
        $response->assertStatus(200);
    }

    public function test_pcnu_hanya_melihat_insiden_milik_pcnu_sendiri(): void
    {
        // Setup PCNU
        $pcnu1 = OrganisasiPcnu::create(['id_pcnu' => 10, 'id_unit' => 1, 'nama_pcnu' => 'PCNU Cilacap']);
        $pcnu2 = OrganisasiPcnu::create(['id_pcnu' => 11, 'id_unit' => 2, 'nama_pcnu' => 'PCNU Banyumas']);

        // Buat insiden di masing-masing PCNU
        $insiden1 = OperasiInsiden::factory()->create(['id_pcnu' => $pcnu1->id_pcnu, 'id_jenis_bencana' => 1]);
        $insiden2 = OperasiInsiden::factory()->create(['id_pcnu' => $pcnu2->id_pcnu, 'id_jenis_bencana' => 1]);

        $user = $this->buatUserDenganRole('pcnu', 'pcnu', $pcnu1->id_pcnu);

        $response = $this->actingAs($user)->get(route('insiden.index'));
        $response->assertStatus(200);

        // Harus melihat insiden Cilacap, tapi tidak melihat Banyumas
        $response->assertSee($insiden1->kode_kejadian);
        $response->assertDontSee($insiden2->kode_kejadian);
    }

    public function test_relawan_diblokir_dari_index_insiden(): void
    {
        $user = $this->buatUserDenganRole('relawan');
        $response = $this->actingAs($user)->get(route('insiden.index'));
        $response->assertStatus(403);
    }

    public function test_guest_diredirect_ke_login(): void
    {
        $response = $this->get(route('insiden.index'));
        $response->assertRedirect('/login');
    }

    public function test_filter_status_bekerja(): void
    {
        $pcnu = OrganisasiPcnu::create(['id_pcnu' => 10, 'id_unit' => 1, 'nama_pcnu' => 'PCNU Cilacap']);
        $insidenDraft = OperasiInsiden::factory()->create(['id_pcnu' => $pcnu->id_pcnu, 'status_insiden' => 'draft', 'id_jenis_bencana' => 1]);
        $insidenRespon = OperasiInsiden::factory()->create(['id_pcnu' => $pcnu->id_pcnu, 'status_insiden' => 'respon', 'id_jenis_bencana' => 1]);

        $user = $this->buatUserDenganRole('super_admin');

        $response = $this->actingAs($user)->get(route('insiden.index', ['status' => 'respon']));
        $response->assertStatus(200);
        $response->assertSee($insidenRespon->kode_kejadian);
        $response->assertDontSee($insidenDraft->kode_kejadian);
    }

    // === STORE ===

    public function test_super_admin_berhasil_store_insiden_baru(): void
    {
        $pcnu = OrganisasiPcnu::create(['id_pcnu' => 10, 'id_unit' => 1, 'nama_pcnu' => 'PCNU Cilacap']);
        $user = $this->buatUserDenganRole('super_admin');

        $response = $this->actingAs($user)->post(route('insiden.store'), [
            'kode_kejadian'    => 'INS-TEST-123',
            'id_jenis_bencana' => 1,
            'id_pcnu'          => $pcnu->id_pcnu,
            'prioritas'        => 'tinggi',
            'waktu_mulai'      => now()->format('Y-m-d H:i:s'),
        ]);

        $insiden = OperasiInsiden::where('kode_kejadian', 'INS-TEST-123')->first();
        $this->assertNotNull($insiden);
        $response->assertRedirect(route('insiden.show', $insiden));
    }

    public function test_pcnu_berhasil_store_insiden_untuk_pcnu_scopenya(): void
    {
        $pcnu = OrganisasiPcnu::create(['id_pcnu' => 10, 'id_unit' => 1, 'nama_pcnu' => 'PCNU Cilacap']);
        $user = $this->buatUserDenganRole('pcnu', 'pcnu', $pcnu->id_pcnu);

        $response = $this->actingAs($user)->post(route('insiden.store'), [
            'kode_kejadian'    => 'INS-TEST-PCNU',
            'id_jenis_bencana' => 1,
            'id_pcnu'          => $pcnu->id_pcnu,
            'prioritas'        => 'sedang',
            'waktu_mulai'      => now()->format('Y-m-d H:i:s'),
        ]);

        $insiden = OperasiInsiden::where('kode_kejadian', 'INS-TEST-PCNU')->first();
        $this->assertNotNull($insiden);
        $response->assertRedirect(route('insiden.show', $insiden));
    }

    public function test_store_gagal_jika_id_jenis_bencana_tidak_ada(): void
    {
        $pcnu = OrganisasiPcnu::create(['id_pcnu' => 10, 'id_unit' => 1, 'nama_pcnu' => 'PCNU Cilacap']);
        $user = $this->buatUserDenganRole('super_admin');

        $response = $this->actingAs($user)->post(route('insiden.store'), [
            'id_jenis_bencana' => 999,
            'id_pcnu'          => $pcnu->id_pcnu,
            'waktu_mulai'      => now()->format('Y-m-d H:i:s'),
        ]);

        $response->assertSessionHasErrors('id_jenis_bencana');
    }

    public function test_store_gagal_jika_kode_kejadian_duplikat(): void
    {
        $pcnu = OrganisasiPcnu::create(['id_pcnu' => 10, 'id_unit' => 1, 'nama_pcnu' => 'PCNU Cilacap']);
        OperasiInsiden::factory()->create(['kode_kejadian' => 'INS-DUPLIKAT', 'id_pcnu' => $pcnu->id_pcnu, 'id_jenis_bencana' => 1]);

        $user = $this->buatUserDenganRole('super_admin');
        $response = $this->actingAs($user)->post(route('insiden.store'), [
            'kode_kejadian'    => 'INS-DUPLIKAT',
            'id_jenis_bencana' => 1,
            'id_pcnu'          => $pcnu->id_pcnu,
            'waktu_mulai'      => now()->format('Y-m-d H:i:s'),
        ]);

        $response->assertSessionHasErrors('kode_kejadian');
    }

    public function test_store_gagal_jika_waktu_mulai_di_masa_depan(): void
    {
        $pcnu = OrganisasiPcnu::create(['id_pcnu' => 10, 'id_unit' => 1, 'nama_pcnu' => 'PCNU Cilacap']);
        $user = $this->buatUserDenganRole('super_admin');

        $response = $this->actingAs($user)->post(route('insiden.store'), [
            'id_jenis_bencana' => 1,
            'id_pcnu'          => $pcnu->id_pcnu,
            'waktu_mulai'      => now()->addDays(5)->format('Y-m-d H:i:s'),
        ]);

        $response->assertSessionHasErrors('waktu_mulai');
    }

    // === UPDATE ===

    public function test_super_admin_berhasil_update_insiden_tidak_terkunci(): void
    {
        $pcnu = OrganisasiPcnu::create(['id_pcnu' => 10, 'id_unit' => 1, 'nama_pcnu' => 'PCNU Cilacap']);
        $insiden = OperasiInsiden::factory()->create(['id_pcnu' => $pcnu->id_pcnu, 'id_jenis_bencana' => 1, 'is_locked' => false]);

        $user = $this->buatUserDenganRole('super_admin');
        $response = $this->actingAs($user)->put(route('insiden.update', $insiden), [
            'kode_kejadian'    => 'INS-UPDATED-OK',
            'id_jenis_bencana' => 2,
            'waktu_mulai'      => now()->format('Y-m-d H:i:s'),
        ]);

        $response->assertRedirect(route('insiden.show', $insiden));
        $this->assertEquals('INS-UPDATED-OK', $insiden->fresh()->kode_kejadian);
    }

    public function test_tidak_bisa_update_insiden_terkunci(): void
    {
        $pcnu = OrganisasiPcnu::create(['id_pcnu' => 10, 'id_unit' => 1, 'nama_pcnu' => 'PCNU Cilacap']);
        $insiden = OperasiInsiden::factory()->create(['id_pcnu' => $pcnu->id_pcnu, 'id_jenis_bencana' => 1, 'is_locked' => true]);

        $user = $this->buatUserDenganRole('super_admin');
        $response = $this->actingAs($user)->put(route('insiden.update', $insiden), [
            'kode_kejadian'    => 'INS-UPDATED-FAIL',
            'id_jenis_bencana' => 2,
            'waktu_mulai'      => now()->format('Y-m-d H:i:s'),
        ]);

        $response->assertStatus(403);
    }

    public function test_pcnu_tidak_bisa_update_insiden_pcnu_lain(): void
    {
        $pcnu1 = OrganisasiPcnu::create(['id_pcnu' => 10, 'id_unit' => 1, 'nama_pcnu' => 'PCNU Cilacap']);
        $pcnu2 = OrganisasiPcnu::create(['id_pcnu' => 11, 'id_unit' => 2, 'nama_pcnu' => 'PCNU Banyumas']);

        $insiden = OperasiInsiden::factory()->create(['id_pcnu' => $pcnu2->id_pcnu, 'id_jenis_bencana' => 1, 'is_locked' => false]);

        $user = $this->buatUserDenganRole('pcnu', 'pcnu', $pcnu1->id_pcnu);
        $response = $this->actingAs($user)->put(route('insiden.update', $insiden), [
            'kode_kejadian'    => 'INS-FAIL',
            'id_jenis_bencana' => 2,
            'waktu_mulai'      => now()->format('Y-m-d H:i:s'),
        ]);

        $response->assertStatus(403);
    }

    // === DELETE ===

    public function test_super_admin_berhasil_soft_delete_insiden(): void
    {
        $pcnu = OrganisasiPcnu::create(['id_pcnu' => 10, 'id_unit' => 1, 'nama_pcnu' => 'PCNU Cilacap']);
        $insiden = OperasiInsiden::factory()->create(['id_pcnu' => $pcnu->id_pcnu, 'id_jenis_bencana' => 1]);

        $user = $this->buatUserDenganRole('super_admin');
        $response = $this->actingAs($user)->delete(route('insiden.destroy', $insiden));

        $response->assertRedirect(route('insiden.index'));
        $this->assertSoftDeleted($insiden);
    }

    public function test_pcnu_tidak_bisa_delete(): void
    {
        $pcnu = OrganisasiPcnu::create(['id_pcnu' => 10, 'id_unit' => 1, 'nama_pcnu' => 'PCNU Cilacap']);
        $insiden = OperasiInsiden::factory()->create(['id_pcnu' => $pcnu->id_pcnu, 'id_jenis_bencana' => 1]);

        $user = $this->buatUserDenganRole('pcnu', 'pcnu', $pcnu->id_pcnu);
        $response = $this->actingAs($user)->delete(route('insiden.destroy', $insiden));

        $response->assertStatus(403);
    }
}
