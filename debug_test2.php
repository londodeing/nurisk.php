<?php
$f = 'tests/Feature/Operasi/Mobilisasi/MobilisasiApiTest.php';
$c = file_get_contents($f);
$c = str_replace(
    "->assertJsonPath('data.status_mobilisasi', 'draft');",
    "->dump()->assertJsonPath('data.status_mobilisasi', 'draft');",
    $c
);
file_put_contents($f, $c);
