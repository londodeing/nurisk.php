<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\Sdui\Scenes\AkunSceneComposer;
use App\Services\Auth\AuthorizationContextService;
use App\Models\AuthUser;
use Illuminate\Support\Facades\DB;

// Mock User
$user = AuthUser::first() ?? new AuthUser(['id_pengguna' => 1]);
$ctx = app(AuthorizationContextService::class);

// 1. DUMP LEGACY JSON
$composer = new AkunSceneComposer($user, $ctx);
$legacyJson = $composer->compose();
file_put_contents('legacy_dump.json', json_encode($legacyJson, JSON_PRETTY_PRINT));

// 2. DUMP RUNTIME JSON
$profileData = (array) DB::selectOne("
    SELECT u.id_pengguna, u.no_hp, u.status_akun, p.nama_lengkap
    FROM auth_users u
    LEFT JOIN auth_pengguna_profil p ON u.id_pengguna = p.id_pengguna
    WHERE u.id_pengguna = ?
", [$user->id_pengguna]);

$activeRole = (array) DB::selectOne("
    SELECT mj.nama_jabatan
    FROM pengguna_jabatan pj
    JOIN master_jabatan mj ON pj.id_jabatan_posisi = mj.id_jabatan_posisi
    WHERE pj.id_pengguna = ? AND pj.status_aktif = 1
    LIMIT 1
", [$user->id_pengguna]);

$screen = \App\Services\Sdui\Runtime\Screens\AccountWorkspaceScreen::build($profileData, $activeRole);

$engine = new \App\Services\Sdui\Runtime\Certification\RuntimeCertificationEngine(
    new \App\Services\Sdui\Runtime\Certification\StructuralValidator(),
    new \App\Services\Sdui\Runtime\Certification\SemanticValidator(),
    new \App\Services\Sdui\Runtime\Certification\RuntimeNormalizer()
);

$result = $engine->certify($screen);
$serializer = new \App\Services\Sdui\Runtime\Serializer\SduiSerializer();
$runtimeJson = $serializer->serialize($result->certifiedRuntime);

file_put_contents('runtime_dump.json', json_encode($runtimeJson, JSON_PRETTY_PRINT));

echo "DUMP COMPLETE.\n";
echo "Legacy Nodes Count: " . countLegacyRecursive($legacyJson['root'] ?? $legacyJson) . "\n";
echo "Runtime Nodes Count: " . countRecursive($runtimeJson) . "\n";

function countRecursive($array) {
    $count = 1; // Count current node
    if (isset($array['children']) && is_array($array['children'])) {
        foreach ($array['children'] as $child) {
            $count += countRecursive($child);
        }
    }
    return $count;
}

function countLegacyRecursive($array) {
    $count = 1;
    if (isset($array['children']) && is_array($array['children'])) {
        foreach ($array['children'] as $child) {
            $count += countLegacyRecursive($child);
        }
    }
    return $count;
}
