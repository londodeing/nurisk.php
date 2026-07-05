<?php
// Attempt to figure out if SQLite is still active
$contents = file_get_contents('http://127.0.0.1:8000/login');
preg_match('/<meta name="csrf-token" content="(.*?)">/', $contents, $matches);
$csrf = $matches[1] ?? '';

$ch = curl_init('http://127.0.0.1:8000/login');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, [
    '_token' => $csrf,
    'no_hp' => '08111111111',
    'kata_sandi' => '12345678'
]);
curl_setopt($ch, CURLOPT_COOKIE, "XSRF-TOKEN={$csrf};");
curl_setopt($ch, CURLOPT_HEADER, true);
$response = curl_exec($ch);
curl_close($ch);
echo $response;
