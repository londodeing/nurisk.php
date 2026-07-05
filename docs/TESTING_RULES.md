# TESTING_RULES.md — NURISK
# Standar Testing — FROZEN

> **DOKUMEN INI DIFREEZE.**
> Standar testing tidak boleh diturunkan tanpa keputusan eksplisit.

---

## 1. Filosofi Testing

- Testing difokuskan pada **feature test** (integration test end-to-end via HTTP)
- Unit test hanya untuk logic kompleks yang independen (kalkulasi nomor surat, hash snapshot)
- **DILARANG skip authorization test** — ini adalah area paling kritis sistem
- **DILARANG skip transaction test** — mutasi logistik, transisi status, dan finalisasi wajib di-test dalam konteks transaksi
- **DILARANG skip workflow test** — setiap state machine wajib di-test transisi valid dan invalid
- Setiap modul HARUS punya minimal **1 feature test passing** sebelum dianggap selesai
- Test harus dijalankan dengan **MySQL** (bukan SQLite in-memory) karena banyak trigger database yang diuji

---

## 2. Konfigurasi Testing

### Environment

Buat file `.env.testing` di root project:

```ini
APP_ENV=testing
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=nurisk_testing
DB_USERNAME=...
DB_PASSWORD=...
```

> **CATATAN:** Gunakan database MySQL terpisah untuk testing (`nurisk_testing`).
> SQLite tidak mendukung trigger dan JSON CHECK constraint yang ada di schema NURISK.

### phpunit.xml

Tambahkan konfigurasi database ke `phpunit.xml`:

```xml
<php>
    <env name="APP_ENV" value="testing"/>
    <env name="DB_CONNECTION" value="mysql"/>
    <env name="DB_DATABASE" value="nurisk_testing"/>
    <env name="CACHE_DRIVER" value="array"/>
    <env name="QUEUE_CONNECTION" value="sync"/>
</php>
```

### Base Test Case

Semua feature test WAJIB extend base test case berikut:

```php
<?php
// tests/Feature/NuriskTestCase.php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

abstract class NuriskTestCase extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\DatabaseSeeder::class);
    }

    /**
     * Buat user dengan role tertentu.
     * id_peran: 1=super_admin, 2=pwnu, 3=pcnu, 4=relawan, 5=publik
     */
    protected function buatUser(int $idPeran, ?int $scopeId = null, string $scopeType = 'pcnu'): \App\Models\AuthUser
    {
        return \App\Models\AuthUser::factory()->create([
            'id_peran'           => $idPeran,
            'default_scope_type' => $scopeType,
            'default_scope_id'   => $scopeId,
            'status_akun'        => 'aktif',
        ]);
    }

    protected function buatUserPwnu(): \App\Models\AuthUser
    {
        return $this->buatUser(2, null, 'pwnu');
    }

    protected function buatUserPcnu(int $idPcnu = 1): \App\Models\AuthUser
    {
        return $this->buatUser(3, $idPcnu, 'pcnu');
    }

    protected function buatUserRelawan(int $idPcnu = 1): \App\Models\AuthUser
    {
        return $this->buatUser(4, $idPcnu, 'pcnu');
    }

    protected function buatUserPublik(): \App\Models\AuthUser
    {
        return $this->buatUser(5, null, 'pwnu');
    }

    protected function buatInsiden(int $idPcnu = 1, string $status = 'terverifikasi'): \App\Models\OperasiInsiden
    {
        return \App\Models\OperasiInsiden::factory()->create([
            'id_pcnu'        => $idPcnu,
            'status_insiden' => $status,
            'is_locked'      => 0,
        ]);
    }
}
```

---

## 3. Feature Test: AUTH

