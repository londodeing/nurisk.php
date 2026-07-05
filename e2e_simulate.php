<?php

require __DIR__.'/vendor/autoload.php';

use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;

$jar = new CookieJar();
$client = new Client([
    'base_uri' => 'http://127.0.0.1:8000',
    'cookies' => $jar,
    'http_errors' => false,
    'allow_redirects' => true
]);

function getCsrfToken($html) {
    preg_match('/<meta name="csrf-token" content="([^"]+)">/', $html, $matches);
    if (!empty($matches[1])) return $matches[1];
    preg_match('/name="_token" value="([^"]+)"/', $html, $matches);
    return $matches[1] ?? '';
}

echo "1. Registering Admin PCNU Kudus...\n";
$res = $client->get('/daftar/admin_pcnu');
$csrf = getCsrfToken((string)$res->getBody());

$res = $client->post('/daftar/admin_pcnu', [
    'form_params' => [
        '_token' => $csrf,
        'no_hp' => '08444444444',
        'kata_sandi' => 'password123',
        'kata_sandi_confirmation' => 'password123',
        'nama_lengkap' => 'Admin Kudus Baru',
        'id_kabupaten' => '3319',
        'id_kecamatan' => '331901',
        'id_desa' => '3319012001',
        'alamat_deskriptif' => 'Jl. Kudus No 1'
    ]
]);

$body = (string)$res->getBody();
if (strpos($body, 'berhasil dikirim') !== false || strpos($body, 'Menunggu Persetujuan') !== false) {
    echo "=> Register success! Waiting for approval...\n";
} else {
    echo "=> Register failed! Status code: " . $res->getStatusCode() . "\n";
    echo substr($body, 0, 1000) . "\n";
    exit(1);
    if (!empty($errs[1])) {
        foreach($errs[1] as $err) echo " - $err\n";
    }
    preg_match('/<strong>Terjadi Kesalahan:<\/strong> (.*?)\s*<\/div>/s', $body, $err);
    if (!empty($err[1])) echo " - " . strip_tags($err[1]) . "\n";
    exit(1);
}

// Clear cookies to simulate new browser session
$jar->clear();

echo "\n2. Logging in as PWNU Admin...\n";
$res = $client->get('/login');
$csrf = getCsrfToken((string)$res->getBody());
$res = $client->post('/login', [
    'form_params' => [
        '_token' => $csrf,
        'no_hp' => '08111111111',
        'kata_sandi' => 'password'
    ]
]);
$body = (string)$res->getBody();
if (strpos($body, 'Dashboard') !== false) {
    echo "=> Logged in as PWNU Admin!\n";
} else {
    echo "=> Login failed!\n";
    exit(1);
}

echo "\n3. Approving Admin Kudus...\n";
// Find the id_pengguna for Admin Kudus
// Need to parse from the /dashboard/admin/pengguna HTML
$res = $client->get('/dashboard/admin/pengguna');
$html = (string)$res->getBody();
if (preg_match('/action="[^"]*\/pengguna\/(\d+)\/setujui"/', $html, $matches)) {
    $idPengguna = $matches[1];
    echo "=> Found pending user ID: $idPengguna\n";
    
    // Get a fresh CSRF from the page
    $csrf = getCsrfToken($html);
    $res = $client->post("/dashboard/admin/pengguna/$idPengguna/setujui", [
        'form_params' => [
            '_token' => $csrf,
            '_method' => 'PATCH'
        ]
    ]);
    if (strpos((string)$res->getBody(), 'berhasil disetujui') !== false) {
        echo "=> User approved successfully!\n";
    } else {
        echo "=> Failed to approve user.\n";
        exit(1);
    }
} else {
    echo "=> No pending user found to approve.\n";
    exit(1);
}

// Logout PWNU
echo "\n4. Logging out PWNU...\n";
$res = $client->post('/logout', [
    'form_params' => [
        '_token' => $csrf
    ]
]);
$jar->clear();

echo "\n5. Logging in as newly approved Admin Kudus...\n";
$res = $client->get('/login');
$csrf = getCsrfToken((string)$res->getBody());
$res = $client->post('/login', [
    'form_params' => [
        '_token' => $csrf,
        'no_hp' => '08333333333',
        'kata_sandi' => 'password123'
    ]
]);

$body = (string)$res->getBody();
if (strpos($body, 'Dashboard') !== false) {
    echo "=> E2E TEST PASSED! Logged in as Admin Kudus successfully.\n";
} else {
    echo "=> E2E TEST FAILED! Could not login as Admin Kudus.\n";
    preg_match('/class="text-danger"[^>]*>(.*?)<\/span>/s', $body, $err);
    if (!empty($err[1])) echo strip_tags($err[1]) . "\n";
    exit(1);
}
