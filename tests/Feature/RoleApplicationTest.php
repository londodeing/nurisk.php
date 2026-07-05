<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\AuthUser;
use App\Models\AuthRole;
use App\Services\Auth\RoleApplicationService;

class RoleApplicationTest extends TestCase
{
    use RefreshDatabase;

    private RoleApplicationService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new RoleApplicationService();
        
        // Seed roles if necessary
        \Illuminate\Support\Facades\DB::table('auth_roles')->insert([
            ['id_peran' => 1, 'nama_peran' => 'super_admin', 'level_otoritas' => 1],
            ['id_peran' => 2, 'nama_peran' => 'pwnu', 'level_otoritas' => 2],
            ['id_peran' => 3, 'nama_peran' => 'pcnu', 'level_otoritas' => 3],
            ['id_peran' => 4, 'nama_peran' => 'relawan', 'level_otoritas' => 4],
            ['id_peran' => 5, 'nama_peran' => 'publik', 'level_otoritas' => 5],
        ]);
    }

    public function test_user_can_apply_for_role()
    {
        $user = AuthUser::create([
            'id_peran' => 5,
            'status_akun' => 'registered',
            'no_hp' => '08111111111',
        ]);

        $application = $this->service->applyForRole($user, 4); // Apply for Relawan

        $this->assertDatabaseHas('auth_role_applications', [
            'id_pengguna' => $user->id_pengguna,
            'id_peran_diminta' => 4,
            'status_aplikasi' => 'pending',
        ]);

        $this->assertEquals('pending_verification', $user->fresh()->status_akun);
    }

    public function test_pcnu_cannot_approve_user_in_v2()
    {
        $applicant = AuthUser::create([
            'id_peran' => 5,
            'status_akun' => 'registered',
            'no_hp' => '08111111111',
            'default_scope_type' => 'pcnu',
            'default_scope_id' => 10,
        ]);

        $pcnuAdmin = AuthUser::create([
            'id_peran' => 3, // PCNU
            'status_akun' => 'aktif',
            'no_hp' => '08222222222',
            'default_scope_type' => 'pcnu',
            'default_scope_id' => 10,
        ]);

        $application = $this->service->applyForRole($applicant, 4);
        
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('PCNU tidak memiliki wewenang approval pada alur pendaftaran baru.');

        $this->service->approveApplication($application, $pcnuAdmin);
    }
}
