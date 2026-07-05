<?php

$file = 'tests/Feature/Operasi/Mobilisasi/MobilisasiSyncTest.php';
$content = file_get_contents($file);

$content = str_replace(
    "'device_id' => 'device-test',",
    "'device_uuid' => (string) \Illuminate\Support\Str::uuid(),",
    $content
);

$content = str_replace(
    "'request_id' => 'req-test-mob-1',",
    "'request_id' => (string) \Illuminate\Support\Str::uuid(),",
    $content
);

$content = str_replace(
    "'request_id' => 'req-test-mob-2',",
    "'request_id' => (string) \Illuminate\Support\Str::uuid(),",
    $content
);

file_put_contents($file, $content);
