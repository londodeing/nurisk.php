<?php

use Illuminate\Support\Facades\Broadcast;
use App\Models\OperasiInsiden;
use App\Models\OperasiPosaju;
use App\Models\OperasiKlaster;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id_pengguna === (int) $id;
});

Broadcast::channel('insiden.{id}', function ($user, $id) {
    $insiden = OperasiInsiden::find($id);
    if (!$insiden) return false;
    return $user->can('view', $insiden);
});

Broadcast::channel('posaju.{id}', function ($user, $id) {
    $posaju = OperasiPosaju::find($id);
    if (!$posaju) return false;
    return $user->can('view', $posaju->insiden);
});

Broadcast::channel('klaster.{id}', function ($user, $id) {
    $klaster = OperasiKlaster::find($id);
    if (!$klaster) return false;
    return $user->can('view', $klaster->insiden);
});
