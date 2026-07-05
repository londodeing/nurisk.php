<?php
$f = 'tests/Feature/Operasi/Mobilisasi/MobilisasiSyncTest.php';
$c = file_get_contents($f);
$c = str_replace(
    "'action' => 'update',",
    "'action' => 'upsert',",
    $c
);
file_put_contents($f, $c);
