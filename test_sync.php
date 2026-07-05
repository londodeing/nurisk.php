<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$request = Illuminate\Http\Request::create('/api/v1/sync', 'POST', [
    'request_id' => Illuminate\Support\Str::uuid()->toString(),
    'device_uuid' => 'client-device-001',
    'cursors' => ['penugasan' => 0],
    'changes' => [
        [
            'table' => 'operasi_penugasan',
            'action' => 'upsert',
            'data' => [
                'uuid_penugasan' => Illuminate\Support\Str::uuid()->toString(),
                'uuid_insiden' => App\Models\OperasiInsiden::first()->uuid_insiden,
                'id_pengguna' => 1,
                'peran_otoritas' => 'trc',
                'status_penugasan' => 'aktif',
                'waktu_mulai' => now()->format('Y-m-d H:i:s'),
                'ditugaskan_oleh' => 1,
                'sync_version' => 1
            ]
        ]
    ]
]);

$response = app()->handle($request);
echo $response->getContent();
