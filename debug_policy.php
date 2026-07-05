<?php
$f = 'app/Policies/OperasiKlasterPolicy.php';
$c = file_get_contents($f);
$c = str_replace(
    'return $this->authContext->hasAnyRole([\'super_admin\', \'pwnu\', \'pcnu\']);',
    '
    \Log::info("User role: " . $this->authContext->getRoleName());
    return $this->authContext->hasAnyRole([\'super_admin\', \'pwnu\', \'pcnu\']);
    ',
    $c
);
file_put_contents($f, $c);
