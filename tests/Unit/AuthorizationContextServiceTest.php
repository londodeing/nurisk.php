<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\AuthUser;
use App\Models\AuthRole;
use App\Services\Auth\AuthorizationContextService;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class AuthorizationContextServiceTest extends TestCase
{
    use DatabaseTransactions;

    protected AuthorizationContextService $contextService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->contextService = new AuthorizationContextService();

        // Bangun skema tabel auth_roles & auth_users di SQLite in-memory untuk testing




        AuthRole::firstOrCreate(['nama_peran' => 'super_admin'], ['level_otoritas' => 1]);

        AuthRole::firstOrCreate(['nama_peran' => 'super_admin'], ['level_otoritas' => 1]);
    }

    /**
     * Test 1: User login adalah super_admin, method isSuperAdmin() mengembalikan true.
     */
    public function test_super_admin_role_returns_true_for_issuperadmin(): void
    {
        $user = AuthUser::factory()->aktif()->create([
            'id_peran' => \App\Models\AuthRole::firstOrCreate(['nama_peran' => 'super_admin'], ['level_otoritas' => 1])->id_peran
        ]);

        Auth::login($user);
        $this->contextService->clearCache();

        $this->assertTrue($this->contextService->isSuperAdmin());
        $this->assertEquals('super_admin', $this->contextService->getRoleName());
        $this->assertEquals(1, $this->contextService->getRoleLevel());
    }

    /**
     * Test 2: User login adalah pcnu, method hasRole('pcnu') mengembalikan true.
     */
    public function test_pcnu_role_returns_true_for_hasrole(): void
    {
        $user = AuthUser::factory()->aktif()->create([
            'id_peran' => \App\Models\AuthRole::firstOrCreate(['nama_peran' => 'pcnu'], ['level_otoritas' => 3])->id_peran
        ]);

        Auth::login($user);
        $this->contextService->clearCache();

        $this->assertTrue($this->contextService->hasRole('pcnu'));
        $this->assertFalse($this->contextService->isSuperAdmin());
    }

    /**
     * Test 3: User login adalah pcnu, method hasAnyRole(['super_admin', 'pcnu']) mengembalikan true.
     */
    public function test_pcnu_role_returns_true_for_hasanyrole(): void
    {
        $user = AuthUser::factory()->aktif()->create([
            'id_peran' => \App\Models\AuthRole::firstOrCreate(['nama_peran' => 'pcnu'], ['level_otoritas' => 3])->id_peran
        ]);

        Auth::login($user);
        $this->contextService->clearCache();

        $this->assertTrue($this->contextService->hasAnyRole(['super_admin', 'pcnu']));
        $this->assertFalse($this->contextService->hasAnyRole(['super_admin', 'pwnu']));
    }

    /**
     * Test 4: Scope mwc pada user diizinkan oleh hasScope('mwc').
     */
    public function test_mwc_scope_user_returns_true_for_hasscope(): void
    {
        $user = AuthUser::factory()->aktif()->create([
            'id_peran' => \App\Models\AuthRole::firstOrCreate(['nama_peran' => 'pcnu'], ['level_otoritas' => 3])->id_peran,
            'default_scope_type' => 'mwc',
            'default_scope_id' => 50
        ]);

        Auth::login($user);
        $this->contextService->clearCache();

        $this->assertTrue($this->contextService->hasScope('mwc'));
        $this->assertEquals(50, $this->contextService->getScopeId());
    }

    /**
     * Test 5: Guest User (tidak login) aman tanpa exception.
     */
    public function test_guest_user_returns_null_safe_responses(): void
    {
        Auth::logout();
        $this->contextService->clearCache();

        $this->assertNull($this->contextService->getCurrentUser());
        $this->assertNull($this->contextService->getRoleName());
        $this->assertNull($this->contextService->getScopeType());
        $this->assertFalse($this->contextService->isSuperAdmin());
    }
}
