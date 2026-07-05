<?php

namespace Tests\Feature\Relawan;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Schema;
use Tests\Support\CreatesRelawanSchema;
use App\Models\AuthUser;
use App\Models\AuthRole;
use App\Models\RelawanKebutuhan;
use App\Models\RelawanPendaftaran;
use App\Models\RelawanPenugasan;
use App\Models\AuthKeahlianMaster;
use App\Services\Relawan\RelawanService;
use Illuminate\Validation\ValidationException;

class RelawanServiceTest extends TestCase
{
    use DatabaseTransactions;
    use CreatesRelawanSchema;

    protected RelawanService $service;
    protected int $idInsiden;
    protected int $idPosaju;

    protected function setUp(): void
    {
        parent::setUp();

        Schema::disableForeignKeyConstraints();


        Schema::enableForeignKeyConstraints();

        $this->createRelawanSchema();

        AuthRole::insertOrIgnore([
            ['id_peran' => 1, 'nama_peran' => 'super_admin', 'level_otoritas' => 1],
            ['id_peran' => 4, 'nama_peran' => 'relawan', 'level_otoritas' => 4],
        ]);

        $this->service = app(RelawanService::class);

        // Seed PCNU unit, PCNU, and master jenis bencana
        \Illuminate\Support\Facades\DB::table('organisasi_unit')->insertOrIgnore(['id_unit' => 1, 'parent_id' => 99, 'nama_unit' => 'PCNU Cilacap', 'tipe_unit' => 'pcnu']);
        \Illuminate\Support\Facades\DB::table('organisasi_unit')->insertOrIgnore(['id_unit' => 99, 'nama_unit' => 'PWNU Jatim', 'tipe_unit' => 'pwnu']);
        \Illuminate\Support\Facades\DB::table('organisasi_pcnu')->insertOrIgnore(['id_pcnu' => 1, 'id_unit' => 1, 'nama_pcnu' => 'PCNU Cilacap']);
        \Illuminate\Support\Facades\DB::table('bencana_master_jenis')->insertOrIgnore(['id_jenis' => 1, 'nama_bencana' => 'Banjir', 'slug' => 'banjir']);

        $insiden = \App\Models\OperasiInsiden::forceCreate([
            'id_pcnu' => 1,
            'kode_kejadian' => 'INS-TEST-001',
            'id_jenis_bencana' => 1,
            'waktu_mulai' => now(),
        ]);
        $this->idInsiden = $insiden->id_insiden;
        $this->uuidInsiden = $insiden->uuid_insiden;

        $userPj = AuthUser::create([
            'no_hp' => '08' . rand(100000000, 999999999),
            'kata_sandi' => bcrypt('password'),
            'id_peran' => \App\Models\AuthRole::firstOrCreate(['nama_peran' => 'super_admin'], ['level_otoritas' => 1])->id_peran,
            'status_akun' => 'aktif',
        ]);

        $this->idPosaju = \Illuminate\Support\Facades\DB::table('operasi_posaju')->insertGetId([
            'id_insiden' => $this->idInsiden,
            'nama_posaju' => 'Pos Aju Cilacap',
            'pj_posaju' => $userPj->id_pengguna,
            'status_alur' => 'aktif',
        ]);
    }

    private function createVolunteerUser(): AuthUser
    {
        return AuthUser::create([
            'no_hp' => '0812' . rand(1000000, 9999999),
            'kata_sandi' => bcrypt('password'),
            'id_peran' => \App\Models\AuthRole::firstOrCreate(['nama_peran' => 'relawan'], ['level_otoritas' => 4])->id_peran,
            'status_akun' => 'aktif',
        ]);
    }

