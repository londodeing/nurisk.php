<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Web\PublicDashboardWebController;
use App\Http\Controllers\Web\LaporController;
use Illuminate\Support\Facades\Route;

// === PUBLIC (tanpa login) ===
Route::controller(PublicDashboardWebController::class)->group(function () {
    Route::get('/',              'index')->name('public.home');
    Route::get('/map',           'map')->name('public.map');
    Route::get('/lapor',         'lapor')->name('public.lapor');
    Route::get('/resource',      'resource')->name('public.resource');
});
Route::post('/lapor', [LaporController::class, 'store'])->middleware('throttle:10,60')->name('public.lapor.store');

Route::get('/health', \App\Http\Controllers\Api\HealthCheckController::class)->name('health');

    // Route /dashboard is now handled by routes/dashboard.php

Route::middleware(['auth'])->group(function () {
    Route::get('/role-application', [App\Http\Controllers\Auth\RoleApplicationController::class, 'create'])->name('role-application.create');
    Route::post('/role-application', [App\Http\Controllers\Auth\RoleApplicationController::class, 'store'])->name('role-application.store');
});

Route::middleware(['auth'])->prefix('dashboard')->name('dashboard.')->group(function() {
    // TRC Mobile Dashboard
    Route::get('trc', [\App\Http\Controllers\Web\TrcDashboardController::class, 'index'])->middleware('role:super_admin,trc')->name('trc');
    Route::get('trc/polling', [\App\Http\Controllers\Web\TrcDashboardController::class, 'polling'])->middleware('role:super_admin,trc')->name('trc.polling');
    Route::post('trc/mulai-penugasan', [\App\Http\Controllers\Web\TrcDashboardController::class, 'mulaiPenugasan'])->middleware('role:super_admin,trc')->name('trc.mulai-penugasan');
    Route::post('trc/tiba-lokasi', [\App\Http\Controllers\Web\TrcDashboardController::class, 'tibaLokasi'])->middleware('role:super_admin,trc')->name('trc.tiba-lokasi');
    Route::get('trc/insiden/{insiden}/assessment/create', [\App\Http\Controllers\Web\TrcDashboardController::class, 'assessmentCreate'])->middleware('role:super_admin,trc')->name('trc.assessment.create');
    Route::post('trc/insiden/{insiden}/assessment/store', [\App\Http\Controllers\Web\TrcDashboardController::class, 'assessmentStore'])->middleware('role:super_admin,trc')->name('trc.assessment.store');
    // Operator Posko Dashboard (Data Entry Center)
    Route::get('operator', [\App\Http\Controllers\Web\OperatorDashboardController::class, 'index'])->middleware('role:super_admin,posko')->name('operator');
    Route::get('operator/polling', [\App\Http\Controllers\Web\OperatorDashboardController::class, 'polling'])->middleware('role:super_admin,posko')->name('operator.polling');

    // Posko Commander Dashboard
    Route::get('posko-commander', [\App\Http\Controllers\Web\PoskoCommanderDashboardController::class, 'index'])->middleware('role:super_admin,posko_commander')->name('posko_commander');
    Route::get('posko-commander/polling', [\App\Http\Controllers\Web\PoskoCommanderDashboardController::class, 'polling'])->middleware('role:super_admin,posko_commander')->name('posko_commander.polling');

    // Cluster Coordinator Dashboard
    Route::get('cluster', [\App\Http\Controllers\Web\ClusterCoordinatorDashboardController::class, 'index'])->middleware('role:super_admin,cluster_coordinator')->name('cluster_coordinator');
    Route::get('cluster/polling', [\App\Http\Controllers\Web\ClusterCoordinatorDashboardController::class, 'polling'])->middleware('role:super_admin,cluster_coordinator')->name('cluster_coordinator.polling');

    // PWNU Executive Dashboard
    Route::get('pwnu', [\App\Http\Controllers\Web\PwnuDashboardController::class, 'index'])->middleware('role:super_admin,pwnu')->name('pwnu');
    Route::get('pwnu/polling', [\App\Http\Controllers\Web\PwnuDashboardController::class, 'polling'])->middleware('role:super_admin,pwnu')->name('pwnu.polling');

    
    // PCNU Mission Coordination Dashboard
    Route::get('pcnu', [\App\Http\Controllers\Web\PcnuDashboardController::class, 'index'])->middleware('role:super_admin,pcnu,pwnu')->name('pcnu');
    Route::get('pcnu/polling', [\App\Http\Controllers\Web\PcnuDashboardController::class, 'polling'])->middleware('role:super_admin,pcnu,pwnu')->name('pcnu.polling');
    
    // Posko Operational Dashboard
    Route::get('posko', [\App\Http\Controllers\Web\PoskoDashboardController::class, 'index'])->middleware('role:super_admin,pcnu,pwnu')->name('posko');
    Route::get('posko/polling', [\App\Http\Controllers\Web\PoskoDashboardController::class, 'polling'])->middleware('role:super_admin,pcnu,pwnu')->name('posko.polling');
    
    Route::get('relawan', function() { return view('dashboard.relawan'); })->middleware('role:relawan')->name('relawan');
    
    // Laporan Kejadian dari Publik (Verifikasi)
    Route::middleware('role:super_admin,posko,pcnu,pwnu')->prefix('laporan-masuk')->name('laporan.')->group(function() {
        Route::get('/', [\App\Http\Controllers\Operasi\LaporanKejadianController::class, 'index'])->name('index');
        Route::get('/{laporan}', [\App\Http\Controllers\Operasi\LaporanKejadianController::class, 'show'])->name('show');
        Route::get('/{laporan}/edit', [\App\Http\Controllers\Operasi\LaporanKejadianController::class, 'edit'])->name('edit');
        Route::put('/{laporan}', [\App\Http\Controllers\Operasi\LaporanKejadianController::class, 'update'])->name('update');
        Route::post('/{laporan}/verify', [\App\Http\Controllers\Operasi\LaporanKejadianController::class, 'verify'])->name('verify');
        Route::post('/{laporan}/reject', [\App\Http\Controllers\Operasi\LaporanKejadianController::class, 'reject'])->name('reject');
    });
});

