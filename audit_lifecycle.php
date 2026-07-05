<?php
$models = [
    'App\Models\LaporanKejadian',
    'App\Models\OperasiInsiden',
    'App\Models\AssessmentUtama',
    'App\Models\OperasiSitrep',
    'App\Models\OperasiPosaju',
    'App\Models\OperasiPenugasan',
    'App\Models\OperasiMobilisasi',
    'App\Models\OperasiPleno',
];

echo "Database Audit:\n";
foreach ($models as $m) {
    if (class_exists($m)) {
        $inst = new $m;
        $table = $inst->getTable();
        $exists = \Illuminate\Support\Facades\Schema::hasTable($table) ? 'YES' : 'NO';
        echo "- Model: $m | Table: $table | Exists: $exists\n";
    } else {
        echo "- Model: $m | DOES NOT EXIST\n";
    }
}
