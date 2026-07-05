<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\WilayahApiController;
use App\Http\Controllers\Api\Auth\DeviceAuthController;

// ============================================================
// API — Wilayah (Publik, untuk cascading dropdown)
// ============================================================
Route::prefix('wilayah')->name('api.wilayah.')->group(function () {
    Route::get('kabupaten', [WilayahApiController::class, 'kabupaten'])->name('kabupaten');
    Route::get('kecamatan', [WilayahApiController::class, 'kecamatan'])->name('kecamatan');
    Route::get('desa',      [WilayahApiController::class, 'desa'])->name('desa');
    Route::get('pcnu',      [WilayahApiController::class, 'pcnu'])->name('pcnu');
});

// ============================================================
// API — Public Dashboard
// ============================================================
Route::prefix('public')->name('api.public.')->group(function () {
    Route::get('dashboard', [\App\Http\Controllers\Api\PublicDashboardApiController::class, 'data'])->name('dashboard');
});

// ============================================================
// API — External Proxies (Publik)
// ============================================================
Route::get('external/bmkg/gempa', [\App\Http\Controllers\Api\BmkgProxyController::class, 'gempa'])->name('api.external.bmkg.gempa');
Route::get('weather/forecast', [\App\Http\Controllers\Api\WeatherForecastController::class, 'forecast'])->name('api.weather.forecast');
Route::get('public/incident/{id}/detail', [\App\Http\Controllers\Api\PublicIncidentDetailController::class, 'show'])->name('api.public.incident.detail');

// ============================================================
// API — Weather Intelligence (Cache-Driven)
// ============================================================
Route::prefix('internal/weather')->name('api.internal.weather.')->group(function () {
    Route::get('current', [\App\Http\Controllers\Api\InternalWeatherController::class, 'current'])->name('current');
    Route::get('hourly',  [\App\Http\Controllers\Api\InternalWeatherController::class, 'hourly'])->name('hourly');
    Route::get('daily',   [\App\Http\Controllers\Api\InternalWeatherController::class, 'daily'])->name('daily');
    Route::get('risk',    [\App\Http\Controllers\Api\InternalWeatherController::class, 'risk'])->name('risk');
    Route::get('summary', [\App\Http\Controllers\Api\InternalWeatherController::class, 'summary'])->name('summary');
});

// ============================================================
// API — Device Token Refresh (Publik — prasyarat autentikasi)
// ============================================================
Route::post('v1/device/refresh-token', [DeviceAuthController::class, 'refreshToken'])->name('api.v1.device.refresh-token');

// ============================================================
// API — Autentikasi Klien (Mobile/Third Party)
// ============================================================
Route::prefix('auth')->name('api.auth.')->group(function () {
    Route::post('register/{jenis}', [\App\Http\Controllers\Api\Auth\AuthenticationApiController::class, 'register'])->name('register');
    Route::post('login', [\App\Http\Controllers\Api\Auth\AuthenticationApiController::class, 'login'])->name('login');
    
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('me', [\App\Http\Controllers\Api\Auth\AuthenticationApiController::class, 'me'])->name('me');
        Route::post('logout', [\App\Http\Controllers\Api\Auth\AuthenticationApiController::class, 'logout'])->name('logout');
    });
});

// ============================================================
// API — Laporan Kejadian (PUBLIK — submit laporan tanpa auth)
// ============================================================
Route::post('laporan', [\App\Http\Controllers\Api\LaporanKejadianApiController::class, 'store'])->name('api.laporan.store');
Route::get('laporan/peta', [\App\Http\Controllers\Api\LaporanKejadianApiController::class, 'peta'])->name('api.laporan.peta');

// ============================================================
// API — Master Data (PUBLIK — read-only untuk semua)
// ============================================================
Route::prefix('master')->name('api.master.')->group(function () {
    Route::get('jabatan',              [\App\Http\Controllers\Api\Master\MasterJabatanController::class, 'index'])->name('jabatan.index');
    Route::get('jabatan/{jabatan}',    [\App\Http\Controllers\Api\Master\MasterJabatanController::class, 'show'])->name('jabatan.show');
    Route::get('klaster',              [\App\Http\Controllers\Api\Master\MasterKlasterController::class, 'index'])->name('klaster.index');
    Route::get('klaster/{klaster}',    [\App\Http\Controllers\Api\Master\MasterKlasterController::class, 'show'])->name('klaster.show');
    Route::get('surat-jenis',          [\App\Http\Controllers\Api\Master\MasterSuratJenisController::class, 'index'])->name('surat-jenis.index');
    Route::get('surat-jenis/{suratJenis}', [\App\Http\Controllers\Api\Master\MasterSuratJenisController::class, 'show'])->name('surat-jenis.show');
    Route::get('surat-template',       [\App\Http\Controllers\Api\Master\MasterSuratTemplateController::class, 'index'])->name('surat-template.index');
    Route::get('surat-template/{suratTemplate}', [\App\Http\Controllers\Api\Master\MasterSuratTemplateController::class, 'show'])->name('surat-template.show');
    Route::get('jenis-bencana',        [\App\Http\Controllers\Api\Master\BencanaMasterJenisController::class, 'index'])->name('jenis-bencana.index');
    Route::get('jenis-bencana/{bencana}', [\App\Http\Controllers\Api\Master\BencanaMasterJenisController::class, 'show'])->name('jenis-bencana.show');
    Route::get('jabatan-ttd',          [\App\Http\Controllers\Api\Master\MasterJabatanPenandatanganController::class, 'index'])->name('jabatan-ttd.index');
    Route::get('jabatan-ttd/{jabatanTtd}', [\App\Http\Controllers\Api\Master\MasterJabatanPenandatanganController::class, 'show'])->name('jabatan-ttd.show');
    Route::get('sertifikasi',           [\App\Http\Controllers\Api\Master\MasterSertifikasiController::class, 'index'])->name('sertifikasi.index');
    Route::get('sertifikasi/{sertifikasi}', [\App\Http\Controllers\Api\Master\MasterSertifikasiController::class, 'show'])->name('sertifikasi.show');
});

