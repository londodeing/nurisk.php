<?php

namespace Tests\Feature\Governance;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;
use App\Models\AuthUser;
use App\Models\AuthRole;
use App\Models\OperasiPleno;
use App\Models\DokumenSuratUtama;
use App\Models\DokumenSuratParaf;
use App\Models\OperasiInsiden;
use App\Models\MasterSuratJenis;
use App\Models\OrganisasiPcnu;
use App\Models\OrganisasiUnit;

class GovernancePermissionTest extends TestCase
{
    use RefreshDatabase;

    private OrganisasiPcnu $pcnu1;
    private OrganisasiPcnu $pcnu2;
    private OperasiInsiden $insiden1;
    private OperasiInsiden $insiden2;
    private OperasiPleno $pleno;
    private OperasiPleno $plenoLain;
    private DokumenSuratUtama $surat;
    private DokumenSuratUtama $suratLain;
    private DokumenSuratParaf $paraf;
    private MasterSuratJenis $jenisSurat;
    private array $users = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $unit = OrganisasiUnit::create(['nama_unit' => 'Unit Perm Test', 'tipe_unit' => 'pcnu']);
        $this->pcnu1 = OrganisasiPcnu::create(['id_pcnu' => 9001, 'id_unit' => $unit->id_unit, 'nama_pcnu' => 'PCNU Perm Test A']);
        $this->pcnu2 = OrganisasiPcnu::create(['id_pcnu' => 9002, 'id_unit' => $unit->id_unit, 'nama_pcnu' => 'PCNU Perm Test B']);

        $bencanaMaster = \App\Models\BencanaMasterJenis::first()
            ?? \App\Models\BencanaMasterJenis::create(['nama_bencana' => 'Perm Test Bencana', 'kategori' => 'alam']);

        $this->insiden1 = OperasiInsiden::create([
            'uuid_insiden' => (string) \Illuminate\Support\Str::uuid(),
            'id_pcnu' => $this->pcnu1->id_pcnu,
            'kode_kejadian' => 'PRM-A-' . now()->timestamp,
            'id_jenis_bencana' => $bencanaMaster->id_jenis,
            'status_insiden' => 'draft',
            'waktu_mulai' => now(),
        ]);

        $this->insiden2 = OperasiInsiden::create([
            'uuid_insiden' => (string) \Illuminate\Support\Str::uuid(),
            'id_pcnu' => $this->pcnu2->id_pcnu,
            'kode_kejadian' => 'PRM-B-' . now()->timestamp,
            'id_jenis_bencana' => $bencanaMaster->id_jenis,
            'status_insiden' => 'draft',
            'waktu_mulai' => now(),
        ]);

        $defaultUser = $this->buatUser('super_admin');

        $this->pleno = OperasiPleno::create([
            'id_insiden' => $this->insiden1->id_insiden,
            'nomor_pleno' => 'PERM/001/' . now()->year,
            'waktu_pleno' => now(),
            'jenis_pleno' => 'aktivasi_operasi',
            'pimpinan_pleno' => $defaultUser->id_pengguna,
            'notulis_pleno' => $defaultUser->id_pengguna,
            'lokasi_pleno' => 'Posko Perm',
            'status_pleno' => 'draft',
            'dibuat_pada' => now(),
        ]);

        $this->plenoLain = OperasiPleno::create([
            'id_insiden' => $this->insiden2->id_insiden,
            'nomor_pleno' => 'PERM/002/' . now()->year,
            'waktu_pleno' => now(),
            'jenis_pleno' => 'aktivasi_operasi',
            'pimpinan_pleno' => $defaultUser->id_pengguna,
            'notulis_pleno' => $defaultUser->id_pengguna,
            'lokasi_pleno' => 'Posko Perm B',
            'status_pleno' => 'draft',
            'dibuat_pada' => now(),
        ]);

        $this->jenisSurat = MasterSuratJenis::first() ?? MasterSuratJenis::create([
            'kode_jenis' => 'PRM',
            'nama_jenis' => 'Perm Test',
            'kategori' => 'UMUM',
        ]);

