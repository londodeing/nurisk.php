<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$policy = \Gate::getPolicyFor(App\Models\OperasiKlaster::class);
echo "Policy class: " . get_class($policy) . "\n";