```php
<?php
// tests/Feature/Auth/LoginTest.php

class LoginTest extends NuriskTestCase
{
    /** @test */
    public function user_dapat_login_dengan_no_hp_dan_kata_sandi_valid(): void
    {
        $user = $this->buatUserPcnu();

        $response = $this->post('/login', [
            'no_hp'      => $user->no_hp,
            'kata_sandi' => 'password',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);
    }

    /** @test */
    public function login_gagal_dengan_kredensial_salah(): void
    {
        $this->post('/login', [
            'no_hp'      => '08000000000',
            'kata_sandi' => 'salah',
        ])->assertSessionHasErrors();

        $this->assertGuest();
    }

    /** @test */
    public function akun_menunggu_tidak_dapat_login(): void
    {
        $user = \App\Models\AuthUser::factory()->create([
            'status_akun' => 'menunggu',
        ]);

        $this->post('/login', [
            'no_hp'      => $user->no_hp,
            'kata_sandi' => 'password',
        ])->assertSessionHasErrors('status_akun');

        $this->assertGuest();
    }

    /** @test */
    public function akun_suspend_tidak_dapat_login(): void
    {
        $user = \App\Models\AuthUser::factory()->create([
            'status_akun' => 'suspend',
        ]);

        $this->post('/login', [
            'no_hp'      => $user->no_hp,
            'kata_sandi' => 'password',
        ])->assertSessionHasErrors();
    }

    /** @test */
    public function publik_tidak_dapat_akses_dashboard_internal(): void
    {
        $publik = $this->buatUserPublik();

        $this->actingAs($publik)
             ->get('/dashboard')
             ->assertForbidden();
    }
}
```

---

## 4. Feature Test: INSIDEN

```php
<?php
// tests/Feature/Insiden/InsidenScopeTest.php

class InsidenScopeTest extends NuriskTestCase
{
    /** @test */
    public function pcnu_hanya_dapat_melihat_insiden_scope_sendiri(): void
    {
        $pcnu1 = $this->buatUserPcnu(idPcnu: 1);
        $pcnu2 = $this->buatUserPcnu(idPcnu: 2);

        $insidenPcnu1 = $this->buatInsiden(idPcnu: 1);
        $insidenPcnu2 = $this->buatInsiden(idPcnu: 2);

        $response = $this->actingAs($pcnu1)->getJson('/api/insiden');

        $response->assertOk();
        $response->assertJsonFragment(['id_insiden' => $insidenPcnu1->id_insiden]);
        $response->assertJsonMissing(['id_insiden' => $insidenPcnu2->id_insiden]);
    }

    /** @test */
    public function pwnu_dapat_melihat_semua_insiden_lintas_pcnu(): void
    {
        $pwnu = $this->buatUserPwnu();

        $insiden1 = $this->buatInsiden(idPcnu: 1);
        $insiden2 = $this->buatInsiden(idPcnu: 2);

        $response = $this->actingAs($pwnu)->getJson('/api/insiden');

        $response->assertOk();
        $response->assertJsonFragment(['id_insiden' => $insiden1->id_insiden]);
        $response->assertJsonFragment(['id_insiden' => $insiden2->id_insiden]);
    }

    /** @test */
    public function transisi_status_insiden_mencatat_ke_riwayat(): void
    {
        $pcnu = $this->buatUserPcnu(1);
        $insiden = $this->buatInsiden(1, 'draft');

        $this->actingAs($pcnu)->patch("/insiden/{$insiden->id_insiden}/status", [
            'status_terbaru' => 'terverifikasi',
            'alasan'         => 'Laporan sudah diverifikasi di lapangan',
        ])->assertRedirect();

        $this->assertDatabaseHas('riwayat_status_insiden', [
            'id_insiden'     => $insiden->id_insiden,
            'status_sebelumnya' => 'draft',
            'status_terbaru' => 'terverifikasi',
            'id_pengguna'    => $pcnu->id_pengguna,
        ]);
    }

    /** @test */
    public function insiden_terkunci_tidak_dapat_diubah(): void
    {
        $pcnu = $this->buatUserPcnu(1);
        $insiden = $this->buatInsiden(1, 'selesai');
        $insiden->update(['is_locked' => 1]);

        $response = $this->actingAs($pcnu)->patch("/insiden/{$insiden->id_insiden}", [
            'prioritas' => 'kritis',
        ]);

        // Harus ditolak karena is_locked = 1
        $response->assertForbidden();

        $this->assertDatabaseHas('operasi_insiden', [
            'id_insiden' => $insiden->id_insiden,
            'is_locked'  => 1,
        ]);
    }

    /** @test */
    public function waktu_selesai_tidak_boleh_sebelum_waktu_mulai(): void
    {
        $pcnu = $this->buatUserPcnu(1);

        $response = $this->actingAs($pcnu)->post('/insiden', [
            'id_jenis_bencana' => 1,
            'id_pcnu'          => 1,
            'prioritas'        => 'sedang',
            'waktu_mulai'      => '2026-06-10 10:00:00',
            'waktu_selesai'    => '2026-06-09 10:00:00', // sebelum waktu_mulai
        ]);

        $response->assertSessionHasErrors('waktu_selesai');
    }

    /** @test */
    public function publik_tidak_dapat_membuat_insiden(): void
    {
        $publik = $this->buatUserPublik();

        $this->actingAs($publik)->post('/insiden', [
            'id_jenis_bencana' => 1,
        ])->assertForbidden();
    }

    /** @test */
    public function transisi_tidak_valid_ditolak(): void
    {
        $pcnu = $this->buatUserPcnu(1);
        // Dari 'selesai' tidak boleh transisi ke apapun
        $insiden = $this->buatInsiden(1, 'selesai');
        $insiden->update(['is_locked' => 1]);

        $this->actingAs($pcnu)->patch("/insiden/{$insiden->id_insiden}/status", [
            'status_terbaru' => 'respon',
        ])->assertForbidden();
    }
}
```

