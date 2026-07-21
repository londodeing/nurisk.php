<?php

use App\Http\Controllers\Api\Admin\MetricsController;
use App\Http\Controllers\Api\Admin\PenggunaApiController;
use App\Http\Controllers\Api\Admin\PenggunaJabatanController;
use App\Http\Controllers\Api\Admin\RoleApplicationController;
use App\Http\Controllers\Api\AsetApiController;
use App\Http\Controllers\Api\AssessmentApiController;
use App\Http\Controllers\Api\AssessmentBiodataController;
use App\Http\Controllers\Api\AssessmentDampakEkoController;
use App\Http\Controllers\Api\AssessmentDampakInfraController;
use App\Http\Controllers\Api\AssessmentDampakLingController;
use App\Http\Controllers\Api\AssessmentDampakManusiaController;
use App\Http\Controllers\Api\AssessmentLengkapController;
use App\Http\Controllers\Api\AssessmentNarasiController;
use App\Http\Controllers\Api\AssessmentSkorController;
use App\Http\Controllers\Api\Auth\AuthenticationApiController;
use App\Http\Controllers\Api\Auth\DeviceAuthController;
use App\Http\Controllers\Api\BmkgProxyController;
use App\Http\Controllers\Api\CommandCenterApiController;
use App\Http\Controllers\Api\Device\DeviceApiController;
use App\Http\Controllers\Api\Governance\GovAuditLogController;
use App\Http\Controllers\Api\Governance\OrgAuthorityController;
use App\Http\Controllers\Api\Governance\OrgDelegationController;
use App\Http\Controllers\Api\Governance\OrgFunctionController;
use App\Http\Controllers\Api\Governance\OrgInstitutionController;
use App\Http\Controllers\Api\Governance\OrgMandateController;
use App\Http\Controllers\Api\Governance\OrgNodeController;
use App\Http\Controllers\Api\Governance\OrgPositionController;
use App\Http\Controllers\Api\Governance\OrgSkController;
use App\Http\Controllers\Api\Governance\OrgStructureLevelController;
use App\Http\Controllers\Api\Governance\PlanoApiController;
use App\Http\Controllers\Api\Governance\SuratApiController;
use App\Http\Controllers\Api\HealthCheckController;
use App\Http\Controllers\Api\HistoriAnalisisController;
use App\Http\Controllers\Api\HistoriBencanaController;
use App\Http\Controllers\Api\HistoriIndikatorController;
use App\Http\Controllers\Api\HistoriPetaRisikoController;
use App\Http\Controllers\Api\HistoriProbabilitasController;
use App\Http\Controllers\Api\InternalWeatherController;
use App\Http\Controllers\Api\InventarisAsetController;
use App\Http\Controllers\Api\InventarisDeploymentController;
use App\Http\Controllers\Api\InventarisDokumenController;
use App\Http\Controllers\Api\InventarisJenisController;
use App\Http\Controllers\Api\InventarisKategoriController;
use App\Http\Controllers\Api\InventarisKondisiLogController;
use App\Http\Controllers\Api\InventarisPemeliharaanController;
use App\Http\Controllers\Api\LaporanKejadianApiController;
use App\Http\Controllers\Api\Logistik\LogistikGudangController;
use App\Http\Controllers\Api\Logistik\LogistikKatalogController;
use App\Http\Controllers\Api\Logistik\LogistikKategoriController;
use App\Http\Controllers\Api\Logistik\LogistikMutasiController;
use App\Http\Controllers\Api\Logistik\LogistikPermintaanController;
use App\Http\Controllers\Api\Logistik\LogistikStokController;
use App\Http\Controllers\Api\Master\BencanaMasterJenisController;
use App\Http\Controllers\Api\Master\MasterJabatanController;
use App\Http\Controllers\Api\Master\MasterJabatanPenandatanganController;
use App\Http\Controllers\Api\Master\MasterKlasterController;
use App\Http\Controllers\Api\Master\MasterSertifikasiController;
use App\Http\Controllers\Api\Master\MasterSuratJenisController;
use App\Http\Controllers\Api\Master\MasterSuratTemplateController;
use App\Http\Controllers\Api\MasterVersionController;
use App\Http\Controllers\Api\MasterIndikatorSkorController;
use App\Http\Controllers\Api\MediaController;
use App\Http\Controllers\Api\Operasi\AktivasiApiController;
use App\Http\Controllers\Api\Operasi\BulkStubController;
use App\Http\Controllers\Api\Operasi\EskalasiApiController;
use App\Http\Controllers\Api\Operasi\InsidenApiController;
use App\Http\Controllers\Api\Operasi\InsidenFullController;
use App\Http\Controllers\Api\Operasi\KlasterApiController;
use App\Http\Controllers\Api\Operasi\MobilisasiApiController;
use App\Http\Controllers\Api\Operasi\OperasiKlasterController;
use App\Http\Controllers\Api\Operasi\OperasiPosajuController;
use App\Http\Controllers\Api\Operasi\OperasiTugasController;
use App\Http\Controllers\Api\Operasi\DistribusiApiController;
use App\Http\Controllers\Api\Operasi\PenugasanApiController;
use App\Http\Controllers\Api\Operasi\SitrepApiController;
use App\Http\Controllers\Api\Operasi\SyncApiController;
use App\Http\Controllers\Api\Organisasi\OrganisasiAuditLogController;
use App\Http\Controllers\Api\Organisasi\OrganisasiDelegasiController;
use App\Http\Controllers\Api\Organisasi\OrganisasiJabatanController;
use App\Http\Controllers\Api\Organisasi\OrganisasiMandatController;
use App\Http\Controllers\Api\Organisasi\OrganisasiSkController;
use App\Http\Controllers\Api\OrganisasiApiController;
use App\Http\Controllers\Api\PublicDashboardApiController;
use App\Http\Controllers\Api\PublicIncidentDetailController;
use App\Http\Controllers\Api\PublicNewsController;
use App\Http\Controllers\Api\PublicResourceController;
use App\Http\Controllers\Api\Relawan\RelawanKebutuhanController;
use App\Http\Controllers\Api\Relawan\RelawanPendaftaranController;
use App\Http\Controllers\Api\Relawan\RelawanPenugasanController;
use App\Http\Controllers\Api\Relawan\RelawanProfilController;
use App\Http\Controllers\Api\Relawan\RelawanSertifikasiController;
use App\Http\Controllers\Api\Relawan\RelawanShiftController;
use App\Http\Controllers\Api\Relawan\VolunteerApiController;
use App\Http\Controllers\Api\SqlViewController;
use App\Http\Controllers\Api\WeatherForecastController;
use App\Http\Controllers\Api\WilayahApiController;
use App\Models\AuthKeahlianMaster;
use Illuminate\Support\Facades\Route;

