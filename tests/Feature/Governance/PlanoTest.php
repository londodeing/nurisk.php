<?php

namespace Tests\Feature\Governance;

use Tests\TestCase;
use App\Models\AssessmentUtama;
use App\Models\AuthUser;
use App\Models\AuthRole;
use App\Models\BencanaMasterJenis;
use App\Models\OrganisasiPcnu;
use App\Models\OrganisasiUnit;
use App\Models\OperasiInsiden;
use App\Models\OperasiPleno;
use App\Models\OperasiPlenoPeserta;
use Database\Seeders\BencanaMasterJenisSeeder;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Schema;

class PlanoTest extends TestCase
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

    private function buatInsiden(int $idPcnu = 10): OperasiInsiden
    {
        return OperasiInsiden::factory()->create([
            'id_pcnu' => $idPcnu,
            'id_jenis_bencana' => 1,
            'status_insiden' => 'respon',
        ]);
    }

    private function buatAssessmentApproved(OperasiInsiden $insiden): AssessmentUtama
    {
        return AssessmentUtama::create([
            'id_insiden' => $insiden->id_insiden,
            'jenis_laporan' => 'kaji_cepat',
            'cakupan_wilayah_deskripsi' => 'Wilayah terdampak',
            'latitude' => -6.8,
            'longitude' => 110.8,
            'is_latest' => true,
            'status_review' => 'approved',
            'waktu_assesment' => now(),
        ]);
    }

    // === AKSES & OTORISASI ===

    public function test_pcnu_dapat_melihat_list_pleno_insidennya(): void
    {
        $pcnu = OrganisasiPcnu::create(['id_pcnu' => 10, 'id_unit' => 1, 'nama_pcnu' => 'PCNU Cilacap']);
        $insiden = $this->buatInsiden($pcnu->id_pcnu);

        $user = $this->buatUserDenganRole('pcnu', 'pcnu', $pcnu->id_pcnu);
        $response = $this->actingAs($user)->get(route('insiden.pleno.index', $insiden));
        $response->assertStatus(200);
    }

    public function test_relawan_diblokir_dari_halaman_pleno(): void
    {
        $pcnu = OrganisasiPcnu::create(['id_pcnu' => 10, 'id_unit' => 1, 'nama_pcnu' => 'PCNU Cilacap']);
        $insiden = $this->buatInsiden($pcnu->id_pcnu);

        $user = $this->buatUserDenganRole('relawan');
        $response = $this->actingAs($user)->get(route('insiden.pleno.index', $insiden));
        $response->assertStatus(403);
    }

    public function test_guest_diredirect_ke_login(): void
    {
        $pcnu = OrganisasiPcnu::create(['id_pcnu' => 10, 'id_unit' => 1, 'nama_pcnu' => 'PCNU Cilacap']);
        $insiden = $this->buatInsiden($pcnu->id_pcnu);

        $response = $this->get(route('insiden.pleno.index', $insiden));
        $response->assertRedirect('/login');
    }

    public function test_pcnu_tidak_dapat_melihat_pleno_insiden_pcnu_lain(): void
    {
        $pcnu1 = OrganisasiPcnu::create(['id_pcnu' => 10, 'id_unit' => 1, 'nama_pcnu' => 'PCNU Cilacap']);
        $pcnu2 = OrganisasiPcnu::create(['id_pcnu' => 11, 'id_unit' => 2, 'nama_pcnu' => 'PCNU Banyumas']);
        $insiden1 = $this->buatInsiden($pcnu1->id_pcnu);
        OperasiPleno::factory()->create([
            'id_insiden' => $insiden1->id_insiden,
        ]);

        $user = $this->buatUserDenganRole('pcnu', 'pcnu', $pcnu2->id_pcnu);
        $response = $this->actingAs($user)->get(route('insiden.pleno.index', $insiden1));
        $response->assertStatus(403);
    }

    // === STORE PLENO ===

    public function test_pcnu_dapat_membuat_pleno_dalam_scope(): void
    {
        $pcnu = OrganisasiPcnu::create(['id_pcnu' => 10, 'id_unit' => 1, 'nama_pcnu' => 'PCNU Cilacap']);
        $insiden = $this->buatInsiden($pcnu->id_pcnu);
        $this->buatAssessmentApproved($insiden);
        $pimpinan = $this->buatUserDenganRole('pcnu', 'pcnu', $pcnu->id_pcnu);
        $notulis = $this->buatUserDenganRole('pcnu', 'pcnu', $pcnu->id_pcnu);
        $user = $this->buatUserDenganRole('pcnu', 'pcnu', $pcnu->id_pcnu);

        $response = $this->actingAs($user)->post(route('insiden.pleno.store', $insiden), [
            'id_insiden' => $insiden->id_insiden,
            'nomor_pleno' => '001/PLENO/PCNU-CILACAP/VI/2026',
            'waktu_pleno' => now()->format('Y-m-d H:i:s'),
            'jenis_pleno' => 'evaluasi_rutin',
            'pimpinan_pleno' => $pimpinan->id_pengguna,
            'notulis_pleno' => $notulis->id_pengguna,
            'lokasi_pleno' => 'Posko Utama',
        ]);

        $this->assertDatabaseHas('operasi_pleno', ['nomor_pleno' => '001/PLENO/PCNU-CILACAP/VI/2026']);
        $response->assertSessionHas('success');
    }

    public function test_store_pleno_menggenerate_nomor_pleno_otomatis_jika_kosong(): void
    {
        $pcnu = OrganisasiPcnu::create(['id_pcnu' => 10, 'id_unit' => 1, 'nama_pcnu' => 'PCNU Cilacap']);
        $insiden = $this->buatInsiden($pcnu->id_pcnu);
        $this->buatAssessmentApproved($insiden);
        $pimpinan = $this->buatUserDenganRole('pcnu', 'pcnu', $pcnu->id_pcnu);
        $notulis = $this->buatUserDenganRole('pcnu', 'pcnu', $pcnu->id_pcnu);
        $user = $this->buatUserDenganRole('pcnu', 'pcnu', $pcnu->id_pcnu);

        $response = $this->actingAs($user)->post(route('insiden.pleno.store', $insiden), [
            'id_insiden' => $insiden->id_insiden,
            'nomor_pleno' => '',
            'waktu_pleno' => now()->format('Y-m-d H:i:s'),
            'jenis_pleno' => 'evaluasi_rutin',
            'pimpinan_pleno' => $pimpinan->id_pengguna,
            'notulis_pleno' => $notulis->id_pengguna,
            'lokasi_pleno' => 'Posko Utama',
        ]);

        $response->assertSessionHas('success');
        $pleno = OperasiPleno::where('id_insiden', $insiden->id_insiden)->first();
        $this->assertNotNull($pleno);
        $this->assertStringContainsString('/PLENO/', $pleno->nomor_pleno);
    }

    public function test_store_gagal_jika_id_insiden_tidak_ada(): void
    {
        $pcnu = OrganisasiPcnu::create(['id_pcnu' => 10, 'id_unit' => 1, 'nama_pcnu' => 'PCNU Cilacap']);
        $pimpinan = $this->buatUserDenganRole('pcnu', 'pcnu', $pcnu->id_pcnu);
        $notulis = $this->buatUserDenganRole('pcnu', 'pcnu', $pcnu->id_pcnu);
        $user = $this->buatUserDenganRole('super_admin');

        $response = $this->actingAs($user)->post(route('insiden.pleno.store', 99999), [
            'id_insiden' => 99999,
            'nomor_pleno' => 'TEST/PLENO/001',
            'waktu_pleno' => now()->format('Y-m-d H:i:s'),
            'jenis_pleno' => 'evaluasi_rutin',
            'pimpinan_pleno' => $pimpinan->id_pengguna,
            'notulis_pleno' => $notulis->id_pengguna,
        ]);

        $response->assertStatus(404);
    }

    public function test_store_gagal_jika_pimpinan_pleno_tidak_ada(): void
    {
        $pcnu = OrganisasiPcnu::create(['id_pcnu' => 10, 'id_unit' => 1, 'nama_pcnu' => 'PCNU Cilacap']);
        $insiden = $this->buatInsiden($pcnu->id_pcnu);
        $this->buatAssessmentApproved($insiden);
        $notulis = $this->buatUserDenganRole('pcnu', 'pcnu', $pcnu->id_pcnu);
        $user = $this->buatUserDenganRole('super_admin');

        $response = $this->actingAs($user)->post(route('insiden.pleno.store', $insiden), [
            'id_insiden' => $insiden->id_insiden,
            'nomor_pleno' => 'TEST/PLENO/001',
            'waktu_pleno' => now()->format('Y-m-d H:i:s'),
            'jenis_pleno' => 'evaluasi_rutin',
            'pimpinan_pleno' => 99999,
            'notulis_pleno' => $notulis->id_pengguna,
        ]);

        $response->assertSessionHasErrors('pimpinan_pleno');
    }

    // === TAMBAH KEPUTUSAN ===

    public function test_pwnu_dapat_tambah_keputusan_ke_pleno_draft(): void
    {
        $pcnu = OrganisasiPcnu::create(['id_pcnu' => 10, 'id_unit' => 1, 'nama_pcnu' => 'PCNU Cilacap']);
        $insiden = $this->buatInsiden($pcnu->id_pcnu);
        $user = $this->buatUserDenganRole('pwnu');
        $pleno = OperasiPleno::factory()->create([
            'id_insiden' => $insiden->id_insiden,
            'pimpinan_pleno' => $user->id_pengguna,
            'notulis_pleno' => $user->id_pengguna,
        ]);

        $response = $this->actingAs($user)->post(route('insiden.pleno.keputusan.store', [$insiden, $pleno]), [
            'kategori_objek' => 'insiden',
            'jenis_keputusan' => 'perubahan_status_insiden',
            'tipe_target_keputusan' => 'insiden',
            'deskripsi_keputusan' => 'Memperpanjang status tanggap darurat selama 7 hari ke depan.',
        ]);

        $response->assertSessionHas('success');
        $this->assertEquals(1, $pleno->keputusan()->count());
    }

    public function test_pcnu_tidak_dapat_tambah_keputusan(): void
    {
        $pcnu = OrganisasiPcnu::create(['id_pcnu' => 10, 'id_unit' => 1, 'nama_pcnu' => 'PCNU Cilacap']);
        $insiden = $this->buatInsiden($pcnu->id_pcnu);
        $user = $this->buatUserDenganRole('pcnu', 'pcnu', $pcnu->id_pcnu);
        $pleno = OperasiPleno::factory()->create([
            'id_insiden' => $insiden->id_insiden,
            'pimpinan_pleno' => $user->id_pengguna,
            'notulis_pleno' => $user->id_pengguna,
        ]);

        $response = $this->actingAs($user)->post(route('insiden.pleno.keputusan.store', [$insiden, $pleno]), [
            'kategori_objek' => 'insiden',
            'jenis_keputusan' => 'perubahan_status_insiden',
            'tipe_target_keputusan' => 'insiden',
            'deskripsi_keputusan' => 'Memperpanjang status tanggap darurat.',
        ]);

        $response->assertStatus(403);
    }

    // === VOTING PESERTA ===

    public function test_peserta_dapat_vote_setuju(): void
    {
        $pcnu = OrganisasiPcnu::create(['id_pcnu' => 10, 'id_unit' => 1, 'nama_pcnu' => 'PCNU Cilacap']);
        $insiden = $this->buatInsiden($pcnu->id_pcnu);
        $user = $this->buatUserDenganRole('pwnu');
        $pleno = OperasiPleno::factory()->create([
            'id_insiden' => $insiden->id_insiden,
            'pimpinan_pleno' => $user->id_pengguna,
            'notulis_pleno' => $user->id_pengguna,
        ]);

        $peserta = $pleno->peserta()->create([
            'id_pengguna' => $user->id_pengguna,
            'hak_suara' => true,
        ]);

        $response = $this->actingAs($user)->patch(route('insiden.pleno.peserta.vote', [$insiden, $pleno, $peserta]), [
            'status_persetujuan' => 'setuju',
        ]);

        $response->assertSessionHas('success');
        $this->assertEquals('setuju', $peserta->fresh()->status_persetujuan);
    }

    public function test_peserta_dapat_vote_tolak_dengan_catatan(): void
    {
        $pcnu = OrganisasiPcnu::create(['id_pcnu' => 10, 'id_unit' => 1, 'nama_pcnu' => 'PCNU Cilacap']);
        $insiden = $this->buatInsiden($pcnu->id_pcnu);
        $user = $this->buatUserDenganRole('pwnu');
        $pleno = OperasiPleno::factory()->create([
            'id_insiden' => $insiden->id_insiden,
            'pimpinan_pleno' => $user->id_pengguna,
            'notulis_pleno' => $user->id_pengguna,
        ]);
        $peserta = $pleno->peserta()->create([
            'id_pengguna' => $user->id_pengguna,
            'hak_suara' => true,
        ]);

        $response = $this->actingAs($user)->patch(route('insiden.pleno.peserta.vote', [$insiden, $pleno, $peserta]), [
            'status_persetujuan' => 'tolak',
            'catatan_peserta' => 'Data pendukung belum lengkap.',
        ]);

        $response->assertSessionHas('success');
        $this->assertEquals('tolak', $peserta->fresh()->status_persetujuan);
        $this->assertEquals('Data pendukung belum lengkap.', $peserta->fresh()->catatan_peserta);
    }

    public function test_tolak_tanpa_catatan_gagal_validasi(): void
    {
        $pcnu = OrganisasiPcnu::create(['id_pcnu' => 10, 'id_unit' => 1, 'nama_pcnu' => 'PCNU Cilacap']);
        $insiden = $this->buatInsiden($pcnu->id_pcnu);
        $user = $this->buatUserDenganRole('pwnu');
        $pleno = OperasiPleno::factory()->create([
            'id_insiden' => $insiden->id_insiden,
            'pimpinan_pleno' => $user->id_pengguna,
            'notulis_pleno' => $user->id_pengguna,
        ]);
        $peserta = $pleno->peserta()->create([
            'id_pengguna' => $user->id_pengguna,
            'hak_suara' => true,
        ]);

        $response = $this->actingAs($user)->patch(route('insiden.pleno.peserta.vote', [$insiden, $pleno, $peserta]), [
            'status_persetujuan' => 'tolak',
            'catatan_peserta' => '',
        ]);

        $response->assertSessionHasErrors('catatan_peserta');
    }

    public function test_tidak_dapat_vote_di_pleno_final(): void
    {
        $pcnu = OrganisasiPcnu::create(['id_pcnu' => 10, 'id_unit' => 1, 'nama_pcnu' => 'PCNU Cilacap']);
        $insiden = $this->buatInsiden($pcnu->id_pcnu);
        $user = $this->buatUserDenganRole('pwnu');
        $pleno = OperasiPleno::factory()->sudahFinal()->create([
            'id_insiden' => $insiden->id_insiden,
            'pimpinan_pleno' => $user->id_pengguna,
            'notulis_pleno' => $user->id_pengguna,
        ]);
        $peserta = $pleno->peserta()->create([
            'id_pengguna' => $user->id_pengguna,
            'hak_suara' => true,
        ]);

        $response = $this->actingAs($user)->patch(route('insiden.pleno.peserta.vote', [$insiden, $pleno, $peserta]), [
            'status_persetujuan' => 'setuju',
        ]);

        $response->assertSessionHas('error');
    }

    // === TRANSISI STATUS ===

    public function test_pwnu_dapat_ubah_status_draft_ke_ditinjau(): void
    {
        $pcnu = OrganisasiPcnu::create(['id_pcnu' => 10, 'id_unit' => 1, 'nama_pcnu' => 'PCNU Cilacap']);
        $insiden = $this->buatInsiden($pcnu->id_pcnu);
        $user = $this->buatUserDenganRole('pwnu');
        $pleno = OperasiPleno::factory()->create([
            'id_insiden' => $insiden->id_insiden,
            'pimpinan_pleno' => $user->id_pengguna,
            'notulis_pleno' => $user->id_pengguna,
            'status_pleno' => 'draft',
        ]);

        $response = $this->actingAs($user)->patch(route('insiden.pleno.tinjau', [$insiden, $pleno]));

        $response->assertSessionHas('success');
        $this->assertEquals('ditinjau', $pleno->fresh()->status_pleno);
    }

    public function test_tidak_dapat_finalisasi_dari_draft(): void
    {
        $pcnu = OrganisasiPcnu::create(['id_pcnu' => 10, 'id_unit' => 1, 'nama_pcnu' => 'PCNU Cilacap']);
        $insiden = $this->buatInsiden($pcnu->id_pcnu);
        $user = $this->buatUserDenganRole('pwnu');
        $pleno = OperasiPleno::factory()->create([
            'id_insiden' => $insiden->id_insiden,
            'pimpinan_pleno' => $user->id_pengguna,
            'notulis_pleno' => $user->id_pengguna,
            'status_pleno' => 'draft',
        ]);

        $response = $this->actingAs($user)->patch(route('insiden.pleno.finalisasi', [$insiden, $pleno]));

        $response->assertSessionHas('error');
    }

    public function test_pwnu_dapat_finalisasi_dari_ditinjau(): void
    {
        $pcnu = OrganisasiPcnu::create(['id_pcnu' => 10, 'id_unit' => 1, 'nama_pcnu' => 'PCNU Cilacap']);
        $insiden = $this->buatInsiden($pcnu->id_pcnu);
        $user = $this->buatUserDenganRole('pwnu');
        $pleno = OperasiPleno::factory()->sudahDitinjau()->create([
            'id_insiden' => $insiden->id_insiden,
            'pimpinan_pleno' => $user->id_pengguna,
            'notulis_pleno' => $user->id_pengguna,
        ]);

        $response = $this->actingAs($user)->patch(route('insiden.pleno.finalisasi', [$insiden, $pleno]));

        $response->assertSessionHas('success');
        $this->assertEquals('final', $pleno->fresh()->status_pleno);
    }

    public function test_pleno_final_mengisi_waktu_difinalisasi_dan_disetujui_oleh(): void
    {
        $pcnu = OrganisasiPcnu::create(['id_pcnu' => 10, 'id_unit' => 1, 'nama_pcnu' => 'PCNU Cilacap']);
        $insiden = $this->buatInsiden($pcnu->id_pcnu);
        $user = $this->buatUserDenganRole('pwnu');
        $pleno = OperasiPleno::factory()->sudahDitinjau()->create([
            'id_insiden' => $insiden->id_insiden,
            'pimpinan_pleno' => $user->id_pengguna,
            'notulis_pleno' => $user->id_pengguna,
        ]);

        $this->actingAs($user)->patch(route('insiden.pleno.finalisasi', [$insiden, $pleno]));

        $pleno->refresh();
        $this->assertEquals($user->id_pengguna, $pleno->disetujui_oleh);
        $this->assertNotNull($pleno->waktu_difinalisasi);
        $this->assertNotNull($pleno->waktu_disetujui);
    }

    public function test_pcnu_tidak_dapat_finalisasi(): void
    {
        $pcnu = OrganisasiPcnu::create(['id_pcnu' => 10, 'id_unit' => 1, 'nama_pcnu' => 'PCNU Cilacap']);
        $insiden = $this->buatInsiden($pcnu->id_pcnu);
        $user = $this->buatUserDenganRole('pcnu', 'pcnu', $pcnu->id_pcnu);
        $pleno = OperasiPleno::factory()->sudahDitinjau()->create([
            'id_insiden' => $insiden->id_insiden,
            'pimpinan_pleno' => $user->id_pengguna,
            'notulis_pleno' => $user->id_pengguna,
        ]);

        $response = $this->actingAs($user)->patch(route('insiden.pleno.finalisasi', [$insiden, $pleno]));

        $response->assertStatus(403);
    }

    public function test_pleno_final_tidak_dapat_tambah_keputusan(): void
    {
        $pcnu = OrganisasiPcnu::create(['id_pcnu' => 10, 'id_unit' => 1, 'nama_pcnu' => 'PCNU Cilacap']);
        $insiden = $this->buatInsiden($pcnu->id_pcnu);
        $user = $this->buatUserDenganRole('pwnu');
        $pleno = OperasiPleno::factory()->sudahFinal()->create([
            'id_insiden' => $insiden->id_insiden,
            'pimpinan_pleno' => $user->id_pengguna,
            'notulis_pleno' => $user->id_pengguna,
        ]);

        $response = $this->actingAs($user)->post(route('insiden.pleno.keputusan.store', [$insiden, $pleno]), [
            'kategori_objek' => 'insiden',
            'jenis_keputusan' => 'perubahan_status_insiden',
            'tipe_target_keputusan' => 'insiden',
            'deskripsi_keputusan' => 'Test keputusan di pleno final.',
        ]);

        $response->assertStatus(403);
    }

    public function test_pleno_final_tidak_dapat_tambah_peserta(): void
    {
        $pcnu = OrganisasiPcnu::create(['id_pcnu' => 10, 'id_unit' => 1, 'nama_pcnu' => 'PCNU Cilacap']);
        $insiden = $this->buatInsiden($pcnu->id_pcnu);
        $user = $this->buatUserDenganRole('pwnu');
        $pleno = OperasiPleno::factory()->sudahFinal()->create([
            'id_insiden' => $insiden->id_insiden,
            'pimpinan_pleno' => $user->id_pengguna,
            'notulis_pleno' => $user->id_pengguna,
        ]);

        $pesertaBaru = $this->buatUserDenganRole('pwnu');

        $response = $this->actingAs($user)->post(route('insiden.pleno.peserta.store', [$insiden, $pleno]), [
            'id_pengguna' => $pesertaBaru->id_pengguna,
        ]);

        $response->assertStatus(403);
    }

    public function test_pleno_final_tidak_dapat_vote_ulang(): void
    {
        $pcnu = OrganisasiPcnu::create(['id_pcnu' => 10, 'id_unit' => 1, 'nama_pcnu' => 'PCNU Cilacap']);
        $insiden = $this->buatInsiden($pcnu->id_pcnu);
        $user = $this->buatUserDenganRole('pwnu');
        $pleno = OperasiPleno::factory()->sudahFinal()->create([
            'id_insiden' => $insiden->id_insiden,
            'pimpinan_pleno' => $user->id_pengguna,
            'notulis_pleno' => $user->id_pengguna,
        ]);
        $peserta = $pleno->peserta()->create([
            'id_pengguna' => $user->id_pengguna,
            'hak_suara' => true,
        ]);

        $response = $this->actingAs($user)->patch(route('insiden.pleno.peserta.vote', [$insiden, $pleno, $peserta]), [
            'status_persetujuan' => 'setuju',
        ]);

        $response->assertSessionHas('error');
    }

    public function test_pleno_final_tidak_dapat_disetujui_ulang(): void
    {
        $pcnu = OrganisasiPcnu::create(['id_pcnu' => 10, 'id_unit' => 1, 'nama_pcnu' => 'PCNU Cilacap']);
        $insiden = $this->buatInsiden($pcnu->id_pcnu);
        $user = $this->buatUserDenganRole('pwnu');
        $pleno = OperasiPleno::factory()->sudahFinal()->create([
            'id_insiden' => $insiden->id_insiden,
            'pimpinan_pleno' => $user->id_pengguna,
            'notulis_pleno' => $user->id_pengguna,
        ]);

        $response = $this->actingAs($user)->patch(route('insiden.pleno.finalisasi', [$insiden, $pleno]));
        $response->assertStatus(403);
    }

    public function test_pcnu_tidak_dapat_buat_pleno_luar_scope(): void
    {
        $pcnu1 = OrganisasiPcnu::create(['id_pcnu' => 10, 'id_unit' => 1, 'nama_pcnu' => 'PCNU Cilacap']);
        $pcnu2 = OrganisasiPcnu::create(['id_pcnu' => 11, 'id_unit' => 2, 'nama_pcnu' => 'PCNU Banyumas']);
        $insiden1 = $this->buatInsiden($pcnu1->id_pcnu);
        $this->buatAssessmentApproved($insiden1);
        $pimpinan = $this->buatUserDenganRole('pcnu', 'pcnu', $pcnu2->id_pcnu);
        $notulis = $this->buatUserDenganRole('pcnu', 'pcnu', $pcnu2->id_pcnu);
        $user = $this->buatUserDenganRole('pcnu', 'pcnu', $pcnu2->id_pcnu);

        $response = $this->actingAs($user)->post(route('insiden.pleno.store', $insiden1), [
            'id_insiden' => $insiden1->id_insiden,
            'nomor_pleno' => '001/PLENO/PCNU-TEST/VI/2026',
            'waktu_pleno' => now()->format('Y-m-d H:i:s'),
            'jenis_pleno' => 'evaluasi_rutin',
            'pimpinan_pleno' => $pimpinan->id_pengguna,
            'notulis_pleno' => $notulis->id_pengguna,
            'lokasi_pleno' => 'Posko Utama',
        ]);

        $response->assertStatus(403);
    }

    public function test_pcnu_tidak_dapat_melihat_pleno_luar_scope(): void
    {
        $pcnu1 = OrganisasiPcnu::create(['id_pcnu' => 10, 'id_unit' => 1, 'nama_pcnu' => 'PCNU Cilacap']);
        $pcnu2 = OrganisasiPcnu::create(['id_pcnu' => 11, 'id_unit' => 2, 'nama_pcnu' => 'PCNU Banyumas']);
        $insiden1 = $this->buatInsiden($pcnu1->id_pcnu);
        $pimpinan = $this->buatUserDenganRole('pwnu');
        $notulis = $this->buatUserDenganRole('pwnu');
        $pleno = OperasiPleno::factory()->create([
            'id_insiden' => $insiden1->id_insiden,
            'pimpinan_pleno' => $pimpinan->id_pengguna,
            'notulis_pleno' => $notulis->id_pengguna,
        ]);

        $user = $this->buatUserDenganRole('pcnu', 'pcnu', $pcnu2->id_pcnu);
        $response = $this->actingAs($user)->get(route('insiden.pleno.show', [$insiden1, $pleno]));
        $response->assertStatus(403);
    }
}
