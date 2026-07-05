<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\AuthUser;
use App\Models\AuthRole;
use App\Http\Middleware\CheckAccountStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class CheckAccountStatusTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        // Bangun skema tabel auth_roles & auth_users di SQLite in-memory untuk testing




        AuthRole::firstOrCreate(['nama_peran' => 'super_admin'], ['level_otoritas' => 1]);
    }

    /**
     * Test user aktif dapat melanjutkan request tanpa di-logout.
     */
    public function test_active_user_is_not_logged_out(): void
    {
        $user = AuthUser::factory()->aktif()->create([
            'id_peran' => 1
        ]);

        Auth::login($user);

        $request = Request::create('/any-route', 'GET');
        $request->setLaravelSession($this->app['session']->driver());

        $middleware = new CheckAccountStatus();
        $response = $middleware->handle($request, function ($req) {
            return response('OK');
        });

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals($user->id_pengguna, Auth::id());
    }

    /**
     * Test user yang statusnya berubah menjadi non-aktif otomatis di-logout secara realtime.
     */
    public function test_inactive_user_is_automatically_logged_out(): void
    {
        $user = AuthUser::factory()->aktif()->create([
            'id_peran' => 1
        ]);

        Auth::login($user);

        // Simulasikan penangguhan akun oleh admin lain (forceFill karena status_akun tidak di fillable)
        $user->forceFill(['status_akun' => 'suspend'])->save();

        $request = Request::create('/any-route', 'GET');
        $request->setLaravelSession($this->app['session']->driver());

        $middleware = new CheckAccountStatus();
        $response = $middleware->handle($request, function ($req) {
            return response('OK');
        });

        // Verifikasi ter-redirect ke login dan status user login dibersihkan (guest)
        $this->assertEquals(302, $response->getStatusCode());
        $this->assertGuest();
    }
}
