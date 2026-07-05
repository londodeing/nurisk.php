<?php

namespace App\Console\Commands;

use App\Models\OperasiJurnal;
use App\Models\SyncTombstone;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;

class SyncPruneTombstones extends Command
{
    protected $signature = 'sync:prune-tombstones
        {--days=90 : Hapus tombstone yang lebih tua dari N hari}
        {--dry-run : Hitung saja tanpa menghapus}';

    protected $description = 'Hapus tombstone sync yang sudah melebihi batas retensi';

    public function handle(): int
    {
        $days = (int) $this->option('days');
        $dryRun = (bool) $this->option('dry-run');
        $cutoff = now()->subDays($days);

        $this->info("Mencari tombstone sebelum {$cutoff->toDateTimeString()} (retensi: {$days} hari)...");

        $query = SyncTombstone::where('deleted_at', '<', $cutoff);
        $count = $query->count();

        if ($count === 0) {
            $this->info('Tidak ada tombstone yang perlu dipurge.');
            return self::SUCCESS;
        }

        $this->warn("Ditemukan {$count} tombstone untuk dipurge.");

        if ($dryRun) {
            $this->info('Mode dry-run: tidak ada perubahan.');
            return self::SUCCESS;
        }

        $query->delete();
        $this->info("Berhasil memurge {$count} tombstone.");

        if (Schema::hasTable('operasi_jurnal')) {
            OperasiJurnal::create([
                'id_insiden' => 0,
                'id_pengguna' => 0,
                'kategori_event' => 'sistem',
                'judul_event' => 'Tombstone Purge',
                'deskripsi_event' => "Retensi {$days} hari: {$count} tombstone dihapus.",
                'tabel_referensi' => 'sync_tombstones',
            ]);
        }

        return self::SUCCESS;
    }
}
