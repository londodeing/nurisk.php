<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle(Illuminate\Http\Request::capture());

$authUser = App\Models\AuthUser::where('nama_peran', 'super_admin')
    ->join('auth_roles', 'auth_users.id_peran', '=', 'auth_roles.id_peran')
    ->first();
if (!$authUser) {
    $authUser = App\Models\AuthUser::first();
}
echo "AuthUser ID: " . $authUser->id_pengguna . "\n";

Illuminate\Support\Facades\Auth::shouldReceive('guard')->with('sanctum')->andReturnSelf();
Illuminate\Support\Facades\Auth::shouldReceive('user')->andReturn($authUser);

$request = Illuminate\Http\Request::create('/api/account/home', 'GET');
$request->setUserResolver(fn() => $authUser);

$controller = app()->make(App\Http\Controllers\Api\AccountHomeController::class);
$response = $controller->index($request);

file_put_contents('runtime_home.json', json_encode(json_decode($response->getContent()), JSON_PRETTY_PRINT));

$ctx = app()->make(\App\Services\Auth\AuthorizationContextService::class);
$composer = new App\Services\Sdui\Scenes\AkunSceneComposer($authUser, $ctx);
$legacyData = $composer->compose();
file_put_contents('legacy_home.json', json_encode($legacyData, JSON_PRETTY_PRINT));

// Extract identity section parts
$l = json_decode(file_get_contents('legacy_home.json'), true);
file_put_contents('legacy_identity.json', json_encode($l['root']['children'][0], JSON_PRETTY_PRINT));
$r = json_decode(file_get_contents('runtime_home.json'), true);
file_put_contents('runtime_identity.json', json_encode($r['nodes'][0]['children'][0]['children'][0], JSON_PRETTY_PRINT));
echo "Dumped JSONs.\n";
