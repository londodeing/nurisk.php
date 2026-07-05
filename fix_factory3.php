<?php
$files = [
    'tests/Unit/AuthenticationServiceTest.php',
    'tests/Unit/AuthorizationContextServiceTest.php',
    'tests/Unit/AuthFactoryTest.php',
    'tests/Feature/CheckAccountStatusTest.php',
    'tests/Feature/Auth/LogoutTest.php',
    'tests/Feature/Auth/LoginControllerTest.php',
    'tests/Feature/Auth/LoginRouteTest.php',
    'tests/Feature/ScopeEnclosureTest.php',
    'tests/Feature/RefreshAuthorizationContextTest.php',
    'tests/Feature/RoleMiddlewareTest.php'
];

foreach ($files as $f) {
    if (file_exists($f)) {
        $c = file_get_contents($f);
        $c = preg_replace('/AuthRole::factory\(\)->create\(\[.*?\]\);/s', 'AuthRole::firstOrCreate([\'nama_peran\' => \'super_admin\'], [\'level_otoritas\' => 1]);', $c);
        $c = preg_replace('/AuthRole::factory\(\)->make\(\);/', 'AuthRole::firstOrNew([\'nama_peran\' => \'super_admin\'], [\'level_otoritas\' => 1]);', $c);
        file_put_contents($f, $c);
        echo "Fixed $f\n";
    }
}