// ============================================================
// API — Keahlian (PUBLIK — untuk form registrasi)
// ============================================================
Route::get('keahlian', function () {
    return response()->json(['data' => \App\Models\AuthKeahlianMaster::all(['id_keahlian', 'nama_keahlian', 'deskripsi'])]);
})->name('api.keahlian.index');

// ============================================================
// API — SQL Views (PUBLIK — read-only aggregated data)
// ============================================================
Route::prefix('views')->name('api.views.')->group(function () {
    Route::get('command-center-summary', [\App\Http\Controllers\Api\SqlViewController::class, 'commandCenterSummary'])->name('command-center-summary');
    Route::get('incident-timeline/{id}', [\App\Http\Controllers\Api\SqlViewController::class, 'incidentTimeline'])->name('incident-timeline');
    Route::get('alert-insiden-baru',     [\App\Http\Controllers\Api\SqlViewController::class, 'alertInsiden'])->name('alert-insiden');
    Route::get('blank-spot',             [\App\Http\Controllers\Api\SqlViewController::class, 'blankSpot'])->name('blank-spot');
    Route::get('logistik-audit',         [\App\Http\Controllers\Api\SqlViewController::class, 'logistikAudit'])->name('logistik-audit');
    Route::get('aset-siap-pakai',        [\App\Http\Controllers\Api\SqlViewController::class, 'asetSiapPakai'])->name('aset-siap-pakai');
    Route::get('aset-operasional-ready', [\App\Http\Controllers\Api\SqlViewController::class, 'asetOperasionalReady'])->name('aset-operasional-ready');
    Route::get('relawan-domisili-check', [\App\Http\Controllers\Api\SqlViewController::class, 'relawanDomisiliCheck'])->name('relawan-domisili-check');
    Route::get('user-access-control',    [\App\Http\Controllers\Api\SqlViewController::class, 'userAccessControl'])->name('user-access-control');
    Route::get('surat-orphans',          [\App\Http\Controllers\Api\SqlViewController::class, 'suratOrphans'])->name('surat-orphans');
});

