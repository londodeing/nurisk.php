<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$service = app(\App\Services\Auth\AuthenticationService::class);
try {
    $res = $service->login(['no_hp' => '08111111111', 'kata_sandi' => 'password']);
    echo "Login Result: " . ($res ? 'SUCCESS' : 'FALSE') . "\n";
} catch (\Illuminate\Validation\ValidationException $e) {
    echo "Validation Exception: ";
    print_r($e->errors());
} catch (\Exception $e) {
    echo "Exception: " . $e->getMessage() . "\n";
}
