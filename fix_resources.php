<?php
// Fix KlasterResource
$f = 'app/Http/Resources/Operasi/KlasterResource.php';
$c = file_get_contents($f);
$c = str_replace("'uuid_klaster_operasi' => \$this->uuid_klaster_operasi,", "'id' => \$this->uuid_klaster_operasi,", $c);
file_put_contents($f, $c);

// Fix OperasiKlasterResource (if it exists)
$f = 'app/Http/Resources/Operasi/OperasiKlasterResource.php';
if(file_exists($f)) {
    $c = file_get_contents($f);
    $c = str_replace("'uuid_klaster_operasi' => \$this->uuid_klaster_operasi,", "'id' => \$this->uuid_klaster_operasi,", $c);
    file_put_contents($f, $c);
}

// Fix PosajuResource
$f = 'app/Http/Resources/Operasi/PosajuResource.php';
if(file_exists($f)) {
    $c = file_get_contents($f);
    $c = preg_replace("/'uuid_posaju' => \\\$this->uuid_posaju,/", "'id' => \$this->uuid_posaju,", $c);
    file_put_contents($f, $c);
}

// Fix OperasiPosajuResource
$f = 'app/Http/Resources/Operasi/OperasiPosajuResource.php';
if(file_exists($f)) {
    $c = file_get_contents($f);
    $c = preg_replace("/'uuid_posaju' => \\\$this->uuid_posaju,/", "'id' => \$this->uuid_posaju,", $c);
    file_put_contents($f, $c);
}
echo "Done";