// ============================================================
// API — Wilayah (Publik, untuk cascading dropdown)
// ============================================================
Route::prefix('wilayah')->name('api.wilayah.')->group(function () {
    Route::get('kabupaten', [WilayahApiController::class, 'kabupaten'])->name('kabupaten');
    Route::get('kecamatan', [WilayahApiController::class, 'kecamatan'])->name('kecamatan');
    Route::get('desa', [WilayahApiController::class, 'desa'])->name('desa');
    Route::get('pcnu', [WilayahApiController::class, 'pcnu'])->name('pcnu');
});

// ============================================================
// API — Public Dashboard
// ============================================================
Route::prefix('public')->name('api.public.')->group(function () {
    // Dashboard Publik & COP
    Route::get('dashboard', [\App\Http\Controllers\Api\PublicDashboardWebController::class, 'index'])->name('dashboard.index');
    Route::get('dashboard/config', [\App\Http\Controllers\Api\PublicDashboardApiController::class, 'config'])->name('dashboard.config');
    Route::get('incident/list', [\App\Http\Controllers\Api\PublicListApiController::class, 'incidentList'])->name('incident.list');
    Route::get('mission/list', [\App\Http\Controllers\Api\PublicListApiController::class, 'missionList'])->name('mission.list');
    
    // Incident Detail for public
    Route::get('incident/{id}/detail', [\App\Http\Controllers\Api\PublicIncidentDetailController::class, 'show'])->name('incident.detail');
    Route::get('dashboard/trc/queue', [\App\Http\Controllers\Api\TrcDashboardApiController::class, 'queue'])->middleware('auth:sanctum')->name('dashboard.trc.queue');
    Route::get('dashboard/trc/assignable', [\App\Http\Controllers\Api\TrcDashboardApiController::class, 'assignable'])->middleware('auth:sanctum')->name('dashboard.trc.assignable');
    Route::get('news', [PublicNewsController::class, 'index'])->name('news.index');
    Route::get('news/{slug}', [PublicNewsController::class, 'show'])->name('news.show');
    Route::get('resources', [PublicResourceController::class, 'index'])->name('resources.index');
});

// ============================================================
// API — Public Map & Layers
// ============================================================
Route::prefix('public/map')->name('api.public.map.')->group(function () {
    Route::get('config', [\App\Http\Controllers\Api\MapLayerController::class, 'config'])->name('config');
    Route::get('providers', [\App\Http\Controllers\Api\MapLayerController::class, 'providers'])->name('providers');
    Route::get('layers', [\App\Http\Controllers\Api\MapLayerController::class, 'index'])->name('layers.index');
    Route::get('layers/{layerId}', [\App\Http\Controllers\Api\MapLayerController::class, 'show'])->name('layers.show');
    
    // Digital Twin Operational Layer API
    Route::get('operational/{type}', [\App\Http\Controllers\Api\MapLayerController::class, 'operationalObjects'])->name('operational');
    Route::get('live-update', [\App\Http\Controllers\Api\MapLiveUpdateController::class, 'stream'])->name('live_update');
    Route::get('proxy/inarisk/wms', [\App\Http\Controllers\Api\InariskWmsProxyController::class, 'proxy'])->name('proxy.inarisk.wms');
    Route::get('tiles/{z}/{x}/{y}.png', [\App\Http\Controllers\Api\InariskWmsProxyController::class, 'tile'])->name('tiles.xyz');
});

// ============================================================
// API — External Proxies (Publik)
// ============================================================
Route::get('external/bmkg/gempa', [BmkgProxyController::class, 'gempa'])->name('api.external.bmkg.gempa');
Route::get('weather/forecast', [WeatherForecastController::class, 'forecast'])->name('api.weather.forecast');
Route::get('public/incident/{id}/detail', [PublicIncidentDetailController::class, 'show'])->name('api.public.incident.detail');

// ============================================================
// API — Weather Intelligence (Cache-Driven)
// ============================================================
Route::prefix('internal/weather')->name('api.internal.weather.')->group(function () {
    Route::get('current', [InternalWeatherController::class, 'current'])->name('current');
    Route::get('hourly', [InternalWeatherController::class, 'hourly'])->name('hourly');
    Route::get('daily', [InternalWeatherController::class, 'daily'])->name('daily');
    Route::get('risk', [InternalWeatherController::class, 'risk'])->name('risk');
    Route::get('summary', [InternalWeatherController::class, 'summary'])->name('summary');
});

// ============================================================
// API — Device Token Refresh (Authenticated)
// ============================================================
Route::post('v1/device/refresh-token', [DeviceAuthController::class, 'refreshToken'])->name('api.v1.device.refresh-token')->middleware('auth:sanctum');

// ============================================================
// API — Autentikasi Klien (Mobile/Third Party)
// ============================================================
Route::prefix('auth')->name('api.auth.')->group(function () {
    Route::post('register/{jenis}', [AuthenticationApiController::class, 'register'])->name('register');
    Route::post('login', [AuthenticationApiController::class, 'login'])->name('login');
});

// ============================================================
// API — Laporan Kejadian (PUBLIK — submit laporan tanpa auth)
// ============================================================
Route::post('laporan', [LaporanKejadianApiController::class, 'store'])->name('api.laporan.store');
Route::get('laporan/peta', [LaporanKejadianApiController::class, 'peta'])->name('api.laporan.peta');
Route::post('lapor', [LaporanKejadianApiController::class, 'store'])->name('api.lapor.store');
Route::get('laporan/{kode_kejadian}/tracking', [LaporanKejadianApiController::class, 'tracking'])->name('api.laporan.tracking');

