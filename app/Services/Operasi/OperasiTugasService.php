<?php

namespace App\Services\Operasi;

use App\Models\OperasiTugas;
use InvalidArgumentException;
use Illuminate\Support\Facades\DB;

class OperasiTugasService
{
    public function start(OperasiTugas $tugas): OperasiTugas
    {
        return DB::transaction(function () use ($tugas) {
            $tugas = OperasiTugas::where('id_tugas', $tugas->id_tugas)->lockForUpdate()->firstOrFail();

            if (!in_array($tugas->status_tugas, ['rencana', 'tertunda'])) {
                throw new InvalidArgumentException("Tugas tidak dapat dimulai dari status saat ini.");
            }

            $tugas->status_tugas = 'berjalan';
            $tugas->save();

            return $tugas;
        });
    }

    public function pause(OperasiTugas $tugas): OperasiTugas
    {
        return DB::transaction(function () use ($tugas) {
            $tugas = OperasiTugas::where('id_tugas', $tugas->id_tugas)->lockForUpdate()->firstOrFail();

            if ($tugas->status_tugas !== 'berjalan') {
                throw new InvalidArgumentException("Hanya tugas berjalan yang bisa ditunda.");
            }

            $tugas->status_tugas = 'tertunda';
            $tugas->save();

            return $tugas;
        });
    }

    public function complete(OperasiTugas $tugas): OperasiTugas
    {
        return DB::transaction(function () use ($tugas) {
            $tugas = OperasiTugas::where('id_tugas', $tugas->id_tugas)->lockForUpdate()->firstOrFail();

            if ($tugas->status_tugas !== 'berjalan') {
                throw new InvalidArgumentException("Hanya tugas berjalan yang bisa diselesaikan.");
            }

            $tugas->status_tugas = 'selesai';
            $tugas->progres_persen = 100.00;
            $tugas->save();

            return $tugas;
        });
    }
}
