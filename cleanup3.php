<?php
// Cleanup dump from MobilisasiApiTest
$f = 'tests/Feature/Operasi/Mobilisasi/MobilisasiApiTest.php';
$c = file_get_contents($f);
$c = str_replace('->dump()->assertJsonPath', '->assertJsonPath', $c);
file_put_contents($f, $c);
