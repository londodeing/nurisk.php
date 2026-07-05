<?php
// Cleanup dump from SyncConflictTest
$f = 'tests/Feature/SyncConflictTest.php';
$c = file_get_contents($f);
$c = str_replace('->dump()->assertStatus', '->assertStatus', $c);
file_put_contents($f, $c);
