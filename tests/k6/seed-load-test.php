<?php

declare(strict_types=1);

/**
 * Seed script for load testing.
 * Bootstraps Laravel, runs fresh migrations + seeders, then inserts
 * substantial test data (PCNU, insiden, penugasan, assessment).
 *
 * Usage: php tests/k6/seed-load-test.php
 */

require __DIR__ . '/../../vendor/autoload.php';

$app = require_once __DIR__ . '/../../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

// ── 1. Fresh migration & existing seeders ──────────────────────────
echo "Running migrate:fresh ...\n";
Artisan::call('migrate:fresh', ['--force' => true, '--seed' => true]);
echo "Seeders done.\n";

// ── 2. OrganisasiUnit + OrganisasiPcnu (2) ────────────────────────
$pcnuUnits = [];
foreach (['PCNU Kota Semarang', 'PCNU Kabupaten Semarang'] as $i => $name) {
    $unitId = DB::table('organisasi_unit')->insertGetId([
        'parent_id' => null,
        'nama_unit' => $name,
        'tipe_unit' => 'pcnu',
        'id_wilayah' => '33.74.01',
    ]);
    $pcnuId = DB::table('organisasi_pcnu')->insertGetId([
        'id_unit' => $unitId,
        'nama_pcnu' => $name,
    ]);
    $pcnuUnits[] = ['id_unit' => $unitId, 'id_pcnu' => $pcnuId, 'nama' => $name];
}
echo "Created " . count($pcnuUnits) . " PCNU.\n";

// ── 3. BencanaMasterJenis (ensure at least one exists) ────────────
$jenisIds = DB::table('bencana_master_jenis')->pluck('id_jenis')->toArray();
if (empty($jenisIds)) {
    $jenisIds[] = DB::table('bencana_master_jenis')->insertGetId([
        'nama_bencana' => 'Banjir',
        'slug' => 'banjir',
        'kategori' => 'alam',
        'deskripsi' => 'Bencana banjir',
        'ikon_map' => 'banjir.png',
    ]);
}
echo "Bencana jenis: " . count($jenisIds) . ".\n";

// ── 4. AuthRole (ensure relawan & super_admin exist) ──────────────
$roles = [
    'super_admin' => DB::table('auth_roles')->where('nama_peran', 'super_admin')->value('id_peran') ?? 1,
    'relawan' => DB::table('auth_roles')->where('nama_peran', 'relawan')->value('id_peran') ?? 4,
];

// ── 5. Users (for assignments & assessment officers) ──────────────
$userIds = [];
$pass = Hash::make('loadtest123');

// Super admin
$userId = DB::table('auth_users')->insertGetId([
    'id_peran' => $roles['super_admin'],
    'no_hp' => '081200000001',
    'kata_sandi' => $pass,
    'status_akun' => 'aktif',
    'is_tersedia' => 1,
    'dibuat_pada' => now(),
    'diperbarui_pada' => now(),
]);
$userIds[] = $userId;

// Relawan users
for ($i = 1; $i <= 20; $i++) {
    $userId = DB::table('auth_users')->insertGetId([
        'id_peran' => $roles['relawan'],
        'no_hp' => '08120000' . str_pad((string) ($i + 1), 4, '0', STR_PAD_LEFT),
        'kata_sandi' => $pass,
        'status_akun' => 'aktif',
        'is_tersedia' => 1,
        'dibuat_pada' => now(),
        'diperbarui_pada' => now(),
    ]);
    $userIds[] = $userId;
}
echo "Created " . count($userIds) . " users.\n";

// ── 6. OperasiInsiden (50, 25 per PCNU) ──────────────────────────
$insidenIds = [];
$statuses = ['draft', 'terverifikasi', 'respon', 'pemulihan', 'selesai'];

foreach ($pcnuUnits as $pcnu) {
    for ($j = 0; $j < 25; $j++) {
        $id = DB::table('operasi_insiden')->insertGetId([
            'uuid_insiden' => (string) Str::uuid(),
            'kode_kejadian' => 'INS-' . strtoupper(Str::random(6)) . '-' . $j,
            'id_jenis_bencana' => $jenisIds[array_rand($jenisIds)],
            'id_pcnu' => $pcnu['id_pcnu'],
            'id_mwc' => null,
            'status_insiden' => $statuses[array_rand($statuses)],
            'status_operasi' => 'tanggap_darurat',
            'is_locked' => false,
            'prioritas' => 'sedang',
            'waktu_mulai' => now()->subDays(random_int(1, 30)),
            'dibuat_pada' => now(),
            'diperbarui_pada' => now(),
        ]);
        $insidenIds[] = $id;
    }
}
echo "Created " . count($insidenIds) . " insiden.\n";

// ── 7. AssessmentUtama (50) ──────────────────────────────────────
$assessmentIds = [];
foreach ($insidenIds as $i => $iid) {
    if ($i >= 50) break;

    $id = DB::table('assessment_utama')->insertGetId([
        'uuid_assessment' => (string) Str::uuid(),
        'id_insiden' => $iid,
        'jenis_laporan' => 'kaji_cepat',
        'cakupan_wilayah_deskripsi' => 'Desa ' . Str::random(6),
        'latitude' => -6.9 + (random_int(-100, 100) / 1000),
        'longitude' => 110.4 + (random_int(-100, 100) / 1000),
        'is_latest' => true,
        'waktu_assesment' => now(),
        'dibuat_pada' => now(),
        'diperbarui_pada' => now(),
    ]);
    $assessmentIds[] = $id;
}
echo "Created " . count($assessmentIds) . " assessment.\n";

// ── 8. OperasiPenugasan (100) ───────────────────────────────────
$statusPenugasan = ['aktif', 'selesai', 'dibatalkan'];
$penugasanIds = [];

for ($i = 0; $i < 100; $i++) {
    $iid = $insidenIds[array_rand($insidenIds)];
    $pengguna = $userIds[array_rand($userIds)];
    $pemberi = $userIds[array_rand($userIds)];

    $id = DB::table('operasi_penugasan')->insertGetId([
        'uuid_penugasan' => (string) Str::uuid(),
        'id_insiden' => $iid,
        'id_pengguna' => $pengguna,
        'id_klaster_operasi' => null,
        'peran_otoritas' => 'relawan',
        'status_penugasan' => $statusPenugasan[array_rand($statusPenugasan)],
        'waktu_mulai' => now()->subHours(random_int(1, 48)),
        'waktu_selesai' => null,
        'ditugaskan_oleh' => $pemberi,
        'catatan' => 'Load test seed penugasan #' . $i,
        'dibuat_pada' => now(),
        'diperbarui_pada' => now(),
    ]);
    $penugasanIds[] = $id;
}
echo "Created " . count($penugasanIds) . " penugasan.\n";

echo "\n✅ Load test seed complete!\n";
echo "   PCNU: " . count($pcnuUnits) . "\n";
echo "   Users: " . count($userIds) . "\n";
echo "   Insiden: " . count($insidenIds) . "\n";
echo "   Assessment: " . count($assessmentIds) . "\n";
echo "   Penugasan: " . count($penugasanIds) . "\n";
