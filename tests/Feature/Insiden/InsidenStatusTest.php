<?php

namespace Tests\Feature\Insiden;

use Tests\TestCase;
use App\Models\AuthUser;
use App\Models\AuthRole;
use App\Models\BencanaMasterJenis;
use App\Models\OrganisasiPcnu;
use App\Models\OperasiInsiden;
use App\Models\RiwayatStatusInsiden;
use App\Services\InsidenService;
use Database\Seeders\BencanaMasterJenisSeeder;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Schema;

class InsidenStatusTest extends TestCase
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

    // === UBAH STATUS ===

    public function test_super_admin_berhasil_mengubah_status_dari_draft_ke_terverifikasi(): void
    {
        $pcnu = OrganisasiPcnu::create(['id_pcnu' => 10, 'id_unit' => 1, 'nama_pcnu' => 'PCNU Cilacap']);
        $insiden = OperasiInsiden::factory()->create([
            'id_pcnu' => $pcnu->id_pcnu,
            'id_jenis_bencana' => 1,
            'status_insiden' => 'draft',
            'waktu_verifikasi' => null
        ]);

        $user = $this->buatUserDenganRole('super_admin');

        $response = $this->actingAs($user)->put(route('insiden.status.update', $insiden), [
            'status_baru' => 'terverifikasi',
            'alasan' => 'Sudah dicek di lapangan',
        ]);

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();

        $insidenUpdated = $insiden->fresh();
        $this->assertEquals('terverifikasi', $insidenUpdated->status_insiden);
        $this->assertNotNull($insidenUpdated->waktu_verifikasi);

        // Riwayat status terbuat
        $this->assertDatabaseHas('riwayat_status_insiden', [
            'id_insiden' => $insiden->id_insiden,
            'status_sebelumnya' => 'draft',
            'status_terbaru' => 'terverifikasi',
            'id_pengguna' => $user->id_pengguna,
            'alasan' => 'Sudah dicek di lapangan'
        ]);
    }

    public function test_mengubah_status_ke_selesai_otomatis_mengunci_dan_mengisi_waktu_ditutup(): void
    {
        $pcnu = OrganisasiPcnu::create(['id_pcnu' => 10, 'id_unit' => 1, 'nama_pcnu' => 'PCNU Cilacap']);
        $insiden = OperasiInsiden::factory()->create([
            'id_pcnu' => $pcnu->id_pcnu,
            'id_jenis_bencana' => 1,
            'status_insiden' => 'respon',
            'is_locked' => false,
        ]);

        $user = $this->buatUserDenganRole('super_admin');

        $response = $this->actingAs($user)->put(route('insiden.status.update', $insiden), [
            'status_baru' => 'selesai',
            'alasan' => 'Operasi selesai sepenuhnya',
        ]);

        $response->assertRedirect();
        $insidenUpdated = $insiden->fresh();
        $this->assertEquals('selesai', $insidenUpdated->status_insiden);
        $this->assertTrue($insidenUpdated->isTerkunci());
        $this->assertNotNull($insidenUpdated->waktu_ditutup);
        $this->assertNotNull($insidenUpdated->waktu_selesai);
    }

    public function test_setelah_is_locked_true_tidak_bisa_ubah_status_lagi(): void
    {
        $pcnu = OrganisasiPcnu::create(['id_pcnu' => 10, 'id_unit' => 1, 'nama_pcnu' => 'PCNU Cilacap']);
        $insiden = OperasiInsiden::factory()->create([
            'id_pcnu' => $pcnu->id_pcnu,
            'id_jenis_bencana' => 1,
            'status_insiden' => 'selesai',
            'is_locked' => true,
        ]);

        $user = $this->buatUserDenganRole('super_admin');

        $response = $this->actingAs($user)->put(route('insiden.status.update', $insiden), [
            'status_baru' => 'dibatalkan',
            'alasan' => 'Coba ubah status',
        ]);

        // Terblokir di level Policy (ubahStatus)
        $response->assertStatus(403);
    }

    public function test_pcnu_tidak_bisa_ubah_status_insiden_pcnu_lain(): void
    {
        $pcnu1 = OrganisasiPcnu::create(['id_pcnu' => 10, 'id_unit' => 1, 'nama_pcnu' => 'PCNU Cilacap']);
        $pcnu2 = OrganisasiPcnu::create(['id_pcnu' => 11, 'id_unit' => 2, 'nama_pcnu' => 'PCNU Banyumas']);

        $insiden = OperasiInsiden::factory()->create([
            'id_pcnu' => $pcnu2->id_pcnu,
            'id_jenis_bencana' => 1,
            'status_insiden' => 'draft',
        ]);

        $user = $this->buatUserDenganRole('pcnu', 'pcnu', $pcnu1->id_pcnu);

        $response = $this->actingAs($user)->put(route('insiden.status.update', $insiden), [
            'status_baru' => 'terverifikasi',
        ]);

        $response->assertStatus(403);
    }

    public function test_relawan_tidak_bisa_ubah_status(): void
    {
        $pcnu = OrganisasiPcnu::create(['id_pcnu' => 10, 'id_unit' => 1, 'nama_pcnu' => 'PCNU Cilacap']);
        $insiden = OperasiInsiden::factory()->create([
            'id_pcnu' => $pcnu->id_pcnu,
            'id_jenis_bencana' => 1,
            'status_insiden' => 'draft',
        ]);

        $user = $this->buatUserDenganRole('relawan');

        $response = $this->actingAs($user)->put(route('insiden.status.update', $insiden), [
            'status_baru' => 'terverifikasi',
        ]);

        $response->assertStatus(403);
    }

    // === LARAVEL-LEVEL TRIGGER REPLICATION ===

    public function test_insiden_service_melempar_exception_jika_waktu_selesai_lebih_awal_dari_mulai(): void
    {
        $pcnu = OrganisasiPcnu::create(['id_pcnu' => 10, 'id_unit' => 1, 'nama_pcnu' => 'PCNU Cilacap']);
        $insiden = OperasiInsiden::factory()->create([
            'id_pcnu' => $pcnu->id_pcnu,
            'id_jenis_bencana' => 1,
            'waktu_mulai' => '2026-06-16 10:00:00',
            'waktu_selesai' => null,
        ]);

        $service = app(InsidenService::class);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Temporal Error: Waktu selesai tidak boleh sebelum waktu mulai!');

        $service->updateInsiden($insiden, [
            'waktu_selesai' => '2026-06-16 09:00:00', // Lebih awal dari waktu_mulai
        ]);
    }

    public function test_insiden_service_melempar_exception_jika_insiden_terkunci_diupdate(): void
    {
        $pcnu = OrganisasiPcnu::create(['id_pcnu' => 10, 'id_unit' => 1, 'nama_pcnu' => 'PCNU Cilacap']);
        $insiden = OperasiInsiden::factory()->create([
            'id_pcnu' => $pcnu->id_pcnu,
            'id_jenis_bencana' => 1,
            'is_locked' => true,
        ]);

        $service = app(InsidenService::class);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Data Terkunci: Insiden ini sudah Closed dan tidak boleh diubah lagi.');

        $service->updateInsiden($insiden, [
            'prioritas' => 'kritis',
        ]);
    }
}