---

## 5. Feature Test: ASSESSMENT

```php
<?php
// tests/Feature/Assessment/AssessmentTest.php

class AssessmentTest extends NuriskTestCase
{
    /** @test */
    public function assessment_baru_otomatis_set_is_latest_dan_reset_yang_lama(): void
    {
        $pcnu = $this->buatUserPcnu(1);
        $insiden = $this->buatInsiden(1, 'terverifikasi');

        // Assessment pertama
        $assessment1 = \App\Models\AssessmentUtama::factory()->create([
            'id_insiden' => $insiden->id_insiden,
            'is_latest'  => 1,
        ]);

        // Buat assessment kedua — trigger harus reset is_latest assessment1 menjadi 0
        $this->actingAs($pcnu)->post("/insiden/{$insiden->id_insiden}/assessment", [
            'jenis_laporan'    => 'pendataan_lanjutan',
            'waktu_assesment'  => now()->format('Y-m-d H:i:s'),
            'id_petugas_assessment' => $pcnu->id_pengguna,
        ])->assertRedirect();

        $this->assertDatabaseHas('assessment_utama', [
            'id_assessment_utama' => $assessment1->id_assessment_utama,
            'is_latest'           => 0, // trigger otomatis reset
        ]);
    }

    /** @test */
    public function assessment_tidak_dapat_dibuat_untuk_insiden_draft(): void
    {
        $pcnu = $this->buatUserPcnu(1);
        $insiden = $this->buatInsiden(1, 'draft');

        $this->actingAs($pcnu)->post("/insiden/{$insiden->id_insiden}/assessment", [
            'jenis_laporan'   => 'kaji_cepat',
            'waktu_assesment' => now()->format('Y-m-d H:i:s'),
        ])->assertForbidden();
    }

    /** @test */
    public function koordinat_di_luar_indonesia_ditolak(): void
    {
        $pcnu = $this->buatUserPcnu(1);
        $insiden = $this->buatInsiden(1, 'terverifikasi');

        $response = $this->actingAs($pcnu)->post("/insiden/{$insiden->id_insiden}/assessment", [
            'jenis_laporan'           => 'kaji_cepat',
            'waktu_assesment'         => now()->format('Y-m-d H:i:s'),
            'latitude_titik_kaji'     => 50.0,  // di luar Indonesia
            'longitude_titik_kaji'    => 106.0,
            'id_petugas_assessment'   => $pcnu->id_pengguna,
        ]);

        // Database trigger atau FormRequest harus menolak koordinat ini
        $response->assertSessionHasErrors();
    }
}
```

---

## 6. Feature Test: SITREP

