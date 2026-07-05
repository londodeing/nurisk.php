<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use App\Models\LaporanKejadian;
use App\Models\OrgAsset;

class MediaCleanupOrphans extends Command
{
    protected $signature = 'media:cleanup-orphans {--dry-run : Only report, do not delete} {--force : Skip confirmation prompt}';

    protected $description = 'Find and clean orphaned media files with no DB record';

    public function handle()
    {
        $disk = Storage::disk('public');
        $deleted = 0;
        $orphans = [];

        $dbPaths = collect();
        LaporanKejadian::whereNotNull('photo_path')->pluck('photo_path')->each(fn($p) => $dbPaths->push($p));
        OrgAsset::whereNotNull('foto_utama_path')->pluck('foto_utama_path')->each(fn($p) => $dbPaths->push($p));

        $allLaporanFiles = array_merge(
            $disk->files('laporan/foto'),
            $disk->files('laporan'),
        );
        foreach ($allLaporanFiles as $file) {
            if (!$dbPaths->contains($file)) {
                $orphans[] = $file;
            }
        }

        $allAsetFiles = $disk->files('aset/foto');
        foreach ($allAsetFiles as $file) {
            if (!$dbPaths->contains($file)) {
                $orphans[] = $file;
            }
        }

        $allCsvFiles = $disk->files('asets/csv-import');
        foreach ($allCsvFiles as $file) {
            $orphans[] = $file;
        }

        if (empty($orphans)) {
            $this->info('No orphaned files found.');
            return 0;
        }

        $this->warn("Found " . count($orphans) . " orphaned file(s):");
        foreach ($orphans as $orphan) {
            $size = $disk->size($orphan);
            $this->line("  - $orphan (" . round($size / 1024, 1) . " KB)");
        }

        if ($this->option('dry-run')) {
            $this->info('Dry-run mode. No files deleted.');
            $this->info("Run without --dry-run to delete " . count($orphans) . " files.");
            return 0;
        }

        if (!$this->option('force') && !$this->confirm('Delete these orphaned files?')) {
            $this->info('Aborted.');
            return 1;
        }

        foreach ($orphans as $orphan) {
            if ($disk->delete($orphan)) {
                $deleted++;
            }
        }

        $this->info("Deleted $deleted orphaned file(s).");
        return 0;
    }
}
