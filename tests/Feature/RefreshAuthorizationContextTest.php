<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\AuthUser;
use App\Models\AuthRole;
use App\Http\Middleware\RefreshAuthorizationContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class RefreshAuthorizationContextTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        // Bangun skema tabel auth_roles & auth_users di SQLite in-memory untuk testing




        AuthRole::firstOrCreate(['nama_peran' => 'super_admin'], ['level_otoritas' => 1]);
        AuthRole::firstOrCreate(['nama_peran' => 'super_admin'], ['level_otoritas' => 1]);
    }

    /**
     * Test bahwa middleware menyinkronkan context peran terbaru ke session.
     */
    public function test_middleware_refreshes_session_with_latest_user_role(): void
    {
        $role1 = AuthRole::factory()->create(['nama_peran' => 'role_satu']);
        $role2 = AuthRole::factory()->create(['nama_peran' => 'role_dua']);

        $user = AuthUser::factory()->aktif()->create([
            'id_peran' => $role1->id_peran,
            'default_scope_type' => 'pcnu',
            'default_scope_id' => 3321
        ]);

        Auth::login($user);

        // Inisialisasi request buatan
        $request = Request::create('/any-route', 'GET');
        $request->setLaravelSession($this->app['session']->driver());

        $middleware = new RefreshAuthorizationContext();
        $middleware->handle($request, function ($req) {
            return response('OK');
        });

        // Verifikasi isi session sinkron
        $this->assertEquals($role1->id_peran, session('id_peran'));
        $this->assertEquals('pcnu', session('default_scope_type'));
        $this->assertEquals(3321, session('default_scope_id'));

        // Simulasikan role drift (peran diubah oleh admin lain di DB)
        $user->update(['id_peran' => $role2->id_peran]);

        // Jalankan middleware kembali per request berikutnya
        $middleware->handle($request, function ($req) {
            return response('OK');
        });

        // Verifikasi session telah tersinkron dengan id_peran baru
        $this->assertEquals($role2->id_peran, session('id_peran'));
    }
}