    private function createKebutuhan(string $status = 'dibuka', int $kuota = 10): RelawanKebutuhan
    {
        return RelawanKebutuhan::create(['deskripsi_tugas' => 'Deskripsi Default', 'judul_posisi' => 'Default Posisi', 'id_insiden' => $this->idInsiden,
            'jumlah_dibutuhkan' => $kuota,
            'tgl_mulai_tugas' => now()->toDateString(),
            'tgl_selesai_tugas' => now()->addDays(7)->toDateString(),
            'deskripsi_tugas' => 'Bantu dapur umum',
            'status_rekrutmen' => $status
        ]);
    }

    // ==========================================
    // 1. REGISTER VOLUNTEER
    // ==========================================

    public function test_relawan_dapat_mendaftar_ke_kebutuhan_terbuka()
    {
        $user = $this->createVolunteerUser();
        $kebutuhan = $this->createKebutuhan('dibuka');

        $pendaftaran = $this->service->registerVolunteer($user->id_pengguna, $kebutuhan->id_relawan_kebutuhan, 'Saya siap');

        $this->assertNotNull($pendaftaran);
        $this->assertEquals('seleksi', $pendaftaran->status_pendaftaran);
        $this->assertEquals($user->id_pengguna, $pendaftaran->id_pengguna);
        $this->assertEquals('Saya siap', $pendaftaran->motivasi_singkat);
        
        $this->assertDatabaseHas('relawan_pendaftaran', [
            'id_pendaftaran' => $pendaftaran->id_pendaftaran,
            'status_pendaftaran' => 'seleksi'
        ]);
    }

    public function test_relawan_gagal_mendaftar_jika_kebutuhan_ditutup()
    {
        $user = $this->createVolunteerUser();
        $kebutuhan = $this->createKebutuhan('ditutup');

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Kebutuhan relawan sudah tidak dibuka.');
        $this->service->registerVolunteer($user->id_pengguna, $kebutuhan->id_relawan_kebutuhan);
    }

    public function test_relawan_gagal_mendaftar_jika_kebutuhan_terpenuhi()
    {
        $user = $this->createVolunteerUser();
        $kebutuhan = $this->createKebutuhan('terpenuhi');

        $this->expectException(ValidationException::class);
        $this->service->registerVolunteer($user->id_pengguna, $kebutuhan->id_relawan_kebutuhan);
    }

    public function test_relawan_gagal_mendaftar_jika_kebutuhan_dibatalkan()
    {
        $user = $this->createVolunteerUser();
        $kebutuhan = $this->createKebutuhan('dibatalkan');

        $this->expectException(ValidationException::class);
        $this->service->registerVolunteer($user->id_pengguna, $kebutuhan->id_relawan_kebutuhan);
    }

    public function test_relawan_gagal_mendaftar_ganda_ke_kebutuhan_yang_sama()
    {
        $user = $this->createVolunteerUser();
        $kebutuhan = $this->createKebutuhan('dibuka');

        $this->service->registerVolunteer($user->id_pengguna, $kebutuhan->id_relawan_kebutuhan);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Anda sudah terdaftar untuk kebutuhan ini.');
        $this->service->registerVolunteer($user->id_pengguna, $kebutuhan->id_relawan_kebutuhan);
    }

    // ==========================================
    // 2. APPROVE REGISTRATION
    // ==========================================

    public function test_admin_dapat_menerima_pendaftaran()
    {
        $user = $this->createVolunteerUser();
        $kebutuhan = $this->createKebutuhan('dibuka');
        $pendaftaran = $this->service->registerVolunteer($user->id_pengguna, $kebutuhan->id_relawan_kebutuhan);

        $approved = $this->service->approveRegistration($pendaftaran->id_pendaftaran);

        $this->assertEquals('diterima', $approved->status_pendaftaran);
        $this->assertDatabaseHas('relawan_pendaftaran', [
            'id_pendaftaran' => $pendaftaran->id_pendaftaran,
            'status_pendaftaran' => 'diterima'
        ]);
    }

