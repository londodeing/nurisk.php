<?php

$file = 'routes/api.php';
$content = file_get_contents($file);

$route = <<<'PHP'
    Route::post('/sync', [SyncApiController::class, 'sync'])->name('api.v1.sync');
    Route::get('/sync/status', [SyncApiController::class, 'status'])->name('api.v1.sync.status');
PHP;

$routeNew = <<<'PHP'
    Route::post('/sync', [SyncApiController::class, 'sync'])->name('api.v1.sync');
    Route::get('/sync/status', [SyncApiController::class, 'status'])->name('api.v1.sync.status');

    // Mobilisasi
    Route::apiResource('mobilisasi', \App\Http\Controllers\Api\Operasi\MobilisasiApiController::class)
        ->parameters(['mobilisasi' => 'uuid'])
        ->names('api.v1.mobilisasi');
        
    Route::post('mobilisasi/{uuid}/approve', [\App\Http\Controllers\Api\Operasi\MobilisasiApiController::class, 'approve'])->name('api.v1.mobilisasi.approve');
    Route::post('mobilisasi/{uuid}/depart', [\App\Http\Controllers\Api\Operasi\MobilisasiApiController::class, 'depart'])->name('api.v1.mobilisasi.depart');
    Route::post('mobilisasi/{uuid}/arrive', [\App\Http\Controllers\Api\Operasi\MobilisasiApiController::class, 'arrive'])->name('api.v1.mobilisasi.arrive');
    Route::post('mobilisasi/{uuid}/finish', [\App\Http\Controllers\Api\Operasi\MobilisasiApiController::class, 'finish'])->name('api.v1.mobilisasi.finish');
    Route::post('mobilisasi/{uuid}/cancel', [\App\Http\Controllers\Api\Operasi\MobilisasiApiController::class, 'cancel'])->name('api.v1.mobilisasi.cancel');
PHP;

$content = str_replace($route, $routeNew, $content);
file_put_contents($file, $content);
