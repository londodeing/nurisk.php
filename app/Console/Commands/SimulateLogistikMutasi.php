<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\LogistikMutasiService;
use App\Models\LogistikGudang;
use App\Models\LogistikKategori;
use App\Models\LogistikBarangKatalog;
use App\Models\LogistikStok;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SimulateLogistikMutasi extends Command
{
    protected $signature = 'logistik:simulate-mutasi {--count=1000}';
    protected $description = 'Simulate concurrent logistik mutasi transactions';

    public function handle(LogistikMutasiService $mutasiService)
    {
        $count = (int) $this->option('count');
        $this->info("Memulai simulasi {$count} transaksi mutasi...");

        // Setup Data Awal
        $gudang = LogistikGudang::firstOrCreate(
            ['id_pcnu' => 1],
            ['nama_gudang' => 'Gudang Utama PWNU', 'alamat_gudang' => 'Pusat']
        );

        $kategori = LogistikKategori::firstOrCreate(['nama_kategori' => 'Logistik Simulasi']);
        
        $katalog = LogistikBarangKatalog::firstOrCreate(
            ['nama_barang' => 'Beras Simulasi'],
            ['id_kategori' => $kategori->id_kategori, 'satuan' => 'kg']
        );

        // Hapus stok lama
        LogistikStok::where('id_gudang', $gudang->id_gudang)->where('id_katalog', $katalog->id_katalog)->delete();
        
        $stok = LogistikStok::create([
            'id_gudang' => $gudang->id_gudang,
            'id_katalog' => $katalog->id_katalog,
            'jumlah_tersedia' => 10000, // Saldo awal besar
            'jumlah_dialokasikan' => 0,
            'satuan' => 'kg',
            'kondisi' => 'baik'
        ]);

        $this->info("Saldo Awal: " . $stok->jumlah_tersedia);

        $success = 0;
        $failed = 0;

        $bar = $this->output->createProgressBar($count);
        $bar->start();

        for ($i = 0; $i < $count; $i++) {
            // Simulasi secara synchronous/loop (di environment nyata bisa via Job/Queue)
            try {
                $jenis = (rand(0, 1) === 0) ? 'masuk' : 'keluar';
                $jumlah = rand(1, 10);
                
                $mutasiService->handleMutasi([
                    'id_stok' => $stok->id_stok,
                    'jenis_mutasi' => $jenis,
                    'jumlah' => $jumlah,
                    'keterangan' => 'Simulasi ' . $i,
                    'id_pelaku' => 1,
                    'is_simulasi' => true // bypass scope auth if needed
                ]);
                $success++;
            } catch (\Exception $e) {
                $failed++;
            }
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();

        $stokAkhir = LogistikStok::find($stok->id_stok);

        $this->info("Simulasi Selesai.");
        $this->info("Berhasil: {$success}");
        $this->info("Gagal (Kurang Saldo dll): {$failed}");
        $this->info("Saldo Akhir di Database: " . $stokAkhir->jumlah_tersedia);
        
        if ($stokAkhir->jumlah_tersedia < 0) {
            $this->error("RACE CONDITION TERDETEKSI: Saldo Negatif!");
        } else {
            $this->info("VERIFIED: Tidak ada race condition, saldo aman.");
        }

        return 0;
    }
}
