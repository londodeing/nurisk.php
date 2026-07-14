<?php

namespace Tests\Feature\Sdui\Scenes;

use Tests\TestCase;
use App\Models\AuthUser;
use App\Models\AuthRole;
use App\Models\OrganisasiPcnu;
use App\Models\AuthPenggunaProfil;
use App\Models\JabatanPosisi;
use App\Models\PenggunaJabatan;
use App\Models\AuthKeahlianMaster;
use App\Models\AuthPenggunaKeahlian;
use App\Services\Sdui\Scenes\AkunSceneComposer;
use App\Services\Auth\AuthorizationContextService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AkunSceneComposerTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_user_receives_login_button()
    {
        $user = new AuthUser();
        $ctx = $this->app->make(AuthorizationContextService::class);
        $composer = new AkunSceneComposer($user, $ctx);
        $json = $composer->compose();

        $this->assertEquals('akun', $json['scene_id']);
        $this->assertEquals('publik', $json['meta']['rendered_for_role']);

        $rootChildren = $json['root']['children'];
        $identityCard = $rootChildren[0];
        $this->assertEquals('container', $identityCard['type']);
        $this->assertEquals('Anda belum login', $identityCard['children'][0]['content']);
        $this->assertEquals('Masuk ke NURISK', $identityCard['children'][2]['label']);
    }

    public function test_guest_user_only_sees_identity_card()
    {
        $user = new AuthUser();
        $ctx = $this->app->make(AuthorizationContextService::class);
        $composer = new AkunSceneComposer($user, $ctx);
        $json = $composer->compose();

        $childrenStr = json_encode($json['root']['children']);
        $this->assertStringNotContainsString('Penugasan Aktif Saya', $childrenStr);
        $this->assertStringNotContainsString('Pusat Komando', $childrenStr);
        $this->assertStringNotContainsString('Ganti Kata Sandi', $childrenStr);
        $this->assertStringNotContainsString('Keluar', $childrenStr);
    }

    public function test_publik_role_only_sees_identity_card()
    {
        $role = AuthRole::factory()->create(['nama_peran' => 'publik']);
        $user = AuthUser::factory()->aktif()->create(['id_peran' => $role->id_peran]);
        AuthPenggunaProfil::factory()->create(['id_pengguna' => $user->id_pengguna, 'nama_lengkap' => 'Publik User']);

        $ctx = $this->app->make(AuthorizationContextService::class);
        $composer = new AkunSceneComposer($user, $ctx);
        $json = $composer->compose();

        $childrenStr = json_encode($json['root']['children']);
        $this->assertStringContainsString('Publik User', $childrenStr);
        $this->assertStringNotContainsString('Penugasan Aktif Saya', $childrenStr);
        $this->assertStringNotContainsString('Pusat Komando', $childrenStr);
        $this->assertStringNotContainsString('Ganti Kata Sandi', $childrenStr);
        $this->assertStringNotContainsString('Keluar', $childrenStr);
        $this->assertStringNotContainsString('Tersedia', $childrenStr);
        $this->assertStringNotContainsString('Tidak Tersedia', $childrenStr);
    }

    public function test_relawan_receives_identity_and_assignment_but_no_command_center()
    {
        $role = AuthRole::factory()->create(['nama_peran' => 'relawan']);
        $user = AuthUser::factory()->aktif()->create(['id_peran' => $role->id_peran, 'is_tersedia' => 1]);
        AuthPenggunaProfil::factory()->create(['id_pengguna' => $user->id_pengguna, 'nama_lengkap' => 'Budi Relawan']);

        $ctx = $this->app->make(AuthorizationContextService::class);
        $composer = new AkunSceneComposer($user, $ctx);
        $json = $composer->compose();

        $this->assertEquals('relawan', $json['meta']['rendered_for_role']);

        $childrenStr = json_encode($json['root']['children']);
        $this->assertStringContainsString('Penugasan Aktif Saya', $childrenStr);
        $this->assertStringNotContainsString('Pusat Komando', $childrenStr);
        $this->assertStringContainsString('Budi Relawan', $childrenStr);
        $this->assertStringContainsString('Tersedia', $childrenStr);
        $this->assertStringContainsString('Ganti Kata Sandi', $childrenStr);
        $this->assertStringContainsString('Keluar', $childrenStr);
    }

    public function test_relawan_off_duty_shows_unavailable()
    {
        $role = AuthRole::factory()->create(['nama_peran' => 'relawan']);
        $user = AuthUser::factory()->aktif()->create(['id_peran' => $role->id_peran, 'is_tersedia' => 0]);
        AuthPenggunaProfil::factory()->create(['id_pengguna' => $user->id_pengguna, 'nama_lengkap' => 'Siti Relawan']);

        $ctx = $this->app->make(AuthorizationContextService::class);
        $composer = new AkunSceneComposer($user, $ctx);
        $json = $composer->compose();

        $childrenStr = json_encode($json['root']['children']);
        $this->assertStringContainsString('Tidak Tersedia', $childrenStr);
    }

    public function test_pcnu_receives_command_center()
    {
        $role = AuthRole::factory()->create(['nama_peran' => 'pcnu']);
        $pcnu = OrganisasiPcnu::factory()->create(['nama_pcnu' => 'Kudus']);
        $user = AuthUser::factory()->aktif()->create([
            'id_peran' => $role->id_peran,
            'default_scope_type' => 'pcnu',
            'default_scope_id' => $pcnu->id_pcnu
        ]);
        AuthPenggunaProfil::factory()->create(['id_pengguna' => $user->id_pengguna, 'nama_lengkap' => 'Admin PCNU']);

        $ctx = $this->app->make(AuthorizationContextService::class);
        $this->actingAs($user, 'sanctum');
        $composer = new AkunSceneComposer($user, $ctx);
        $json = $composer->compose();

        $this->assertEquals('pcnu', $json['meta']['rendered_for_role']);

        $childrenStr = json_encode($json['root']['children']);
        $this->assertStringContainsString('Pusat Komando', $childrenStr);
    }

    public function test_super_admin_receives_all_sections()
    {
        $role = AuthRole::factory()->create(['nama_peran' => 'super_admin']);
        $user = AuthUser::factory()->aktif()->create(['id_peran' => $role->id_peran]);
        AuthPenggunaProfil::factory()->create(['id_pengguna' => $user->id_pengguna, 'nama_lengkap' => 'Super Admin']);

        $ctx = $this->app->make(AuthorizationContextService::class);
        $this->actingAs($user, 'sanctum');
        $composer = new AkunSceneComposer($user, $ctx);
        $json = $composer->compose();

        $this->assertEquals('super_admin', $json['meta']['rendered_for_role']);

        $childrenStr = json_encode($json['root']['children']);
        $this->assertStringContainsString('Penugasan Aktif Saya', $childrenStr);
        $this->assertStringContainsString('Pusat Komando', $childrenStr);
        $this->assertStringContainsString('Akses Penuh', $childrenStr);
        $this->assertStringContainsString('Ganti Kata Sandi', $childrenStr);
        $this->assertStringContainsString('Keluar', $childrenStr);
    }

    public function test_pwnu_receives_command_center()
    {
        $role = AuthRole::factory()->create(['nama_peran' => 'pwnu']);
        $user = AuthUser::factory()->aktif()->create(['id_peran' => $role->id_peran]);
        AuthPenggunaProfil::factory()->create(['id_pengguna' => $user->id_pengguna, 'nama_lengkap' => 'PWNU Admin']);

        $ctx = $this->app->make(AuthorizationContextService::class);
        $this->actingAs($user, 'sanctum');
        $composer = new AkunSceneComposer($user, $ctx);
        $json = $composer->compose();

        $this->assertEquals('pwnu', $json['meta']['rendered_for_role']);

        $childrenStr = json_encode($json['root']['children']);
        $this->assertStringContainsString('Pusat Komando', $childrenStr);
        $this->assertStringContainsString('PWNU Jawa Tengah', $childrenStr);
    }

    public function test_empty_penugasan_shows_empty_state()
    {
        $role = AuthRole::factory()->create(['nama_peran' => 'relawan']);
        $user = AuthUser::factory()->aktif()->create(['id_peran' => $role->id_peran]);
        AuthPenggunaProfil::factory()->create(['id_pengguna' => $user->id_pengguna, 'nama_lengkap' => 'Relawan Tanpa Tugas']);

        $ctx = $this->app->make(AuthorizationContextService::class);
        $composer = new AkunSceneComposer($user, $ctx);
        $json = $composer->compose();

        $childrenStr = json_encode($json['root']['children']);
        $this->assertStringContainsString('Penugasan Aktif Saya', $childrenStr);
        $this->assertStringContainsString('Tidak ada penugasan aktif saat ini', $childrenStr);
    }

    public function test_empty_command_center_shows_empty_state()
    {
        $role = AuthRole::factory()->create(['nama_peran' => 'pcnu']);
        $pcnu = OrganisasiPcnu::factory()->create(['nama_pcnu' => 'Kudus']);
        $user = AuthUser::factory()->aktif()->create([
            'id_peran' => $role->id_peran,
            'default_scope_type' => 'pcnu',
            'default_scope_id' => $pcnu->id_pcnu
        ]);
        AuthPenggunaProfil::factory()->create(['id_pengguna' => $user->id_pengguna, 'nama_lengkap' => 'PCNU Admin']);

        $ctx = $this->app->make(AuthorizationContextService::class);
        $this->actingAs($user, 'sanctum');
        $composer = new AkunSceneComposer($user, $ctx);
        $json = $composer->compose();

        $childrenStr = json_encode($json['root']['children']);
        $this->assertStringContainsString('Pusat Komando', $childrenStr);
        $this->assertStringContainsString('Tidak ada insiden aktif', $childrenStr);
    }

    public function test_jabatan_aktif_not_rendered_when_null()
    {
        $role = AuthRole::factory()->create(['nama_peran' => 'relawan']);
        $user = AuthUser::factory()->aktif()->create(['id_peran' => $role->id_peran]);
        AuthPenggunaProfil::factory()->create(['id_pengguna' => $user->id_pengguna, 'nama_lengkap' => 'Relawan']);

        $ctx = $this->app->make(AuthorizationContextService::class);
        $composer = new AkunSceneComposer($user, $ctx);
        $json = $composer->compose();

        $childrenStr = json_encode($json['root']['children']);
        $this->assertStringNotContainsString('Koordinator', $childrenStr);
    }

    public function test_jabatan_aktif_rendered_when_present()
    {
        $role = AuthRole::factory()->create(['nama_peran' => 'relawan']);
        $user = AuthUser::factory()->aktif()->create(['id_peran' => $role->id_peran]);
        AuthPenggunaProfil::factory()->create(['id_pengguna' => $user->id_pengguna, 'nama_lengkap' => 'Relawan Jabatan']);

        $jabatan = JabatanPosisi::factory()->create(['nama_jabatan' => 'Koordinator TRC PCNU', 'slug' => 'koordinator-trc-pcnu']);
        PenggunaJabatan::factory()->create([
            'id_pengguna' => $user->id_pengguna,
            'id_jabatan_posisi' => $jabatan->id_jabatan_posisi,
            'status_aktif' => 1,
        ]);

        $ctx = $this->app->make(AuthorizationContextService::class);
        $composer = new AkunSceneComposer($user, $ctx);
        $json = $composer->compose();

        $childrenStr = json_encode($json['root']['children']);
        $this->assertStringContainsString('Koordinator TRC PCNU', $childrenStr);
    }

    public function test_keahlian_chips_rendered_with_more_than_4()
    {
        $role = AuthRole::factory()->create(['nama_peran' => 'relawan']);
        $user = AuthUser::factory()->aktif()->create(['id_peran' => $role->id_peran]);
        AuthPenggunaProfil::factory()->create(['id_pengguna' => $user->id_pengguna, 'nama_lengkap' => 'Ahli']);

        $keahlianNama = ['Medis', 'Water Rescue', 'Vertical Rescue', 'Logistik', 'Dapur Umum', 'Psikososial', 'Komunikasi Radio'];
        foreach ($keahlianNama as $nama) {
            $k = AuthKeahlianMaster::create(['nama_keahlian' => $nama]);
            AuthPenggunaKeahlian::create([
                'id_pengguna' => $user->id_pengguna,
                'id_keahlian' => $k->id_keahlian,
            ]);
        }

        $ctx = $this->app->make(AuthorizationContextService::class);
        $composer = new AkunSceneComposer($user, $ctx);
        $json = $composer->compose();

        $childrenStr = json_encode($json['root']['children']);
        $this->assertStringContainsString('Medis', $childrenStr);
        $this->assertStringContainsString('Logistik', $childrenStr);
        $this->assertStringContainsString('& 3 lainnya', $childrenStr);
    }

    public function test_keahlian_chips_rendered_with_4_or_less()
    {
        $role = AuthRole::factory()->create(['nama_peran' => 'relawan']);
        $user = AuthUser::factory()->aktif()->create(['id_peran' => $role->id_peran]);
        AuthPenggunaProfil::factory()->create(['id_pengguna' => $user->id_pengguna, 'nama_lengkap' => 'Ahli']);

        $keahlianNama = ['Medis', 'Water Rescue', 'Logistik'];
        foreach ($keahlianNama as $nama) {
            $k = AuthKeahlianMaster::create(['nama_keahlian' => $nama]);
            AuthPenggunaKeahlian::create([
                'id_pengguna' => $user->id_pengguna,
                'id_keahlian' => $k->id_keahlian,
            ]);
        }

        $ctx = $this->app->make(AuthorizationContextService::class);
        $composer = new AkunSceneComposer($user, $ctx);
        $json = $composer->compose();

        $childrenStr = json_encode($json['root']['children']);
        $this->assertStringContainsString('Medis', $childrenStr);
        $this->assertStringNotContainsString('lainnya', $childrenStr);
    }

    public function test_profil_null_nama_lengkap_shows_warning_banner()
    {
        $role = AuthRole::factory()->create(['nama_peran' => 'relawan']);
        $user = AuthUser::factory()->aktif()->create(['id_peran' => $role->id_peran]);
        AuthPenggunaProfil::factory()->create([
            'id_pengguna' => $user->id_pengguna,
            'nama_lengkap' => null,
        ]);

        $ctx = $this->app->make(AuthorizationContextService::class);
        $composer = new AkunSceneComposer($user, $ctx);
        $json = $composer->compose();

        $childrenStr = json_encode($json['root']['children']);
        $this->assertStringContainsString('Lengkapi profil Anda untuk pengalaman lebih baik', $childrenStr);
        $this->assertStringContainsString($user->no_hp, $childrenStr);
    }

    public function test_status_akun_badge_mapping()
    {
        $role = AuthRole::factory()->create(['nama_peran' => 'relawan']);

        $cases = [
            'menunggu' => 'Menunggu',
            'aktif' => 'Aktif',
            'nonaktif' => 'Nonaktif',
            'suspend' => 'Suspend',
        ];

        foreach ($cases as $status => $expectedLabel) {
            $user = AuthUser::factory()->create([
                'id_peran' => $role->id_peran,
                'status_akun' => $status,
            ]);
            AuthPenggunaProfil::factory()->create(['id_pengguna' => $user->id_pengguna, 'nama_lengkap' => 'User ' . $status]);

            $ctx = $this->app->make(AuthorizationContextService::class);
            $composer = new AkunSceneComposer($user, $ctx);
            $json = $composer->compose();

            $childrenStr = json_encode($json['root']['children']);
            $this->assertStringContainsString($expectedLabel, $childrenStr, "Status $status should show label $expectedLabel");
        }
    }

    public function test_toggle_is_tersedia_not_shown_when_status_not_aktif()
    {
        $role = AuthRole::factory()->create(['nama_peran' => 'relawan']);
        $user = AuthUser::factory()->create([
            'id_peran' => $role->id_peran,
            'status_akun' => 'menunggu',
            'is_tersedia' => 1,
        ]);
        AuthPenggunaProfil::factory()->create(['id_pengguna' => $user->id_pengguna, 'nama_lengkap' => 'User Menunggu']);

        $ctx = $this->app->make(AuthorizationContextService::class);
        $composer = new AkunSceneComposer($user, $ctx);
        $json = $composer->compose();

        $childrenStr = json_encode($json['root']['children']);
        $this->assertStringNotContainsString('Tersedia', $childrenStr);
        $this->assertStringNotContainsString('Tidak Tersedia', $childrenStr);
    }

    public function test_toggle_is_tersedia_action_works()
    {
        $role = AuthRole::factory()->create(['nama_peran' => 'relawan']);
        $user = AuthUser::factory()->aktif()->create(['id_peran' => $role->id_peran, 'is_tersedia' => 1, 'status_akun' => 'aktif']);

        $response = $this->actingAs($user, 'sanctum')->postJson('/api/v1/action', [
            'action_type' => 'profil.toggle_tersedia'
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'type' => 'reload_scene',
            'scene_id' => 'akun'
        ]);

        $this->assertFalse((bool) $user->fresh()->is_tersedia);
    }

    public function test_logout_sets_is_tersedia_to_false()
    {
        $role = AuthRole::factory()->create(['nama_peran' => 'relawan']);
        $user = AuthUser::factory()->aktif()->create(['id_peran' => $role->id_peran, 'is_tersedia' => 1]);

        $response = $this->actingAs($user, 'sanctum')->postJson('/api/v1/auth/logout');
        $response->assertStatus(200);

        $this->assertFalse((bool) $user->fresh()->is_tersedia);
    }

    public function test_app_bar_present_in_envelope()
    {
        $role = AuthRole::factory()->create(['nama_peran' => 'relawan']);
        $user = AuthUser::factory()->aktif()->create(['id_peran' => $role->id_peran]);
        AuthPenggunaProfil::factory()->create(['id_pengguna' => $user->id_pengguna, 'nama_lengkap' => 'Test']);

        $ctx = $this->app->make(AuthorizationContextService::class);
        $composer = new AkunSceneComposer($user, $ctx);
        $json = $composer->compose();

        $this->assertArrayHasKey('app_bar', $json);
        $this->assertEquals('Akun & Pusat Komando', $json['app_bar']['title']);
        $this->assertEquals('refresh', $json['app_bar']['actions'][0]['icon']);
        $this->assertEquals('reload', $json['app_bar']['actions'][0]['action']['type']);
    }

    public function test_etag_includes_role_and_scope()
    {
        $role = AuthRole::factory()->create(['nama_peran' => 'relawan']);
        $user = AuthUser::factory()->aktif()->create(['id_peran' => $role->id_peran]);
        AuthPenggunaProfil::factory()->create(['id_pengguna' => $user->id_pengguna, 'nama_lengkap' => 'Test']);

        $ctx = $this->app->make(AuthorizationContextService::class);
        $composer = new AkunSceneComposer($user, $ctx);
        $json = $composer->compose();

        $this->assertArrayHasKey('etag', $json);
        $this->assertArrayHasKey('version', $json);
        $this->assertArrayHasKey('ttl_seconds', $json);
        $this->assertEquals(120, $json['ttl_seconds']);
    }

    public function test_root_is_scrollable()
    {
        $role = AuthRole::factory()->create(['nama_peran' => 'relawan']);
        $user = AuthUser::factory()->aktif()->create(['id_peran' => $role->id_peran]);
        AuthPenggunaProfil::factory()->create(['id_pengguna' => $user->id_pengguna, 'nama_lengkap' => 'Test']);

        $ctx = $this->app->make(AuthorizationContextService::class);
        $composer = new AkunSceneComposer($user, $ctx);
        $json = $composer->compose();

        $this->assertEquals('scrollable', $json['root']['type']);
        $this->assertEquals('gray-100', $json['root']['style']['bg']);
    }
}