```php
<?php
// tests/Feature/Sitrep/SitrepTest.php

class SitrepTest extends NuriskTestCase
{
    /** @test */
    public function nomor_sitrep_harus_unik_per_insiden(): void
    {
        $pcnu = $this->buatUserPcnu(1);
        $insiden = $this->buatInsiden(1, 'respon');

        // Buat sitrep pertama
        \App\Models\OperasiSitrep::factory()->create([
            'id_insiden'  => $insiden->id_insiden,
            'nomor_sitrep' => 1,
        ]);

        // Coba buat sitrep dengan nomor yang sama
        $response = $this->actingAs($pcnu)->post("/insiden/{$insiden->id_insiden}/sitrep", [
            'nomor_sitrep'   => 1,
            'waktu_pelaporan' => now()->format('Y-m-d H:i:s'),
            'id_petugas'     => $pcnu->id_pengguna,
        ]);

        $response->assertSessionHasErrors('nomor_sitrep');
    }

    /** @test */
    public function sitrep_final_tidak_dapat_diubah(): void
    {
        $pwnu = $this->buatUserPwnu();
        $insiden = $this->buatInsiden(1, 'respon');
        $sitrep = \App\Models\OperasiSitrep::factory()->create([
            'id_insiden'    => $insiden->id_insiden,
            'status_sitrep' => 'final',
        ]);

        $this->actingAs($pwnu)->patch("/sitrep/{$sitrep->id_sitrep}", [
            'kondisi_umum' => 'Diubah setelah final',
        ])->assertForbidden();
    }

    /** @test */
    public function hash_snapshot_terbuat_saat_finalisasi(): void
    {
        $pwnu = $this->buatUserPwnu();
        $insiden = $this->buatInsiden(1, 'respon');
        $sitrep = \App\Models\OperasiSitrep::factory()->create([
            'id_insiden'    => $insiden->id_insiden,
            'status_sitrep' => 'ditinjau',
        ]);

        $this->actingAs($pwnu)->patch("/sitrep/{$sitrep->id_sitrep}/finalisasi")->assertRedirect();

        $this->assertDatabaseHas('operasi_sitrep', [
            'id_sitrep'      => $sitrep->id_sitrep,
            'status_sitrep'  => 'final',
        ]);

        $sitrep->refresh();
        $this->assertNotNull($sitrep->hash_snapshot);
        $this->assertNotNull($sitrep->waktu_difinalisasi);
        $this->assertNotNull($sitrep->id_penfinalisasi);
    }

    /** @test */
    public function sitrep_draft_tidak_dapat_langsung_final(): void
    {
        $pwnu = $this->buatUserPwnu();
        $insiden = $this->buatInsiden(1, 'respon');
        $sitrep = \App\Models\OperasiSitrep::factory()->create([
            'id_insiden'    => $insiden->id_insiden,
            'status_sitrep' => 'draft',
        ]);

        // Tidak boleh lompat dari draft langsung ke final
        $this->actingAs($pwnu)->patch("/sitrep/{$sitrep->id_sitrep}/finalisasi")
             ->assertForbidden();
    }
}
```

---

## 7. Feature Test: PLENO DAN SURAT

