<?php
// Cleanup HttpKlasterTest
$f = 'tests/Feature/Operasi/HttpKlasterTest.php';
$c = file_get_contents($f);
$c = str_replace('->dump()->assertStatus(201)', '->assertStatus(201)', $c);
$c = preg_replace('/\\\\Log::info\("Testing route: " \. route\(\'api\.operasi\.klaster\.store\'\)\);\s+/', '', $c);
file_put_contents($f, $c);

// Cleanup OperasiKlasterController
$fc = 'app/Http/Controllers/Api/Operasi/OperasiKlasterController.php';
$cc = file_get_contents($fc);
$cc = preg_replace('/\\\\Log::info\("In OperasiKlasterController@store.*?\n\s+\\\\Log::info\("Role: ".*?\n\s+/s', '', $cc);
file_put_contents($fc, $cc);
