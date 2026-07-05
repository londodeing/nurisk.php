<?php
$lines = file('storage/logs/laravel.log');
for($i=count($lines)-1; $i>=0; $i--) {
    if(strpos($lines[$i], 'local.ERROR') !== false) {
        echo $lines[$i] . "\n";
        break;
    }
}
