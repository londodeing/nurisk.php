<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Inventaris\InventarisDokumen;

class CekDokumenInventaris extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'nurisk:cek-dokumen-inventaris {--days=30}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cek dokumen inventaris yang akan kedaluwarsa';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $days = (int) $this->option('days');
        $targetDate = now()->addDays($days);

        $dokumenExpiring = InventarisDokumen::whereNotNull('berlaku_hingga')
            ->where('berlaku_hingga', '<=', $targetDate)
            ->where('berlaku_hingga', '>=', now())
            ->get();

        if ($dokumenExpiring->isEmpty()) {
            $this->info("Tidak ada dokumen yang akan kedaluwarsa dalam $days hari ke depan.");
            return;
        }

        $this->warn("Ditemukan {$dokumenExpiring->count()} dokumen yang akan kedaluwarsa dalam $days hari ke depan:");

        foreach ($dokumenExpiring as $doc) {
            $this->line("- ID Aset: {$doc->id_aset} | Dokumen: {$doc->nama_dokumen} ({$doc->jenis_dokumen}) | Berlaku Hingga: {$doc->berlaku_hingga}");
        }
    }
}