    public function test_approve_gagal_jika_status_sudah_diterima()
    {
        $user = $this->createVolunteerUser();
        $kebutuhan = $this->createKebutuhan('dibuka');
        $pendaftaran = $this->service->registerVolunteer($user->id_pengguna, $kebutuhan->id_relawan_kebutuhan);
        
        // Approve pertama
        $this->service->approveRegistration($pendaftaran->id_pendaftaran);

        // Approve kedua harus gagal karena status sudah bukan seleksi
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Hanya pendaftaran berstatus seleksi yang dapat disetujui.');
        $this->service->approveRegistration($pendaftaran->id_pendaftaran);
    }

    public function test_approve_gagal_jika_status_ditolak()
    {
        $user = $this->createVolunteerUser();
        $kebutuhan = $this->createKebutuhan('dibuka');
        $pendaftaran = $this->service->registerVolunteer($user->id_pengguna, $kebutuhan->id_relawan_kebutuhan);
        
        $this->service->rejectRegistration($pendaftaran->id_pendaftaran, 'Maaf');

        $this->expectException(ValidationException::class);
        $this->service->approveRegistration($pendaftaran->id_pendaftaran);
    }

    public function test_approve_gagal_jika_sudah_ditugaskan()
    {
        $user = $this->createVolunteerUser();
        $kebutuhan = $this->createKebutuhan('dibuka', 5);
        $pendaftaran = $this->service->registerVolunteer($user->id_pengguna, $kebutuhan->id_relawan_kebutuhan);
        $this->service->approveRegistration($pendaftaran->id_pendaftaran);
        $this->service->assignVolunteer($pendaftaran->id_pendaftaran, $this->idPosaju);

        $this->expectException(ValidationException::class);
        $this->service->approveRegistration($pendaftaran->id_pendaftaran);
    }

    // ==========================================
    // 3. REJECT REGISTRATION
    // ==========================================

    public function test_admin_dapat_menolak_pendaftaran()
    {
        $user = $this->createVolunteerUser();
        $kebutuhan = $this->createKebutuhan('dibuka');
        $pendaftaran = $this->service->registerVolunteer($user->id_pengguna, $kebutuhan->id_relawan_kebutuhan);

        $rejected = $this->service->rejectRegistration($pendaftaran->id_pendaftaran, 'Kapasitas penuh');

        $this->assertEquals('ditolak', $rejected->status_pendaftaran);
        $this->assertEquals('Kapasitas penuh', $rejected->catatan_verifikator);
    }

    public function test_reject_gagal_jika_status_sudah_diterima()
    {
        $user = $this->createVolunteerUser();
        $kebutuhan = $this->createKebutuhan('dibuka');
        $pendaftaran = $this->service->registerVolunteer($user->id_pengguna, $kebutuhan->id_relawan_kebutuhan);
        $this->service->approveRegistration($pendaftaran->id_pendaftaran);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Hanya pendaftaran berstatus seleksi yang dapat ditolak.');
        $this->service->rejectRegistration($pendaftaran->id_pendaftaran, 'Telat');
    }

    // ==========================================
    // 4. ASSIGN VOLUNTEER
    // ==========================================

    public function test_relawan_diterima_dapat_ditugaskan()
    {
        $user = $this->createVolunteerUser();
        $kebutuhan = $this->createKebutuhan('dibuka');
        $pendaftaran = $this->service->registerVolunteer($user->id_pengguna, $kebutuhan->id_relawan_kebutuhan);
        $this->service->approveRegistration($pendaftaran->id_pendaftaran);

        $penugasan = $this->service->assignVolunteer($pendaftaran->id_pendaftaran, $this->idPosaju, 'Logistik');

        $this->assertTrue($penugasan->status_aktif);
        $this->assertEquals($this->idPosaju, $penugasan->id_posaju);
        $this->assertEquals('Logistik', $penugasan->peran_lapangan);
        $this->assertEquals('ditugaskan', $penugasan->pendaftaran->fresh()->status_pendaftaran);
        $this->assertNotNull($penugasan->pendaftaran->waktu_penugasan_dimulai);
        
        $this->assertDatabaseHas('relawan_penugasan', [
            'id_penugasan_relawan' => $penugasan->id_penugasan_relawan,
            'status_aktif' => 1
        ]);
    }

