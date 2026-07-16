<?php

namespace App\Console\Commands;

use App\Models\BencanaMasterJenis;
use App\Models\WilayahKabupaten;
use App\Models\WilayahKecamatan;
use App\Models\WilayahDesa;
use App\Models\MasterKlaster;
use App\Models\AuthKeahlianMaster;
use App\Models\LogistikKategori;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class MasterExportCommand extends Command
{
    protected $signature = 'master:export {--format=json : Output format (json or sqlite)} {--dir= : Output directory}';
    protected $description = 'Export master data to JSON files or SQLite for Flutter assets';

    public function handle(): int
    {
        $format = $this->option('format');
        $dir = $this->option('dir') ?? resource_path('master-export');

        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $this->info("Exporting master data to: $dir");

        if ($format === 'json') {
            $this->exportJson($dir);
        } elseif ($format === 'sqlite') {
            $this->exportSqlite($dir);
        }

        $this->info('Export selesai.');
        return Command::SUCCESS;
    }

    protected function exportJson(string $dir): void
    {
        // Tier A — Bencana
        $this->exportJsonFile($dir . '/bencana/jenis.json', BencanaMasterJenis::all()->map(fn($b) => [
            'id'       => $b->id_jenis,
            'nama'     => $b->nama_bencana,
            'slug'     => $b->slug,
            'kategori' => $b->kategori,
            'ikon_map' => $b->ikon_map,
        ])->toArray());

        // Tier A — Klaster
        $this->exportJsonFile($dir . '/klaster/data.json', DB::table('master_klaster')->get()->map(fn($k) => [
            'id'        => $k->id_master_klaster,
            'nama'      => $k->nama_klaster,
            'deskripsi' => $k->deskripsi,
        ])->toArray());

        // Tier A — Keahlian Relawan
        $this->exportJsonFile($dir . '/relawan/jenis.json', AuthKeahlianMaster::all()->map(fn($r) => [
            'id'        => $r->id_keahlian,
            'nama'      => $r->nama_keahlian,
            'deskripsi' => $r->deskripsi,
        ])->toArray());

        // Tier A — Logistik Kategori
        $this->exportJsonFile($dir . '/logistik/jenis.json', LogistikKategori::all()->map(fn($l) => [
            'id'   => $l->id_kategori,
            'nama' => $l->nama_kategori,
        ])->toArray());

        // Tier B — Wilayah (full export)
        $this->exportWilayahJson($dir);
    }

    protected function exportWilayahJson(string $dir): void
    {
        // Kabupaten
        $kabList = WilayahKabupaten::all()->map(fn($k) => [
            'id_kab'  => $k->id_kab,
            'nama_kab' => $k->tipe . ' ' . $k->nama_kab,
        ])->values()->toArray();
        $this->exportJsonFile($dir . '/wilayah/kabupaten.json', $kabList);

        // Kecamatan
        $kecList = WilayahKecamatan::all()->map(fn($k) => [
            'id_kec'  => $k->id_kec,
            'id_kab'  => $k->id_kab,
            'nama_kec' => $k->nama_kec,
        ])->values()->toArray();
        $this->exportJsonFile($dir . '/wilayah/kecamatan.json', $kecList);

        // Desa
        $desaList = WilayahDesa::all()->map(fn($d) => [
            'id_desa'  => $d->id_desa,
            'id_kec'   => $d->id_kec,
            'nama_desa' => $d->nama_desa,
        ])->values()->toArray();
        $this->exportJsonFile($dir . '/wilayah/desa.json', $desaList);
    }

    protected function exportSqlite(string $dir): void
    {
        $dbPath = $dir . '/master.db';

        if (file_exists($dbPath)) {
            unlink($dbPath);
        }

        $pdo = new \PDO("sqlite:$dbPath");
        $pdo->exec('PRAGMA journal_mode=WAL');
        $pdo->exec('PRAGMA foreign_keys=ON');

        // Kabupaten
        $pdo->exec("CREATE TABLE IF NOT EXISTS kabupaten (
            id_kab TEXT PRIMARY KEY,
            nama_kab TEXT NOT NULL
        )");
        $stmt = $pdo->prepare("INSERT OR REPLACE INTO kabupaten (id_kab, nama_kab) VALUES (?, ?)");
        foreach (WilayahKabupaten::all() as $k) {
            $stmt->execute([$k->id_kab, $k->tipe . ' ' . $k->nama_kab]);
        }

        // Kecamatan
        $pdo->exec("CREATE TABLE IF NOT EXISTS kecamatan (
            id_kec TEXT PRIMARY KEY,
            id_kab TEXT NOT NULL,
            nama_kec TEXT NOT NULL
        )");
        $stmt = $pdo->prepare("INSERT OR REPLACE INTO kecamatan (id_kec, id_kab, nama_kec) VALUES (?, ?, ?)");
        foreach (WilayahKecamatan::all() as $k) {
            $stmt->execute([$k->id_kec, $k->id_kab, $k->nama_kec]);
        }

        // Desa
        $pdo->exec("CREATE TABLE IF NOT EXISTS desa (
            id_desa TEXT PRIMARY KEY,
            id_kec TEXT NOT NULL,
            nama_desa TEXT NOT NULL
        )");
        $stmt = $pdo->prepare("INSERT OR REPLACE INTO desa (id_desa, id_kec, nama_desa) VALUES (?, ?, ?)");
        foreach (WilayahDesa::all() as $d) {
            $stmt->execute([$d->id_desa, $d->id_kec, $d->nama_desa]);
        }

        // Kebutuhan Numerik Master
        $pdo->exec("CREATE TABLE IF NOT EXISTS kebutuhan_numerik_master (
            id_item INTEGER PRIMARY KEY,
            kategori TEXT NOT NULL,
            kode_item TEXT NOT NULL,
            nama_item TEXT NOT NULL,
            satuan_default TEXT NOT NULL,
            urutan INTEGER NOT NULL
        )");
        $stmt = $pdo->prepare("INSERT OR REPLACE INTO kebutuhan_numerik_master (id_item, kategori, kode_item, nama_item, satuan_default, urutan) VALUES (?, ?, ?, ?, ?, ?)");
        foreach (\App\Models\Assessment\AssessmentKebutuhanNumerikMaster::where('aktif', 1)->get() as $n) {
            $stmt->execute([$n->id_item, $n->kategori, $n->kode_item, $n->nama_item, $n->satuan_default, $n->urutan]);
        }

        // Indexes
        $pdo->exec("CREATE INDEX IF NOT EXISTS idx_kecamatan_id_kab ON kecamatan(id_kab)");
        $pdo->exec("CREATE INDEX IF NOT EXISTS idx_desa_id_kec ON desa(id_kec)");

        $this->info("SQLite master db created: $dbPath");
    }

    protected function exportJsonFile(string $path, array $data): void
    {
        $dir = dirname($path);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        file_put_contents($path, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        $this->info("  Created: $path (" . count($data) . " rows)");
    }
}