Route::middleware(['auth'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Governance Approval Center Routes
Route::middleware(['auth', 'role:super_admin,pwnu,pcnu', 'active_approver'])
     ->prefix('governance/approval')
     ->name('governance.approval.')
     ->group(function () {
    Route::get('/', [\App\Http\Controllers\Web\GovernanceApprovalController::class, 'index'])->name('index');
    Route::patch('paraf/{paraf}', [\App\Http\Controllers\Web\GovernanceApprovalController::class, 'prosesParaf'])->name('paraf.proses');
    Route::patch('pleno/{pleno}/finalisasi', [\App\Http\Controllers\Web\GovernanceApprovalController::class, 'finalisasiPleno'])->name('pleno.finalisasi');
    Route::patch('surat/{surat}/tandatangani', [\App\Http\Controllers\Web\GovernanceApprovalController::class, 'tandatanganiSurat'])->name('surat.tandatangani');
    Route::get('surat/{surat}/preview', [\App\Http\Controllers\Web\GovernanceApprovalController::class, 'previewSurat'])->name('surat.preview');
});

Route::middleware(['auth', 'role:super_admin,pwnu,pcnu', 'active_approver'])
     ->prefix('governance/role-approval')
     ->name('governance.role-approval.')
     ->group(function () {
    Route::get('/', [\App\Http\Controllers\Web\GovernanceRoleApprovalController::class, 'index'])->name('index');
    Route::post('/{id}/approve', [\App\Http\Controllers\Web\GovernanceRoleApprovalController::class, 'approve'])->name('approve');
    Route::post('/{id}/reject', [\App\Http\Controllers\Web\GovernanceRoleApprovalController::class, 'reject'])->name('reject');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/command-center', [\App\Http\Controllers\Web\CommandCenterController::class, 'index'])
         ->name('command-center');
});

Route::prefix('admin')->name('admin.')->middleware(['auth'])->group(function () {
    Route::middleware(['role:super_admin,pwnu,pcnu'])->group(function () {
        Route::get('jabatan', [\App\Http\Controllers\Admin\JabatanController::class, 'index'])->name('jabatan.index');
        Route::post('assets/import', [\App\Http\Controllers\OrgAssetController::class, 'import'])->name('assets.import');
        Route::resource('assets', \App\Http\Controllers\OrgAssetController::class);
        
        Route::get('pengguna', [\App\Http\Controllers\Web\Admin\PenggunaWebController::class, 'index'])->name('pengguna.index');
        Route::get('pengguna/{pengguna}', [\App\Http\Controllers\Web\Admin\PenggunaWebController::class, 'show'])->name('pengguna.show');
        Route::get('pengguna/{pengguna}/edit', [\App\Http\Controllers\Web\Admin\PenggunaWebController::class, 'edit'])->name('pengguna.edit');
        Route::put('pengguna/{pengguna}', [\App\Http\Controllers\Web\Admin\PenggunaWebController::class, 'update'])->name('pengguna.update');
        Route::patch('pengguna/{pengguna}/setujui', [\App\Http\Controllers\Web\Admin\PenggunaWebController::class, 'setujui'])->name('pengguna.setujui');
        Route::patch('pengguna/{pengguna}/tolak', [\App\Http\Controllers\Web\Admin\PenggunaWebController::class, 'tolak'])->name('pengguna.tolak');
        Route::patch('pengguna-jabatan/{pengguna_jabatan}/toggle', [\App\Http\Controllers\Web\Admin\PenggunaWebController::class, 'toggleJabatan'])->name('pengguna-jabatan.toggle');
    });
    Route::middleware(['role:super_admin'])->group(function () {
        Route::resource('jabatan', \App\Http\Controllers\Admin\JabatanController::class)->except(['index', 'show']);
    });
});

use App\Http\Controllers\Governance\EskalasiController;
use App\Http\Controllers\Governance\PlanoController;
use App\Http\Controllers\Governance\PlanoKeputusanController;
use App\Http\Controllers\Governance\PlanoPesertaController;
use App\Http\Controllers\Operasi\InsidenController;
use App\Http\Controllers\Operasi\InsidenStatusController;
use App\Http\Controllers\Operasi\PosAjuWebController;
use App\Http\Controllers\Operasi\KlasterWebController;
use App\Http\Controllers\Operasi\SitrepWebController;

Route::middleware(['auth', 'role:super_admin,pwnu,pcnu,trc'])->group(function () {
    Route::resource('insiden', InsidenController::class);
    Route::put('insiden/{insiden}/status', [InsidenStatusController::class, 'update'])
         ->name('insiden.status.update');
    Route::post('insiden/{insiden}/spk', [\App\Http\Controllers\Operasi\InsidenSpkController::class, 'store'])
         ->name('insiden.spk.store');
    Route::resource('insiden.assessment', \App\Http\Controllers\Operasi\AssessmentController::class)->only(['create', 'store', 'show', 'edit', 'update']);
    Route::post('insiden/{insiden}/assessment/{assessment}/submit', [\App\Http\Controllers\Operasi\AssessmentController::class, 'submit'])
         ->name('insiden.assessment.submit');
    Route::post('insiden/{insiden}/assessment/{assessment}/review', [\App\Http\Controllers\Operasi\AssessmentController::class, 'review'])
         ->name('insiden.assessment.review');
    Route::get('insiden/{insiden}/assessment/{assessment}/cetak', [\App\Http\Controllers\Operasi\AssessmentController::class, 'cetak'])
         ->name('insiden.assessment.cetak');
    // Pleno
    Route::resource('insiden.pleno', \App\Http\Controllers\Operasi\PlenoController::class);
    Route::post('insiden/{insiden}/pleno/{pleno}/finalize', [\App\Http\Controllers\Operasi\PlenoController::class, 'finalize'])
         ->name('insiden.pleno.finalize');
    Route::post('insiden/{insiden}/pleno/{pleno}/peserta', [\App\Http\Controllers\Operasi\PlenoPesertaController::class, 'store'])
         ->name('insiden.pleno.peserta.store');
    Route::delete('insiden/{insiden}/pleno/{pleno}/peserta/{peserta}', [\App\Http\Controllers\Operasi\PlenoPesertaController::class, 'destroy'])
         ->name('insiden.pleno.peserta.destroy');
    Route::post('insiden/{insiden}/pleno/{pleno}/keputusan', [\App\Http\Controllers\Operasi\PlenoKeputusanController::class, 'store'])
         ->name('insiden.pleno.keputusan.store');
    Route::delete('insiden/{insiden}/pleno/{pleno}/keputusan/{keputusan}', [\App\Http\Controllers\Operasi\PlenoKeputusanController::class, 'destroy'])
         ->name('insiden.pleno.keputusan.destroy');

    // Penugasan Personel Web (Blade)
    Route::prefix('insiden/{insiden}/penugasan')->name('insiden.penugasan.')->controller(\App\Http\Controllers\Operasi\PenugasanController::class)->group(function () {
        Route::get('/',                 'index')->name('index');
        Route::get('/create',           'create')->name('create');
        Route::post('/',                'store')->name('store');
        Route::get('/{penugasan}',      'show')->name('show');
        Route::get('/{penugasan}/edit', 'edit')->name('edit');
        Route::put('/{penugasan}',      'update')->name('update');
        Route::patch('/{penugasan}/status', 'updateStatus')->name('status');
        Route::delete('/{penugasan}',   'destroy')->name('destroy');
        Route::post('/{penugasan}/terbitkan-surat-tugas', [\App\Http\Controllers\Operasi\TerbitkanSuratTugasController::class, 'store'])->name('terbitkan-surat-tugas');
    });

    // Pos Aju Web (Blade) — legacy flat routes
    Route::prefix('posaju')->name('posaju.')->controller(PosAjuWebController::class)->group(function () {
        Route::get('/',                 'index')->name('index');
        Route::get('/create',           'create')->name('create');
        Route::post('/',                'store')->name('store');
        Route::get('/{posaju}',         'show')->name('show');
        Route::post('/{posaju}/activate', 'activate')->name('activate');
        Route::post('/{posaju}/close',  'close')->name('close');
        Route::post('/{posaju}/extend', 'extend')->name('extend');
    });

    // Pos Aju Web — scoped under insiden
    Route::prefix('insiden/{insiden}/posaju')->name('insiden.posaju.')->controller(PosAjuWebController::class)->group(function () {
        Route::get('/',                 'index')->name('index');
        Route::get('/create',           'create')->name('create');
        Route::post('/',                'store')->name('store');
        Route::get('/{posaju}',         'show')->name('show');
        Route::post('/{posaju}/activate', 'activate')->name('activate');
        Route::post('/{posaju}/tutup',  'tutup')->name('tutup');
        Route::post('/{posaju}/perpanjang', 'extend')->name('extend');
    });
    Route::post('insiden/{insiden}/posaju/{posaju}/komandan', [\App\Http\Controllers\Operasi\PosajuKomandanController::class, 'store'])
        ->name('insiden.posaju.komandan.store');
    Route::delete('insiden/{insiden}/posaju/{posaju}/komandan/{komandan}', [\App\Http\Controllers\Operasi\PosajuKomandanController::class, 'destroy'])
        ->name('insiden.posaju.komandan.destroy');

    // Feedback Klaster Web (Blade)
    Route::prefix('insiden/{insiden}/feedback-klaster')
        ->name('insiden.feedback-klaster.')
        ->controller(\App\Http\Controllers\Operasi\FeedbackKlasterController::class)
        ->group(function () {
            Route::get('/',                 'index')->name('index');
            Route::get('/buat',             'create')->name('create');
            Route::post('/',                'store')->name('store');
            Route::get('/{feedback}',       'show')->name('show');
        });

    // Distribusi Bantuan Web (Blade)
Route::prefix('insiden/{insiden}/posaju/{posaju}/distribusi')
        ->name('insiden.posaju.distribusi.')
        ->controller(\App\Http\Controllers\Operasi\DistribusiWebController::class)
        ->group(function () {
            Route::get('/',                 'index')->name('index');
            Route::get('/buat',             'create')->name('create');
            Route::post('/',                'store')->name('store');
            Route::get('/{distribusi}',     'show')->name('show');
            Route::post('/{distribusi}/distribusikan', 'distribusikan')->name('distribusikan');
            Route::post('/{distribusi}/terima',        'terima')->name('terima');
            Route::post('/{distribusi}/feedback',      'feedback')->name('feedback');
        });

    // Logistik Permintaan Web (Blade)
    Route::prefix('logistik/permintaan')->name('logistik.permintaan.')->controller(\App\Http\Controllers\Logistik\PermintaanWebController::class)->group(function () {
        Route::get('/',             'index')->name('index');
        Route::get('/create',       'create')->name('create');
        Route::post('/',            'store')->name('store');
        Route::patch('/{permintaan}/setujui', 'setujui')->name('setujui');
    });

    // Klaster Web (Blade)
    Route::prefix('klaster')->name('klaster.')->controller(KlasterWebController::class)->group(function () {
        Route::get('/',                 'index')->name('index');
        Route::get('/create',           'create')->name('create');
        Route::post('/',                'store')->name('store');
        Route::get('/{klaster}',        'show')->name('show');
        Route::post('/{klaster}/progress', 'updateProgress')->name('progress');
        Route::post('/{klaster}/complete', 'complete')->name('complete');
    });

    // Sitrep Web (Blade)
    Route::prefix('sitrep')->name('sitrep.')->controller(SitrepWebController::class)->group(function () {
        Route::get('/',                 'index')->name('index');
        Route::get('/create',           'create')->name('create');
        Route::post('/',                'store')->name('store');
        Route::get('/{sitrep}',         'show')->name('show');
        Route::get('/{sitrep}/pdf',     'pdf')->name('pdf');
    });

    // Governance — Eskalasi (langsung di bawah insiden)
    Route::post('insiden/{insiden}/eskalasi', [EskalasiController::class, 'store'])->name('insiden.eskalasi.store');

    // Command Center moved outside to allow relawan
});

// Governance — Surat
use App\Http\Controllers\Governance\SuratController;
use App\Http\Controllers\Governance\SuratParafController;

Route::middleware(['auth', 'role:super_admin,pwnu,pcnu', 'active_approver'])
     ->prefix('surat')
     ->name('surat.')
     ->group(function () {

    Route::get('/',             [SuratController::class, 'index'])->name('index');
    Route::get('/buat',         [SuratController::class, 'create'])->name('create');
    Route::post('/',            [SuratController::class, 'store'])->name('store');
    Route::get('/{surat}',      [SuratController::class, 'show'])->name('show');
    Route::get('/{surat}/edit', [SuratController::class, 'edit'])->name('edit');
    Route::put('/{surat}',      [SuratController::class, 'update'])->name('update');

    Route::patch('/{surat}/kirim-review', [SuratController::class, 'kirimReview'])
         ->name('kirim-review');
    Route::patch('/{surat}/finalisasi',   [SuratController::class, 'finalisasi'])
         ->name('finalisasi');
    Route::get('/{surat}/pdf',            [SuratController::class, 'downloadPdf'])
         ->name('pdf');

    Route::post('/{surat}/paraf',
                [SuratParafController::class, 'store'])->name('paraf.store');
    Route::patch('/{surat}/paraf/{paraf}',
                 [SuratParafController::class, 'update'])->name('paraf.update');
});

// ============================================================
// 3 NEW DOMAIN UI DEMO ROUTES
// ============================================================
Route::middleware(['auth'])->group(function () {
    // Assessment Extension
    Route::get('/operasi/assessment/form-lengkap', function() { return view('operasi.assessment.form-lengkap'); })->name('operasi.assessment.form-lengkap');
    Route::get('/operasi/insiden/{insiden}/assessment/{assessment}/skor', [\App\Http\Controllers\Operasi\AssessmentController::class, 'skor'])->name('operasi.assessment.skor');
    Route::get('/operasi/assessment/laporan', function() { return view('operasi.assessment.laporan'); })->name('operasi.assessment.laporan');
    
    // Inventaris PWNU
    Route::get('/inventaris', function() { return view('inventaris.index'); })->name('inventaris.index');
    Route::get('/inventaris/create', function() { return view('inventaris.create'); })->name('inventaris.create');
    Route::get('/inventaris/{id}', function() { return view('inventaris.show'); })->name('inventaris.show');
    
    // Histori & Trend
    Route::get('/histori', function() { return view('histori.index'); })->name('histori.index');
    Route::get('/histori/bencana', function() { return view('histori.bencana.index'); })->name('histori.bencana.index');
    Route::get('/histori/analisis', function() { return view('histori.analisis.wilayah'); })->name('histori.analisis.wilayah');
});

require __DIR__.'/auth.php';
