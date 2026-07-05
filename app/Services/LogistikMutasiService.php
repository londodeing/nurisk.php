<?php

namespace App\Services;

use App\Models\LogistikMutasi;
use App\Models\LogistikStok;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class LogistikMutasiService
{
    /**
     * @throws \Exception
     */
    public function catatMutasi(array $data, ?int $idPenginput = null): LogistikMutasi
    {
        return DB::transaction(function () use ($data, $idPenginput) {
            $stok = LogistikStok::lockForUpdate()->findOrFail($data['id_stok']);
            
            if ($data['tipe_mutasi'] === 'keluar' && $stok->jumlah_tersedia < $data['jumlah']) {
                throw new \Exception('Stok fisik tidak mencukupi untuk mutasi keluar!');
            }

            $mutasi = new LogistikMutasi();
            $mutasi->uuid_mutasi = (string) Str::uuid();
            $mutasi->id_penginput = $idPenginput;
            $mutasi->id_stok = $stok->id_stok;
            $mutasi->tipe_mutasi = $data['tipe_mutasi'];
            $mutasi->jumlah = $data['jumlah'];
            $mutasi->asal_tujuan = $data['asal_tujuan'];
            $mutasi->keterangan = $data['keterangan'] ?? null;
            $mutasi->save();
            
            // Perbarui jumlah_tersedia manual jika driver sqlite (karena trigger di-skip)
            if (DB::connection()->getDriverName() === 'sqlite') {
                if ($mutasi->tipe_mutasi === 'masuk') {
                    $stok->jumlah_tersedia += $mutasi->jumlah;
                } elseif ($mutasi->tipe_mutasi === 'keluar') {
                    $stok->jumlah_tersedia -= $mutasi->jumlah;
                } elseif ($mutasi->tipe_mutasi === 'penyesuaian') {
                    $stok->jumlah_tersedia = $mutasi->jumlah;
                }
                $stok->save();
            }

            return $mutasi;
        });
    }
}
