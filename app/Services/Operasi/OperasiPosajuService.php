<?php

namespace App\Services\Operasi;

use App\Models\OperasiPosaju;
use InvalidArgumentException;
use Illuminate\Support\Facades\DB;

class OperasiPosajuService
{
    public function __construct(
        private PosajuJurnalService $jurnal
    ) {}

    /**
     * direncanakan -> aktif
     */
    public function activate(OperasiPosaju $posaju): OperasiPosaju
    {
        return DB::transaction(function () use ($posaju) {
            $posaju = OperasiPosaju::where('id_posaju', $posaju->id_posaju)->with('insiden')->lockForUpdate()->firstOrFail();

            if ($posaju->status_alur !== 'direncanakan') {
                throw new InvalidArgumentException("Hanya posaju berstatus direncanakan yang bisa diaktifkan.");
            }

            if (empty($posaju->id_pleno_keputusan)) {
                throw new InvalidArgumentException("Pos aju tidak dapat diaktifkan tanpa referensi keputusan pleno.");
            }

            $posaju->status_alur = 'aktif';
            $posaju->waktu_diaktifkan = now();
            $posaju->save();

            $this->jurnal->catat('posaju_diaktifkan', $posaju);

            return $posaju;
        });
    }

    /**
     * aktif -> diperpanjang
     */
    public function extend(OperasiPosaju $posaju, \DateTimeInterface $hingga, ?string $alasan = null): OperasiPosaju
    {
        return DB::transaction(function () use ($posaju, $hingga, $alasan) {
            $posaju = OperasiPosaju::where('id_posaju', $posaju->id_posaju)->with('insiden')->lockForUpdate()->firstOrFail();

            if ($posaju->status_alur !== 'aktif') {
                throw new InvalidArgumentException("Hanya posaju berstatus aktif yang bisa diperpanjang.");
            }

            $posaju->status_alur = 'diperpanjang';
            $posaju->diperpanjang_hingga = $hingga;
            if ($alasan) {
                $posaju->alasan_perpanjangan = $alasan;
            }
            $posaju->save();

            $this->jurnal->catat('posaju_diperpanjang', $posaju);

            return $posaju;
        });
    }

    /**
     * aktif/diperpanjang -> ditutup
     */
    public function close(OperasiPosaju $posaju, ?string $alasan = null): OperasiPosaju
    {
        return DB::transaction(function () use ($posaju, $alasan) {
            $posaju = OperasiPosaju::where('id_posaju', $posaju->id_posaju)->with('insiden')->lockForUpdate()->firstOrFail();

            if (!in_array($posaju->status_alur, ['aktif', 'diperpanjang'])) {
                throw new InvalidArgumentException("Hanya posaju berstatus aktif atau diperpanjang yang bisa ditutup.");
            }

            $posaju->status_alur = 'ditutup';
            $posaju->waktu_ditutup = now();
            if ($alasan) {
                $posaju->alasan_penutupan = $alasan;
            }
            $posaju->save();

            $this->jurnal->catat('posaju_ditutup', $posaju);

            return $posaju;
        });
    }
}