// ============================================================
// API — Master Version & Delta (PUBLIK — untuk Flutter offline-first)
// ============================================================
Route::prefix('master')->name('api.master.')->group(function () {
    Route::get('version', [MasterVersionController::class, 'version'])->name('version');
    Route::get('delta', [MasterVersionController::class, 'delta'])->name('delta');

    // Deprecated: akan dihapus bertahap setelah Flutter migrasi ke lokal
    // DEPRECATED: akan dihapus setelah Flutter migrasi ke lokal
    Route::get('jabatan', [MasterJabatanController::class, 'index'])->name('jabatan.index');
    Route::get('jabatan/{jabatan}', [MasterJabatanController::class, 'show'])->name('jabatan.show');
    Route::get('klaster', [MasterKlasterController::class, 'index'])->name('klaster.index');
    Route::get('klaster/{klaster}', [MasterKlasterController::class, 'show'])->name('klaster.show');
    Route::get('surat-jenis', [MasterSuratJenisController::class, 'index'])->name('surat-jenis.index');
    Route::get('surat-jenis/{suratJenis}', [MasterSuratJenisController::class, 'show'])->name('surat-jenis.show');
    Route::get('surat-template', [MasterSuratTemplateController::class, 'index'])->name('surat-template.index');
    Route::get('surat-template/{suratTemplate}', [MasterSuratTemplateController::class, 'show'])->name('surat-template.show');
    Route::get('jenis-bencana', [BencanaMasterJenisController::class, 'index'])->name('jenis-bencana.index');
    Route::get('jenis-bencana/{bencana}', [BencanaMasterJenisController::class, 'show'])->name('jenis-bencana.show');
    Route::get('jabatan-ttd', [MasterJabatanPenandatanganController::class, 'index'])->name('jabatan-ttd.index');
    Route::get('jabatan-ttd/{jabatanTtd}', [MasterJabatanPenandatanganController::class, 'show'])->name('jabatan-ttd.show');
    Route::get('sertifikasi', [MasterSertifikasiController::class, 'index'])->name('sertifikasi.index');
    Route::get('sertifikasi/{sertifikasi}', [MasterSertifikasiController::class, 'show'])->name('sertifikasi.show');
});

// ============================================================
// API — Keahlian (PUBLIK — untuk form registrasi)
// ============================================================
Route::get('keahlian', function () {
    return response()->json(['data' => AuthKeahlianMaster::all(['id_keahlian', 'nama_keahlian', 'deskripsi'])]);
})->name('api.keahlian.index');

// ============================================================
// API — SQL Views (PUBLIK — read-only aggregated data)
// ============================================================
Route::prefix('views')->name('api.views.')->group(function () {
    Route::get('command-center-summary', [SqlViewController::class, 'commandCenterSummary'])->name('command-center-summary');
    Route::get('incident-timeline/{id}', [SqlViewController::class, 'incidentTimeline'])->name('incident-timeline');
    Route::get('alert-insiden-baru', [SqlViewController::class, 'alertInsiden'])->name('alert-insiden');
    Route::get('blank-spot', [SqlViewController::class, 'blankSpot'])->name('blank-spot');
    Route::get('logistik-audit', [SqlViewController::class, 'logistikAudit'])->name('logistik-audit');
    Route::get('aset-siap-pakai', [SqlViewController::class, 'asetSiapPakai'])->name('aset-siap-pakai');
    Route::get('aset-operasional-ready', [SqlViewController::class, 'asetOperasionalReady'])->name('aset-operasional-ready');
    Route::get('relawan-domisili-check', [SqlViewController::class, 'relawanDomisiliCheck'])->name('relawan-domisili-check');
    Route::get('user-access-control', [SqlViewController::class, 'userAccessControl'])->name('user-access-control');
    Route::get('surat-orphans', [SqlViewController::class, 'suratOrphans'])->name('surat-orphans');
});

// ============================================================
// P4 Profile Command Center API (Hybrid/Optional Auth)
// ============================================================
Route::prefix('profile')->group(function () {
    Route::get('/', [\App\Http\Controllers\Api\ProfileApiController::class, 'index'])->name('api.profile.index');
});

// ============================================================
// P5 Account & Dashboard BFF (Phase 2.3 — Dynamic Account & Command Center)
// ============================================================
Route::prefix('account')->group(function () {
    Route::get('home', [\App\Http\Controllers\Api\AccountHomeController::class, 'index'])->name('api.account.home');
});

Route::middleware('auth:sanctum')->delete('account', [\App\Http\Controllers\Api\AccountController::class, 'destroy'])
    ->name('api.account.destroy');
Route::prefix('dashboard')->group(function () {
    Route::get('home', [\App\Http\Controllers\Api\DashboardHomeController::class, 'index'])->name('api.dashboard.home');
});

