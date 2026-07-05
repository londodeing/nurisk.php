<?php
$f = 'app/Http/Controllers/Api/Operasi/OperasiKlasterController.php';
$c = file_get_contents($f);
$c = str_replace(
    '$klaster = OperasiKlaster::create($request->validated());',
    '
    $data = $request->validated();
    $insiden = \App\Models\OperasiInsiden::where("uuid_insiden", $data["uuid_insiden"])->firstOrFail();
    $data["id_insiden"] = $insiden->id_insiden;
    unset($data["uuid_insiden"]);
    $klaster = OperasiKlaster::create($data);
    ',
    $c
);
file_put_contents($f, $c);
