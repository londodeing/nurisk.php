<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\AuthUser;
use App\Models\AuthPenggunaProfil;
use App\Models\OperasiInsiden;
use App\Services\Auth\RoleApplicationService;
use App\Services\Operasi\PenugasanService;
use App\Services\Auth\AuthorizationContextService;

class EndToEndVolunteerJourneyTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Seed roles
        \Illuminate\Support\Facades\DB::table('auth_roles')->insert([
            ['id_peran' => 1, 'nama_peran' => 'super_admin', 'level_otoritas' => 1],
            ['id_peran' => 3, 'nama_peran' => 'pcnu', 'level_otoritas' => 3],
            ['id_peran' => 4, 'nama_peran' => 'relawan', 'level_otoritas' => 4],
            ['id_peran' => 5, 'nama_peran' => 'publik', 'level_otoritas' => 5],
        ]);
    }

    public function test_volunteer_end_to_end_journey()
    {
        // 1. Daftar Akun
        $applicant = AuthUser::create([
            'id_peran' => 5,
            'status_akun' => 'registered',
            'no_hp' => '08123456789',
            'default_scope_type' => 'pcnu',
            'default_scope_id' => 1,
            'status_ketersediaan' => 'available',
        ]);
        
        AuthPenggunaProfil::create([
            'id_pengguna' => $applicant->id_pengguna,
            'nama_lengkap' => 'Relawan Test',
            'nik' => '1234567890123456',
            'id_desa_domisili' => '111',
        ]);

        // 2. Ajukan Role
        $roleService = new RoleApplicationService();
        $application = $roleService->applyForRole($applicant, 4);
        
        $this->assertEquals('pending_verification', $applicant->fresh()->status_akun);

        // 3. Disetujui Organisasi
        $superAdmin = AuthUser::create([
            'id_peran' => 1,
            'status_akun' => 'aktif',
            'no_hp' => '08987654321',
        ]);

        $roleService->approveApplication($application, $superAdmin);

        $this->assertEquals(AuthUser::STATUS_ACTIVE, $applicant->fresh()->status_akun);
        $this->assertEquals(4, $applicant->fresh()->id_peran);



        \Illuminate\Support\Facades\DB::table('bencana_master_jenis')->insert([
            ['id_jenis' => 1, 'nama_bencana' => 'Banjir', 'slug' => 'banjir'],
        ]);

        \Illuminate\Support\Facades\DB::table('organisasi_unit')->insert([
            ['id_unit' => 1, 'nama_unit' => 'Unit Test', 'tipe_unit' => 'pcnu'],
        ]);

        \Illuminate\Support\Facades\DB::table('organisasi_pcnu')->insert([
            ['id_pcnu' => 1, 'nama_pcnu' => 'PCNU Test', 'id_unit' => 1],
        ]);

        // 4. Ditugaskan
        $insiden = OperasiInsiden::create([
            'kode_kejadian' => 'Bencana-01',
            'id_jenis_bencana' => 1,
            'id_pcnu' => 1,
            'status_insiden' => 'respon',
            'waktu_mulai' => now(),
        ]);

        // Mock auth context for service
        $authCtx = $this->createMock(AuthorizationContextService::class);
        $authCtx->method('getCurrentUser')->willReturn($superAdmin);

        $penugasanService = new PenugasanService($authCtx);
        
        $penugasan = $penugasanService->createPenugasan([
            'id_insiden' => $insiden->id_insiden,
            'id_pengguna' => $applicant->id_pengguna,
            'peran_otoritas' => 'Evakuasi',
        ]);

        $this->assertEquals('draft', $penugasan->status_penugasan);

        // 5. Transisi Status Penugasan
        $penugasanService->updateStatus($penugasan, 'assigned');
        $this->assertEquals('assigned', $penugasan->fresh()->status_penugasan);
        
        $penugasanService->updateStatus($penugasan, 'notified');
        $penugasanService->updateStatus($penugasan, 'accepted');
        $penugasanService->updateStatus($penugasan, 'on_route');
        $penugasanService->updateStatus($penugasan, 'on_site');
        
        // 6. Selesai Tugas
        $penugasanService->updateStatus($penugasan, 'completed');
        $this->assertEquals('completed', $penugasan->fresh()->status_penugasan);
        $this->assertNotNull($penugasan->fresh()->waktu_selesai);
    }
}