// ============================================================
// API — Terautentikasi (Sanctum)
// ============================================================
Route::middleware(['auth:sanctum', 'role:super_admin,pwnu,pcnu,relawan,trc'])->group(function () {

    // Operasi
    Route::prefix('operasi')->name('api.operasi.')->group(function () {
        Route::apiResource('posaju', OperasiPosajuController::class)->except(['destroy']);
        Route::get('insiden/{insiden:uuid_insiden}/posaju/aktif', [OperasiPosajuController::class, 'activeByInsiden'])->name('posaju.active-by-insiden');
        Route::post('posaju/{posaju}/activate', [OperasiPosajuController::class, 'activate'])->name('posaju.activate');
        Route::post('posaju/{posaju}/extend', [OperasiPosajuController::class, 'extend'])->name('posaju.extend');
        Route::post('posaju/{posaju}/close', [OperasiPosajuController::class, 'close'])->name('posaju.close');

        Route::apiResource('distribusi', DistribusiApiController::class)->except(['destroy']);
        Route::post('distribusi/{distribusi}/feedback', [DistribusiApiController::class, 'feedback'])->name('distribusi.feedback');

        Route::apiResource('klaster', OperasiKlasterController::class)->except(['destroy']);
        Route::post('klaster/{klaster}/progress', [OperasiKlasterController::class, 'updateProgress'])->name('klaster.progress');
        Route::post('klaster/{klaster}/complete', [OperasiKlasterController::class, 'complete'])->name('klaster.complete');

        Route::apiResource('tugas', OperasiTugasController::class)->except(['destroy'])->parameters(['tugas' => 'tugas']);
        Route::post('tugas/{tugas}/start', [OperasiTugasController::class, 'start'])->name('tugas.start');
        Route::post('tugas/{tugas}/pause', [OperasiTugasController::class, 'pause'])->name('tugas.pause');
        Route::post('tugas/{tugas}/complete', [OperasiTugasController::class, 'complete'])->name('tugas.complete');
    });

    // API v1
    Route::prefix('v1')->name('api.v1.')->group(function () {
        Route::post('sync', [SyncApiController::class, 'sync'])->name('sync');
        Route::get('sync/state', [SyncApiController::class, 'state'])->name('sync.state');
        Route::get('sync/status', [SyncApiController::class, 'status'])->name('sync.status');
        Route::get('sync/metrics', [SyncApiController::class, 'metrics'])->name('sync.metrics');
        Route::post('bootstrap', [SyncApiController::class, 'bootstrap'])->name('bootstrap');
        Route::get('snapshot-download', [SyncApiController::class, 'downloadSnapshot'])->name('sync.snapshot-download');

        // Action payload handler for SDUI scenes
        Route::post('action', [\App\Http\Controllers\Api\SduiActionController::class, 'handle'])->name('action');

        // Profil — toggle availability (reference implementation)
        Route::post('profil/toggle-tersedia', [\App\Http\Controllers\Api\ProfilController::class, 'toggleTersedia'])->name('profil.toggle-tersedia');
        
        // Mobile App Auth
        Route::get('auth/user', [\App\Http\Controllers\Api\Auth\AuthenticationApiController::class, 'me'])->name('auth.user');
        Route::post('auth/logout', [\App\Http\Controllers\Api\Auth\AuthenticationApiController::class, 'logout'])->name('auth.logout');

        // Device & Token Lifecycle
        Route::prefix('devices')->name('devices.')->controller(DeviceApiController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::delete('{uuid}', 'destroy')->name('destroy');
            Route::post('logout-all', 'logoutAll')->name('logout-all');
        });

        // Assessment
        Route::get('assessment', [App\Http\Controllers\Api\Operasi\AssessmentApiController::class, 'index'])->name('assessment.index');
        Route::post('assessment', [App\Http\Controllers\Api\Operasi\AssessmentApiController::class, 'store'])->name('assessment.store');
        Route::get('assessment/{assessment}', [App\Http\Controllers\Api\Operasi\AssessmentApiController::class, 'show'])->name('assessment.show');
        Route::put('assessment/{assessment}', [App\Http\Controllers\Api\Operasi\AssessmentApiController::class, 'update'])->name('assessment.update');
        Route::delete('assessment/{assessment}', [App\Http\Controllers\Api\Operasi\AssessmentApiController::class, 'destroy'])->name('assessment.destroy');
        Route::get('assessment/{assessment}/pdf', [App\Http\Controllers\Api\Operasi\AssessmentApiController::class, 'downloadPdf'])->name('assessment.pdf');
        // Assessment — nested children
        Route::get('assessment/{assessment}/dampak', [App\Http\Controllers\Api\Operasi\AssessmentApiController::class, 'dampakIndex'])->name('assessment.dampak.index');
        Route::post('assessment/{assessment}/dampak', [App\Http\Controllers\Api\Operasi\AssessmentApiController::class, 'dampakStore'])->name('assessment.dampak.store');
        Route::put('assessment/{assessment}/dampak/{dampak}', [App\Http\Controllers\Api\Operasi\AssessmentApiController::class, 'dampakUpdate'])->name('assessment.dampak.update');
        Route::delete('assessment/{assessment}/dampak/{dampak}', [App\Http\Controllers\Api\Operasi\AssessmentApiController::class, 'dampakDestroy'])->name('assessment.dampak.destroy');
        Route::get('assessment/{assessment}/kebutuhan', [App\Http\Controllers\Api\Operasi\AssessmentApiController::class, 'kebutuhanIndex'])->name('assessment.kebutuhan.index');
        Route::post('assessment/{assessment}/kebutuhan', [App\Http\Controllers\Api\Operasi\AssessmentApiController::class, 'kebutuhanStore'])->name('assessment.kebutuhan.store');
        Route::put('assessment/{assessment}/kebutuhan/{kebutuhan}', [App\Http\Controllers\Api\Operasi\AssessmentApiController::class, 'kebutuhanUpdate'])->name('assessment.kebutuhan.update');
        Route::delete('assessment/{assessment}/kebutuhan/{kebutuhan}', [App\Http\Controllers\Api\Operasi\AssessmentApiController::class, 'kebutuhanDestroy'])->name('assessment.kebutuhan.destroy');

        // Sitrep
        Route::get('sitrep', [SitrepApiController::class, 'index'])->name('sitrep.index');
        Route::post('sitrep', [SitrepApiController::class, 'store'])->name('sitrep.store');
        Route::get('sitrep/{sitrep}', [SitrepApiController::class, 'show'])->name('sitrep.show');
        Route::put('sitrep/{sitrep}', [SitrepApiController::class, 'update'])->name('sitrep.update');
        Route::delete('sitrep/{sitrep}', [SitrepApiController::class, 'destroy'])->name('sitrep.destroy');
        // Sitrep — nested children
        Route::get('sitrep/{sitrep}/dampak', [SitrepApiController::class, 'dampakIndex'])->name('sitrep.dampak.index');
        Route::post('sitrep/{sitrep}/dampak', [SitrepApiController::class, 'dampakStore'])->name('sitrep.dampak.store');
        Route::put('sitrep/{sitrep}/dampak/{dampak}', [SitrepApiController::class, 'dampakUpdate'])->name('sitrep.dampak.update');
        Route::delete('sitrep/{sitrep}/dampak/{dampak}', [SitrepApiController::class, 'dampakDestroy'])->name('sitrep.dampak.destroy');
        Route::get('sitrep/{sitrep}/kebutuhan', [SitrepApiController::class, 'kebutuhanIndex'])->name('sitrep.kebutuhan.index');
        Route::post('sitrep/{sitrep}/kebutuhan', [SitrepApiController::class, 'kebutuhanStore'])->name('sitrep.kebutuhan.store');
        Route::put('sitrep/{sitrep}/kebutuhan/{kebutuhan}', [SitrepApiController::class, 'kebutuhanUpdate'])->name('sitrep.kebutuhan.update');
        Route::delete('sitrep/{sitrep}/kebutuhan/{kebutuhan}', [SitrepApiController::class, 'kebutuhanDestroy'])->name('sitrep.kebutuhan.destroy');

        // Insiden — CRUD lengkap + status lifecycle
        Route::get('insiden', [InsidenFullController::class, 'index'])->name('insiden.index');
        Route::post('insiden', [InsidenFullController::class, 'store'])->name('insiden.store');
        Route::get('insiden/{insiden:uuid_insiden}', [InsidenFullController::class, 'show'])->name('insiden.show');
        Route::put('insiden/{insiden:uuid_insiden}', [InsidenFullController::class, 'update'])->name('insiden.update');
        Route::delete('insiden/{insiden:uuid_insiden}', [InsidenFullController::class, 'destroy'])->name('insiden.destroy');
        Route::patch('insiden/{insiden:uuid_insiden}/status', [InsidenFullController::class, 'ubahStatus'])->name('insiden.ubah-status');
        Route::post('insiden/{insiden:uuid_insiden}/lock', [InsidenFullController::class, 'lock'])->name('insiden.lock');
        Route::post('insiden/{insiden:uuid_insiden}/unlock', [InsidenFullController::class, 'unlock'])->name('insiden.unlock');
        Route::get('insiden/{insiden:uuid_insiden}/spk/pdf', [InsidenFullController::class, 'downloadSpkPdf'])->name('insiden.spk.pdf');

        // Eskalasi
        Route::get('eskalasi', [EskalasiApiController::class, 'index'])->name('eskalasi.index');
        Route::post('eskalasi', [EskalasiApiController::class, 'store'])->name('eskalasi.store');
        Route::get('eskalasi/{eskalasi}', [EskalasiApiController::class, 'show'])->name('eskalasi.show');
        Route::delete('eskalasi/{eskalasi}', [EskalasiApiController::class, 'destroy'])->name('eskalasi.destroy');

        // Aktivasi
        Route::get('aktivasi', [AktivasiApiController::class, 'index'])->name('aktivasi.index');
        Route::post('aktivasi', [AktivasiApiController::class, 'store'])->name('aktivasi.store');
        Route::get('aktivasi/{aktivasi}', [AktivasiApiController::class, 'show'])->name('aktivasi.show');
        Route::put('aktivasi/{aktivasi}', [AktivasiApiController::class, 'update'])->name('aktivasi.update');
        Route::post('aktivasi/{aktivasi}/selesai', [AktivasiApiController::class, 'selesai'])->name('aktivasi.selesai');
        Route::delete('aktivasi/{aktivasi}', [AktivasiApiController::class, 'destroy'])->name('aktivasi.destroy');

        // Penugasan
        Route::get('penugasan', [PenugasanApiController::class, 'index'])->name('penugasan.index');
        Route::post('penugasan/bulk', [PenugasanApiController::class, 'bulk'])->name('penugasan.bulk');
        Route::post('logistik/bulk', [BulkStubController::class, 'logistikBulk'])->name('logistik.bulk');
        Route::post('mobilisasi/bulk', [BulkStubController::class, 'mobilisasiBulk'])->name('mobilisasi.bulk');

        Route::apiResource('mobilisasi', MobilisasiApiController::class)
            ->parameters(['mobilisasi' => 'uuid'])
            ->names('mobilisasi');

        Route::post('mobilisasi/{uuid}/approve', [MobilisasiApiController::class, 'approve'])->name('mobilisasi.approve');
        Route::post('mobilisasi/{uuid}/depart', [MobilisasiApiController::class, 'depart'])->name('mobilisasi.depart');
        Route::post('mobilisasi/{uuid}/arrive', [MobilisasiApiController::class, 'arrive'])->name('mobilisasi.arrive');
        Route::post('mobilisasi/{uuid}/finish', [MobilisasiApiController::class, 'finish'])->name('mobilisasi.finish');
        Route::post('mobilisasi/{uuid}/cancel', [MobilisasiApiController::class, 'cancel'])->name('mobilisasi.cancel');

        Route::get('penugasan/{uuid}', [PenugasanApiController::class, 'show'])->name('penugasan.show');
        Route::post('penugasan', [PenugasanApiController::class, 'store'])->name('penugasan.store');
        Route::patch('penugasan/{uuid}', [PenugasanApiController::class, 'updateStatus'])->name('penugasan.update');
        Route::patch('penugasan/{uuid}/status', [PenugasanApiController::class, 'updateStatus'])->name('penugasan.status');
        Route::delete('penugasan/{uuid}', [PenugasanApiController::class, 'destroy'])->name('penugasan.destroy');

        Route::get('klaster', [KlasterApiController::class, 'index'])->name('klaster.index');
        Route::post('klaster', [KlasterApiController::class, 'store'])->name('klaster.store');
        Route::get('klaster/{uuid}', [KlasterApiController::class, 'show'])->name('klaster.show');
        Route::patch('klaster/{uuid}', [KlasterApiController::class, 'update'])->name('klaster.update');
        Route::delete('klaster/{uuid}', [KlasterApiController::class, 'destroy'])->name('klaster.destroy');

        // Governance — Pleno
        Route::get('insiden/{insiden:uuid_insiden}/pleno', [PlanoApiController::class, 'index'])->name('insiden.pleno.index');
        Route::post('insiden/{insiden:uuid_insiden}/pleno', [PlanoApiController::class, 'store'])->name('insiden.pleno.store');
        Route::get('insiden/{insiden:uuid_insiden}/pleno/{pleno}', [PlanoApiController::class, 'show'])->name('insiden.pleno.show');
        Route::put('insiden/{insiden:uuid_insiden}/pleno/{pleno}', [PlanoApiController::class, 'update'])->name('insiden.pleno.update');
        Route::delete('insiden/{insiden:uuid_insiden}/pleno/{pleno}', [PlanoApiController::class, 'destroy'])->name('insiden.pleno.destroy');
        Route::post('insiden/{insiden:uuid_insiden}/pleno/{pleno}/finalisasi', [PlanoApiController::class, 'finalisasi'])->name('insiden.pleno.finalisasi');
        Route::post('insiden/{insiden:uuid_insiden}/pleno/{pleno}/keputusan', [PlanoApiController::class, 'tambahKeputusan'])->name('insiden.pleno.keputusan.store');
        Route::delete('insiden/{insiden:uuid_insiden}/pleno/{pleno}/keputusan/{keputusan}', [PlanoApiController::class, 'hapusKeputusan'])->name('insiden.pleno.keputusan.destroy');
        Route::post('insiden/{insiden:uuid_insiden}/pleno/{pleno}/peserta', [PlanoApiController::class, 'tambahPeserta'])->name('insiden.pleno.peserta.store');
        Route::delete('insiden/{insiden:uuid_insiden}/pleno/{pleno}/peserta/{peserta}', [PlanoApiController::class, 'hapusPeserta'])->name('insiden.pleno.peserta.destroy');
        Route::get('insiden/{insiden:uuid_insiden}/pleno/{pleno}/pdf', [PlanoApiController::class, 'downloadPdf'])->name('insiden.pleno.pdf');

        // Governance — Surat
        Route::get('surat', [SuratApiController::class, 'index'])->name('surat.index');
        Route::post('surat', [SuratApiController::class, 'store'])->name('surat.store');
        Route::get('surat/{surat}', [SuratApiController::class, 'show'])->name('surat.show');
        Route::put('surat/{surat}', [SuratApiController::class, 'update'])->name('surat.update');
        Route::delete('surat/{surat}', [SuratApiController::class, 'destroy'])->name('surat.destroy');
        Route::post('surat/{surat}/ajukan-paraf', [SuratApiController::class, 'ajukanParaf'])->name('surat.ajukan-paraf');
        Route::patch('surat/paraf/{paraf}', [SuratApiController::class, 'parafAction'])->name('surat.paraf.action');
        Route::post('surat/{surat}/finalisasi', [SuratApiController::class, 'finalisasi'])->name('surat.finalisasi');
        // Surat — Tembusan
        Route::get('surat/{surat}/tembusan', [SuratApiController::class, 'tembusanIndex'])->name('surat.tembusan.index');
        Route::post('surat/{surat}/tembusan', [SuratApiController::class, 'tembusanStore'])->name('surat.tembusan.store');
        Route::delete('surat/{surat}/tembusan/{tembusan}', [SuratApiController::class, 'tembusanDestroy'])->name('surat.tembusan.destroy');
    });

    // Logistik
    Route::prefix('logistik')->name('api.logistik.')->group(function () {
        Route::apiResource('kategori', LogistikKategoriController::class);
        Route::apiResource('katalog', LogistikKatalogController::class);

        Route::post('mutasi', [LogistikMutasiController::class, 'store'])->name('mutasi.store');

        Route::get('stok', [LogistikStokController::class, 'index'])->name('stok.index');
        Route::get('stok/{id}', [LogistikStokController::class, 'show'])->name('stok.show');
        Route::post('stok/{id}/koreksi', [LogistikStokController::class, 'koreksi'])->name('stok.koreksi');
        Route::get('histori', [LogistikStokController::class, 'history'])->name('stok.history');
        Route::get('summary', [InsidenApiController::class, 'summary'])->name('summary');

        // Gudang
        Route::apiResource('gudang', LogistikGudangController::class)->except(['edit', 'create']);

        // Permintaan
        Route::get('permintaan', [LogistikPermintaanController::class, 'index'])->name('permintaan.index');
        Route::post('permintaan', [LogistikPermintaanController::class, 'store'])->name('permintaan.store');
        Route::get('permintaan/{permintaan}', [LogistikPermintaanController::class, 'show'])->name('permintaan.show');
        Route::patch('permintaan/{permintaan}/proses', [LogistikPermintaanController::class, 'proses'])->name('permintaan.proses');
    });

    // Laporan Kejadian (management — validasi, eskalasi)
    // Map Layers Universal Engine    // M1.8 Auth & Mandate API
    Route::prefix('auth')->group(function () {
        Route::post('mandate', [\App\Http\Controllers\Api\AuthApiController::class, 'selectMandate'])->name('api.auth.mandate');
        Route::post('pin/set', [\App\Http\Controllers\Api\AuthApiController::class, 'setPin'])->name('api.auth.pin.set');
        Route::post('pin/verify', [\App\Http\Controllers\Api\AuthApiController::class, 'verifyPin'])->name('api.auth.pin.verify')->middleware('throttle:5,1');
    });



    // M5 Executive Governance Dashboard API (MOCK)
    Route::prefix('governance')->group(function () {
        Route::post('process', [\App\Http\Controllers\Api\MockMobileApiController::class, 'processDecision'])->name('api.governance.process');
    });

    // M6 Backend-For-Frontend (BFF) & Server-Driven UI (Phase 2.2B)
    Route::prefix('bff')->group(function () {
        Route::get('dashboard', [\App\Http\Controllers\Api\Bff\DashboardBffController::class, 'index'])->name('api.bff.dashboard');
    });

    // M5.5 Decision Package API
    Route::get('governance/decision-packages/{id}', [\App\Http\Controllers\Api\DecisionPackageController::class, 'show'])->name('api.governance.decision_packages.show');

    // P4 Profile Command Center API dipindahkan ke area Hybrid Auth di atas

    // M1 Public Lapor & Laporan API
    Route::get('laporan', [LaporanKejadianApiController::class, 'index'])->name('api.laporan.index');
    Route::get('laporan/{laporan}', [LaporanKejadianApiController::class, 'show'])->name('api.laporan.show');
    Route::patch('laporan/{laporan}/validasi', [LaporanKejadianApiController::class, 'validasi'])->name('api.laporan.validasi');
    Route::post('laporan/{laporan}/eskalasi-insiden', [LaporanKejadianApiController::class, 'eskalasiInsiden'])->name('api.laporan.eskalasi');

    // Admin — Manajemen Pengguna
    Route::get('admin/pengguna', [PenggunaApiController::class, 'index'])->middleware('role:super_admin,pwnu,pcnu,trc,relawan')->name('api.admin.pengguna.index');

    Route::prefix('admin')->name('api.admin.')->middleware('role:super_admin,pwnu')->group(function () {
        Route::get('pengguna/menunggu', [PenggunaApiController::class, 'menunggu'])->name('pengguna.menunggu');
        Route::get('pengguna/{pengguna}', [PenggunaApiController::class, 'show'])->name('pengguna.show');
        Route::put('pengguna/{pengguna}', [PenggunaApiController::class, 'update'])->name('pengguna.update');
        Route::patch('pengguna/{pengguna}/setujui', [PenggunaApiController::class, 'setujui'])->name('pengguna.setujui');
        Route::patch('pengguna/{pengguna}/tolak', [PenggunaApiController::class, 'tolak'])->name('pengguna.tolak');

        // PenggunaJabatan — mapping user ke jabatan dengan scope
        Route::apiResource('pengguna-jabatan', PenggunaJabatanController::class)->except(['edit', 'create']);
        Route::post('pengguna-jabatan/{pengguna_jabatan}/aktifkan', [PenggunaJabatanController::class, 'activate'])->name('pengguna-jabatan.aktifkan');
        Route::post('pengguna-jabatan/{pengguna_jabatan}/nonaktifkan', [PenggunaJabatanController::class, 'deactivate'])->name('pengguna-jabatan.nonaktifkan');

        // Role Application — pengajuan peran
        Route::get('role-applications', [RoleApplicationController::class, 'index'])->name('role-applications.index');
        Route::get('role-applications/{roleApplication}', [RoleApplicationController::class, 'show'])->name('role-applications.show');
        Route::post('role-applications/{roleApplication}/setujui', [RoleApplicationController::class, 'approve'])->name('role-applications.setujui');
        Route::post('role-applications/{roleApplication}/tolak', [RoleApplicationController::class, 'reject'])->name('role-applications.tolak');
    });

    // Organisasi (Legacy)
    Route::prefix('organisasi')->name('api.organisasi.')->group(function () {
        Route::get('pcnu', [OrganisasiApiController::class, 'pcnu'])->name('pcnu');
        Route::get('pcnu/{pcnu}', [OrganisasiApiController::class, 'pcnuDetail'])->name('pcnu.detail');
        Route::get('pcnu/{pcnu}/mwc', [OrganisasiApiController::class, 'pcnuMwc'])->name('pcnu.mwc');
        Route::get('mwc/{mwc}/ranting', [OrganisasiApiController::class, 'mwcRanting'])->name('mwc.ranting');
        Route::get('unit', [OrganisasiApiController::class, 'unit'])->name('unit');

        // Legacy CRUD
        Route::apiResource('sk', OrganisasiSkController::class)->except(['edit', 'create']);
        Route::get('sk/{sk}/pengurus', [OrganisasiSkController::class, 'pengurus'])->name('sk.pengurus');
        Route::post('sk/{sk}/pengurus', [OrganisasiSkController::class, 'tambahPengurus'])->name('sk.pengurus.store');
        Route::delete('sk/{sk}/pengurus/{pengurus}', [OrganisasiSkController::class, 'hapusPengurus'])->name('sk.pengurus.destroy');
        Route::apiResource('jabatan', OrganisasiJabatanController::class)->except(['edit', 'create']);
        Route::apiResource('mandat', OrganisasiMandatController::class)->except(['edit', 'create']);
        Route::apiResource('delegasi', OrganisasiDelegasiController::class)->except(['edit', 'create']);
    });

    // Aset
    Route::prefix('aset')->name('api.aset.')->group(function () {
        Route::get('/', [AsetApiController::class, 'index'])->name('index');
        Route::post('/', [AsetApiController::class, 'store'])->name('store');
        Route::get('tersedia', [AsetApiController::class, 'tersedia'])->name('tersedia');
        Route::get('{aset}', [AsetApiController::class, 'show'])->name('show');
        Route::put('{aset}', [AsetApiController::class, 'update'])->name('update');
        Route::patch('{aset}/status', [AsetApiController::class, 'updateStatus'])->name('status');
        Route::delete('{aset}', [AsetApiController::class, 'destroy'])->name('destroy');
    });

    // Command Center API
    Route::prefix('command-center')->name('api.command-center.')->group(function () {
        Route::get('insiden-aktif', [CommandCenterApiController::class, 'insidenAktif'])->name('insiden-aktif');
        Route::get('statistik', [CommandCenterApiController::class, 'statistik'])->name('statistik');
        Route::get('stok-kritis', [CommandCenterApiController::class, 'stokKritis'])->name('stok-kritis');
        Route::get('jurnal-terbaru', [CommandCenterApiController::class, 'jurnalTerbaru'])->name('jurnal-terbaru');
    });

    // Relawan
    Route::prefix('relawan')->name('api.relawan.')->group(function () {
        Route::post('pendaftaran/{pendaftaran}/approve', [RelawanPendaftaranController::class, 'approve'])->name('pendaftaran.approve');
        Route::post('pendaftaran/{pendaftaran}/reject', [RelawanPendaftaranController::class, 'reject'])->name('pendaftaran.reject');
        Route::post('pendaftaran/{pendaftaran}/assign', [RelawanPendaftaranController::class, 'assign'])->name('pendaftaran.assign');
        Route::post('penugasan/{penugasan}/complete', [RelawanPenugasanController::class, 'complete'])->name('penugasan.complete');

        Route::get('profil/{profil}', [RelawanProfilController::class, 'show'])->name('profil.show');
        Route::put('profil/{profil}', [RelawanProfilController::class, 'update'])->name('profil.update');
        Route::post('profil/{profil}/keahlian', [RelawanProfilController::class, 'syncSkills'])->name('profil.sync_skills');
        Route::get('available', [VolunteerApiController::class, 'getAvailableVolunteers'])->name('available');

        // Relawan kebutuhan, sertifikasi, shift
        Route::apiResource('kebutuhan', RelawanKebutuhanController::class)->except(['edit', 'create']);
        Route::get('sertifikasi', [RelawanSertifikasiController::class, 'index'])->name('sertifikasi.index');
        Route::post('sertifikasi', [RelawanSertifikasiController::class, 'store'])->name('sertifikasi.store');
        Route::delete('sertifikasi', [RelawanSertifikasiController::class, 'destroy'])->name('sertifikasi.destroy');
        Route::apiResource('shift', RelawanShiftController::class)->except(['edit', 'create']);
    });

    // Governance — Org* baru
    Route::prefix('governance')->name('api.governance.')->group(function () {
        Route::apiResource('structure-levels', OrgStructureLevelController::class)->except(['edit', 'create', 'show']);
        Route::apiResource('institutions', OrgInstitutionController::class)->except(['edit', 'create', 'show']);
        Route::apiResource('nodes', OrgNodeController::class)->except(['edit', 'create']);
        Route::apiResource('positions', OrgPositionController::class)->except(['edit', 'create', 'show']);
        Route::apiResource('sks', OrgSkController::class)->except(['edit', 'create']);
        Route::apiResource('mandates', OrgMandateController::class)->except(['edit', 'create']);
        Route::apiResource('delegations', OrgDelegationController::class)->except(['edit', 'create']);

        // Functions & Authorities
        Route::apiResource('functions', OrgFunctionController::class)->except(['edit', 'create']);
        Route::apiResource('authorities', OrgAuthorityController::class)->except(['edit', 'create']);

        // Pivot assignments
        Route::post('node-positions', [OrgNodeController::class, 'assignPosition'])->name('node-positions.assign');
        Route::delete('node-positions/{nodePosition}', [OrgNodeController::class, 'removePosition'])->name('node-positions.remove');
        Route::post('position-functions', [OrgPositionController::class, 'assignFunction'])->name('position-functions.assign');
        Route::delete('position-functions/{positionFunction}', [OrgPositionController::class, 'removeFunction'])->name('position-functions.remove');
        Route::post('function-authorities', [OrgFunctionController::class, 'assignAuthority'])->name('function-authorities.assign');
        Route::delete('function-authorities/{functionAuthority}', [OrgFunctionController::class, 'removeAuthority'])->name('function-authorities.remove');

        // Audit log
        Route::get('audit-logs', [GovAuditLogController::class, 'index'])->name('audit-logs.index');
    });

    // Organisasi Audit Log (Legacy)
    Route::get('organisasi/audit-log', [OrganisasiAuditLogController::class, 'index'])->name('api.organisasi.audit-log.index');

    // Operasi Penugasan History
    Route::get('v1/penugasan/{uuid}/history', [PenugasanApiController::class, 'history'])->name('api.v1.penugasan.history');

    // Operasi Metrics Daily
    Route::get('admin/metrics', [MetricsController::class, 'index'])->name('api.admin.metrics.index');

    // ============================================================
    // 3 NEW DOMAINS (Assessment Extended, Inventaris, Histori)
    // ============================================================

    // Assessment Extension
    Route::post('insiden/{insiden:uuid_insiden}/assessment', [AssessmentApiController::class, 'store']);
    Route::get('insiden/{insiden:uuid_insiden}/assessment/{assessment}', [AssessmentApiController::class, 'show']);
    Route::put('insiden/{insiden:uuid_insiden}/assessment/{assessment}', [AssessmentApiController::class, 'update']);
    Route::get('master/kebutuhan-numerik', [AssessmentApiController::class, 'masterKebutuhanNumerik']);

    Route::prefix('insiden/{insiden:uuid_insiden}/assessment/{assessment}')->group(function () {
        Route::get('lengkap', [AssessmentLengkapController::class, 'show']);
        Route::post('biodata', [AssessmentBiodataController::class, 'store']);
        Route::post('narasi', [AssessmentNarasiController::class, 'store']);
        Route::put('dampak-manusia', [AssessmentDampakManusiaController::class, 'update']);
        Route::put('dampak-infrastruktur', [AssessmentDampakInfraController::class, 'update']);
        Route::put('dampak-lingkungan', [AssessmentDampakLingController::class, 'update']);
        Route::put('dampak-ekonomi', [AssessmentDampakEkoController::class, 'update']);
        Route::post('skor/{indikator}', [AssessmentSkorController::class, 'store']);
        Route::get('ringkasan-skor', [AssessmentSkorController::class, 'ringkasan']);
        Route::post('hitung-skor', [AssessmentSkorController::class, 'hitung']);
        Route::post('submit', [AssessmentApiController::class, 'submit'])->name('api.insiden.assessment.submit');
        Route::post('review', [AssessmentApiController::class, 'review'])->name('api.insiden.assessment.review');
    });
    Route::get('master/indikator-skor', [MasterIndikatorSkorController::class, 'index']);

    // Inventaris PWNU
    Route::prefix('inventaris')->group(function () {
        Route::apiResource('kategori', InventarisKategoriController::class)->only(['index', 'show']);
        Route::apiResource('jenis', InventarisJenisController::class)->only(['index', 'show']);
        Route::apiResource('aset', InventarisAsetController::class);
        Route::get('aset/{aset}/dokumen-kadaluwarsa', [InventarisAsetController::class, 'dokumenKadaluwarsa']);
        Route::get('aset/{aset}/riwayat-kondisi', [InventarisKondisiLogController::class, 'index']);
        Route::post('aset/{aset}/kondisi', [InventarisKondisiLogController::class, 'store']);
        Route::apiResource('aset.pemeliharaan', InventarisPemeliharaanController::class);
        Route::apiResource('aset.dokumen', InventarisDokumenController::class);
        Route::post('aset/{aset}/deploy', [InventarisDeploymentController::class, 'store']);
        Route::patch('deployment/{dep}/kembali', [InventarisDeploymentController::class, 'kembali']);
        Route::get('statistik', [InventarisAsetController::class, 'statistik']);
        Route::get('perlu-maintenance', [InventarisPemeliharaanController::class, 'jadwalTerdekat']);
        Route::get('dokumen-jatuh-tempo', [InventarisDokumenController::class, 'jatuhTempo']);
    });

    // History & Trend
    Route::prefix('histori')->group(function () {
        Route::apiResource('bencana', HistoriBencanaController::class);
        Route::patch('bencana/{id}/verifikasi', [HistoriBencanaController::class, 'verifikasi']);
        Route::get('analisis-musiman/{kab}/{jenis}', [HistoriAnalisisController::class, 'show']);
        Route::post('hitung-analisis-musiman', [HistoriAnalisisController::class, 'hitung']);
        Route::get('probabilitas/{kab}/{jenis}', [HistoriProbabilitasController::class, 'index']);
        Route::get('peta-risiko/{kab}', [HistoriPetaRisikoController::class, 'show']);
        Route::get('trend-tahunan/{kab}', [HistoriAnalisisController::class, 'trendTahunan']);
        Route::get('kalender-bencana/{kab}', [HistoriAnalisisController::class, 'kalenderMusiman']);
        Route::get('indikator-risiko/{kab}', [HistoriIndikatorController::class, 'index']);
        Route::put('indikator-risiko/{kab}', [HistoriIndikatorController::class, 'update']);
        Route::get('perbandingan-antar-wilayah', [HistoriAnalisisController::class, 'perbandinganWilayah']);
    });

});

