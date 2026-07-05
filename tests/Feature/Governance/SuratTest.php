<?php

namespace Tests\Feature\Governance;

use Tests\TestCase;
use App\Models\AuthUser;
use App\Models\AuthRole;
use App\Models\BencanaMasterJenis;
use App\Models\MasterSuratJenis;
use App\Models\MasterJabatanPenandatangan;
use App\Models\DokumenSuratUtama;
use App\Models\DokumenSuratParaf;
use App\Models\OperasiInsiden;
use App\Models\OrganisasiPcnu;
use App\Models\OrganisasiUnit;
use Database\Seeders\BencanaMasterJenisSeeder;
use Database\Seeders\MasterSuratSeeder;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Schema;

class SuratTest extends TestCase
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
        $this->seed(MasterSuratSeeder::class);
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

    private function buatDataSurat(array $data = []): array
    {
        return array_merge([
            'id_jenis_surat' => MasterSuratJenis::first()?->id_jenis_surat ?? 1,
            'perihal' => 'Permohonan Bantuan Logistik',
            'tgl_terbit' => now()->format('Y-m-d'),
        ], $data);
    }

    // === AKSES & OTORISASI ===

    public function test_super_admin_dapat_melihat_daftar_surat(): void
    {
        $user = $this->buatUserDenganRole('super_admin');
        $response = $this->actingAs($user)->get(route('surat.index'));
        $response->assertStatus(200);
    }

    public function test_pcnu_dapat_melihat_daftar_surat(): void
    {
        $pcnu = OrganisasiPcnu::create(['id_pcnu' => 10, 'id_unit' => 1, 'nama_pcnu' => 'PCNU Cilacap']);
        $user = $this->buatUserDenganRole('pcnu', 'pcnu', $pcnu->id_pcnu);
        $response = $this->actingAs($user)->get(route('surat.index'));
        $response->assertStatus(200);
    }

    public function test_relawan_diblokir_dari_daftar_surat(): void
    {
        $user = $this->buatUserDenganRole('relawan');
        $response = $this->actingAs($user)->get(route('surat.index'));
        $response->assertStatus(403);
    }

    public function test_guest_diredirect_ke_login(): void
    {
        $response = $this->get(route('surat.index'));
        $response->assertRedirect('/login');
    }

    // === MEMBUAT SURAT ===

    public function test_super_admin_dapat_membuat_surat(): void
    {
        $user = $this->buatUserDenganRole('super_admin');
        $ttdUser = $this->buatUserDenganRole('pwnu');

        $response = $this->actingAs($user)->post(route('surat.store'), $this->buatDataSurat([
            'id_pengguna_ttd' => $ttdUser->id_pengguna,
        ]));

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertEquals(1, DokumenSuratUtama::count());
    }

    public function test_surat_baru_berstatus_draft(): void
    {
        $user = $this->buatUserDenganRole('super_admin');
        $ttdUser = $this->buatUserDenganRole('pwnu');

        $response = $this->actingAs($user)->post(route('surat.store'), $this->buatDataSurat([
            'id_pengguna_ttd' => $ttdUser->id_pengguna,
        ]));

        $surat = DokumenSuratUtama::first();
        $this->assertEquals('draft', $surat->status_surat);
    }

    public function test_surat_mendapat_nomor_surat_resmi_otomatis(): void
    {
        $user = $this->buatUserDenganRole('super_admin');
        $ttdUser = $this->buatUserDenganRole('pwnu');

        $response = $this->actingAs($user)->post(route('surat.store'), $this->buatDataSurat([
            'id_pengguna_ttd' => $ttdUser->id_pengguna,
        ]));

        $surat = DokumenSuratUtama::first();
        $this->assertNotNull($surat->nomor_surat_resmi);
        $this->assertStringContainsString('PCNU/SK/', $surat->nomor_surat_resmi);
    }

    public function test_store_gagal_jika_jenis_surat_tidak_ada(): void
    {
        $user = $this->buatUserDenganRole('super_admin');
        $ttdUser = $this->buatUserDenganRole('pwnu');

        $response = $this->actingAs($user)->post(route('surat.store'), $this->buatDataSurat([
            'id_jenis_surat' => 99999,
            'id_pengguna_ttd' => $ttdUser->id_pengguna,
        ]));

        $response->assertSessionHasErrors('id_jenis_surat');
    }

    public function test_store_gagal_jika_penandatangan_tidak_ada(): void
    {
        $user = $this->buatUserDenganRole('super_admin');

        $response = $this->actingAs($user)->post(route('surat.store'), $this->buatDataSurat([
            'id_pengguna_ttd' => 99999,
        ]));

        $response->assertSessionHasErrors('id_pengguna_ttd');
    }

    public function test_tgl_terbit_tidak_boleh_mendahului_waktu_mulai_insiden(): void
    {
        $pcnu = OrganisasiPcnu::create(['id_pcnu' => 10, 'id_unit' => 1, 'nama_pcnu' => 'PCNU Cilacap']);
        $user = $this->buatUserDenganRole('super_admin');
        $ttdUser = $this->buatUserDenganRole('pwnu');
        $insiden = $this->buatInsiden($pcnu->id_pcnu);
        $insiden->waktu_mulai = now()->addDay();
        $insiden->save();

        $response = $this->actingAs($user)->post(route('surat.store'), $this->buatDataSurat([
            'id_insiden' => $insiden->id_insiden,
            'tgl_terbit' => now()->subDay()->format('Y-m-d'),
            'id_pengguna_ttd' => $ttdUser->id_pengguna,
        ]));

        $response->assertSessionHasErrors('tgl_terbit');
    }

    // === PARAF ===

    public function test_tambah_paraf_ke_surat_draft(): void
    {
        $user = $this->buatUserDenganRole('super_admin');
        $ttdUser = $this->buatUserDenganRole('pwnu');
        $parafUser = $this->buatUserDenganRole('pwnu');

        $this->actingAs($user)->post(route('surat.store'), $this->buatDataSurat([
            'id_pengguna_ttd' => $ttdUser->id_pengguna,
        ]));

        $surat = DokumenSuratUtama::first();

        $response = $this->actingAs($user)->post(route('surat.paraf.store', $surat), [
            'id_pengguna' => $parafUser->id_pengguna,
            'urutan' => 1,
        ]);

        $response->assertSessionHas('success');
        $this->assertEquals(1, $surat->paraf()->count());
        $this->assertEquals('menunggu', $surat->paraf()->first()->status_paraf);
    }

    public function test_paraf_tidak_bisa_ditambah_ke_surat_non_draft(): void
    {
        $user = $this->buatUserDenganRole('super_admin');
        $ttdUser = $this->buatUserDenganRole('pwnu');
        $parafUser = $this->buatUserDenganRole('pwnu');

        $this->actingAs($user)->post(route('surat.store'), $this->buatDataSurat([
            'id_pengguna_ttd' => $ttdUser->id_pengguna,
        ]));

        $surat = DokumenSuratUtama::first();
        $surat->update(['status_surat' => 'review_paraf']);

        $response = $this->actingAs($user)->post(route('surat.paraf.store', $surat), [
            'id_pengguna' => $parafUser->id_pengguna,
            'urutan' => 1,
        ]);

        $response->assertStatus(403);
    }

    // === ALUR PARAF ===

    public function test_kirim_ke_review_mengubah_status_menjadi_review_paraf(): void
    {
        $user = $this->buatUserDenganRole('super_admin');
        $ttdUser = $this->buatUserDenganRole('pwnu');
        $parafUser = $this->buatUserDenganRole('pwnu');

        $this->actingAs($user)->post(route('surat.store'), $this->buatDataSurat([
            'id_pengguna_ttd' => $ttdUser->id_pengguna,
        ]));

        $surat = DokumenSuratUtama::first();
        $surat->paraf()->create(['id_pengguna' => $parafUser->id_pengguna, 'urutan' => 1, 'status_paraf' => 'menunggu']);

        $response = $this->actingAs($user)->patch(route('surat.kirim-review', $surat));
        $response->assertSessionHas('success');
        $this->assertEquals('review_paraf', $surat->fresh()->status_surat);
    }

    public function test_kirim_ke_review_gagal_jika_tidak_ada_paraf(): void
    {
        $user = $this->buatUserDenganRole('super_admin');
        $ttdUser = $this->buatUserDenganRole('pwnu');

        $this->actingAs($user)->post(route('surat.store'), $this->buatDataSurat([
            'id_pengguna_ttd' => $ttdUser->id_pengguna,
        ]));

        $surat = DokumenSuratUtama::first();

        $response = $this->actingAs($user)->patch(route('surat.kirim-review', $surat));
        $response->assertSessionHas('error');
    }

    public function test_user_dapat_menyetujui_paraf_sendiri(): void
    {
        $pcnu = OrganisasiPcnu::create(['id_pcnu' => 10, 'id_unit' => 1, 'nama_pcnu' => 'PCNU Cilacap']);
        $admin = $this->buatUserDenganRole('super_admin');
        $ttdUser = $this->buatUserDenganRole('pwnu');
        $parafUser = $this->buatUserDenganRole('pcnu', 'pcnu', $pcnu->id_pcnu);

        $this->actingAs($admin)->post(route('surat.store'), $this->buatDataSurat([
            'id_pengguna_ttd' => $ttdUser->id_pengguna,
        ]));

        $surat = DokumenSuratUtama::first();
        $surat->paraf()->create(['id_pengguna' => $parafUser->id_pengguna, 'urutan' => 1, 'status_paraf' => 'menunggu']);
        $surat->update(['status_surat' => 'review_paraf']);

        $paraf = $surat->paraf()->first();

        $response = $this->actingAs($parafUser)->patch(route('surat.paraf.update', [$surat, $paraf]), [
            'status_paraf' => 'disetujui',
        ]);

        $response->assertSessionHas('success');
        $this->assertEquals('disetujui', $paraf->fresh()->status_paraf);
    }

    public function test_user_tidak_dapat_menyetujui_paraf_milik_orang_lain(): void
    {
        $pcnu = OrganisasiPcnu::create(['id_pcnu' => 10, 'id_unit' => 1, 'nama_pcnu' => 'PCNU Cilacap']);
        $admin = $this->buatUserDenganRole('super_admin');
        $ttdUser = $this->buatUserDenganRole('pwnu');
        $parafUser1 = $this->buatUserDenganRole('pcnu', 'pcnu', $pcnu->id_pcnu);
        $parafUser2 = $this->buatUserDenganRole('pcnu', 'pcnu', $pcnu->id_pcnu);

        $this->actingAs($admin)->post(route('surat.store'), $this->buatDataSurat([
            'id_pengguna_ttd' => $ttdUser->id_pengguna,
        ]));

        $surat = DokumenSuratUtama::first();
        $surat->paraf()->create(['id_pengguna' => $parafUser1->id_pengguna, 'urutan' => 1, 'status_paraf' => 'menunggu']);
        $surat->update(['status_surat' => 'review_paraf']);

        $paraf = $surat->paraf()->first();

        $response = $this->actingAs($parafUser2)->patch(route('surat.paraf.update', [$surat, $paraf]), [
            'status_paraf' => 'disetujui',
        ]);

        $response->assertStatus(403);
    }

    public function test_paraf_tidak_dapat_diproses_dua_kali(): void
    {
        $pcnu = OrganisasiPcnu::create(['id_pcnu' => 10, 'id_unit' => 1, 'nama_pcnu' => 'PCNU Cilacap']);
        $admin = $this->buatUserDenganRole('super_admin');
        $ttdUser = $this->buatUserDenganRole('pwnu');
        $parafUser = $this->buatUserDenganRole('pcnu', 'pcnu', $pcnu->id_pcnu);

        $this->actingAs($admin)->post(route('surat.store'), $this->buatDataSurat([
            'id_pengguna_ttd' => $ttdUser->id_pengguna,
        ]));

        $surat = DokumenSuratUtama::first();
        $surat->paraf()->create(['id_pengguna' => $parafUser->id_pengguna, 'urutan' => 1, 'status_paraf' => 'menunggu']);
        $surat->update(['status_surat' => 'review_paraf']);

        $paraf = $surat->paraf()->first();

        $this->actingAs($parafUser)->patch(route('surat.paraf.update', [$surat, $paraf]), [
            'status_paraf' => 'disetujui',
        ]);

        $response = $this->actingAs($parafUser)->patch(route('surat.paraf.update', [$surat, $paraf]), [
            'status_paraf' => 'disetujui',
        ]);

        $response->assertStatus(403);
    }

    public function test_paraf_ditolak_dengan_catatan_mengembalikan_ke_draft(): void
    {
        $pcnu = OrganisasiPcnu::create(['id_pcnu' => 10, 'id_unit' => 1, 'nama_pcnu' => 'PCNU Cilacap']);
        $admin = $this->buatUserDenganRole('super_admin');
        $ttdUser = $this->buatUserDenganRole('pwnu');
        $parafUser = $this->buatUserDenganRole('pcnu', 'pcnu', $pcnu->id_pcnu);

        $this->actingAs($admin)->post(route('surat.store'), $this->buatDataSurat([
            'id_pengguna_ttd' => $ttdUser->id_pengguna,
        ]));

        $surat = DokumenSuratUtama::first();
        $surat->paraf()->create(['id_pengguna' => $parafUser->id_pengguna, 'urutan' => 1, 'status_paraf' => 'menunggu']);
        $surat->update(['status_surat' => 'review_paraf']);

        $paraf = $surat->paraf()->first();

        $response = $this->actingAs($parafUser)->patch(route('surat.paraf.update', [$surat, $paraf]), [
            'status_paraf' => 'ditolak',
            'catatan' => 'Perbaiki data lampiran.',
        ]);

        $response->assertSessionHas('success');
        $this->assertEquals('ditolak', $paraf->fresh()->status_paraf);
        $this->assertEquals('draft', $surat->fresh()->status_surat);
        $this->assertEquals('Perbaiki data lampiran.', $paraf->fresh()->catatan);
    }

    public function test_tolak_tanpa_catatan_gagal_validasi(): void
    {
        $pcnu = OrganisasiPcnu::create(['id_pcnu' => 10, 'id_unit' => 1, 'nama_pcnu' => 'PCNU Cilacap']);
        $admin = $this->buatUserDenganRole('super_admin');
        $ttdUser = $this->buatUserDenganRole('pwnu');
        $parafUser = $this->buatUserDenganRole('pcnu', 'pcnu', $pcnu->id_pcnu);

        $this->actingAs($admin)->post(route('surat.store'), $this->buatDataSurat([
            'id_pengguna_ttd' => $ttdUser->id_pengguna,
        ]));

        $surat = DokumenSuratUtama::first();
        $surat->paraf()->create(['id_pengguna' => $parafUser->id_pengguna, 'urutan' => 1, 'status_paraf' => 'menunggu']);
        $surat->update(['status_surat' => 'review_paraf']);

        $paraf = $surat->paraf()->first();

        $response = $this->actingAs($parafUser)->patch(route('surat.paraf.update', [$surat, $paraf]), [
            'status_paraf' => 'ditolak',
            'catatan' => '',
        ]);

        $response->assertSessionHasErrors('catatan');
    }

    // === FINALISASI ===

    public function test_surat_siap_tanda_tangan_setelah_semua_paraf_disetujui(): void
    {
        $admin = $this->buatUserDenganRole('super_admin');
        $ttdUser = $this->buatUserDenganRole('pwnu');
        $parafUser = $this->buatUserDenganRole('pwnu');

        $this->actingAs($admin)->post(route('surat.store'), $this->buatDataSurat([
            'id_pengguna_ttd' => $ttdUser->id_pengguna,
        ]));

        $surat = DokumenSuratUtama::first();
        $surat->paraf()->create(['id_pengguna' => $parafUser->id_pengguna, 'urutan' => 1, 'status_paraf' => 'menunggu']);
        $surat->update(['status_surat' => 'review_paraf']);

        $paraf = $surat->paraf()->first();
        $this->actingAs($parafUser)->patch(route('surat.paraf.update', [$surat, $paraf]), [
            'status_paraf' => 'disetujui',
        ]);

        $this->assertEquals('siap_tanda_tangan', $surat->fresh()->status_surat);
    }

    public function test_super_admin_dapat_finalisasi_surat(): void
    {
        $admin = $this->buatUserDenganRole('super_admin');
        $ttdUser = $this->buatUserDenganRole('pwnu');
        $parafUser = $this->buatUserDenganRole('pwnu');

        $this->actingAs($admin)->post(route('surat.store'), $this->buatDataSurat([
            'id_pengguna_ttd' => $ttdUser->id_pengguna,
        ]));

        $surat = DokumenSuratUtama::first();
        $surat->paraf()->create(['id_pengguna' => $parafUser->id_pengguna, 'urutan' => 1, 'status_paraf' => 'menunggu']);
        $surat->update(['status_surat' => 'review_paraf']);

        $paraf = $surat->paraf()->first();
        $this->actingAs($parafUser)->patch(route('surat.paraf.update', [$surat, $paraf]), ['status_paraf' => 'disetujui']);

        $response = $this->actingAs($admin)->patch(route('surat.finalisasi', $surat), [
            'isi_surat_snapshot' => 'Isi surat test.',
        ]);

        $response->assertSessionHas('success');
        $this->assertEquals('ditandatangani', $surat->fresh()->status_surat);
        $this->assertEquals('Isi surat test.', $surat->fresh()->isi_surat_snapshot);
    }

    public function test_pcnu_tidak_dapat_finalisasi_surat(): void
    {
        $pcnu = OrganisasiPcnu::create(['id_pcnu' => 10, 'id_unit' => 1, 'nama_pcnu' => 'PCNU Cilacap']);
        $admin = $this->buatUserDenganRole('super_admin');
        $ttdUser = $this->buatUserDenganRole('pwnu');
        $parafUser = $this->buatUserDenganRole('pwnu');

        $this->actingAs($admin)->post(route('surat.store'), $this->buatDataSurat([
            'id_pengguna_ttd' => $ttdUser->id_pengguna,
        ]));

        $surat = DokumenSuratUtama::first();
        $surat->paraf()->create(['id_pengguna' => $parafUser->id_pengguna, 'urutan' => 1, 'status_paraf' => 'menunggu']);
        $surat->update(['status_surat' => 'review_paraf']);

        $paraf = $surat->paraf()->first();
        $this->actingAs($parafUser)->patch(route('surat.paraf.update', [$surat, $paraf]), ['status_paraf' => 'disetujui']);

        $pcnuUser = $this->buatUserDenganRole('pcnu', 'pcnu', $pcnu->id_pcnu);
        $response = $this->actingAs($pcnuUser)->patch(route('surat.finalisasi', $surat), [
            'isi_surat_snapshot' => 'Isi surat test.',
        ]);

        $response->assertStatus(403);
    }

    public function test_tidak_dapat_finalisasi_jika_belum_siap_tanda_tangan(): void
    {
        $admin = $this->buatUserDenganRole('super_admin');
        $ttdUser = $this->buatUserDenganRole('pwnu');

        $this->actingAs($admin)->post(route('surat.store'), $this->buatDataSurat([
            'id_pengguna_ttd' => $ttdUser->id_pengguna,
        ]));

        $surat = DokumenSuratUtama::first();

        $response = $this->actingAs($admin)->patch(route('surat.finalisasi', $surat), [
            'isi_surat_snapshot' => 'Isi surat test.',
        ]);

        $response->assertStatus(403);
    }

    // === IMMUTABILITY ===

    public function test_surat_ditandatangani_tidak_dapat_diedit(): void
    {
        $admin = $this->buatUserDenganRole('super_admin');
        $ttdUser = $this->buatUserDenganRole('pwnu');
        $parafUser = $this->buatUserDenganRole('pwnu');

        $this->actingAs($admin)->post(route('surat.store'), $this->buatDataSurat([
            'id_pengguna_ttd' => $ttdUser->id_pengguna,
        ]));

        $surat = DokumenSuratUtama::first();
        $surat->paraf()->create(['id_pengguna' => $parafUser->id_pengguna, 'urutan' => 1, 'status_paraf' => 'menunggu']);
        $surat->update(['status_surat' => 'review_paraf']);

        $paraf = $surat->paraf()->first();
        $this->actingAs($parafUser)->patch(route('surat.paraf.update', [$surat, $paraf]), ['status_paraf' => 'disetujui']);
        $this->actingAs($admin)->patch(route('surat.finalisasi', $surat), ['isi_surat_snapshot' => 'Isi surat test.']);

        $response = $this->actingAs($admin)->get(route('surat.edit', $surat));
        $response->assertStatus(403);
    }

    // === PARAF BERURUTAN ===

    public function test_paraf_kedua_tidak_bisa_sebelum_paraf_pertama_disetujui(): void
    {
        $pcnu = OrganisasiPcnu::create(['id_pcnu' => 10, 'id_unit' => 1, 'nama_pcnu' => 'PCNU Cilacap']);
        $admin = $this->buatUserDenganRole('super_admin');
        $ttdUser = $this->buatUserDenganRole('pwnu');
        $paraf1 = $this->buatUserDenganRole('pcnu', 'pcnu', $pcnu->id_pcnu);
        $paraf2 = $this->buatUserDenganRole('pcnu', 'pcnu', $pcnu->id_pcnu);

        $this->actingAs($admin)->post(route('surat.store'), $this->buatDataSurat([
            'id_pengguna_ttd' => $ttdUser->id_pengguna,
        ]));

        $surat = DokumenSuratUtama::first();
        $surat->paraf()->create(['id_pengguna' => $paraf1->id_pengguna, 'urutan' => 1, 'status_paraf' => 'menunggu']);
        $surat->paraf()->create(['id_pengguna' => $paraf2->id_pengguna, 'urutan' => 2, 'status_paraf' => 'menunggu']);
        $surat->update(['status_surat' => 'review_paraf']);

        $paraf2Record = $surat->paraf()->where('urutan', 2)->first();

        $response = $this->actingAs($paraf2)->patch(route('surat.paraf.update', [$surat, $paraf2Record]), ['status_paraf' => 'disetujui']);

        $response->assertSessionHas('error');
    }

    public function test_paraf_kedua_bisa_setelah_paraf_pertama_disetujui(): void
    {
        $pcnu = OrganisasiPcnu::create(['id_pcnu' => 10, 'id_unit' => 1, 'nama_pcnu' => 'PCNU Cilacap']);
        $admin = $this->buatUserDenganRole('super_admin');
        $ttdUser = $this->buatUserDenganRole('pwnu');
        $paraf1 = $this->buatUserDenganRole('pcnu', 'pcnu', $pcnu->id_pcnu);
        $paraf2 = $this->buatUserDenganRole('pcnu', 'pcnu', $pcnu->id_pcnu);

        $this->actingAs($admin)->post(route('surat.store'), $this->buatDataSurat([
            'id_pengguna_ttd' => $ttdUser->id_pengguna,
        ]));

        $surat = DokumenSuratUtama::first();
        $surat->paraf()->create(['id_pengguna' => $paraf1->id_pengguna, 'urutan' => 1, 'status_paraf' => 'menunggu']);
        $surat->paraf()->create(['id_pengguna' => $paraf2->id_pengguna, 'urutan' => 2, 'status_paraf' => 'menunggu']);
        $surat->update(['status_surat' => 'review_paraf']);

        $paraf1Record = $surat->paraf()->where('urutan', 1)->first();
        $paraf2Record = $surat->paraf()->where('urutan', 2)->first();

        $this->actingAs($paraf1)->patch(route('surat.paraf.update', [$surat, $paraf1Record]), ['status_paraf' => 'disetujui']);

        $response = $this->actingAs($paraf2)->patch(route('surat.paraf.update', [$surat, $paraf2Record]), ['status_paraf' => 'disetujui']);

        $response->assertSessionHas('success');
        $this->assertEquals('disetujui', $paraf2Record->fresh()->status_paraf);
    }

    public function test_surat_siap_ttd_setelah_semua_paraf_berurutan_disetujui(): void
    {
        $pcnu = OrganisasiPcnu::create(['id_pcnu' => 10, 'id_unit' => 1, 'nama_pcnu' => 'PCNU Cilacap']);
        $admin = $this->buatUserDenganRole('super_admin');
        $ttdUser = $this->buatUserDenganRole('pwnu');
        $paraf1 = $this->buatUserDenganRole('pcnu', 'pcnu', $pcnu->id_pcnu);
        $paraf2 = $this->buatUserDenganRole('pcnu', 'pcnu', $pcnu->id_pcnu);

        $this->actingAs($admin)->post(route('surat.store'), $this->buatDataSurat([
            'id_pengguna_ttd' => $ttdUser->id_pengguna,
        ]));

        $surat = DokumenSuratUtama::first();
        $surat->paraf()->create(['id_pengguna' => $paraf1->id_pengguna, 'urutan' => 1, 'status_paraf' => 'menunggu']);
        $surat->paraf()->create(['id_pengguna' => $paraf2->id_pengguna, 'urutan' => 2, 'status_paraf' => 'menunggu']);
        $surat->update(['status_surat' => 'review_paraf']);

        $p1 = $surat->paraf()->where('urutan', 1)->first();
        $p2 = $surat->paraf()->where('urutan', 2)->first();

        $this->actingAs($paraf1)->patch(route('surat.paraf.update', [$surat, $p1]), ['status_paraf' => 'disetujui']);
        $this->actingAs($paraf2)->patch(route('surat.paraf.update', [$surat, $p2]), ['status_paraf' => 'disetujui']);

        $this->assertEquals('siap_tanda_tangan', $surat->fresh()->status_surat);
    }

    public function test_paraf_ditolak_mengembalikan_surat_ke_draft(): void
    {
        $pcnu = OrganisasiPcnu::create(['id_pcnu' => 10, 'id_unit' => 1, 'nama_pcnu' => 'PCNU Cilacap']);
        $admin = $this->buatUserDenganRole('super_admin');
        $ttdUser = $this->buatUserDenganRole('pwnu');
        $paraf1 = $this->buatUserDenganRole('pcnu', 'pcnu', $pcnu->id_pcnu);

        $this->actingAs($admin)->post(route('surat.store'), $this->buatDataSurat([
            'id_pengguna_ttd' => $ttdUser->id_pengguna,
        ]));

        $surat = DokumenSuratUtama::first();
        $p1 = $surat->paraf()->create(['id_pengguna' => $paraf1->id_pengguna, 'urutan' => 1, 'status_paraf' => 'menunggu']);
        $surat->update(['status_surat' => 'review_paraf']);

        $response = $this->actingAs($paraf1)->patch(route('surat.paraf.update', [$surat, $p1]), [
            'status_paraf' => 'ditolak',
            'catatan' => 'Perbaiki data lampiran.',
        ]);

        $response->assertSessionHas('success');
        $this->assertEquals('ditolak', $p1->fresh()->status_paraf);
        $this->assertEquals('draft', $surat->fresh()->status_surat);
    }

    public function test_pdf_gagal_tidak_mengubah_status_surat(): void
    {
        $ttdUser = $this->buatUserDenganRole('pwnu');
        $surat = DokumenSuratUtama::create([
            'id_jenis_surat' => 1,
            'nomor_surat_resmi' => 'TEST/PDF-FAIL/' . now()->timestamp,
            'perihal' => 'Test PDF Fail',
            'tgl_terbit' => now()->format('Y-m-d'),
            'id_pengguna_ttd' => $ttdUser->id_pengguna,
            'status_surat' => 'siap_tanda_tangan',
        ]);

        $pdfServiceMock = $this->createMock(\App\Services\SuratPdfService::class);
        $pdfServiceMock->method('generate')
            ->willThrowException(new \RuntimeException('PDF generation failed!'));

        $job = new \App\Jobs\GenerateSuratPdfJob($surat->id_surat, 'Test snapshot');
        
        $exceptionThrown = false;
        try {
            $job->handle($pdfServiceMock);
        } catch (\Throwable $e) {
            $exceptionThrown = true;
        }

        $this->assertTrue($exceptionThrown, 'Exception should have been thrown from Job when PDF generation fails');

        $surat->refresh();
        $this->assertEquals('siap_tanda_tangan', $surat->status_surat);
        $this->assertNull($surat->file_pdf_path);
    }

    public function test_paraf_approve_dicatat_ke_jurnal(): void
    {
        if (!\Illuminate\Support\Facades\Schema::hasTable('operasi_jurnal')) {
            $this->markTestSkipped('Tabel operasi_jurnal belum tersedia.');
        }

        $pcnu = OrganisasiPcnu::create(['id_pcnu' => 10, 'id_unit' => 1, 'nama_pcnu' => 'PCNU Cilacap']);
        $admin = $this->buatUserDenganRole('super_admin');
        $ttdUser = $this->buatUserDenganRole('pwnu');
        $parafUser = $this->buatUserDenganRole('pcnu', 'pcnu', $pcnu->id_pcnu);

        $this->actingAs($admin)->post(route('surat.store'), $this->buatDataSurat([
            'id_pengguna_ttd' => $ttdUser->id_pengguna,
        ]));

        $surat = DokumenSuratUtama::first();
        $surat->paraf()->create(['id_pengguna' => $parafUser->id_pengguna, 'urutan' => 1, 'status_paraf' => 'menunggu']);
        $surat->update(['status_surat' => 'review_paraf']);

        $paraf = $surat->paraf()->first();

        $this->actingAs($parafUser)->patch(route('surat.paraf.update', [$surat, $paraf]), [
            'status_paraf' => 'disetujui',
        ]);

        $this->assertDatabaseHas('operasi_jurnal', [
            'id_pengguna' => $parafUser->id_pengguna,
            'judul_event' => 'Paraf disetujui',
            'tabel_referensi' => 'operasi_surat_keluar',
            'id_referensi' => $surat->id_surat,
        ]);
    }

    public function test_paraf_reject_dicatat_ke_jurnal(): void
    {
        if (!\Illuminate\Support\Facades\Schema::hasTable('operasi_jurnal')) {
            $this->markTestSkipped('Tabel operasi_jurnal belum tersedia.');
        }

        $pcnu = OrganisasiPcnu::create(['id_pcnu' => 10, 'id_unit' => 1, 'nama_pcnu' => 'PCNU Cilacap']);
        $admin = $this->buatUserDenganRole('super_admin');
        $ttdUser = $this->buatUserDenganRole('pwnu');
        $parafUser = $this->buatUserDenganRole('pcnu', 'pcnu', $pcnu->id_pcnu);

        $this->actingAs($admin)->post(route('surat.store'), $this->buatDataSurat([
            'id_pengguna_ttd' => $ttdUser->id_pengguna,
        ]));

        $surat = DokumenSuratUtama::first();
        $surat->paraf()->create(['id_pengguna' => $parafUser->id_pengguna, 'urutan' => 1, 'status_paraf' => 'menunggu']);
        $surat->update(['status_surat' => 'review_paraf']);

        $paraf = $surat->paraf()->first();

        $this->actingAs($parafUser)->patch(route('surat.paraf.update', [$surat, $paraf]), [
            'status_paraf' => 'ditolak',
            'catatan' => 'Perlu diperbaiki.',
        ]);

        $this->assertDatabaseHas('operasi_jurnal', [
            'id_pengguna' => $parafUser->id_pengguna,
            'judul_event' => 'Paraf ditolak',
            'tabel_referensi' => 'operasi_surat_keluar',
            'id_referensi' => $surat->id_surat,
        ]);
    }

    public function test_penandatangan_tanpa_jabatan_valid(): void
    {
        $pcnu = OrganisasiPcnu::create(['id_pcnu' => 10, 'id_unit' => 1, 'nama_pcnu' => 'PCNU Cilacap']);
        $user = $this->buatUserDenganRole('super_admin');
        $ttdUser = $this->buatUserDenganRole('pwnu');

        $response = $this->actingAs($user)->post(route('surat.store'), [
            'id_jenis_surat' => 1,
            'perihal' => 'Surat Test',
            'tgl_terbit' => now()->format('Y-m-d'),
            'id_pengguna_ttd' => $ttdUser->id_pengguna,
            'id_jabatan_ttd' => 99999,
        ]);

        $response->assertSessionHasErrors('id_jabatan_ttd');
    }

    public function test_surat_ditandatangani_tidak_dapat_diubah_metadata(): void
    {
        $admin = $this->buatUserDenganRole('super_admin');
        $ttdUser = $this->buatUserDenganRole('pwnu');
        $parafUser = $this->buatUserDenganRole('pwnu');

        $this->actingAs($admin)->post(route('surat.store'), $this->buatDataSurat([
            'id_pengguna_ttd' => $ttdUser->id_pengguna,
        ]));

        $surat = DokumenSuratUtama::first();
        $surat->paraf()->create(['id_pengguna' => $parafUser->id_pengguna, 'urutan' => 1, 'status_paraf' => 'menunggu']);
        $surat->update(['status_surat' => 'review_paraf']);

        $paraf = $surat->paraf()->first();
        $this->actingAs($parafUser)->patch(route('surat.paraf.update', [$surat, $paraf]), ['status_paraf' => 'disetujui']);
        $this->actingAs($admin)->patch(route('surat.finalisasi', $surat), ['isi_surat_snapshot' => 'Isi surat test.']);

        $response = $this->actingAs($admin)->put(route('surat.update', $surat), [
            'id_jenis_surat' => 1,
            'perihal' => 'Perubahan tidak diizinkan',
            'tgl_terbit' => now()->format('Y-m-d'),
            'id_pengguna_ttd' => $ttdUser->id_pengguna,
        ]);

        $response->assertStatus(403);
    }

    public function test_surat_arsip_tidak_dapat_diedit(): void
    {
        $admin = $this->buatUserDenganRole('super_admin');
        $ttdUser = $this->buatUserDenganRole('pwnu');

        $this->actingAs($admin)->post(route('surat.store'), $this->buatDataSurat([
            'id_pengguna_ttd' => $ttdUser->id_pengguna,
        ]));

        $surat = DokumenSuratUtama::first();
        $surat->update(['status_surat' => 'arsip']);

        $response = $this->actingAs($admin)->get(route('surat.edit', $surat));
        $response->assertStatus(403);
    }
}
