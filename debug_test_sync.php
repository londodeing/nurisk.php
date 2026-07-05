<?php
$f = 'tests/Feature/Operasi/Mobilisasi/MobilisasiSyncTest.php';
$c = file_get_contents($f);
$c = str_replace(
    '$response->assertStatus(409)',
    '$response->dump()->assertStatus(409)',
    $c
);
file_put_contents($f, $c);
