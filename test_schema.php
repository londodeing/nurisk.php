<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
Config::set('database.default', 'sqlite');
Config::set('database.connections.sqlite.database', ':memory:');
Artisan::call('migrate', ['--force' => true]);
$res = DB::select("SELECT sql FROM sqlite_master WHERE type='table' AND name='operasi_penugasan'");
echo $res[0]->sql;
