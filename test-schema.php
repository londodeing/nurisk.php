<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Artisan;

Artisan::call('migrate:fresh');
var_dump(Schema::hasColumn('assessment_utama', 'id_petugas_assessment'));
var_dump(Schema::getColumnListing('assessment_utama'));