// ============================================================
// API — Terautentikasi (Sanctum)
// ============================================================
Route::middleware(['auth:sanctum', 'role:super_admin,pwnu,pcnu,relawan'])->group(function () {

    // Operasi
    Route::prefix('operasi')->name('api.operasi.')->group(function () {
        Route::apiResource('posaju', \App\Http\Controllers\Api\Operasi\OperasiPosajuController::class)->except(['destroy']);
        Route::post('posaju/{posaju}/activate', [\App\Http\Controllers\Api\Operasi\OperasiPosajuController::class, 'activate'])->name('posaju.activate');
        Route::post('posaju/{posaju}/extend', [\App\Http\Controllers\Api\Operasi\OperasiPosajuController::class, 'extend'])->name('posaju.extend');
        Route::post('posaju/{posaju}/close', [\App\Http\Controllers\Api\Operasi\OperasiPosajuController::class, 'close'])->name('posaju.close');

        Route::apiResource('klaster', \App\Http\Controllers\Api\Operasi\OperasiKlasterController::class)->except(['destroy']);
        Route::post('klaster/{klaster}/progress', [\App\Http\Controllers\Api\Operasi\OperasiKlasterController::class, 'updateProgress'])->name('klaster.progress');
        Route::post('klaster/{klaster}/complete', [\App\Http\Controllers\Api\Operasi\OperasiKlasterController::class, 'complete'])->name('klaster.complete');

        Route::apiResource('tugas', \App\Http\Controllers\Api\Operasi\OperasiTugasController::class)->except(['destroy'])->parameters(['tugas' => 'tugas']);
        Route::post('tugas/{tugas}/start', [\App\Http\Controllers\Api\Operasi\OperasiTugasController::class, 'start'])->name('tugas.start');
        Route::post('tugas/{tugas}/pause', [\App\Http\Controllers\Api\Operasi\OperasiTugasController::class, 'pause'])->name('tugas.pause');
        Route::post('tugas/{tugas}/complete', [\App\Http\Controllers\Api\Operasi\OperasiTugasController::class, 'complete'])->name('tugas.complete');
    });

    // API v1
    Route::prefix('v1')->name('api.v1.')->group(function () {
        Route::post('sync', [\App\Http\Controllers\Api\Operasi\SyncApiController::class, 'sync'])->name('sync');
        Route::get('sync/state', [\App\Http\Controllers\Api\Operasi\SyncApiController::class, 'state'])->name('sync.state');
        Route::get('sync/status', [\App\Http\Controllers\Api\Operasi\SyncApiController::class, 'status'])->name('sync.status');
        Route::get('sync/metrics', [\App\Http\Controllers\Api\Operasi\SyncApiController::class, 'metrics'])->name('sync.metrics');
        Route::post('bootstrap', [\App\Http\Controllers\Api\Operasi\SyncApiController::class, 'bootstrap'])->name('bootstrap');
        Route::get('snapshot-download', [\App\Http\Controllers\Api\Operasi\SyncApiController::class, 'downloadSnapshot'])->name('sync.snapshot-download');

        // Device & Token Lifecycle
        Route::prefix('devices')->name('devices.')->controller(\App\Http\Controllers\Api\Device\DeviceApiController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::delete('{uuid}', 'destroy')->name('destroy');
            Route::post('logout-all', 'logoutAll')->name('logout-all');
        });

        // Assessment
        Route::get('assessment', [\App\Http\Controllers\Api\Operasi\AssessmentApiController::class, 'index'])->name('assessment.index');
        Route::post('assessment', [\App\Http\Controllers\Api\Operasi\AssessmentApiController::class, 'store'])->name('assessment.store');
        Route::get('assessment/{assessment}', [\App\Http\Controllers\Api\Operasi\AssessmentApiController::class, 'show'])->name('assessment.show');
        Route::put('assessment/{assessment}', [\App\Http\Controllers\Api\Operasi\AssessmentApiController::class, 'update'])->name('assessment.update');
        Route::delete('assessment/{assessment}', [\App\Http\Controllers\Api\Operasi\AssessmentApiController::class, 'destroy'])->name('assessment.destroy');
        // Assessment — nested children
        Route::get('assessment/{assessment}/dampak', [\App\Http\Controllers\Api\Operasi\AssessmentApiController::class, 'dampakIndex'])->name('assessment.dampak.index');
        Route::post('assessment/{assessment}/dampak', [\App\Http\Controllers\Api\Operasi\AssessmentApiController::class, 'dampakStore'])->name('assessment.dampak.store');
        Route::put('assessment/{assessment}/dampak/{dampak}', [\App\Http\Controllers\Api\Operasi\AssessmentApiController::class, 'dampakUpdate'])->name('assessment.dampak.update');
        Route::delete('assessment/{assessment}/dampak/{dampak}', [\App\Http\Controllers\Api\Operasi\AssessmentApiController::class, 'dampakDestroy'])->name('assessment.dampak.destroy');
        Route::get('assessment/{assessment}/kebutuhan', [\App\Http\Controllers\Api\Operasi\AssessmentApiController::class, 'kebutuhanIndex'])->name('assessment.kebutuhan.index');
        Route::post('assessment/{assessment}/kebutuhan', [\App\Http\Controllers\Api\Operasi\AssessmentApiController::class, 'kebutuhanStore'])->name('assessment.kebutuhan.store');
        Route::put('assessment/{assessment}/kebutuhan/{kebutuhan}', [\App\Http\Controllers\Api\Operasi\AssessmentApiController::class, 'kebutuhanUpdate'])->name('assessment.kebutuhan.update');
        Route::delete('assessment/{assessment}/kebutuhan/{kebutuhan}', [\App\Http\Controllers\Api\Operasi\AssessmentApiController::class, 'kebutuhanDestroy'])->name('assessment.kebutuhan.destroy');

        // Sitrep
        Route::get('sitrep', [\App\Http\Controllers\Api\Operasi\SitrepApiController::class, 'index'])->name('sitrep.index');
        Route::post('sitrep', [\App\Http\Controllers\Api\Operasi\SitrepApiController::class, 'store'])->name('sitrep.store');
        Route::get('sitrep/{sitrep}', [\App\Http\Controllers\Api\Operasi\SitrepApiController::class, 'show'])->name('sitrep.show');
        Route::put('sitrep/{sitrep}', [\App\Http\Controllers\Api\Operasi\SitrepApiController::class, 'update'])->name('sitrep.update');
        Route::delete('sitrep/{sitrep}', [\App\Http\Controllers\Api\Operasi\SitrepApiController::class, 'destroy'])->name('sitrep.destroy');
        // Sitrep — nested children
        Route::get('sitrep/{sitrep}/dampak', [\App\Http\Controllers\Api\Operasi\SitrepApiController::class, 'dampakIndex'])->name('sitrep.dampak.index');
        Route::post('sitrep/{sitrep}/dampak', [\App\Http\Controllers\Api\Operasi\SitrepApiController::class, 'dampakStore'])->name('sitrep.dampak.store');
        Route::put('sitrep/{sitrep}/dampak/{dampak}', [\App\Http\Controllers\Api\Operasi\SitrepApiController::class, 'dampakUpdate'])->name('sitrep.dampak.update');
        Route::delete('sitrep/{sitrep}/dampak/{dampak}', [\App\Http\Controllers\Api\Operasi\SitrepApiController::class, 'dampakDestroy'])->name('sitrep.dampak.destroy');
        Route::get('sitrep/{sitrep}/kebutuhan', [\App\Http\Controllers\Api\Operasi\SitrepApiController::class, 'kebutuhanIndex'])->name('sitrep.kebutuhan.index');
        Route::post('sitrep/{sitrep}/kebutuhan', [\App\Http\Controllers\Api\Operasi\SitrepApiController::class, 'kebutuhanStore'])->name('sitrep.kebutuhan.store');
        Route::put('sitrep/{sitrep}/kebutuhan/{kebutuhan}', [\App\Http\Controllers\Api\Operasi\SitrepApiController::class, 'kebutuhanUpdate'])->name('sitrep.kebutuhan.update');
        Route::delete('sitrep/{sitrep}/kebutuhan/{kebutuhan}', [\App\Http\Controllers\Api\Operasi\SitrepApiController::class, 'kebutuhanDestroy'])->name('sitrep.kebutuhan.destroy');

        // Insiden — CRUD lengkap + status lifecycle
        Route::get('insiden', [\App\Http\Controllers\Api\Operasi\InsidenFullController::class, 'index'])->name('insiden.index');
        Route::post('insiden', [\App\Http\Controllers\Api\Operasi\InsidenFullController::class, 'store'])->name('insiden.store');
        Route::get('insiden/{insiden}', [\App\Http\Controllers\Api\Operasi\InsidenFullController::class, 'show'])->name('insiden.show');
        Route::put('insiden/{insiden}', [\App\Http\Controllers\Api\Operasi\InsidenFullController::class, 'update'])->name('insiden.update');
        Route::delete('insiden/{insiden}', [\App\Http\Controllers\Api\Operasi\InsidenFullController::class, 'destroy'])->name('insiden.destroy');
        Route::patch('insiden/{insiden}/status', [\App\Http\Controllers\Api\Operasi\InsidenFullController::class, 'ubahStatus'])->name('insiden.ubah-status');
        Route::post('insiden/{insiden}/lock', [\App\Http\Controllers\Api\Operasi\InsidenFullController::class, 'lock'])->name('insiden.lock');
        Route::post('insiden/{insiden}/unlock', [\App\Http\Controllers\Api\Operasi\InsidenFullController::class, 'unlock'])->name('insiden.unlock');

        // Eskalasi
        Route::get('eskalasi', [\App\Http\Controllers\Api\Operasi\EskalasiApiController::class, 'index'])->name('eskalasi.index');
        Route::post('eskalasi', [\App\Http\Controllers\Api\Operasi\EskalasiApiController::class, 'store'])->name('eskalasi.store');
        Route::get('eskalasi/{eskalasi}', [\App\Http\Controllers\Api\Operasi\EskalasiApiController::class, 'show'])->name('eskalasi.show');
        Route::delete('eskalasi/{eskalasi}', [\App\Http\Controllers\Api\Operasi\EskalasiApiController::class, 'destroy'])->name('eskalasi.destroy');

        // Aktivasi
        Route::get('aktivasi', [\App\Http\Controllers\Api\Operasi\AktivasiApiController::class, 'index'])->name('aktivasi.index');
        Route::post('aktivasi', [\App\Http\Controllers\Api\Operasi\AktivasiApiController::class, 'store'])->name('aktivasi.store');
        Route::get('aktivasi/{aktivasi}', [\App\Http\Controllers\Api\Operasi\AktivasiApiController::class, 'show'])->name('aktivasi.show');
        Route::put('aktivasi/{aktivasi}', [\App\Http\Controllers\Api\Operasi\AktivasiApiController::class, 'update'])->name('aktivasi.update');
        Route::post('aktivasi/{aktivasi}/selesai', [\App\Http\Controllers\Api\Operasi\AktivasiApiController::class, 'selesai'])->name('aktivasi.selesai');
        Route::delete('aktivasi/{aktivasi}', [\App\Http\Controllers\Api\Operasi\AktivasiApiController::class, 'destroy'])->name('aktivasi.destroy');

        // Penugasan
        Route::get('penugasan', [\App\Http\Controllers\Api\Operasi\PenugasanApiController::class, 'index'])->name('penugasan.index');
        Route::post('penugasan/bulk', [\App\Http\Controllers\Api\Operasi\PenugasanApiController::class, 'bulk'])->name('penugasan.bulk');
        Route::post('logistik/bulk', [\App\Http\Controllers\Api\Operasi\BulkStubController::class, 'logistikBulk'])->name('logistik.bulk');
        Route::post('mobilisasi/bulk', [\App\Http\Controllers\Api\Operasi\BulkStubController::class, 'mobilisasiBulk'])->name('mobilisasi.bulk');

        Route::apiResource('mobilisasi', \App\Http\Controllers\Api\Operasi\MobilisasiApiController::class)
            ->parameters(['mobilisasi' => 'uuid'])
            ->names('mobilisasi');

        Route::post('mobilisasi/{uuid}/approve', [\App\Http\Controllers\Api\Operasi\MobilisasiApiController::class, 'approve'])->name('mobilisasi.approve');
        Route::post('mobilisasi/{uuid}/depart', [\App\Http\Controllers\Api\Operasi\MobilisasiApiController::class, 'depart'])->name('mobilisasi.depart');
        Route::post('mobilisasi/{uuid}/arrive', [\App\Http\Controllers\Api\Operasi\MobilisasiApiController::class, 'arrive'])->name('mobilisasi.arrive');
        Route::post('mobilisasi/{uuid}/finish', [\App\Http\Controllers\Api\Operasi\MobilisasiApiController::class, 'finish'])->name('mobilisasi.finish');
        Route::post('mobilisasi/{uuid}/cancel', [\App\Http\Controllers\Api\Operasi\MobilisasiApiController::class, 'cancel'])->name('mobilisasi.cancel');

        Route::get('penugasan/{uuid}', [\App\Http\Controllers\Api\Operasi\PenugasanApiController::class, 'show'])->name('penugasan.show');
        Route::post('penugasan', [\App\Http\Controllers\Api\Operasi\PenugasanApiController::class, 'store'])->name('penugasan.store');
        Route::patch('penugasan/{uuid}', [\App\Http\Controllers\Api\Operasi\PenugasanApiController::class, 'updateStatus'])->name('penugasan.update');
        Route::patch('penugasan/{uuid}/status', [\App\Http\Controllers\Api\Operasi\PenugasanApiController::class, 'updateStatus'])->name('penugasan.status');
        Route::delete('penugasan/{uuid}', [\App\Http\Controllers\Api\Operasi\PenugasanApiController::class, 'destroy'])->name('penugasan.destroy');

        Route::get('klaster', [\App\Http\Controllers\Api\Operasi\KlasterApiController::class, 'index'])->name('klaster.index');
        Route::post('klaster', [\App\Http\Controllers\Api\Operasi\KlasterApiController::class, 'store'])->name('klaster.store');
        Route::get('klaster/{uuid}', [\App\Http\Controllers\Api\Operasi\KlasterApiController::class, 'show'])->name('klaster.show');
        Route::patch('klaster/{uuid}', [\App\Http\Controllers\Api\Operasi\KlasterApiController::class, 'update'])->name('klaster.update');
        Route::delete('klaster/{uuid}', [\App\Http\Controllers\Api\Operasi\KlasterApiController::class, 'destroy'])->name('klaster.destroy');

        // Governance — Pleno
        Route::get('insiden/{insiden}/pleno', [\App\Http\Controllers\Api\Governance\PlanoApiController::class, 'index'])->name('insiden.pleno.index');
        Route::post('insiden/{insiden}/pleno', [\App\Http\Controllers\Api\Governance\PlanoApiController::class, 'store'])->name('insiden.pleno.store');
        Route::get('insiden/{insiden}/pleno/{pleno}', [\App\Http\Controllers\Api\Governance\PlanoApiController::class, 'show'])->name('insiden.pleno.show');
        Route::put('insiden/{insiden}/pleno/{pleno}', [\App\Http\Controllers\Api\Governance\PlanoApiController::class, 'update'])->name('insiden.pleno.update');
        Route::delete('insiden/{insiden}/pleno/{pleno}', [\App\Http\Controllers\Api\Governance\PlanoApiController::class, 'destroy'])->name('insiden.pleno.destroy');
        Route::post('insiden/{insiden}/pleno/{pleno}/finalisasi', [\App\Http\Controllers\Api\Governance\PlanoApiController::class, 'finalisasi'])->name('insiden.pleno.finalisasi');
        Route::post('insiden/{insiden}/pleno/{pleno}/keputusan', [\App\Http\Controllers\Api\Governance\PlanoApiController::class, 'tambahKeputusan'])->name('insiden.pleno.keputusan.store');
        Route::delete('insiden/{insiden}/pleno/{pleno}/keputusan/{keputusan}', [\App\Http\Controllers\Api\Governance\PlanoApiController::class, 'hapusKeputusan'])->name('insiden.pleno.keputusan.destroy');
        Route::post('insiden/{insiden}/pleno/{pleno}/peserta', [\App\Http\Controllers\Api\Governance\PlanoApiController::class, 'tambahPeserta'])->name('insiden.pleno.peserta.store');
        Route::delete('insiden/{insiden}/pleno/{pleno}/peserta/{peserta}', [\App\Http\Controllers\Api\Governance\PlanoApiController::class, 'hapusPeserta'])->name('insiden.pleno.peserta.destroy');

        // Governance — Surat
        Route::get('surat', [\App\Http\Controllers\Api\Governance\SuratApiController::class, 'index'])->name('surat.index');
        Route::post('surat', [\App\Http\Controllers\Api\Governance\SuratApiController::class, 'store'])->name('surat.store');
        Route::get('surat/{surat}', [\App\Http\Controllers\Api\Governance\SuratApiController::class, 'show'])->name('surat.show');
        Route::put('surat/{surat}', [\App\Http\Controllers\Api\Governance\SuratApiController::class, 'update'])->name('surat.update');
        Route::delete('surat/{surat}', [\App\Http\Controllers\Api\Governance\SuratApiController::class, 'destroy'])->name('surat.destroy');
        Route::post('surat/{surat}/ajukan-paraf', [\App\Http\Controllers\Api\Governance\SuratApiController::class, 'ajukanParaf'])->name('surat.ajukan-paraf');
        Route::patch('surat/paraf/{paraf}', [\App\Http\Controllers\Api\Governance\SuratApiController::class, 'parafAction'])->name('surat.paraf.action');
        Route::post('surat/{surat}/finalisasi', [\App\Http\Controllers\Api\Governance\SuratApiController::class, 'finalisasi'])->name('surat.finalisasi');
        // Surat — Tembusan
        Route::get('surat/{surat}/tembusan', [\App\Http\Controllers\Api\Governance\SuratApiController::class, 'tembusanIndex'])->name('surat.tembusan.index');
        Route::post('surat/{surat}/tembusan', [\App\Http\Controllers\Api\Governance\SuratApiController::class, 'tembusanStore'])->name('surat.tembusan.store');
        Route::delete('surat/{surat}/tembusan/{tembusan}', [\App\Http\Controllers\Api\Governance\SuratApiController::class, 'tembusanDestroy'])->name('surat.tembusan.destroy');
    });

    // Logistik
    Route::prefix('logistik')->name('api.logistik.')->group(function () {
        Route::apiResource('kategori', \App\Http\Controllers\Api\Logistik\LogistikKategoriController::class);
        Route::apiResource('katalog', \App\Http\Controllers\Api\Logistik\LogistikKatalogController::class);

        Route::post('mutasi', [\App\Http\Controllers\Api\Logistik\LogistikMutasiController::class, 'store'])->name('mutasi.store');

        Route::get('stok', [\App\Http\Controllers\Api\Logistik\LogistikStokController::class, 'index'])->name('stok.index');
        Route::get('stok/{id}', [\App\Http\Controllers\Api\Logistik\LogistikStokController::class, 'show'])->name('stok.show');
        Route::post('stok/{id}/koreksi', [\App\Http\Controllers\Api\Logistik\LogistikStokController::class, 'koreksi'])->name('stok.koreksi');
        Route::get('histori', [\App\Http\Controllers\Api\Logistik\LogistikStokController::class, 'history'])->name('stok.history');
        Route::get('summary', [\App\Http\Controllers\Api\Operasi\InsidenApiController::class, 'summary'])->name('summary');

        // Gudang
        Route::apiResource('gudang', \App\Http\Controllers\Api\Logistik\LogistikGudangController::class)->except(['edit', 'create']);

        // Permintaan
        Route::get('permintaan', [\App\Http\Controllers\Api\Logistik\LogistikPermintaanController::class, 'index'])->name('permintaan.index');
        Route::post('permintaan', [\App\Http\Controllers\Api\Logistik\LogistikPermintaanController::class, 'store'])->name('permintaan.store');
        Route::get('permintaan/{permintaan}', [\App\Http\Controllers\Api\Logistik\LogistikPermintaanController::class, 'show'])->name('permintaan.show');
        Route::patch('permintaan/{permintaan}/proses', [\App\Http\Controllers\Api\Logistik\LogistikPermintaanController::class, 'proses'])->name('permintaan.proses');
    });

    // Laporan Kejadian (management — validasi, eskalasi)
    Route::get('laporan',                    [\App\Http\Controllers\Api\LaporanKejadianApiController::class, 'index'])->name('api.laporan.index');
    Route::get('laporan/{laporan}',          [\App\Http\Controllers\Api\LaporanKejadianApiController::class, 'show'])->name('api.laporan.show');
    Route::patch('laporan/{laporan}/validasi', [\App\Http\Controllers\Api\LaporanKejadianApiController::class, 'validasi'])->name('api.laporan.validasi');
    Route::post('laporan/{laporan}/eskalasi-insiden', [\App\Http\Controllers\Api\LaporanKejadianApiController::class, 'eskalasiInsiden'])->name('api.laporan.eskalasi');

    // Admin — Manajemen Pengguna
    Route::prefix('admin')->name('api.admin.')->middleware('role:super_admin,pwnu')->group(function () {
        Route::get('pengguna',              [\App\Http\Controllers\Api\Admin\PenggunaApiController::class, 'index'])->name('pengguna.index');
        Route::get('pengguna/menunggu',     [\App\Http\Controllers\Api\Admin\PenggunaApiController::class, 'menunggu'])->name('pengguna.menunggu');
        Route::get('pengguna/{pengguna}',   [\App\Http\Controllers\Api\Admin\PenggunaApiController::class, 'show'])->name('pengguna.show');
        Route::put('pengguna/{pengguna}',   [\App\Http\Controllers\Api\Admin\PenggunaApiController::class, 'update'])->name('pengguna.update');
        Route::patch('pengguna/{pengguna}/setujui', [\App\Http\Controllers\Api\Admin\PenggunaApiController::class, 'setujui'])->name('pengguna.setujui');
        Route::patch('pengguna/{pengguna}/tolak',   [\App\Http\Controllers\Api\Admin\PenggunaApiController::class, 'tolak'])->name('pengguna.tolak');

        // PenggunaJabatan — mapping user ke jabatan dengan scope
        Route::apiResource('pengguna-jabatan', \App\Http\Controllers\Api\Admin\PenggunaJabatanController::class)->except(['edit', 'create']);
        Route::post('pengguna-jabatan/{pengguna_jabatan}/aktifkan', [\App\Http\Controllers\Api\Admin\PenggunaJabatanController::class, 'activate'])->name('pengguna-jabatan.aktifkan');
        Route::post('pengguna-jabatan/{pengguna_jabatan}/nonaktifkan', [\App\Http\Controllers\Api\Admin\PenggunaJabatanController::class, 'deactivate'])->name('pengguna-jabatan.nonaktifkan');

        // Role Application — pengajuan peran
        Route::get('role-applications', [\App\Http\Controllers\Api\Admin\RoleApplicationController::class, 'index'])->name('role-applications.index');
        Route::get('role-applications/{roleApplication}', [\App\Http\Controllers\Api\Admin\RoleApplicationController::class, 'show'])->name('role-applications.show');
        Route::post('role-applications/{roleApplication}/setujui', [\App\Http\Controllers\Api\Admin\RoleApplicationController::class, 'approve'])->name('role-applications.setujui');
        Route::post('role-applications/{roleApplication}/tolak', [\App\Http\Controllers\Api\Admin\RoleApplicationController::class, 'reject'])->name('role-applications.tolak');
    });

    // Organisasi (Legacy)
    Route::prefix('organisasi')->name('api.organisasi.')->group(function () {
        Route::get('pcnu',                  [\App\Http\Controllers\Api\OrganisasiApiController::class, 'pcnu'])->name('pcnu');
        Route::get('pcnu/{pcnu}',           [\App\Http\Controllers\Api\OrganisasiApiController::class, 'pcnuDetail'])->name('pcnu.detail');
        Route::get('pcnu/{pcnu}/mwc',       [\App\Http\Controllers\Api\OrganisasiApiController::class, 'pcnuMwc'])->name('pcnu.mwc');
        Route::get('mwc/{mwc}/ranting',     [\App\Http\Controllers\Api\OrganisasiApiController::class, 'mwcRanting'])->name('mwc.ranting');
        Route::get('unit',                  [\App\Http\Controllers\Api\OrganisasiApiController::class, 'unit'])->name('unit');

        // Legacy CRUD
        Route::apiResource('sk', \App\Http\Controllers\Api\Organisasi\OrganisasiSkController::class)->except(['edit', 'create']);
        Route::get('sk/{sk}/pengurus', [\App\Http\Controllers\Api\Organisasi\OrganisasiSkController::class, 'pengurus'])->name('sk.pengurus');
        Route::post('sk/{sk}/pengurus', [\App\Http\Controllers\Api\Organisasi\OrganisasiSkController::class, 'tambahPengurus'])->name('sk.pengurus.store');
        Route::delete('sk/{sk}/pengurus/{pengurus}', [\App\Http\Controllers\Api\Organisasi\OrganisasiSkController::class, 'hapusPengurus'])->name('sk.pengurus.destroy');
        Route::apiResource('jabatan', \App\Http\Controllers\Api\Organisasi\OrganisasiJabatanController::class)->except(['edit', 'create']);
        Route::apiResource('mandat', \App\Http\Controllers\Api\Organisasi\OrganisasiMandatController::class)->except(['edit', 'create']);
        Route::apiResource('delegasi', \App\Http\Controllers\Api\Organisasi\OrganisasiDelegasiController::class)->except(['edit', 'create']);
    });

    // Aset
    Route::prefix('aset')->name('api.aset.')->group(function () {
        Route::get('/',                     [\App\Http\Controllers\Api\AsetApiController::class, 'index'])->name('index');
        Route::post('/',                    [\App\Http\Controllers\Api\AsetApiController::class, 'store'])->name('store');
        Route::get('tersedia',              [\App\Http\Controllers\Api\AsetApiController::class, 'tersedia'])->name('tersedia');
        Route::get('{aset}',                [\App\Http\Controllers\Api\AsetApiController::class, 'show'])->name('show');
        Route::put('{aset}',                [\App\Http\Controllers\Api\AsetApiController::class, 'update'])->name('update');
        Route::patch('{aset}/status',       [\App\Http\Controllers\Api\AsetApiController::class, 'updateStatus'])->name('status');
        Route::delete('{aset}',             [\App\Http\Controllers\Api\AsetApiController::class, 'destroy'])->name('destroy');
    });

    // Command Center API
    Route::prefix('command-center')->name('api.command-center.')->group(function () {
        Route::get('insiden-aktif', [\App\Http\Controllers\Api\CommandCenterApiController::class, 'insidenAktif'])->name('insiden-aktif');
        Route::get('statistik', [\App\Http\Controllers\Api\CommandCenterApiController::class, 'statistik'])->name('statistik');
        Route::get('stok-kritis', [\App\Http\Controllers\Api\CommandCenterApiController::class, 'stokKritis'])->name('stok-kritis');
        Route::get('jurnal-terbaru', [\App\Http\Controllers\Api\CommandCenterApiController::class, 'jurnalTerbaru'])->name('jurnal-terbaru');
    });

    // Relawan
    Route::prefix('relawan')->name('api.relawan.')->group(function () {
        Route::post('pendaftaran/{pendaftaran}/approve', [\App\Http\Controllers\Api\Relawan\RelawanPendaftaranController::class, 'approve'])->name('pendaftaran.approve');
        Route::post('pendaftaran/{pendaftaran}/reject', [\App\Http\Controllers\Api\Relawan\RelawanPendaftaranController::class, 'reject'])->name('pendaftaran.reject');
        Route::post('pendaftaran/{pendaftaran}/assign', [\App\Http\Controllers\Api\Relawan\RelawanPendaftaranController::class, 'assign'])->name('pendaftaran.assign');
        Route::post('penugasan/{penugasan}/complete', [\App\Http\Controllers\Api\Relawan\RelawanPenugasanController::class, 'complete'])->name('penugasan.complete');

        Route::get('profil/{profil}', [\App\Http\Controllers\Api\Relawan\RelawanProfilController::class, 'show'])->name('profil.show');
        Route::put('profil/{profil}', [\App\Http\Controllers\Api\Relawan\RelawanProfilController::class, 'update'])->name('profil.update');
        Route::post('profil/{profil}/keahlian', [\App\Http\Controllers\Api\Relawan\RelawanProfilController::class, 'syncSkills'])->name('profil.sync_skills');
        Route::get('available', [\App\Http\Controllers\Api\Relawan\VolunteerApiController::class, 'getAvailableVolunteers'])->name('available');

        // Relawan kebutuhan, sertifikasi, shift
        Route::apiResource('kebutuhan', \App\Http\Controllers\Api\Relawan\RelawanKebutuhanController::class)->except(['edit', 'create']);
        Route::get('sertifikasi', [\App\Http\Controllers\Api\Relawan\RelawanSertifikasiController::class, 'index'])->name('sertifikasi.index');
        Route::post('sertifikasi', [\App\Http\Controllers\Api\Relawan\RelawanSertifikasiController::class, 'store'])->name('sertifikasi.store');
        Route::delete('sertifikasi', [\App\Http\Controllers\Api\Relawan\RelawanSertifikasiController::class, 'destroy'])->name('sertifikasi.destroy');
        Route::apiResource('shift', \App\Http\Controllers\Api\Relawan\RelawanShiftController::class)->except(['edit', 'create']);
    });

    // Governance — Org* baru
    Route::prefix('governance')->name('api.governance.')->group(function () {
        Route::apiResource('structure-levels', \App\Http\Controllers\Api\Governance\OrgStructureLevelController::class)->except(['edit', 'create', 'show']);
        Route::apiResource('institutions', \App\Http\Controllers\Api\Governance\OrgInstitutionController::class)->except(['edit', 'create', 'show']);
        Route::apiResource('nodes', \App\Http\Controllers\Api\Governance\OrgNodeController::class)->except(['edit', 'create']);
        Route::apiResource('positions', \App\Http\Controllers\Api\Governance\OrgPositionController::class)->except(['edit', 'create', 'show']);
        Route::apiResource('sks', \App\Http\Controllers\Api\Governance\OrgSkController::class)->except(['edit', 'create']);
        Route::apiResource('mandates', \App\Http\Controllers\Api\Governance\OrgMandateController::class)->except(['edit', 'create']);
        Route::apiResource('delegations', \App\Http\Controllers\Api\Governance\OrgDelegationController::class)->except(['edit', 'create']);

        // Functions & Authorities
        Route::apiResource('functions', \App\Http\Controllers\Api\Governance\OrgFunctionController::class)->except(['edit', 'create']);
        Route::apiResource('authorities', \App\Http\Controllers\Api\Governance\OrgAuthorityController::class)->except(['edit', 'create']);

        // Pivot assignments
        Route::post('node-positions', [\App\Http\Controllers\Api\Governance\OrgNodeController::class, 'assignPosition'])->name('node-positions.assign');
        Route::delete('node-positions/{nodePosition}', [\App\Http\Controllers\Api\Governance\OrgNodeController::class, 'removePosition'])->name('node-positions.remove');
        Route::post('position-functions', [\App\Http\Controllers\Api\Governance\OrgPositionController::class, 'assignFunction'])->name('position-functions.assign');
        Route::delete('position-functions/{positionFunction}', [\App\Http\Controllers\Api\Governance\OrgPositionController::class, 'removeFunction'])->name('position-functions.remove');
        Route::post('function-authorities', [\App\Http\Controllers\Api\Governance\OrgFunctionController::class, 'assignAuthority'])->name('function-authorities.assign');
        Route::delete('function-authorities/{functionAuthority}', [\App\Http\Controllers\Api\Governance\OrgFunctionController::class, 'removeAuthority'])->name('function-authorities.remove');

        // Audit log
        Route::get('audit-logs', [\App\Http\Controllers\Api\Governance\GovAuditLogController::class, 'index'])->name('audit-logs.index');
    });

    // Organisasi Audit Log (Legacy)
    Route::get('organisasi/audit-log', [\App\Http\Controllers\Api\Organisasi\OrganisasiAuditLogController::class, 'index'])->name('api.organisasi.audit-log.index');

    // Operasi Penugasan History
    Route::get('v1/penugasan/{uuid}/history', [\App\Http\Controllers\Api\Operasi\PenugasanApiController::class, 'history'])->name('api.v1.penugasan.history');

    // Operasi Metrics Daily
    Route::get('admin/metrics', [\App\Http\Controllers\Api\Admin\MetricsController::class, 'index'])->name('api.admin.metrics.index');

    // ============================================================
    // 3 NEW DOMAINS (Assessment Extended, Inventaris, Histori)
    // ============================================================
    
    // Assessment Extension
    Route::post('insiden/{insiden}/assessment', [\App\Http\Controllers\Api\AssessmentApiController::class, 'store']);
    Route::get('insiden/{insiden}/assessment/{assessment}', [\App\Http\Controllers\Api\AssessmentApiController::class, 'show']);
    Route::put('insiden/{insiden}/assessment/{assessment}', [\App\Http\Controllers\Api\AssessmentApiController::class, 'update']);
    Route::get('master/kebutuhan-numerik', [\App\Http\Controllers\Api\AssessmentApiController::class, 'masterKebutuhanNumerik']);

    Route::prefix('insiden/{insiden}/assessment/{assessment}')->group(function () {
        Route::get('lengkap',          [\App\Http\Controllers\Api\AssessmentLengkapController::class, 'show']);
        Route::post('biodata',         [\App\Http\Controllers\Api\AssessmentBiodataController::class, 'store']);
        Route::post('narasi',          [\App\Http\Controllers\Api\AssessmentNarasiController::class, 'store']);
        Route::put('dampak-manusia',   [\App\Http\Controllers\Api\AssessmentDampakManusiaController::class, 'update']);
        Route::put('dampak-infrastruktur', [\App\Http\Controllers\Api\AssessmentDampakInfraController::class, 'update']);
        Route::put('dampak-lingkungan',    [\App\Http\Controllers\Api\AssessmentDampakLingController::class, 'update']);
        Route::put('dampak-ekonomi',       [\App\Http\Controllers\Api\AssessmentDampakEkoController::class, 'update']);
        Route::post('skor/{indikator}',    [\App\Http\Controllers\Api\AssessmentSkorController::class, 'store']);
        Route::get('ringkasan-skor',       [\App\Http\Controllers\Api\AssessmentSkorController::class, 'ringkasan']);
        Route::post('hitung-skor',         [\App\Http\Controllers\Api\AssessmentSkorController::class, 'hitung']);
        Route::post('submit',              [\App\Http\Controllers\Api\AssessmentApiController::class, 'submit'])->name('api.insiden.assessment.submit');
        Route::post('review',              [\App\Http\Controllers\Api\AssessmentApiController::class, 'review'])->name('api.insiden.assessment.review');
    });
    Route::get('master/indikator-skor',    [\App\Http\Controllers\Api\MasterIndikatorSkorController::class, 'index']);

    // Inventaris PWNU
    Route::prefix('inventaris')->group(function () {
        Route::apiResource('kategori',   \App\Http\Controllers\Api\InventarisKategoriController::class)->only(['index','show']);
        Route::apiResource('jenis',      \App\Http\Controllers\Api\InventarisJenisController::class)->only(['index','show']);
        Route::apiResource('aset',       \App\Http\Controllers\Api\InventarisAsetController::class);
        Route::get('aset/{aset}/dokumen-kadaluwarsa',  [\App\Http\Controllers\Api\InventarisAsetController::class, 'dokumenKadaluwarsa']);
        Route::get('aset/{aset}/riwayat-kondisi',      [\App\Http\Controllers\Api\InventarisKondisiLogController::class, 'index']);
        Route::post('aset/{aset}/kondisi',             [\App\Http\Controllers\Api\InventarisKondisiLogController::class, 'store']);
        Route::apiResource('aset.pemeliharaan',        \App\Http\Controllers\Api\InventarisPemeliharaanController::class);
        Route::apiResource('aset.dokumen',             \App\Http\Controllers\Api\InventarisDokumenController::class);
        Route::post('aset/{aset}/deploy',              [\App\Http\Controllers\Api\InventarisDeploymentController::class, 'store']);
        Route::patch('deployment/{dep}/kembali',       [\App\Http\Controllers\Api\InventarisDeploymentController::class, 'kembali']);
        Route::get('statistik',           [\App\Http\Controllers\Api\InventarisAsetController::class, 'statistik']);
        Route::get('perlu-maintenance',   [\App\Http\Controllers\Api\InventarisPemeliharaanController::class, 'jadwalTerdekat']);
        Route::get('dokumen-jatuh-tempo', [\App\Http\Controllers\Api\InventarisDokumenController::class, 'jatuhTempo']);
    });

    // History & Trend
    Route::prefix('histori')->group(function () {
        Route::apiResource('bencana',   \App\Http\Controllers\Api\HistoriBencanaController::class);
        Route::patch('bencana/{id}/verifikasi', [\App\Http\Controllers\Api\HistoriBencanaController::class, 'verifikasi']);
        Route::get('analisis-musiman/{kab}/{jenis}',  [\App\Http\Controllers\Api\HistoriAnalisisController::class, 'show']);
        Route::post('hitung-analisis-musiman',        [\App\Http\Controllers\Api\HistoriAnalisisController::class, 'hitung']);
        Route::get('probabilitas/{kab}/{jenis}',      [\App\Http\Controllers\Api\HistoriProbabilitasController::class, 'index']);
        Route::get('peta-risiko/{kab}',               [\App\Http\Controllers\Api\HistoriPetaRisikoController::class, 'show']);
        Route::get('trend-tahunan/{kab}',             [\App\Http\Controllers\Api\HistoriAnalisisController::class, 'trendTahunan']);
        Route::get('kalender-bencana/{kab}',          [\App\Http\Controllers\Api\HistoriAnalisisController::class, 'kalenderMusiman']);
        Route::get('indikator-risiko/{kab}',          [\App\Http\Controllers\Api\HistoriIndikatorController::class, 'index']);
        Route::put('indikator-risiko/{kab}',          [\App\Http\Controllers\Api\HistoriIndikatorController::class, 'update']);
        Route::get('perbandingan-antar-wilayah',      [\App\Http\Controllers\Api\HistoriAnalisisController::class, 'perbandinganWilayah']);
    });

});

// Relawan pendaftaran — tetap publik (registrasi awal)
Route::post('relawan/daftar', [\App\Http\Controllers\Api\Relawan\RelawanPendaftaranController::class, 'daftar'])->name('api.relawan.daftar');

// Role Application — perlu auth
Route::post('role-applications', [\App\Http\Controllers\Api\Admin\RoleApplicationController::class, 'store'])->name('api.role-applications.store')->middleware('auth:sanctum');

// ============================================================
// API — Health Check (publik)
// ============================================================
Route::get('health', \App\Http\Controllers\Api\HealthCheckController::class)->name('api.health');
