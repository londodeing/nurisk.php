<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\AuthUser;
use App\Models\AuthRole;
use App\Http\Middleware\ScopeEnclosure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ScopeEnclosureTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        // Bangun skema tabel auth_roles & auth_users di SQLite in-memory untuk testing




        AuthRole::firstOrCreate(['nama_peran' => 'super_admin'], ['level_otoritas' => 1]);
    }

    /**
     * Test 1: User dengan default_scope_type = pcnu mengakses scope:pcnu (Lolos).
     */
    public function test_user_with_pcnu_scope_can_pass_pcnu_gate(): void
    {
        $user = AuthUser::factory()->aktif()->create([
            'id_peran' => 1,
            'default_scope_type' => 'pcnu',
            'default_scope_id' => 3321
        ]);

        Auth::login($user);

        $request = Request::create('/any-route', 'GET');
        $request->setLaravelSession($this->app['session']->driver());

        $middleware = new ScopeEnclosure();
        $response = $middleware->handle($request, function ($req) {
            return response('OK');
        }, 'pcnu');

        $this->assertEquals(200, $response->getStatusCode());
    }

    /**
     * Test 2: User dengan default_scope_type = mwc mengakses scope:pcnu (Ditolak 403).
     */
    public function test_user_with_mwc_scope_is_denied_pcnu_gate(): void
    {
        $user = AuthUser::factory()->aktif()->create([
            'id_peran' => 1,
            'default_scope_type' => 'mwc',
            'default_scope_id' => 550
        ]);

        Auth::login($user);

        $request = Request::create('/any-route', 'GET');
        $request->setLaravelSession($this->app['session']->driver());

        $middleware = new ScopeEnclosure();

        $this->expectException(HttpException::class);
        $this->expectExceptionMessage('Aksi ditolak. Wilayah kepengurusan (scope) Anda tidak memiliki wewenang untuk area ini.');

        $middleware->handle($request, function ($req) {
            return response('OK');
        }, 'pcnu');
    }

    /**
     * Test 3: User dengan default_scope_type = mwc mengakses scope:pcnu,mwc (Lolos).
     */
    public function test_user_with_mwc_scope_can_pass_multi_scope_gate(): void
    {
        $user = AuthUser::factory()->aktif()->create([
            'id_peran' => 1,
            'default_scope_type' => 'mwc',
            'default_scope_id' => 550
        ]);

        Auth::login($user);

        $request = Request::create('/any-route', 'GET');
        $request->setLaravelSession($this->app['session']->driver());

        $middleware = new ScopeEnclosure();
        $response = $middleware->handle($request, function ($req) {
            return response('OK');
        }, 'pcnu', 'mwc');

        $this->assertEquals(200, $response->getStatusCode());
    }

    /**
     * Test 4: Guest User mengakses route scope (Redirect Login).
     */
    public function test_guest_accessing_scope_gate_redirects_to_login(): void
    {
        $request = Request::create('/any-route', 'GET');
        $request->setLaravelSession($this->app['session']->driver());

        $middleware = new ScopeEnclosure();
        $response = $middleware->handle($request, function ($req) {
            return response('OK');
        }, 'pcnu');

        $this->assertEquals(302, $response->getStatusCode());
    }
}