    public function test_assign_gagal_jika_pendaftaran_masih_seleksi()
    {
        $user = $this->createVolunteerUser();
        $kebutuhan = $this->createKebutuhan('dibuka');
        $pendaftaran = $this->service->registerVolunteer($user->id_pengguna, $kebutuhan->id_relawan_kebutuhan);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Hanya pendaftaran yang diterima yang dapat ditugaskan.');
        $this->service->assignVolunteer($pendaftaran->id_pendaftaran, $this->idPosaju);
    }

    public function test_assign_gagal_jika_pendaftaran_ditolak()
    {
        $user = $this->createVolunteerUser();
        $kebutuhan = $this->createKebutuhan('dibuka');
        $pendaftaran = $this->service->registerVolunteer($user->id_pengguna, $kebutuhan->id_relawan_kebutuhan);
        $this->service->rejectRegistration($pendaftaran->id_pendaftaran, 'Maaf');

        $this->expectException(ValidationException::class);
        $this->service->assignVolunteer($pendaftaran->id_pendaftaran, $this->idPosaju);
    }

    public function test_assign_gagal_jika_sudah_ada_penugasan_aktif()
    {
        $user = $this->createVolunteerUser();
        
        // Kebutuhan 1
        $kebutuhan1 = $this->createKebutuhan('dibuka');
        $pendaftaran1 = $this->service->registerVolunteer($user->id_pengguna, $kebutuhan1->id_relawan_kebutuhan);
        $this->service->approveRegistration($pendaftaran1->id_pendaftaran);
        $this->service->assignVolunteer($pendaftaran1->id_pendaftaran, $this->idPosaju);

        // Kebutuhan 2 (User daftar lagi di tempat lain)
        $kebutuhan2 = $this->createKebutuhan('dibuka');
        $pendaftaran2 = $this->service->registerVolunteer($user->id_pengguna, $kebutuhan2->id_relawan_kebutuhan);
        $this->service->approveRegistration($pendaftaran2->id_pendaftaran);

        // Assign kedua gagal karena user sudah aktif bertugas
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Relawan ini sudah memiliki penugasan aktif.');
        $this->service->assignVolunteer($pendaftaran2->id_pendaftaran, $this->idPosaju);
    }

    public function test_assign_gagal_jika_melebihi_kapasitas_kebutuhan()
    {
        $kebutuhan = $this->createKebutuhan('dibuka', 2); // Kuota hanya 2

        // User 1 masuk
        $u1 = $this->createVolunteerUser();
        $p1 = $this->service->registerVolunteer($u1->id_pengguna, $kebutuhan->id_relawan_kebutuhan);
        $this->service->approveRegistration($p1->id_pendaftaran);
        $this->service->assignVolunteer($p1->id_pendaftaran, $this->idPosaju);

        // User 2 masuk
        $u2 = $this->createVolunteerUser();
        $p2 = $this->service->registerVolunteer($u2->id_pengguna, $kebutuhan->id_relawan_kebutuhan);
        $this->service->approveRegistration($p2->id_pendaftaran);
        $this->service->assignVolunteer($p2->id_pendaftaran, $this->idPosaju);

        // User 3 harus gagal ditugaskan karena kuota penuh
        $u3 = $this->createVolunteerUser();
        $p3 = $this->service->registerVolunteer($u3->id_pengguna, $kebutuhan->id_relawan_kebutuhan);
        $this->service->approveRegistration($p3->id_pendaftaran);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Kapasitas kebutuhan ini sudah terpenuhi.');
        $this->service->assignVolunteer($p3->id_pendaftaran, $this->idPosaju);
    }

    // ==========================================
    // 5. COMPLETE ASSIGNMENT
    // ==========================================

