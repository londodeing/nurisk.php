<?php

namespace App\Providers;

use App\Application\Media\Events\MediaDeletionRequested;
use App\Application\Media\Events\MediaReplacementRequested;
use App\Application\Media\Events\ThumbnailGenerationRequested;
use App\Application\Media\Events\WebpConversionRequested;
use App\Events\Governance\MeetingStatusChanged;
use App\Infrastructure\Media\Persistence\Models\MediaModel;
use App\Listeners\Governance\RecordMeetingAuditTrail;
use App\Listeners\Media\DispatchStorageDeletion;
use App\Listeners\Media\DispatchThumbnailGeneration;
use App\Listeners\Media\DispatchWebpConversion;
use App\Listeners\Operasi\ExecutePlenoDecisions;
use App\Events\Operasi\PlenoFinalized;
use App\Models\AssessmentUtama;
use App\Models\AuthPenggunaProfil;
use App\Models\AuthRoleApplication;
use App\Models\AuthUser;
use App\Models\BencanaMasterJenis;
use App\Models\DokumenSuratParaf;
use App\Models\DokumenSuratUtama;
use App\Models\JabatanPosisi;
use App\Models\LaporanKejadian;
use App\Models\LogistikBarangKatalog;
use App\Models\LogistikGudang;
use App\Models\LogistikKategori;
use App\Models\LogistikMutasi;
use App\Models\LogistikPermintaan;
use App\Models\LogistikStok;
use App\Models\MasterJabatanPenandatangan;
use App\Models\MasterKlaster;
use App\Models\MasterSuratJenis;
use App\Models\MasterSuratTemplate;
use App\Models\MeetingSession;
use App\Models\OperasiAktivasi;
use App\Models\OperasiEskalasi;
use App\Models\OperasiInsiden;
use App\Models\OperasiKlaster;
use App\Models\OperasiMobilisasi;
use App\Models\OperasiPenugasan;
use App\Models\OperasiPleno;
use App\Models\OperasiPosaju;
use App\Models\OperasiSitrep;
use App\Models\OperasiTugas;
use App\Models\OrganisasiDelegasi;
use App\Models\OrganisasiJabatan;
use App\Models\OrganisasiMandat;
use App\Models\OrganisasiMwc;
use App\Models\OrganisasiPcnu;
use App\Models\OrganisasiRanting;
use App\Models\OrganisasiSk;
use App\Models\OrganisasiUnit;
use App\Models\OrgAsset;
use App\Models\PenggunaJabatan;
use App\Models\RelawanKebutuhan;
use App\Models\RelawanPendaftaran;
use App\Models\RelawanPenugasan;
use App\Models\RelawanSertifikasi;
use App\Models\RelawanShift;
use App\Observers\AssessmentUtamaObserver;
use App\Observers\AuthUserObserver;
use App\Observers\SyncObserver;
use App\Policies\AssessmentPolicy;
use App\Policies\AuthUserPolicy;
use App\Policies\DashboardPolicy;
use App\Policies\EskalasiPolicy;
use App\Policies\Governance\MeetingPolicy;
use App\Policies\InsidenPolicy;
use App\Policies\JabatanPolicy;
use App\Policies\LaporanKejadianPolicy;
use App\Policies\LogistikPolicy;
use App\Policies\MasterDataPolicy;
use App\Policies\MediaPolicy;
use App\Policies\OperasiKlasterPolicy;
use App\Policies\OperasiMobilisasiPolicy;
use App\Policies\OperasiPosajuPolicy;
use App\Policies\OperasiTugasPolicy;
use App\Policies\OrganisasiPolicy;
use App\Policies\OrgAssetPolicy;
use App\Policies\PenugasanPolicy;
use App\Policies\PlanoPolicy;
use App\Policies\RelawanKebutuhanPolicy;
use App\Policies\RelawanPendaftaranPolicy;
use App\Policies\RelawanPenugasanPolicy;
use App\Policies\RelawanProfilPolicy;
use App\Policies\SitrepPolicy;
use App\Policies\SuratPolicy;
use App\View\Composers\DashboardComposer;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(
            \App\Services\Sdui\Runtime\Certification\StructuralValidator::class
        );
        $this->app->singleton(
            \App\Services\Sdui\Runtime\Certification\SemanticValidator::class
        );
        $this->app->singleton(
            \App\Services\Sdui\Runtime\Certification\RuntimeNormalizer::class
        );
        $this->app->singleton(
            \App\Services\Sdui\Runtime\Certification\RuntimeCertificationEngine::class
        );
        $this->app->singleton(
            \App\Services\Sdui\Runtime\Serializer\SduiSerializer::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        RateLimiter::for('api', fn (Request $request) => Limit::perMinute(60)->by($request->user()?->id_pengguna ?? $request->ip()));

        Gate::policy(
            AuthUser::class,
            AuthUserPolicy::class
        );

        Gate::policy(
            JabatanPosisi::class,
            JabatanPolicy::class
        );

        Gate::policy(
            OperasiInsiden::class,
            InsidenPolicy::class
        );

        Gate::policy(
            RelawanKebutuhan::class,
            RelawanKebutuhanPolicy::class
        );

        Gate::policy(
            RelawanPendaftaran::class,
            RelawanPendaftaranPolicy::class
        );

        Gate::policy(
            AuthPenggunaProfil::class,
            RelawanProfilPolicy::class
        );

        Gate::policy(
            RelawanPenugasan::class,
            RelawanPenugasanPolicy::class
        );

        Gate::policy(
            OperasiPosaju::class,
            OperasiPosajuPolicy::class
        );

        Gate::policy(
            OperasiKlaster::class,
            OperasiKlasterPolicy::class
        );

        Gate::policy(
            OperasiTugas::class,
            OperasiTugasPolicy::class
        );

        Gate::policy(
            AssessmentUtama::class,
            AssessmentPolicy::class
        );

        Gate::policy(
            OperasiSitrep::class,
            SitrepPolicy::class
        );

        Gate::policy(
            OperasiPenugasan::class,
            PenugasanPolicy::class
        );

        Gate::policy(
            OperasiMobilisasi::class,
            OperasiMobilisasiPolicy::class
        );

        Gate::policy(
            OperasiPleno::class,
            PlanoPolicy::class
        );

        Gate::policy(
            OperasiEskalasi::class,
            EskalasiPolicy::class
        );

        Gate::policy(
            DokumenSuratUtama::class,
            SuratPolicy::class
        );

        Gate::policy(
            DokumenSuratParaf::class,
            SuratPolicy::class
        );

        Gate::policy(
            LogistikKategori::class,
            LogistikPolicy::class
        );

        Gate::policy(
            LogistikBarangKatalog::class,
            LogistikPolicy::class
        );

        Gate::policy(
            LogistikStok::class,
            LogistikPolicy::class
        );

        Gate::policy(
            LogistikMutasi::class,
            LogistikPolicy::class
        );

        Gate::policy(
            LaporanKejadian::class,
            LaporanKejadianPolicy::class
        );

        Gate::policy(
            OrgAsset::class,
            OrgAssetPolicy::class
        );

        Gate::policy(
            OrganisasiPcnu::class,
            OrganisasiPolicy::class
        );

        Gate::policy(
            OrganisasiMwc::class,
            OrganisasiPolicy::class
        );

        Gate::policy(
            OrganisasiUnit::class,
            OrganisasiPolicy::class
        );

        Gate::policy(
            MasterJabatanPenandatangan::class,
            MasterDataPolicy::class
        );

        Gate::policy(
            MasterSuratTemplate::class,
            MasterDataPolicy::class
        );

        Gate::policy(
            MasterSuratJenis::class,
            MasterDataPolicy::class
        );

        Gate::policy(
            MasterKlaster::class,
            MasterDataPolicy::class
        );

        Gate::policy(
            BencanaMasterJenis::class,
            MasterDataPolicy::class
        );

        Gate::policy(
            LogistikGudang::class,
            MasterDataPolicy::class
        );

        Gate::policy(
            LogistikPermintaan::class,
            MasterDataPolicy::class
        );

        Gate::policy(
            OrganisasiSk::class,
            MasterDataPolicy::class
        );

        Gate::policy(
            OrganisasiMandat::class,
            MasterDataPolicy::class
        );

        Gate::policy(
            OrganisasiJabatan::class,
            MasterDataPolicy::class
        );

        Gate::policy(
            OrganisasiDelegasi::class,
            MasterDataPolicy::class
        );

        Gate::policy(
            RelawanSertifikasi::class,
            MasterDataPolicy::class
        );

        Gate::policy(
            RelawanShift::class,
            MasterDataPolicy::class
        );

        Gate::policy(
            OperasiAktivasi::class,
            MasterDataPolicy::class
        );

        Gate::policy(
            OrganisasiRanting::class,
            OrganisasiPolicy::class
        );

        Gate::policy(
            PenggunaJabatan::class,
            MasterDataPolicy::class
        );

        Gate::policy(
            AuthRoleApplication::class,
            MasterDataPolicy::class
        );

        Gate::define('viewCommandCenter', [DashboardPolicy::class, 'viewCommandCenter']);

        // ============================================================
        // Governance Workflow Policies (Mandate-Based)
        // ============================================================
        Gate::policy(
            MeetingSession::class,
            MeetingPolicy::class
        );

        // ============================================================
        // Governance Event Listeners
        // ============================================================
        Event::listen(
            MeetingStatusChanged::class,
            RecordMeetingAuditTrail::class
        );

        // ============================================================
        // Media Policy
        // ============================================================
        Gate::policy(MediaModel::class, MediaPolicy::class);

        // ============================================================
        // Media Event Listeners
        // ============================================================
        Event::listen(
            ThumbnailGenerationRequested::class,
            DispatchThumbnailGeneration::class,
        );
        Event::listen(
            WebpConversionRequested::class,
            DispatchWebpConversion::class,
        );
        Event::listen(
            MediaDeletionRequested::class,
            DispatchStorageDeletion::class,
        );
        Event::listen(
            MediaReplacementRequested::class,
            DispatchStorageDeletion::class,
        );

        Event::listen(
            PlenoFinalized::class,
            ExecutePlenoDecisions::class,
        );

        Model::preventLazyLoading();

        AuthUser::observe(AuthUserObserver::class);
        AssessmentUtama::observe(AssessmentUtamaObserver::class);
        AssessmentUtama::observe(SyncObserver::class);
        OperasiSitrep::observe(SyncObserver::class);
        OperasiKlaster::observe(SyncObserver::class);
        OperasiPenugasan::observe(SyncObserver::class);
        OperasiMobilisasi::observe(SyncObserver::class);
        \App\Models\OperasiInsiden::observe(\App\Observers\OperasiInsidenObserver::class);

        RateLimiter::for('login', fn (Request $request) => [
            Limit::perMinute(10)->by($request->ip()),
            Limit::perMinute(10)->by($request->input('no_hp', $request->ip())),
        ]);

        View::composer('dashboard.layouts.master', DashboardComposer::class);
        View::composer('dashboard.posko.dashboard', DashboardComposer::class);
    }
}
