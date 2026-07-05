<?php
$f = 'tests/Feature/Operasi/ConflictResolutionTest.php';
$c = file_get_contents($f);
$c = str_replace(
    '->assertStatus(409)',
    '->dump()->assertStatus(409)',
    $c
);
file_put_contents($f, $c);
