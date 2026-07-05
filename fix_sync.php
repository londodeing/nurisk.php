<?php
$f = 'app/Http/Controllers/Api/Operasi/SyncApiController.php';
$c = file_get_contents($f);
$c = str_replace(
    'return response()->json($responseData, 200);',
    '
    if (!empty($conflictsResponse)) {
        $responseData["success"] = false;
        $responseData["message"] = "Conflict detected";
        return response()->json($responseData, 409);
    }
    return response()->json($responseData, 200);
    ',
    $c
);
file_put_contents($f, $c);