    public function test_penugasan_aktif_dapat_diselesaikan()
    {
        $user = $this->createVolunteerUser();
        $kebutuhan = $this->createKebutuhan('dibuka');
        $pendaftaran = $this->service->registerVolunteer($user->id_pengguna, $kebutuhan->id_relawan_kebutuhan);
        $this->service->approveRegistration($pendaftaran->id_pendaftaran);
        $penugasan = $this->service->assignVolunteer($pendaftaran->id_pendaftaran, $this->idPosaju);

        $selesai = $this->service->completeAssignment($penugasan->id_penugasan_relawan);

        $this->assertFalse($selesai->status_aktif);
        $this->assertNotNull($selesai->tgl_selesai_aktif);
        $this->assertEquals('selesai', $selesai->pendaftaran->status_pendaftaran);
        $this->assertNotNull($selesai->pendaftaran->waktu_penugasan_selesai);
    }

    public function test_complete_gagal_jika_status_bukan_aktif()
    {
        $user = $this->createVolunteerUser();
        $kebutuhan = $this->createKebutuhan('dibuka');
        $pendaftaran = $this->service->registerVolunteer($user->id_pengguna, $kebutuhan->id_relawan_kebutuhan);
        $this->service->approveRegistration($pendaftaran->id_pendaftaran);
        $penugasan = $this->service->assignVolunteer($pendaftaran->id_pendaftaran, $this->idPosaju);

        $this->service->completeAssignment($penugasan->id_penugasan_relawan);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Hanya penugasan aktif yang dapat diselesaikan.');
        $this->service->completeAssignment($penugasan->id_penugasan_relawan);
    }

    // ==========================================
    // 6. VOLUNTEER SKILLS
    // ==========================================

    public function test_sinkronisasi_keahlian_relawan()
    {
        $user = $this->createVolunteerUser();
        $skill1 = AuthKeahlianMaster::create(['nama_keahlian' => 'P3K']);
        $skill2 = AuthKeahlianMaster::create(['nama_keahlian' => 'Dapur Umum']);

        $this->service->syncVolunteerSkills($user->id_pengguna, [
            $skill1->id_keahlian => ['tingkat_keahlian' => 'ahli'],
            $skill2->id_keahlian => ['tingkat_keahlian' => 'menengah']
        ]);

        $this->assertDatabaseHas('auth_pengguna_keahlian', [
            'id_pengguna' => $user->id_pengguna,
            'id_keahlian' => $skill1->id_keahlian,
        ]);
        
        $this->assertCount(2, $user->fresh()->keahlian);
    }

    public function test_sinkronisasi_keahlian_relawan_kosong_menghapus_semua()
    {
        $user = $this->createVolunteerUser();
        $skill1 = AuthKeahlianMaster::create(['nama_keahlian' => 'P3K']);
        
        $this->service->syncVolunteerSkills($user->id_pengguna, [$skill1->id_keahlian => []]);
        $this->assertCount(1, $user->fresh()->keahlian);

        // Hapus semua
        $this->service->syncVolunteerSkills($user->id_pengguna, []);
        $this->assertCount(0, $user->fresh()->keahlian);
    }

    // ==========================================
    // 7. GET PROFILE
    // ==========================================

    public function test_dapat_mengambil_dan_membuat_profil_otomatis()
    {
        $user = $this->createVolunteerUser();
        
        $profil = $this->service->getVolunteerProfile($user->id_pengguna);
        
        $this->assertNotNull($profil);
        $this->assertEquals($user->id_pengguna, $profil->id_pengguna);
        $this->assertStringContainsString('TEMP-', $profil->nik);
        $this->assertEquals('Profil Belum Lengkap', $profil->nama_lengkap);
    }

    public function test_mengambil_profil_yang_sudah_ada_tidak_membuat_baru()
    {
        $user = $this->createVolunteerUser();
        
        $profil1 = $this->service->getVolunteerProfile($user->id_pengguna);
        $profil2 = $this->service->getVolunteerProfile($user->id_pengguna);
        
        $this->assertEquals($profil1->nik, $profil2->nik);
        $this->assertDatabaseCount('auth_pengguna_profil', 1);
    }

