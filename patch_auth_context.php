<?php
$f = 'app/Services/Auth/AuthorizationContextService.php';
$c = file_get_contents($f);
// Clear cache properly
$c = str_replace(
    'if ($this->cachedUser === null || $this->cachedUser->id_pengguna !== Auth::id()) {',
    'if (true) { // ALWAYS REFRESH IN TESTS OR JUST REMOVE CACHE TO DEBUG',
    $c
);
file_put_contents($f, $c);