```php
<?php
// tests/Feature/Pleno/PlanoSigningTest.php

class PlanoSigningTest extends NuriskTestCase
{
    /** @test */
    public function pleno_final_tidak_dapat_diubah(): void
    {
        $pwnu = $this->buatUserPwnu();
        $insiden = $this->buatInsiden(1, 'respon');
        $pleno = \App\Models\OperasiPleno::factory()->create([
            'id_insiden'  => $insiden->id_insiden,
            'status_pleno' => 'final',
        ]);

        $this->actingAs($pwnu)->patch("/pleno/{$pleno->id_pleno}", [
            'judul_pleno' => 'Diubah setelah final',
        ])->assertForbidden();
    }

    /** @test */
    public function pcnu_tidak_dapat_finalisasi_pleno_lintas_wilayah(): void
    {
        $pcnu1 = $this->buatUserPcnu(1);
        $insiden = $this->buatInsiden(idPcnu: 2); // insiden milik PCNU 2
        $pleno = \App\Models\OperasiPleno::factory()->create([
            'id_insiden'   => $insiden->id_insiden,
            'status_pleno' => 'disetujui',
        ]);

        // PCNU 1 tidak boleh finalisasi pleno insiden PCNU 2
        $this->actingAs($pcnu1)->patch("/pleno/{$pleno->id_pleno}/finalisasi")
             ->assertForbidden();
    }

    /** @test */
    public function pcnu_tidak_dapat_melakukan_eskalasi_mandiri(): void
    {
        $pcnu = $this->buatUserPcnu(1);
        $insiden = $this->buatInsiden(1, 'respon');

        $this->actingAs($pcnu)->post('/eskalasi', [
            'id_insiden'        => $insiden->id_insiden,
            'level_sebelumnya'  => 'lokal',
            'level_baru'        => 'pcnu',
            'alasan_eskalasi'   => 'Butuh bantuan lebih',
            'id_pleno'          => 1,
        ])->assertForbidden();
    }
}

// tests/Feature/Surat/SuratSigningTest.php

class SuratSigningTest extends NuriskTestCase
{
    /** @test */
    public function paraf_surat_harus_berurutan_sesuai_urutan(): void
    {
        $user2 = $this->buatUserPcnu(1);
        $surat = \App\Models\DokumenSuratUtama::factory()->create();

        // Buat 2 paraf: urutan 1 dan 2
        $paraf1 = \App\Models\DokumenSuratParaf::factory()->create([
            'id_surat' => $surat->id_surat,
            'urutan'   => 1,
            'status_paraf' => 'menunggu',
        ]);
        $paraf2 = \App\Models\DokumenSuratParaf::factory()->create([
            'id_surat' => $surat->id_surat,
            'urutan'   => 2,
            'status_paraf' => 'menunggu',
        ]);

        // User2 mencoba paraf urutan 2 sebelum urutan 1 selesai — harus ditolak
        $this->actingAs($user2)->patch("/surat/{$surat->id_surat}/paraf/{$paraf2->id_paraf}", [
            'status_paraf' => 'disetujui',
        ])->assertForbidden();
    }

    /** @test */
    public function paraf_ditolak_mengembalikan_surat_ke_draft(): void
    {
        $user = $this->buatUserPcnu(1);
        $surat = \App\Models\DokumenSuratUtama::factory()->create([
            'status_surat' => 'review',
        ]);
        $paraf = \App\Models\DokumenSuratParaf::factory()->create([
            'id_surat'    => $surat->id_surat,
            'id_pengguna' => $user->id_pengguna,
            'urutan'      => 1,
            'status_paraf' => 'menunggu',
        ]);

        $this->actingAs($user)->patch("/surat/{$surat->id_surat}/paraf/{$paraf->id_paraf}", [
            'status_paraf' => 'ditolak',
            'catatan'      => 'Ada kesalahan pada isi surat',
        ])->assertRedirect();

        $this->assertDatabaseHas('operasi_surat_keluar', [
            'id_surat'    => $surat->id_surat,
            'status_surat' => 'draft', // harus kembali ke draft
        ]);
    }

    /** @test */
    public function surat_final_tidak_dapat_diedit(): void
    {
        $pwnu = $this->buatUserPwnu();
        $surat = \App\Models\DokumenSuratUtama::factory()->create([
            'status_surat' => 'finalized',
        ]);

        $this->actingAs($pwnu)->patch("/surat/{$surat->id_surat}", [
            'perihal' => 'Ubah setelah final',
        ])->assertForbidden();
    }
}
```

---

## 8. Feature Test: LOGISTIK (STOCK MUTATION)

