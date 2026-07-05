<?php
$f = 'app/Http/Controllers/Api/Operasi/OperasiKlasterController.php';
$c = file_get_contents($f);
$c = str_replace(
    '$this->authorize(\'create\', OperasiKlaster::class);',
    '
    \Log::info("In OperasiKlasterController@store. User ID: " . \Auth::id());
    \Log::info("Role: " . app(\App\Services\Auth\AuthorizationContextService::class)->getRoleName());
    $this->authorize(\'create\', OperasiKlaster::class);
    ',
    $c
);
file_put_contents($f, $c);
