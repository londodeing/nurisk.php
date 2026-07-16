<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// login as user 1
\Illuminate\Support\Facades\Auth::loginUsingId(1);

$insiden = \App\Models\OperasiInsiden::where('uuid_insiden', '60120475-252a-4f65-8e36-089b1c5e1b1b')->first();
$assessment = $insiden->assessments()->first();

$controller = $app->make(\App\Http\Controllers\Api\AssessmentApiController::class);

// Try to mimic a POST request
$request = \App\Http\Requests\Operasi\StoreAssessmentRequest::create(
    '/api/v1/insiden/60120475-252a-4f65-8e36-089b1c5e1b1b/assessment',
    'POST',
    [
        'uuid_insiden' => '60120475-252a-4f65-8e36-089b1c5e1b1b',
        'jenis_laporan' => 'pendataan_lanjutan',
        'lokasi_detail' => [
            'id_kec' => '331906',
            'id_desa' => '3319062002',
            'alamat_spesifik' => 'Test'
        ]
    ]
);

try {
    $response = $controller->store($request, $insiden);
    echo $response->content();
} catch (\Illuminate\Validation\ValidationException $e) {
    echo json_encode($e->errors());
} catch (\Exception $e) {
    echo $e->getMessage();
}