```php
<?php
// tests/Feature/Logistik/MutasiLogistikTest.php

class MutasiLogistikTest extends NuriskTestCase
{
    protected \App\Models\LogistikStok $stok;

    public function setUp(): void
    {
        parent::setUp();
        // Setup stok awal dengan jumlah 100
        $this->stok = \App\Models\LogistikStok::factory()->create([
            'jumlah_tersedia' => 100.00,
        ]);
    }

    /** @test */
    public function mutasi_masuk_menambah_jumlah_tersedia(): void
    {
        $pcnu = $this->buatUserPcnu(1);

        $this->actingAs($pcnu)->post('/logistik/mutasi', [
            'id_stok'      => $this->stok->id_stok,
            'tipe_mutasi'  => 'masuk',
            'jumlah'       => 50,
            'asal_tujuan'  => 'Gudang Pusat',
            'keterangan'   => 'Kiriman dari PWNU',
        ])->assertRedirect();

        $this->assertDatabaseHas('logistik_stok', [
            'id_stok'         => $this->stok->id_stok,
            'jumlah_tersedia' => 150.00, // 100 + 50
        ]);
    }

    /** @test */
    public function mutasi_keluar_melebihi_stok_ditolak(): void
    {
        $pcnu = $this->buatUserPcnu(1);

        $response = $this->actingAs($pcnu)->post('/logistik/mutasi', [
            'id_stok'     => $this->stok->id_stok,
            'tipe_mutasi' => 'keluar',
            'jumlah'      => 150, // melebihi stok 100
            'asal_tujuan' => 'Pos Aju 1',
        ]);

        // Trigger database atau service harus menolak ini
        $response->assertSessionHasErrors();

        $this->assertDatabaseHas('logistik_stok', [
            'id_stok'         => $this->stok->id_stok,
            'jumlah_tersedia' => 100.00, // tidak berubah
        ]);
    }

    /** @test */
    public function mutasi_penyesuaian_mengeset_nilai_absolut(): void
    {
        $pcnu = $this->buatUserPcnu(1);

        $this->actingAs($pcnu)->post('/logistik/mutasi', [
            'id_stok'     => $this->stok->id_stok,
            'tipe_mutasi' => 'penyesuaian',
            'jumlah'      => 75,
            'asal_tujuan' => 'Penyesuaian stok opname',
        ])->assertRedirect();

        $this->assertDatabaseHas('logistik_stok', [
            'id_stok'         => $this->stok->id_stok,
            'jumlah_tersedia' => 75.00, // absolute value, bukan tambah/kurang
        ]);
    }

    /** @test */
    public function update_langsung_logistik_stok_tidak_diizinkan(): void
    {
        // Di aplikasi: DILARANG memanggil $stok->update(['jumlah_tersedia' => X]) langsung
        // Test ini memverifikasi bahwa endpoint update stok tidak tersedia
        $pcnu = $this->buatUserPcnu(1);

        // Tidak boleh ada route PATCH /logistik/stok/{id} yang mengubah jumlah_tersedia
        $this->actingAs($pcnu)
             ->patch("/logistik/stok/{$this->stok->id_stok}", ['jumlah_tersedia' => 999])
             ->assertNotFound(); // route tidak boleh ada
    }

    /** @test */
    public function gudang_pcnu_lain_tidak_dapat_mensuplai_insiden(): void
    {
        // Trigger tr_validate_stock_ownership harus menolak ini
        $pcnu1 = $this->buatUserPcnu(1);

        $gudangPcnu2 = \App\Models\LogistikGudang::factory()->create(['id_pcnu' => 2]);
        $insiden_pcnu1 = $this->buatInsiden(1, 'respon');
        $posaju = \App\Models\OperasiPosaju::factory()->create(['id_insiden' => $insiden_pcnu1->id_insiden]);

        $stokBaru = \App\Models\LogistikStok::factory()->make([
            'id_posaju' => $posaju->id_posaju,
            'id_gudang' => $gudangPcnu2->id_gudang, // gudang PCNU 2
        ]);

        // Insert ini harus ditolak oleh trigger tr_validate_stock_ownership
        $this->expectException(\Illuminate\Database\QueryException::class);
        $stokBaru->save();
    }
}
```

---

## 9. Feature Test: AUTHORIZATION

```php
<?php
// tests/Feature/Auth/AuthorizationTest.php

class AuthorizationTest extends NuriskTestCase
{
    /** @test */
    public function relawan_tidak_ditugaskan_tidak_dapat_membuat_sitrep(): void
    {
        $relawan = $this->buatUserRelawan(1);
        $insiden = $this->buatInsiden(1, 'respon');

        // Tidak ada operasi_penugasan aktif untuk relawan ini
        $this->actingAs($relawan)->post("/insiden/{$insiden->id_insiden}/sitrep", [
            'nomor_sitrep'   => 1,
            'waktu_pelaporan' => now()->format('Y-m-d H:i:s'),
        ])->assertForbidden();
    }

    /** @test */
    public function relawan_yang_ditugaskan_dapat_membuat_sitrep(): void
    {
        $relawan = $this->buatUserRelawan(1);
        $insiden = $this->buatInsiden(1, 'respon');

        // Buat assignment aktif
        \App\Models\OperasiPenugasan::factory()->create([
            'id_insiden'    => $insiden->id_insiden,
            'id_pengguna'   => $relawan->id_pengguna,
            'peran_otoritas' => 'trc',
            'waktu_selesai'  => null, // masih aktif
        ]);

        $this->actingAs($relawan)->post("/insiden/{$insiden->id_insiden}/sitrep", [
            'nomor_sitrep'   => 1,
            'waktu_pelaporan' => now()->format('Y-m-d H:i:s'),
            'id_petugas'     => $relawan->id_pengguna,
        ])->assertRedirect(); // boleh
    }

    /** @test */
    public function pcnu_tidak_dapat_akses_logistik_pcnu_lain(): void
    {
        $pcnu1 = $this->buatUserPcnu(1);
        $gudangPcnu2 = \App\Models\LogistikGudang::factory()->create(['id_pcnu' => 2]);

        $this->actingAs($pcnu1)
             ->get("/logistik/gudang/{$gudangPcnu2->id_gudang}")
             ->assertForbidden();
    }

    /** @test */
    public function super_admin_dapat_akses_semua_data(): void
    {
        $superAdmin = $this->buatUser(1);
        $insidenPcnu99 = $this->buatInsiden(99, 'respon');

        $this->actingAs($superAdmin)
             ->get("/insiden/{$insidenPcnu99->id_insiden}")
             ->assertOk();
    }
}
```

