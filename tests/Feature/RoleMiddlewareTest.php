<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\AuthUser;
use App\Models\AuthRole;
use App\Http\Middleware\RoleMiddleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Symfony\Component\HttpKernel\Exception\HttpException;

class RoleMiddlewareTest extends TestCase
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
     * Test pengguna dengan role yang tepat diizinkan melanjutkan request.
     */
    public function test_user_with_allowed_role_can_pass(): void
    {
        $role = AuthRole::factory()->create(['nama_peran' => 'super_admin']);
        $user = AuthUser::factory()->aktif()->create([
            'id_peran' => $role->id_peran
        ]);

        Auth::login($user);

        $request = Request::create('/any-route', 'GET');
        $request->setLaravelSession($this->app['session']->driver());

        $middleware = new RoleMiddleware();
        $response = $middleware->handle($request, function ($req) {
            return response('OK');
        }, 'super_admin', 'pwnu');

        $this->assertEquals(200, $response->getStatusCode());
    }

    /**
     * Test pengguna dengan role yang tidak terdaftar menghasilkan HTTP 403.
     */
    public function test_user_without_allowed_role_gets_403(): void
    {
        $role = AuthRole::factory()->create(['nama_peran' => 'pwnu']);
        $user = AuthUser::factory()->aktif()->create([
            'id_peran' => $role->id_peran
        ]);

        Auth::login($user);

        $request = Request::create('/any-route', 'GET');
        $request->setLaravelSession($this->app['session']->driver());

        $middleware = new RoleMiddleware();

        $this->expectException(HttpException::class);
        $this->expectExceptionMessage('Aksi tidak diizinkan. Peran Anda tidak memiliki hak akses.');

        $middleware->handle($request, function ($req) {
            return response('OK');
        }, 'super_admin');
    }
}
