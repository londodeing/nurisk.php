<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

\Illuminate\Support\Facades\Auth::loginUsingId(1);
$request = \Illuminate\Http\Request::create('/governance/approval', 'GET');
$response = $kernel->handle($request);
echo "Status: " . $response->getStatusCode() . "\n";
if ($response->getStatusCode() !== 200) {
    echo "Error: " . strip_tags($response->getContent());
}