---

## 10. Feature Test: RELAWAN

```php
<?php
// tests/Feature/Relawan/RelawanTest.php

class RelawanTest extends NuriskTestCase
{
    /** @test */
    public function relawan_tidak_dapat_mendaftar_dua_kali_untuk_kebutuhan_yang_sama(): void
    {
        $relawan = $this->buatUserRelawan(1);
        $kebutuhan = \App\Models\RelawanKebutuhan::factory()->create(); // tabel terkait

        // Pendaftaran pertama
        \App\Models\RelawanPendaftaran::factory()->create([
            'id_pengguna'          => $relawan->id_pengguna,
            'id_relawan_kebutuhan' => $kebutuhan->id,
        ]);

        // Pendaftaran kedua untuk kebutuhan yang sama harus ditolak (UNIQUE constraint)
        $this->actingAs($relawan)->post('/relawan/daftar', [
            'id_relawan_kebutuhan' => $kebutuhan->id,
        ])->assertSessionHasErrors();
    }

    /** @test */
    public function relawan_belum_terverifikasi_tidak_dapat_ditugaskan(): void
    {
        $pcnu = $this->buatUserPcnu(1);
        $relawan = $this->buatUserRelawan(1);
        $insiden = $this->buatInsiden(1, 'respon');

        // Buat pendaftaran relawan yang belum terverifikasi
        \App\Models\RelawanPendaftaran::factory()->create([
            'id_pengguna'   => $relawan->id_pengguna,
            'status'        => 'menunggu', // belum terverifikasi
        ]);

        $this->actingAs($pcnu)->post('/penugasan', [
            'id_insiden'     => $insiden->id_insiden,
            'id_pengguna'    => $relawan->id_pengguna,
            'peran_otoritas' => 'relawan',
            'waktu_mulai'    => now()->format('Y-m-d H:i:s'),
        ])->assertForbidden();
    }

    /** @test */
    public function relawan_dapat_ditugaskan_lintas_pcnu(): void
    {
        $komandan = $this->buatUserPcnu(2); // komandan PCNU 2
        $relawan = $this->buatUserRelawan(1); // relawan dari PCNU 1
        $insiden = $this->buatInsiden(2, 'respon'); // insiden di PCNU 2

        // Verifikasi relawan terlebih dahulu
        \App\Models\RelawanPendaftaran::factory()->create([
            'id_pengguna' => $relawan->id_pengguna,
            'status'      => 'aktif',
        ]);

        $response = $this->actingAs($komandan)->post('/penugasan', [
            'id_insiden'     => $insiden->id_insiden,
            'id_pengguna'    => $relawan->id_pengguna,
            'peran_otoritas' => 'relawan',
            'asal_lingkup'   => 'PCNU Kota Semarang', // cross-region dicatat
            'tujuan_lingkup' => 'PCNU Kabupaten Demak',
            'waktu_mulai'    => now()->format('Y-m-d H:i:s'),
        ]);

        $response->assertRedirect();

        // Organisasi asal tidak berubah
        $this->assertDatabaseHas('auth_users', [
            'id_pengguna' => $relawan->id_pengguna,
            'id_unit'     => $relawan->id_unit, // tidak berubah
        ]);

        // Penugasan lintas wilayah tercatat
        $this->assertDatabaseHas('operasi_penugasan', [
            'id_pengguna'   => $relawan->id_pengguna,
            'asal_lingkup'  => 'PCNU Kota Semarang',
            'tujuan_lingkup' => 'PCNU Kabupaten Demak',
        ]);
    }
}
```

---

## 11. Feature Test: ASET

