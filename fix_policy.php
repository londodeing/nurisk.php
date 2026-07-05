<?php
$f = 'app/Http/Controllers/Api/Operasi/KlasterApiController.php';
$c = file_get_contents($f);
$c = str_replace(
    '$this->authorize(\'create\', [OperasiKlaster::class, $insiden]);',
    '$this->authorize(\'create\', OperasiKlaster::class);',
    $c
);
file_put_contents($f, $c);
