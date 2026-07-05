<?php

namespace App\Providers;

use App\View\Composers\DashboardComposer;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use App\Services\Media\MediaServiceProvider;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        RateLimiter::for('api', fn (Request $request) => Limit::perMinute(60)->by($request->user()?->id_pengguna ?? $request->ip()));

        \Illuminate\Support\Facades\Gate::policy(
            \App\Models\AuthUser::class,
            \App\Policies\AuthUserPolicy::class
        );

        \Illuminate\Support\Facades\Gate::policy(
            \App\Models\JabatanPosisi::class,
            \App\Policies\JabatanPolicy::class
        );

        \Illuminate\Support\Facades\Gate::policy(
            \App\Models\OperasiInsiden::class,
            \App\Policies\InsidenPolicy::class
        );

        \Illuminate\Support\Facades\Gate::policy(
            \App\Models\RelawanKebutuhan::class,
            \App\Policies\RelawanKebutuhanPolicy::class
        );

        \Illuminate\Support\Facades\Gate::policy(
            \App\Models\RelawanPendaftaran::class,
            \App\Policies\RelawanPendaftaranPolicy::class
        );

        \Illuminate\Support\Facades\Gate::policy(
            \App\Models\AuthPenggunaProfil::class,
            \App\Policies\RelawanProfilPolicy::class
        );

        \Illuminate\Support\Facades\Gate::policy(
            \App\Models\RelawanPenugasan::class,
            \App\Policies\RelawanPenugasanPolicy::class
        );

        \Illuminate\Support\Facades\Gate::policy(
            \App\Models\OperasiPosaju::class,
            \App\Policies\OperasiPosajuPolicy::class
        );

        \Illuminate\Support\Facades\Gate::policy(
            \App\Models\OperasiKlaster::class,
            \App\Policies\OperasiKlasterPolicy::class
        );

        \Illuminate\Support\Facades\Gate::policy(
            \App\Models\OperasiTugas::class,
            \App\Policies\OperasiTugasPolicy::class
        );

        \Illuminate\Support\Facades\Gate::policy(
            \App\Models\AssessmentUtama::class,
            \App\Policies\AssessmentPolicy::class
        );

        \Illuminate\Support\Facades\Gate::policy(
            \App\Models\OperasiSitrep::class,
            \App\Policies\SitrepPolicy::class
        );

        \Illuminate\Support\Facades\Gate::policy(
            \App\Models\OperasiPenugasan::class,
            \App\Policies\PenugasanPolicy::class
        );

        \Illuminate\Support\Facades\Gate::policy(
            \App\Models\OperasiMobilisasi::class,
            \App\Policies\OperasiMobilisasiPolicy::class
        );

        \Illuminate\Support\Facades\Gate::policy(
            \App\Models\OperasiPleno::class,
            \App\Policies\PlanoPolicy::class
        );

        \Illuminate\Support\Facades\Gate::policy(
            \App\Models\OperasiEskalasi::class,
            \App\Policies\EskalasiPolicy::class
        );

        \Illuminate\Support\Facades\Gate::policy(
            \App\Models\DokumenSuratUtama::class,
            \App\Policies\SuratPolicy::class
        );

        \Illuminate\Support\Facades\Gate::policy(
            \App\Models\DokumenSuratParaf::class,
            \App\Policies\SuratPolicy::class
        );

        \Illuminate\Support\Facades\Gate::policy(
            \App\Models\LogistikKategori::class,
            \App\Policies\LogistikPolicy::class
        );

        \Illuminate\Support\Facades\Gate::policy(
            \App\Models\LogistikBarangKatalog::class,
            \App\Policies\LogistikPolicy::class
        );

        \Illuminate\Support\Facades\Gate::policy(
            \App\Models\LogistikStok::class,
            \App\Policies\LogistikPolicy::class
        );

        \Illuminate\Support\Facades\Gate::policy(
            \App\Models\LogistikMutasi::class,
            \App\Policies\LogistikPolicy::class
        );

        \Illuminate\Support\Facades\Gate::policy(
            \App\Models\LaporanKejadian::class,
            \App\Policies\LaporanKejadianPolicy::class
        );

        \Illuminate\Support\Facades\Gate::policy(
            \App\Models\OrgAsset::class,
            \App\Policies\OrgAssetPolicy::class
        );

        \Illuminate\Support\Facades\Gate::policy(
            \App\Models\OrganisasiPcnu::class,
            \App\Policies\OrganisasiPolicy::class
        );

        \Illuminate\Support\Facades\Gate::policy(
            \App\Models\OrganisasiMwc::class,
            \App\Policies\OrganisasiPolicy::class
        );

        \Illuminate\Support\Facades\Gate::policy(
            \App\Models\OrganisasiUnit::class,
            \App\Policies\OrganisasiPolicy::class
        );

        \Illuminate\Support\Facades\Gate::policy(
            \App\Models\MasterJabatanPenandatangan::class,
            \App\Policies\MasterDataPolicy::class
        );

        \Illuminate\Support\Facades\Gate::policy(
            \App\Models\MasterSuratTemplate::class,
            \App\Policies\MasterDataPolicy::class
        );

        \Illuminate\Support\Facades\Gate::policy(
            \App\Models\MasterSuratJenis::class,
            \App\Policies\MasterDataPolicy::class
        );

        \Illuminate\Support\Facades\Gate::policy(
            \App\Models\MasterKlaster::class,
            \App\Policies\MasterDataPolicy::class
        );

        \Illuminate\Support\Facades\Gate::policy(
            \App\Models\BencanaMasterJenis::class,
            \App\Policies\MasterDataPolicy::class
        );

        \Illuminate\Support\Facades\Gate::policy(
            \App\Models\LogistikGudang::class,
            \App\Policies\MasterDataPolicy::class
        );

        \Illuminate\Support\Facades\Gate::policy(
            \App\Models\LogistikPermintaan::class,
            \App\Policies\MasterDataPolicy::class
        );

        \Illuminate\Support\Facades\Gate::policy(
            \App\Models\OrganisasiSk::class,
            \App\Policies\MasterDataPolicy::class
        );

        \Illuminate\Support\Facades\Gate::policy(
            \App\Models\OrganisasiMandat::class,
            \App\Policies\MasterDataPolicy::class
        );

        \Illuminate\Support\Facades\Gate::policy(
            \App\Models\OrganisasiJabatan::class,
            \App\Policies\MasterDataPolicy::class
        );

        \Illuminate\Support\Facades\Gate::policy(
            \App\Models\OrganisasiDelegasi::class,
            \App\Policies\MasterDataPolicy::class
        );

        \Illuminate\Support\Facades\Gate::policy(
            \App\Models\RelawanSertifikasi::class,
            \App\Policies\MasterDataPolicy::class
        );

        \Illuminate\Support\Facades\Gate::policy(
            \App\Models\RelawanShift::class,
            \App\Policies\MasterDataPolicy::class
        );

        \Illuminate\Support\Facades\Gate::policy(
            \App\Models\OperasiAktivasi::class,
            \App\Policies\MasterDataPolicy::class
        );

        \Illuminate\Support\Facades\Gate::policy(
            \App\Models\OrganisasiRanting::class,
            \App\Policies\OrganisasiPolicy::class
        );

        \Illuminate\Support\Facades\Gate::policy(
            \App\Models\PenggunaJabatan::class,
            \App\Policies\MasterDataPolicy::class
        );

        \Illuminate\Support\Facades\Gate::policy(
            \App\Models\AuthRoleApplication::class,
            \App\Policies\MasterDataPolicy::class
        );

        \Illuminate\Support\Facades\Gate::define('viewCommandCenter', [\App\Policies\DashboardPolicy::class, 'viewCommandCenter']);

        // ============================================================
        // Governance Workflow Policies (Mandate-Based)
        // ============================================================
        \Illuminate\Support\Facades\Gate::policy(
            \App\Models\MeetingSession::class,
            \App\Policies\Governance\MeetingPolicy::class
        );

        // ============================================================
        // Governance Event Listeners
        // ============================================================
        \Illuminate\Support\Facades\Event::listen(
            \App\Events\Governance\MeetingStatusChanged::class,
            \App\Listeners\Governance\RecordMeetingAuditTrail::class
        );

        \Illuminate\Database\Eloquent\Model::preventLazyLoading();

        \App\Models\AuthUser::observe(\App\Observers\AuthUserObserver::class);
        \App\Models\AssessmentUtama::observe(\App\Observers\AssessmentUtamaObserver::class);
        \App\Models\AssessmentUtama::observe(\App\Observers\SyncObserver::class);
        \App\Models\OperasiSitrep::observe(\App\Observers\SyncObserver::class);
        \App\Models\OperasiKlaster::observe(\App\Observers\SyncObserver::class);
        \App\Models\OperasiPenugasan::observe(\App\Observers\SyncObserver::class);
        \App\Models\OperasiMobilisasi::observe(\App\Observers\SyncObserver::class);

        RateLimiter::for('login', fn (Request $request) => [
            Limit::perMinute(10)->by($request->ip()),
            Limit::perMinute(10)->by($request->input('no_hp', $request->ip())),
        ]);

        \Illuminate\Support\Facades\View::composer('dashboard.layouts.master', DashboardComposer::class);
        \Illuminate\Support\Facades\View::composer('dashboard.posko.dashboard', DashboardComposer::class);
    }
}