```php
<?php
// tests/Feature/Aset/AsetTest.php

class AsetTest extends NuriskTestCase
{
    /** @test */
    public function aset_yang_sedang_bertugas_tidak_dapat_dipinjam_lagi(): void
    {
        $pcnu = $this->buatUserPcnu(1);
        $insiden = $this->buatInsiden(1, 'respon');

        // Aset dalam status "Dalam Tugas" (id_status = 2)
        $aset = \App\Models\AsetUnit::factory()->create(['id_status' => 2]);

        // Coba pinjam aset yang sudah bertugas — trigger harus menolak
        $response = $this->actingAs($pcnu)->post('/aset/pinjam', [
            'id_unit_aset'        => $aset->id_unit_aset,
            'id_insiden'          => $insiden->id_insiden,
            'id_pengguna_peminjam' => $pcnu->id_pengguna,
            'waktu_pinjam'        => now()->format('Y-m-d H:i:s'),
            'tujuan_penggunaan'   => 'Evakuasi korban',
        ]);

        $response->assertSessionHasErrors(); // trigger DB menolak
    }

    /** @test */
    public function aset_kembali_tersedia_setelah_waktu_kembali_diisi(): void
    {
        $pcnu = $this->buatUserPcnu(1);
        $insiden = $this->buatInsiden(1, 'respon');

        $aset = \App\Models\AsetUnit::factory()->create(['id_status' => 1]);

        // Pinjam aset
        $penggunaan = \App\Models\AsetPenggunaan::factory()->create([
            'id_unit_aset'        => $aset->id_unit_aset,
            'id_insiden'          => $insiden->id_insiden,
            'waktu_pinjam'        => now(),
            'waktu_kembali'       => null,
        ]);

        $this->assertDatabaseHas('aset_unit', [
            'id_unit_aset' => $aset->id_unit_aset,
            'id_status'    => 2, // Dalam Tugas
        ]);

        // Set waktu_kembali — trigger tr_aset_return_to_available harus set status ke 1
        $penggunaan->update(['waktu_kembali' => now()]);

        $this->assertDatabaseHas('aset_unit', [
            'id_unit_aset' => $aset->id_unit_aset,
            'id_status'    => 1, // Kembali Tersedia
        ]);
    }
}
```

---

## 12. Larangan Testing

| Larangan | Alasan |
|---|---|
| Test hanya cek HTTP 200 tanpa validasi data | Tidak membuktikan logika bisnis benar |
| Mock database trigger | Trigger adalah bagian dari domain logic NURISK |
| Skip test authorization karena "complex" | Authorization adalah area paling kritis |
| Factory yang menghasilkan data melanggar constraint | Factory harus menghasilkan data valid |
| `RefreshDatabase` tanpa seed data | Seed master data wajib ada agar test bisa berjalan |
| Test dengan SQLite jika ada trigger | SQLite tidak mendukung trigger MariaDB/MySQL |

---

## 13. Standar Penamaan Test

Format nama test method:

```
{subjek}_{kondisi}_{hasil_yang_diharapkan}()
```

Contoh:
- `relawan_tidak_ditugaskan_tidak_dapat_membuat_sitrep()`
- `mutasi_keluar_melebihi_stok_ditolak()`
- `insiden_terkunci_tidak_dapat_diubah()`
- `pcnu_hanya_dapat_melihat_insiden_scope_sendiri()`

---

## 14. CI/CD Requirements

```bash
# Jalankan sebelum setiap merge ke main
php artisan test --env=testing

# Coverage check
php artisan test --coverage --min=70
```

**Coverage minimum per area:**
| Area | Coverage Minimum |
|---|---|
| Controller | 70% |
| Policy | 80% |
| Service/FormRequest | 70% |
| Model (relasi) | 60% |

---

## 15. Factory Guidelines

Setiap Model wajib memiliki Factory yang:
1. Menghasilkan data valid secara default
2. Menggunakan nama kolom nyata dari SQL (bukan `created_at`, gunakan `dibuat_pada`)
3. Mendefinisikan state method untuk kondisi khusus:

```php
class OperasiInsidenFactory extends Factory
{
    protected $model = OperasiInsiden::class;

    public function definition(): array
    {
        return [
            'kode_kejadian'    => 'INS-' . $this->faker->unique()->numerify('######'),
            'id_jenis_bencana' => 1,
            'id_pcnu'          => 1,
            'status_insiden'   => 'draft',
            'status_operasi'   => 'monitoring',
            'prioritas'        => 'sedang',
            'waktu_mulai'      => now(),
            'is_locked'        => 0,
            'dibuat_pada'      => now(),
            'diperbarui_pada'  => now(),
        ];
    }

    public function terkunci(): static
    {
        return $this->state(['status_insiden' => 'selesai', 'is_locked' => 1]);
    }

    public function respon(): static
    {
        return $this->state(['status_insiden' => 'respon', 'status_operasi' => 'tanggap_darurat']);
    }
}
```
