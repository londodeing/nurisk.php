<?php

namespace App\Services\Operasi;

use App\Models\OperasiKlaster;
use InvalidArgumentException;
use Illuminate\Support\Facades\DB;

class OperasiKlasterService
{
    /**
     * nonaktif -> aktif
     */
    public function activate(OperasiKlaster $klaster): OperasiKlaster
    {
        return DB::transaction(function () use ($klaster) {
            $klaster = OperasiKlaster::where('id_klaster_operasi', $klaster->id_klaster_operasi)->lockForUpdate()->firstOrFail();

            if ($klaster->status_klaster !== 'nonaktif') {
                throw new InvalidArgumentException("Klaster tidak dalam status nonaktif.");
            }

            $klaster->status_klaster = 'aktif';
            $klaster->waktu_aktivasi = now();
            $klaster->save();

            return $klaster;
        });
    }

    public function updateProgress(OperasiKlaster $klaster, float $progress): OperasiKlaster
    {
        return DB::transaction(function () use ($klaster, $progress) {
            $klaster = OperasiKlaster::where('id_klaster_operasi', $klaster->id_klaster_operasi)->lockForUpdate()->firstOrFail();

            if ($klaster->status_klaster !== 'aktif') {
                throw new InvalidArgumentException("Hanya klaster aktif yang dapat diupdate progress-nya.");
            }

            $klaster->progres_persen = $progress;
            $klaster->save();

            return $klaster;
        });
    }

    public function complete(OperasiKlaster $klaster): OperasiKlaster
    {
        return DB::transaction(function () use ($klaster) {
            $klaster = OperasiKlaster::where('id_klaster_operasi', $klaster->id_klaster_operasi)->lockForUpdate()->firstOrFail();

            if ($klaster->status_klaster !== 'aktif') {
                throw new InvalidArgumentException("Klaster belum aktif atau sudah selesai.");
            }

            // Cek child tasks
            $hasActiveTasks = $klaster->tugas()->whereIn('status_tugas', ['rencana', 'berjalan', 'tertunda'])->exists();
            if ($hasActiveTasks) {
                throw new \LogicException("Tidak dapat menyelesaikan klaster karena masih ada tugas yang aktif.");
            }

            $klaster->status_klaster = 'selesai';
            $klaster->waktu_ditutup = now();
            $klaster->progres_persen = 100.00;
            $klaster->save();

            return $klaster;
        });
    }
}
