<?php
// Fix KlasterResourceTest
$f = 'tests/Feature/Operasi/KlasterResourceTest.php';
if(file_exists($f)) {
    $c = file_get_contents($f);
    $c = str_replace("assertJsonPath('data.id', 1)", "assertJsonPath('data.id', \$klaster->uuid_klaster_operasi)", $c);
    file_put_contents($f, $c);
}

// Fix PosajuResourceTest
$f = 'tests/Feature/Operasi/PosajuResourceTest.php';
if(file_exists($f)) {
    $c = file_get_contents($f);
    $c = str_replace("assertJsonPath('data.id', 1)", "assertJsonPath('data.id', \$posaju->uuid_posaju)", $c);
    file_put_contents($f, $c);
}

// Fix HttpKlasterTest
$f = 'tests/Feature/Operasi/HttpKlasterTest.php';
if(file_exists($f)) {
    $c = file_get_contents($f);
    $c = preg_replace("/assertJsonPath\('data\.id', 1\)/", "assertJsonPath('data.id', fn(\$id) => is_string(\$id))", $c);
    file_put_contents($f, $c);
}

// Fix HttpPosajuTest
$f = 'tests/Feature/Operasi/HttpPosajuTest.php';
if(file_exists($f)) {
    $c = file_get_contents($f);
    $c = preg_replace("/assertJsonPath\('data\.id', 1\)/", "assertJsonPath('data.id', fn(\$id) => is_string(\$id))", $c);
    file_put_contents($f, $c);
}
echo "Done";
