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
echo "--- DB Audit ---\n";
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
echo "--- Routes Audit ---\n";
$routes = Route::getRoutes();
$keywords = ['laporan', 'insiden', 'assessment', 'sitrep', 'posaju', 'penugasan', 'mobilisasi', 'pleno'];
foreach ($keywords as $k) {
    echo "Routes containing '$k':\n";
    foreach ($routes as $route) {
        if (str_contains($route->uri(), $k)) {
            echo "  [" . implode('|', $route->methods()) . "] " . $route->uri() . " -> " . $route->getActionName() . "\n";
        }
    }
}
