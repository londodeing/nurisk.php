<?php

declare(strict_types=1);

/**
 * Token generation script for load testing.
 * Bootstraps Laravel, creates test users (if not exist),
 * generates Sanctum tokens, and outputs JSON array.
 *
 * Usage: php tests/k6/generate-tokens.php
 * Output: JSON array of {token: string, role: string} objects
 */

require __DIR__ . '/../../vendor/autoload.php';

$app = require_once __DIR__ . '/../../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\AuthUser;
use Illuminate\Support\Facades\Hash;

// Ensure roles exist (idempotent)
\Illuminate\Support\Facades\Artisan::call('db:seed', [
    '--class' => 'Database\Seeders\AuthRoleSeeder',
    '--force' => true,
]);

$password = 'loadtest123';
$passHash = Hash::make($password);
$tokens = [];

// ── Super admin ─────────────────────────────────────────────────
$superAdmin = AuthUser::firstOrCreate(
    ['no_hp' => '081200009001'],
    [
        'id_peran' => 1,
        'kata_sandi' => $passHash,
        'status_akun' => 'aktif',
        'is_tersedia' => 1,
    ]
);
$token = $superAdmin->createToken('load-test')->plainTextToken;
$tokens[] = ['token' => $token, 'role' => 'super_admin'];
echo "super_admin token generated.\n";

// ── Relawan ─────────────────────────────────────────────────────
$relawan = AuthUser::firstOrCreate(
    ['no_hp' => '081200009002'],
    [
        'id_peran' => 4,
        'kata_sandi' => $passHash,
        'status_akun' => 'aktif',
        'is_tersedia' => 1,
    ]
);
$token = $relawan->createToken('load-test')->plainTextToken;
$tokens[] = ['token' => $token, 'role' => 'relawan'];
echo "relawan token generated.\n";

echo "\n" . json_encode($tokens, JSON_PRETTY_PRINT) . "\n";
