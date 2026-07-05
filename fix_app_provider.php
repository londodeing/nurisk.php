<?php
$f = 'app/Providers/AppServiceProvider.php';
$c = file_get_contents($f);
$c = str_replace('
        \Illuminate\Support\Facades\Gate::policy(
            \App\Models\OperasiKlaster::class,
            \App\Policies\KlasterPolicy::class
        );', '', $c);
file_put_contents($f, $c);
