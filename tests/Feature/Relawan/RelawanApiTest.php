<?php

namespace Tests\Feature\Relawan;

use App\Models\AuthKeahlianMaster;
use App\Models\AuthPenggunaProfil;
use App\Models\AuthRole;
use App\Models\AuthUser;
use App\Models\OperasiInsiden;
use App\Models\OperasiPosaju;
use App\Models\RelawanKebutuhan;
use App\Models\RelawanPendaftaran;
use App\Models\RelawanPenugasan;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\Support\CreatesOperasiSchema;
use Tests\Support\CreatesRelawanSchema;
use Tests\TestCase;

class RelawanApiTest extends TestCase
{
    use DatabaseTransactions, CreatesOperasiSchema, CreatesRelawanSchema;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createOperasiSchema();
        $this->createRelawanSchema();
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
            'kode_kejadian' => 'INS-REL-001',
            'id_jenis_bencana' => 1,
            'waktu_mulai' => now(),
        ]);
    }

    // ─────────────────────────────────────────────────────────
    // 1. Pendaftaran Relawan
    // ─────────────────────────────────────────────────────────

    public function test_relawan_can_register_success()
    {
        $relawan = $this->mockUser('relawan', 'pcnu', 1);
        $insiden = $this->createInsiden(1);
        
        $kebutuhan = RelawanKebutuhan::create(['deskripsi_tugas' => 'Deskripsi Default', 'judul_posisi' => 'Default Posisi', 'id_insiden' => $insiden->id_insiden,
            'judul_posisi' => 'Evakuator TRC',
            'jumlah_dibutuhkan' => 5,
            'status_rekrutmen' => 'dibuka',
        ]);

        $response = $this->actingAs($relawan)->postJson(route('api.relawan.daftar'), [
            'id_relawan_kebutuhan' => $kebutuhan->id_relawan_kebutuhan,
            'motivasi_singkat' => 'Ingin mengabdi untuk kemanusiaan',
        ]);

        $response->assertStatus(201)
                 ->assertJsonPath('data.status', 'seleksi')
                 ->assertJsonPath('data.motivasi', 'Ingin mengabdi untuk kemanusiaan')
                 ->assertJsonPath('data.kebutuhan.id', $kebutuhan->id_relawan_kebutuhan)
                 ->assertJsonMissingPath('data.motivasi_singkat')
                 ->assertJsonMissingPath('data.status_pendaftaran');

        $this->assertDatabaseHas('relawan_pendaftaran', [
            'id_pengguna' => $relawan->id_pengguna,
            'id_relawan_kebutuhan' => $kebutuhan->id_relawan_kebutuhan,
            'status_pendaftaran' => 'seleksi',
        ]);
    }

    // ─────────────────────────────────────────────────────────
    // 2. Approval & Rejection
    // ─────────────────────────────────────────────────────────

    public function test_admin_can_approve_registration()
    {
        $admin = $this->mockUser('pcnu', 'pcnu', 1);
        $relawan = $this->mockUser('relawan', 'pcnu', 1);
        $insiden = $this->createInsiden(1);
        
        $kebutuhan = RelawanKebutuhan::create(['deskripsi_tugas' => 'Deskripsi Default', 'judul_posisi' => 'Default Posisi', 'id_insiden' => $insiden->id_insiden,
            'judul_posisi' => 'Medis',
            'jumlah_dibutuhkan' => 2,
            'status_rekrutmen' => 'dibuka',
        ]);

        $pendaftaran = RelawanPendaftaran::create([
            'id_relawan_kebutuhan' => $kebutuhan->id_relawan_kebutuhan,
            'id_pengguna' => $relawan->id_pengguna,
            'status_pendaftaran' => 'seleksi',
        ]);

        $response = $this->actingAs($admin)->postJson(route('api.relawan.pendaftaran.approve', $pendaftaran));

        $response->assertStatus(200)
                 ->assertJsonPath('data.status', 'diterima');

        $this->assertEquals('diterima', $pendaftaran->fresh()->status_pendaftaran);
    }

    public function test_admin_can_reject_registration()
    {
        $admin = $this->mockUser('pcnu', 'pcnu', 1);
        $relawan = $this->mockUser('relawan', 'pcnu', 1);
        $insiden = $this->createInsiden(1);
        
        $kebutuhan = RelawanKebutuhan::create(['deskripsi_tugas' => 'Deskripsi Default', 'judul_posisi' => 'Default Posisi', 'id_insiden' => $insiden->id_insiden,
            'judul_posisi' => 'Medis',
            'jumlah_dibutuhkan' => 2,
            'status_rekrutmen' => 'dibuka',
        ]);

        $pendaftaran = RelawanPendaftaran::create([
            'id_relawan_kebutuhan' => $kebutuhan->id_relawan_kebutuhan,
            'id_pengguna' => $relawan->id_pengguna,
            'status_pendaftaran' => 'seleksi',
        ]);

        $response = $this->actingAs($admin)->postJson(route('api.relawan.pendaftaran.reject', $pendaftaran), [
            'catatan_verifikator' => 'Berkas kurang lengkap',
        ]);

        $response->assertStatus(200)
                 ->assertJsonPath('data.status', 'ditolak')
                 ->assertJsonPath('data.catatan_verifikator', 'Berkas kurang lengkap');

        $this->assertEquals('ditolak', $pendaftaran->fresh()->status_pendaftaran);
    }

    // ─────────────────────────────────────────────────────────
    // 3. Assignment & Completion
    // ─────────────────────────────────────────────────────────

    public function test_admin_can_assign_volunteer()
    {
        $admin = $this->mockUser('pcnu', 'pcnu', 1);
        $relawan = $this->mockUser('relawan', 'pcnu', 1);
        $insiden = $this->createInsiden(1);
        
        $posaju = OperasiPosaju::create(['alamat_lokasi' => 'Alamat Default', 'id_insiden' => $insiden->id_insiden,
            'pj_posaju' => $admin->id_pengguna,
            'nama_posaju' => 'Pos Aju Relawan',
            'status_alur' => 'aktif',
        ]);

        $kebutuhan = RelawanKebutuhan::create(['deskripsi_tugas' => 'Deskripsi Default', 'judul_posisi' => 'Default Posisi', 'id_insiden' => $insiden->id_insiden,
            'judul_posisi' => 'Logistik',
            'jumlah_dibutuhkan' => 2,
            'status_rekrutmen' => 'dibuka',
        ]);

        $pendaftaran = RelawanPendaftaran::create([
            'id_relawan_kebutuhan' => $kebutuhan->id_relawan_kebutuhan,
            'id_pengguna' => $relawan->id_pengguna,
            'status_pendaftaran' => 'diterima', // Must be diterima to assign
        ]);

        $response = $this->actingAs($admin)->postJson(route('api.relawan.pendaftaran.assign', $pendaftaran), [
            'id_posaju' => $posaju->id_posaju,
            'peran_lapangan' => 'Dapur Umum',
        ]);

        $response->assertStatus(201)
                 ->assertJsonPath('data.peran', 'Dapur Umum')
                 ->assertJsonPath('data.posaju.nama', 'Pos Aju Relawan')
                 ->assertJsonMissingPath('data.peran_lapangan');

        $this->assertEquals('ditugaskan', $pendaftaran->fresh()->status_pendaftaran);
        $this->assertDatabaseHas('relawan_penugasan', [
            'id_pendaftaran' => $pendaftaran->id_pendaftaran,
            'id_posaju' => $posaju->id_posaju,
            'status_aktif' => true,
        ]);
    }

    public function test_admin_can_complete_assignment()
    {
        $admin = $this->mockUser('pcnu', 'pcnu', 1);
        $relawan = $this->mockUser('relawan', 'pcnu', 1);
        $insiden = $this->createInsiden(1);

        $kebutuhan = RelawanKebutuhan::create(['deskripsi_tugas' => 'Deskripsi Default', 'judul_posisi' => 'Default Posisi', 'id_insiden' => $insiden->id_insiden,
            'judul_posisi' => 'Logistik',
            'jumlah_dibutuhkan' => 2,
            'status_rekrutmen' => 'dibuka',
        ]);

        $pendaftaran = RelawanPendaftaran::create([
            'id_relawan_kebutuhan' => $kebutuhan->id_relawan_kebutuhan,
            'id_pengguna' => $relawan->id_pengguna,
            'status_pendaftaran' => 'ditugaskan',
        ]);

        $penugasan = RelawanPenugasan::create([
            'id_pendaftaran' => $pendaftaran->id_pendaftaran,
            'status_aktif' => true,
        ]);

        $response = $this->actingAs($admin)->postJson(route('api.relawan.penugasan.complete', $penugasan));

        $response->assertStatus(200)
                 ->assertJsonPath('data.status_aktif', false);

        $this->assertEquals('selesai', $pendaftaran->fresh()->status_pendaftaran);
        $this->assertFalse((bool) $penugasan->fresh()->status_aktif);
    }

    // ─────────────────────────────────────────────────────────
    // 4. Profil & Keahlian
    // ─────────────────────────────────────────────────────────

    public function test_relawan_can_view_own_profile()
    {
        $relawan = $this->mockUser('relawan', 'pcnu', 1);
        $profil = AuthPenggunaProfil::create([
            'id_pengguna' => $relawan->id_pengguna,
            'nik' => '3201010101010001',
            'nama_lengkap' => 'Ahmad Relawan',
            'email' => 'ahmad@relawan.nu',
        ]);

        $response = $this->actingAs($relawan)->getJson(route('api.relawan.profil.show', $profil));

        $response->assertStatus(200)
                 ->assertJsonPath('data.nama', 'Ahmad Relawan')
                 ->assertJsonPath('data.nik', '3201010101010001')
                 ->assertJsonMissingPath('data.nama_lengkap');
    }

    public function test_unauthorized_user_cannot_view_other_profile()
    {
        $relawan1 = $this->mockUser('relawan', 'pcnu', 1);
        $relawan2 = $this->mockUser('relawan', 'pcnu', 1);

        $profil2 = AuthPenggunaProfil::create([
            'id_pengguna' => $relawan2->id_pengguna,
            'nik' => '3201010101010002',
            'nama_lengkap' => 'Budi Relawan',
            'email' => 'budi@relawan.nu',
        ]);

        $response = $this->actingAs($relawan1)->getJson(route('api.relawan.profil.show', $profil2));

        $response->assertStatus(403);
    }

    public function test_relawan_can_sync_skills()
    {
        $relawan = $this->mockUser('relawan', 'pcnu', 1);
        
        $profil = AuthPenggunaProfil::create([
            'id_pengguna' => $relawan->id_pengguna,
            'nik' => '3201010101010001',
            'nama_lengkap' => 'Ahmad Relawan',
            'email' => 'ahmad@relawan.nu',
        ]);

        $skill1 = AuthKeahlianMaster::create(['nama_keahlian' => 'Navigasi Darat']);
        $skill2 = AuthKeahlianMaster::create(['nama_keahlian' => 'P3K']);

        $response = $this->actingAs($relawan)->postJson(route('api.relawan.profil.sync_skills', $profil), [
            'keahlian' => [$skill1->id_keahlian, $skill2->id_keahlian]
        ]);

        $response->assertStatus(200)
                 ->assertJsonCount(2, 'data.keahlian')
                 ->assertJsonPath('data.keahlian.0.nama', 'Navigasi Darat');
    }

    // ─────────────────────────────────────────────────────────
    // 5. Soft Delete Consistency Tests
    // ─────────────────────────────────────────────────────────

    public function test_soft_deleted_pendaftaran_returns_404()
    {
        $admin = $this->mockUser('pcnu', 'pcnu', 1);
        $relawan = $this->mockUser('relawan', 'pcnu', 1);
        $insiden = $this->createInsiden(1);
        
        $kebutuhan = RelawanKebutuhan::create(['deskripsi_tugas' => 'Deskripsi Default', 'judul_posisi' => 'Default Posisi', 'id_insiden' => $insiden->id_insiden,
            'judul_posisi' => 'Medis',
            'jumlah_dibutuhkan' => 2,
            'status_rekrutmen' => 'dibuka',
        ]);

        $pendaftaran = RelawanPendaftaran::create([
            'id_relawan_kebutuhan' => $kebutuhan->id_relawan_kebutuhan,
            'id_pengguna' => $relawan->id_pengguna,
            'status_pendaftaran' => 'seleksi',
        ]);

        // Soft delete the pendaftaran record
        $pendaftaran->delete();

        $response = $this->actingAs($admin)->postJson(route('api.relawan.pendaftaran.approve', $pendaftaran));

        // It should return 404 since Route Model Binding excludes soft deleted records by default
        $response->assertStatus(404);
    }

    public function test_soft_deleted_penugasan_returns_404()
    {
        $admin = $this->mockUser('pcnu', 'pcnu', 1);
        $relawan = $this->mockUser('relawan', 'pcnu', 1);
        $insiden = $this->createInsiden(1);

        $kebutuhan = RelawanKebutuhan::create(['deskripsi_tugas' => 'Deskripsi Default', 'judul_posisi' => 'Default Posisi', 'id_insiden' => $insiden->id_insiden,
            'judul_posisi' => 'Logistik',
            'jumlah_dibutuhkan' => 2,
            'status_rekrutmen' => 'dibuka',
        ]);

        $pendaftaran = RelawanPendaftaran::create([
            'id_relawan_kebutuhan' => $kebutuhan->id_relawan_kebutuhan,
            'id_pengguna' => $relawan->id_pengguna,
            'status_pendaftaran' => 'ditugaskan',
        ]);

        $penugasan = RelawanPenugasan::create([
            'id_pendaftaran' => $pendaftaran->id_pendaftaran,
            'status_aktif' => true,
        ]);

        // Soft delete the penugasan record
        $penugasan->delete();

        $response = $this->actingAs($admin)->postJson(route('api.relawan.penugasan.complete', $penugasan));

        // It should return 404 since Route Model Binding excludes soft deleted records by default
        $response->assertStatus(404);
    }
}