// Relawan pendaftaran — tetap publik (registrasi awal)
Route::post('relawan/daftar', [RelawanPendaftaranController::class, 'daftar'])->name('api.relawan.daftar');

// Role Application — perlu auth
Route::post('role-applications', [RoleApplicationController::class, 'store'])->name('api.role-applications.store')->middleware('auth:sanctum');

// Generic Media Stream Proxy (to bypass MinIO signature host mismatch for emulators)
Route::get('/stream-media', function (\Illuminate\Http\Request $request) {
    $path = $request->query('path');
    if (!$path) return abort(404);
    $disk = $request->query('disk', config('filesystems.default'));
    if (!\Illuminate\Support\Facades\Storage::disk($disk)->exists($path)) return abort(404);
    return \Illuminate\Support\Facades\Storage::disk($disk)->response($path);
})->name('stream-media');

// ============================================================
// API — Media (auth:sanctum)
// ============================================================
Route::middleware('auth:sanctum')->prefix('media')->name('api.media.')->group(function () {
    Route::post('/', [MediaController::class, 'store'])->name('upload');
    Route::get('{id}', [MediaController::class, 'show'])->name('show');
    Route::post('{id}/replace', [MediaController::class, 'replace'])->name('replace');
    Route::delete('{id}', [MediaController::class, 'destroy'])->name('delete');
});

// ============================================================
// API — Health Check (publik)
// ============================================================
Route::get('health', HealthCheckController::class)->name('api.health');