        $this->surat = DokumenSuratUtama::create([
            'id_insiden' => $this->insiden1->id_insiden,
            'id_jenis_surat' => $this->jenisSurat->id_jenis_surat,
            'nomor_surat_resmi' => 'PERM/001/' . now()->year,
            'perihal' => 'Perm Test Surat',
            'tgl_terbit' => now()->format('Y-m-d'),
            'id_pengguna_ttd' => $defaultUser->id_pengguna,
            'status_surat' => 'draft',
        ]);

        $this->suratLain = DokumenSuratUtama::create([
            'id_insiden' => $this->insiden2->id_insiden,
            'id_jenis_surat' => $this->jenisSurat->id_jenis_surat,
            'nomor_surat_resmi' => 'PERM/002/' . now()->year,
            'perihal' => 'Perm Test Surat Lain',
            'tgl_terbit' => now()->format('Y-m-d'),
            'id_pengguna_ttd' => $defaultUser->id_pengguna,
            'status_surat' => 'draft',
        ]);

        $this->users['super_admin'] = $this->buatUser('super_admin');

        $this->paraf = DokumenSuratParaf::create([
            'id_surat' => $this->surat->id_surat,
            'id_pengguna' => $this->users['super_admin']->id_pengguna,
            'urutan' => 1,
            'status_paraf' => 'menunggu',
        ]);
        $this->users['pwnu'] = $this->buatUser('pwnu');
        $this->users['pcnu_own'] = $this->buatUser('pcnu', 'pcnu', $this->pcnu1->id_pcnu);
        $this->users['pcnu_other'] = $this->buatUser('pcnu', 'pcnu', $this->pcnu2->id_pcnu);
        $this->users['relawan'] = $this->buatUser('relawan');
    }

    private function buatUser(string $namaPeran, ?string $scopeType = null, ?int $scopeId = null): AuthUser
    {
        $peran = AuthRole::where('nama_peran', $namaPeran)->first();
        $data = [
            'id_peran' => $peran->id_peran,
            'status_akun' => 'aktif',
        ];
        if ($scopeType) {
            $data['default_scope_type'] = $scopeType;
            $data['default_scope_id'] = $scopeId;
        }
        return AuthUser::factory()->create($data);
    }

    private function assertGate(string $ability, mixed $arguments, bool $expected, string $label): void
    {
        $this->assertEquals($expected, Gate::allows($ability, $arguments), $label);
    }

    // ========================================================================
    //  RELAWAN — all actions denied
    // ========================================================================

    public function test_relawan_denied_pleno_viewAny(): void
    {
        $this->actingAs($this->users['relawan']);
        $this->assertGate('viewAny', OperasiPleno::class, false, 'relawan pleno.viewAny');
    }

    public function test_relawan_denied_pleno_crud_finalisasi(): void
    {
        $this->actingAs($this->users['relawan']);
        $this->assertGate('view', $this->pleno, false, 'relawan pleno.view');
        $this->assertGate('create', OperasiPleno::class, false, 'relawan pleno.create');
        $this->assertGate('update', $this->pleno, false, 'relawan pleno.update');
        $this->assertGate('delete', $this->pleno, false, 'relawan pleno.delete');
        $this->assertGate('finalisasi', $this->pleno, false, 'relawan pleno.finalisasi');
        $this->assertGate('tambahKeputusan', $this->pleno, false, 'relawan pleno.tambahKeputusan');
        $this->assertGate('tambahPeserta', $this->pleno, false, 'relawan pleno.tambahPeserta');
    }

    public function test_relawan_denied_surat_viewAny(): void
    {
        $this->actingAs($this->users['relawan']);
        $this->assertGate('viewAny', DokumenSuratUtama::class, false, 'relawan surat.viewAny');
    }

    public function test_relawan_denied_surat_crud_finalisasi_paraf(): void
    {
        $this->actingAs($this->users['relawan']);
        $this->assertGate('view', $this->surat, false, 'relawan surat.view');
        $this->assertGate('create', DokumenSuratUtama::class, false, 'relawan surat.create');
        $this->assertGate('update', $this->surat, false, 'relawan surat.update');
        $this->assertGate('delete', $this->surat, false, 'relawan surat.delete');
        $this->assertGate('finalisasi', $this->surat, false, 'relawan surat.finalisasi');
        $this->assertGate('paraf', $this->paraf, false, 'relawan surat.paraf');
    }

    // ========================================================================
    //  SUPER_ADMIN — all allowed (with status constraints)
    // ========================================================================

    public function test_super_admin_pleno_viewAny(): void
    {
        $this->actingAs($this->users['super_admin']);
        $this->assertGate('viewAny', OperasiPleno::class, true, 'super_admin pleno.viewAny');
    }

    public function test_super_admin_pleno_crud_draft(): void
    {
        $this->actingAs($this->users['super_admin']);
        $this->assertGate('view', $this->pleno, true, 'super_admin pleno.view');
        $this->assertGate('create', OperasiPleno::class, true, 'super_admin pleno.create');
        $this->assertGate('update', $this->pleno, true, 'super_admin pleno.update');
        $this->assertGate('delete', $this->pleno, true, 'super_admin pleno.delete');
        $this->assertGate('tambahKeputusan', $this->pleno, true, 'super_admin pleno.tambahKeputusan');
        $this->assertGate('tambahPeserta', $this->pleno, true, 'super_admin pleno.tambahPeserta');
    }

    public function test_super_admin_surat_viewAny(): void
    {
        $this->actingAs($this->users['super_admin']);
        $this->assertGate('viewAny', DokumenSuratUtama::class, true, 'super_admin surat.viewAny');
    }

    public function test_super_admin_surat_crud_draft(): void
    {
        $this->actingAs($this->users['super_admin']);
        $this->assertGate('view', $this->surat, true, 'super_admin surat.view');
        $this->assertGate('create', DokumenSuratUtama::class, true, 'super_admin surat.create');
        $this->assertGate('update', $this->surat, true, 'super_admin surat.update');
        $this->assertGate('delete', $this->surat, true, 'super_admin surat.delete');
    }

    public function test_super_admin_paraf_own(): void
    {
        $this->actingAs($this->users['super_admin']);
        $this->assertGate('paraf', $this->paraf, true, 'super_admin paraf own (same user)');
    }

    // ========================================================================
    //  PWNU — same as super_admin except delete
    // ========================================================================

    public function test_pwnu_pleno_viewAny(): void
    {
        $this->actingAs($this->users['pwnu']);
        $this->assertGate('viewAny', OperasiPleno::class, true, 'pwnu pleno.viewAny');
    }

    public function test_pwnu_pleno_crud_draft(): void
    {
        $this->actingAs($this->users['pwnu']);
        $this->assertGate('view', $this->pleno, true, 'pwnu pleno.view');
        $this->assertGate('create', OperasiPleno::class, true, 'pwnu pleno.create');
        $this->assertGate('update', $this->pleno, true, 'pwnu pleno.update');
        $this->assertGate('delete', $this->pleno, false, 'pwnu pleno.delete');
        $this->assertGate('tambahKeputusan', $this->pleno, true, 'pwnu pleno.tambahKeputusan');
        $this->assertGate('tambahPeserta', $this->pleno, true, 'pwnu pleno.tambahPeserta');
    }

    public function test_pwnu_surat_viewAny(): void
    {
        $this->actingAs($this->users['pwnu']);
        $this->assertGate('viewAny', DokumenSuratUtama::class, true, 'pwnu surat.viewAny');
    }

    public function test_pwnu_surat_crud_draft(): void
    {
        $this->actingAs($this->users['pwnu']);
        $this->assertGate('view', $this->surat, true, 'pwnu surat.view');
        $this->assertGate('create', DokumenSuratUtama::class, true, 'pwnu surat.create');
        $this->assertGate('update', $this->surat, true, 'pwnu surat.update');
        $this->assertGate('delete', $this->surat, false, 'pwnu surat.delete');
    }

    // ========================================================================
    //  PCNU — own scope allowed, other scope denied for view
    // ========================================================================

    public function test_pcnu_own_scope_pleno_viewAny(): void
    {
        $this->actingAs($this->users['pcnu_own']);
        $this->assertGate('viewAny', OperasiPleno::class, true, 'pcnu_own pleno.viewAny');
    }

    public function test_pcnu_own_scope_pleno_view_create_update(): void
    {
        $this->actingAs($this->users['pcnu_own']);
        $this->assertGate('view', $this->pleno, true, 'pcnu_own pleno.view own');
        $this->assertGate('create', OperasiPleno::class, true, 'pcnu_own pleno.create');
        $this->assertGate('update', $this->pleno, true, 'pcnu_own pleno.update own');
    }

    public function test_pcnu_own_scope_pleno_denied_delete_finalisasi_tambahKeputusan(): void
    {
        $this->actingAs($this->users['pcnu_own']);
        $this->assertGate('delete', $this->pleno, false, 'pcnu_own pleno.delete');
        $this->assertGate('finalisasi', $this->pleno, false, 'pcnu_own pleno.finalisasi');
        $this->assertGate('tambahKeputusan', $this->pleno, false, 'pcnu_own pleno.tambahKeputusan');
    }

    public function test_pcnu_own_scope_pleno_tambahPeserta(): void
    {
        $this->actingAs($this->users['pcnu_own']);
        $this->assertGate('tambahPeserta', $this->pleno, true, 'pcnu_own pleno.tambahPeserta own');
    }

    public function test_pcnu_own_scope_surat_viewAny(): void
    {
        $this->actingAs($this->users['pcnu_own']);
        $this->assertGate('viewAny', DokumenSuratUtama::class, true, 'pcnu_own surat.viewAny');
    }

    public function test_pcnu_own_scope_surat_view_create_update(): void
    {
        $this->actingAs($this->users['pcnu_own']);
        $this->assertGate('view', $this->surat, true, 'pcnu_own surat.view own');
        $this->assertGate('create', DokumenSuratUtama::class, true, 'pcnu_own surat.create');
        $this->assertGate('update', $this->surat, true, 'pcnu_own surat.update own');
    }

    public function test_pcnu_own_scope_surat_denied_delete_finalisasi(): void
    {
        $this->actingAs($this->users['pcnu_own']);
        $this->assertGate('delete', $this->surat, false, 'pcnu_own surat.delete');
        $this->assertGate('finalisasi', $this->surat, false, 'pcnu_own surat.finalisasi');
    }

    // ========================================================================
    //  PCNU other scope — view denied, create/update allowed
    // ========================================================================

    public function test_pcnu_other_scope_pleno_viewAny(): void
    {
        $this->actingAs($this->users['pcnu_other']);
        $this->assertGate('viewAny', OperasiPleno::class, true, 'pcnu_other pleno.viewAny');
    }

    public function test_pcnu_other_scope_pleno_view_denied_create_update_allowed(): void
    {
        $this->actingAs($this->users['pcnu_other']);
        $this->assertGate('view', $this->pleno, false, 'pcnu_other pleno.view other');
        $this->assertGate('create', OperasiPleno::class, true, 'pcnu_other pleno.create');
        $this->assertGate('update', $this->pleno, true, 'pcnu_other pleno.update other');
    }

    public function test_pcnu_other_scope_pleno_tambahPeserta_denied(): void
    {
        $this->actingAs($this->users['pcnu_other']);
        $this->assertGate('tambahPeserta', $this->pleno, false, 'pcnu_other pleno.tambahPeserta other');
    }

    public function test_pcnu_other_scope_pleno_denied_delete_finalisasi(): void
    {
        $this->actingAs($this->users['pcnu_other']);
        $this->assertGate('delete', $this->pleno, false, 'pcnu_other pleno.delete');
        $this->assertGate('finalisasi', $this->pleno, false, 'pcnu_other pleno.finalisasi');
    }

    public function test_pcnu_other_scope_surat_view_denied_create_update_allowed(): void
    {
        $this->actingAs($this->users['pcnu_other']);
        $this->assertGate('view', $this->surat, false, 'pcnu_other surat.view other');
        $this->assertGate('create', DokumenSuratUtama::class, true, 'pcnu_other surat.create');
        $this->assertGate('update', $this->surat, true, 'pcnu_other surat.update other');
    }

    public function test_pcnu_other_scope_surat_denied_delete_finalisasi(): void
    {
        $this->actingAs($this->users['pcnu_other']);
        $this->assertGate('delete', $this->surat, false, 'pcnu_other surat.delete');
        $this->assertGate('finalisasi', $this->surat, false, 'pcnu_other surat.finalisasi');
    }

    // ========================================================================
    //  STATUS-BASED CONSTRAINTS — Pleno
    // ========================================================================

    public function test_pleno_ditinjau_view_update_allowed_delete_denied(): void
    {
        $this->pleno->update(['status_pleno' => 'ditinjau']);
        $this->actingAs($this->users['super_admin']);
        $this->assertGate('view', $this->pleno, true, 'pleno ditinjau view');
        $this->assertGate('update', $this->pleno, true, 'pleno ditinjau update');
        $this->assertGate('delete', $this->pleno, false, 'pleno ditinjau delete');
    }

    public function test_pleno_ditinjau_tambahPeserta_denied(): void
    {
        $this->pleno->update(['status_pleno' => 'ditinjau']);
        $this->actingAs($this->users['super_admin']);
        $this->assertGate('tambahPeserta', $this->pleno, false, 'pleno ditinjau tambahPeserta');
    }

    public function test_pleno_ditinjau_tambahKeputusan_allowed(): void
    {
        $this->pleno->update(['status_pleno' => 'ditinjau']);
        $this->actingAs($this->users['super_admin']);
        $this->assertGate('tambahKeputusan', $this->pleno, true, 'pleno ditinjau tambahKeputusan');
    }

    public function test_pleno_final_view_allowed_update_finalisasi_delete_denied(): void
    {
        $this->pleno->update(['status_pleno' => 'final']);
        $this->actingAs($this->users['super_admin']);
        $this->assertGate('view', $this->pleno, true, 'pleno final view');
        $this->assertGate('update', $this->pleno, false, 'pleno final update');
        $this->assertGate('finalisasi', $this->pleno, false, 'pleno final finalisasi');
        $this->assertGate('delete', $this->pleno, false, 'pleno final delete');
        $this->assertGate('tambahKeputusan', $this->pleno, false, 'pleno final tambahKeputusan');
    }

    // ========================================================================
    //  STATUS-BASED CONSTRAINTS — Surat
    // ========================================================================

    public function test_surat_review_paraf_view_allowed_update_delete_denied(): void
    {
        $this->surat->update(['status_surat' => 'review_paraf']);
        $this->actingAs($this->users['super_admin']);
        $this->assertGate('view', $this->surat, true, 'surat review view');
        $this->assertGate('update', $this->surat, false, 'surat review update');
        $this->assertGate('delete', $this->surat, false, 'surat review delete');
    }

    public function test_surat_siap_tanda_tangan_finalisasi_allowed_for_super_admin_pwnu(): void
    {
        $this->paraf->update(['status_paraf' => 'disetujui']);
        $this->surat->update(['status_surat' => 'siap_tanda_tangan']);
        $this->actingAs($this->users['super_admin']);
        $this->assertGate('finalisasi', $this->surat, true, 'super_admin surat siap finalisasi');
        $this->actingAs($this->users['pwnu']);
        $this->assertGate('finalisasi', $this->surat, true, 'pwnu surat siap finalisasi');
    }

    public function test_surat_siap_tanda_tangan_finalisasi_denied_for_pcnu_relawan(): void
    {
        $this->paraf->update(['status_paraf' => 'disetujui']);
        $this->surat->update(['status_surat' => 'siap_tanda_tangan']);
        $this->actingAs($this->users['pcnu_own']);
        $this->assertGate('finalisasi', $this->surat, false, 'pcnu surat siap finalisasi');
        $this->actingAs($this->users['relawan']);
        $this->assertGate('finalisasi', $this->surat, false, 'relawan surat siap finalisasi');
    }

    public function test_surat_ditandatangani_view_allowed_update_delete_finalisasi_denied(): void
    {
        $this->surat->update(['status_surat' => 'ditandatangani']);
        $this->actingAs($this->users['super_admin']);
        $this->assertGate('view', $this->surat, true, 'surat signed view');
        $this->assertGate('update', $this->surat, false, 'surat signed update');
        $this->assertGate('delete', $this->surat, false, 'surat signed delete');
        $this->assertGate('finalisasi', $this->surat, false, 'surat signed finalisasi');
    }

    public function test_surat_draft_finalisasi_denied(): void
    {
        $this->actingAs($this->users['super_admin']);
        $this->assertGate('finalisasi', $this->surat, false, 'surat draft finalisasi');
    }

    // ========================================================================
    //  PARAF — only the assigned user can paraf
    // ========================================================================

    public function test_paraf_only_assigned_user_allowed(): void
    {
        $this->actingAs($this->users['pwnu']);
        $this->assertGate('paraf', $this->paraf, false, 'pwnu paraf not assigned');

        $assignedUser = $this->users['super_admin'];
        $this->actingAs($assignedUser);
        $this->assertGate('paraf', $this->paraf, true, 'assigned user paraf');
    }

    public function test_paraf_already_approved_denied(): void
    {
        $this->paraf->update(['status_paraf' => 'disetujui']);
        $this->actingAs($this->users['super_admin']);
        $this->assertGate('paraf', $this->paraf, false, 'paraf already approved');
    }

    public function test_paraf_rejected_denied(): void
    {
        $this->paraf->update(['status_paraf' => 'ditolak']);
        $this->actingAs($this->users['super_admin']);
        $this->assertGate('paraf', $this->paraf, false, 'paraf rejected');
    }

    // ========================================================================
    //  FINALISASI PLENO — only super_admin/pwnu, only ditinjau
    // ========================================================================

    public function test_pleno_finalisasi_ditinjau_allowed_for_super_admin_pwnu(): void
    {
        $this->pleno->update(['status_pleno' => 'ditinjau']);
        $this->actingAs($this->users['super_admin']);
        $this->assertGate('finalisasi', $this->pleno, true, 'super_admin pleno ditinjau finalisasi');
        $this->actingAs($this->users['pwnu']);
        $this->assertGate('finalisasi', $this->pleno, true, 'pwnu pleno ditinjau finalisasi');
    }

    public function test_pleno_finalisasi_ditinjau_denied_for_pcnu_relawan(): void
    {
        $this->pleno->update(['status_pleno' => 'ditinjau']);
        $this->actingAs($this->users['pcnu_own']);
        $this->assertGate('finalisasi', $this->pleno, false, 'pcnu pleno ditinjau finalisasi');
        $this->actingAs($this->users['relawan']);
        $this->assertGate('finalisasi', $this->pleno, false, 'relawan pleno ditinjau finalisasi');
    }

    public function test_pleno_draft_finalisasi_gate_allowed_service_blocks(): void
    {
        $this->actingAs($this->users['super_admin']);
        $this->assertGate('finalisasi', $this->pleno, true, 'pleno draft finalisasi gate allowed (service blocks ditinjau-only)');
    }

    // ========================================================================
    //  SCOPE ISOLATION — cross-wilayah
    // ========================================================================

    public function test_pcnu_cannot_view_other_wilayah_pleno(): void
    {
        $this->actingAs($this->users['pcnu_own']);
        $this->assertGate('view', $this->plenoLain, false, 'pcnu_own view pleno other wilayah');
    }

    public function test_pcnu_cannot_view_other_wilayah_surat(): void
    {
        $this->actingAs($this->users['pcnu_own']);
        $this->assertGate('view', $this->suratLain, false, 'pcnu_own view surat other wilayah');
    }

    public function test_pwnu_can_view_all_wilayah(): void
    {
        $this->actingAs($this->users['pwnu']);
        $this->assertGate('view', $this->plenoLain, true, 'pwnu view pleno other wilayah');
        $this->assertGate('view', $this->suratLain, true, 'pwnu view surat other wilayah');
    }

    public function test_super_admin_can_view_all_wilayah(): void
    {
        $this->actingAs($this->users['super_admin']);
        $this->assertGate('view', $this->plenoLain, true, 'super_admin view pleno other wilayah');
        $this->assertGate('view', $this->suratLain, true, 'super_admin view surat other wilayah');
    }
}
