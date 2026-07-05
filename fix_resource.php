<?php
$f = 'app/Http/Resources/Operasi/OperasiKlasterResource.php';
$c = file_get_contents($f);
$c = str_replace(
    '\'id\'        => $this->id_klaster_operasi,',
    '\'id\'        => $this->uuid_klaster_operasi,',
    $c
);
file_put_contents($f, $c);
