<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$request = \Illuminate\Http\Request::create('/lapor', 'POST', [
    'nama' => 'Test User',
    'no_hp' => '08123456789',
    'id_jenis_bencana' => 1,
    'lokasi' => 'Rumah',
    'deskripsi' => 'Kebakaran',
    'latitude' => -6.7,
    'longitude' => 111.0,
    'waktu_kejadian' => now()->format('Y-m-d H:i:s'),
]);

// Handle the request to trigger the controller
$response = $kernel->handle($request);
echo "Status: " . $response->getStatusCode() . "\n";
if ($response->isRedirect()) {
    echo "Redirect: " . $response->headers->get('Location') . "\n";
    echo "Session Success: " . session('success') . "\n";
    echo "Session Error: " . session('error') . "\n";
    if (session()->has('errors')) {
        echo "Validation Errors:\n";
        print_r(session('errors')->getBag('default')->getMessages());
    }
} else {
    echo "Body: " . substr($response->getContent(), 0, 500) . "\n";
}

$count = \App\Models\LaporanKejadian::count();
echo "Total Laporan: " . $count . "\n";