    // ==========================================
    // 8. GET AVAILABLE VOLUNTEERS (Termasuk Soft Delete Awareness)
    // ==========================================

    public function test_mengambil_relawan_yang_tersedia()
    {
        $user1 = $this->createVolunteerUser(); // Tersedia
        
        $user2 = $this->createVolunteerUser();
        $kebutuhan = $this->createKebutuhan('dibuka');
        $pend2 = $this->service->registerVolunteer($user2->id_pengguna, $kebutuhan->id_relawan_kebutuhan);
        $this->service->rejectRegistration($pend2->id_pendaftaran, 'Maaf'); // Tersedia (ditolak)

        $user3 = $this->createVolunteerUser();
        $pend3 = $this->service->registerVolunteer($user3->id_pengguna, $kebutuhan->id_relawan_kebutuhan);
        $this->service->approveRegistration($pend3->id_pendaftaran);
        $this->service->assignVolunteer($pend3->id_pendaftaran, $this->idPosaju); // TIDAK TERSEDIA (Aktif)

        $user4 = $this->createVolunteerUser();
        $pend4 = $this->service->registerVolunteer($user4->id_pengguna, $kebutuhan->id_relawan_kebutuhan);
        $this->service->approveRegistration($pend4->id_pendaftaran);
        $pen4 = $this->service->assignVolunteer($pend4->id_pendaftaran, $this->idPosaju);
        $this->service->completeAssignment($pen4->id_penugasan_relawan); // Tersedia (Selesai tugas)

        $available = $this->service->getAvailableVolunteers();

        $ids = $available->pluck('id_pengguna')->toArray();
        $this->assertContains($user1->id_pengguna, $ids);
        $this->assertContains($user2->id_pengguna, $ids);
        $this->assertNotContains($user3->id_pengguna, $ids);
        $this->assertContains($user4->id_pengguna, $ids);
    }

    public function test_tersedia_kembali_jika_penugasan_di_soft_delete()
    {
        $user = $this->createVolunteerUser();
        $kebutuhan = $this->createKebutuhan('dibuka');
        
        $pend = $this->service->registerVolunteer($user->id_pengguna, $kebutuhan->id_relawan_kebutuhan);
        $this->service->approveRegistration($pend->id_pendaftaran);
        $pen = $this->service->assignVolunteer($pend->id_pendaftaran, $this->idPosaju);

        // Pastikan tidak tersedia karena sedang aktif
        $available = $this->service->getAvailableVolunteers();
        $this->assertNotContains($user->id_pengguna, $available->pluck('id_pengguna')->toArray());

        // Simulasi Admin menghapus penugasan (soft delete)
        $pen->delete();

        // Relawan harus kembali tersedia karena penugasannya sudah di-soft-delete (dianggap tidak ada penugasan aktif)
        $availableAfterDelete = $this->service->getAvailableVolunteers();
        $this->assertContains($user->id_pengguna, $availableAfterDelete->pluck('id_pengguna')->toArray());
    }

    public function test_tersedia_kembali_jika_pendaftaran_di_soft_delete()
    {
        $user = $this->createVolunteerUser();
        $kebutuhan = $this->createKebutuhan('dibuka');
        
        $pend = $this->service->registerVolunteer($user->id_pengguna, $kebutuhan->id_relawan_kebutuhan);
        $this->service->approveRegistration($pend->id_pendaftaran);
        $pen = $this->service->assignVolunteer($pend->id_pendaftaran, $this->idPosaju);

        $pend->delete(); // Ini otomatis men-cascade penugasan jika diset, tapi kita asumsikan soft delete

        // Karena pendaftaran soft-deleted, penugasannya otomatis diabaikan oleh relasi Eloquent
        $availableAfterDelete = $this->service->getAvailableVolunteers();
        $this->assertContains($user->id_pengguna, $availableAfterDelete->pluck('id_pengguna')->toArray());
    }
}
