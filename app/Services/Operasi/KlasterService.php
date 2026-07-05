<?php

namespace App\Services\Operasi;

use App\Models\OperasiKlaster;
use App\Models\OperasiInsiden;
use App\Services\Auth\AuthorizationContextService;
use Exception;
use Illuminate\Support\Facades\DB;

class KlasterService
{
    private AuthorizationContextService $authCtx;

    public function __construct(AuthorizationContextService $authCtx)
    {
        $this->authCtx = $authCtx;
    }

    public function createKlaster(array $data): OperasiKlaster
    {
        return DB::transaction(function () use ($data) {
            $user = $this->authCtx->getCurrentUser();
            if (!$user) {
                throw new Exception("User tidak terautentikasi.");
            }

            // Validasi apakah klaster ini sudah aktif di insiden ini
            $isAlreadyActive = OperasiKlaster::where('id_insiden', $data['id_insiden'])
                ->where('id_master_klaster', $data['id_master_klaster'])
                ->where('status_klaster', 'aktif')
                ->exists();

            if ($isAlreadyActive) {
                throw new Exception("Klaster tersebut sudah aktif di insiden ini.");
            }

            return OperasiKlaster::create([
                'id_insiden' => $data['id_insiden'],
                'id_master_klaster' => $data['id_master_klaster'],
                'status_klaster' => 'aktif',
                'prioritas' => $data['prioritas'] ?? 'sedang',
                'waktu_aktivasi' => now(),
                'target_cakupan' => $data['target_cakupan'] ?? null,
                'catatan' => $data['catatan'] ?? null,
                'id_pembuat' => $user->id_pengguna,
            ]);
        });
    }

    public function closeKlaster(OperasiKlaster $klaster, ?string $catatan = null): OperasiKlaster
    {
        if ($klaster->status_klaster === 'selesai') {
            throw new Exception("Klaster sudah ditutup.");
        }

        $klaster->status_klaster = 'selesai';
        $klaster->waktu_ditutup = now();
        
        if ($catatan) {
            $klaster->catatan = $catatan;
        }

        $klaster->save();
        return $klaster;
    }

    public function updateKlaster(OperasiKlaster $klaster, array $data): OperasiKlaster
    {
        return DB::transaction(function () use ($klaster, $data) {
            if (isset($data['status_klaster']) && $data['status_klaster'] === 'selesai' && $klaster->status_klaster !== 'selesai') {
                return $this->closeKlaster($klaster, $data['catatan'] ?? null);
            }

            $klaster->update($data);
            return $klaster->fresh();
        });
    }

    public function deleteKlaster(OperasiKlaster $klaster): void
    {
        DB::transaction(function () use ($klaster) {
            $klaster->delete();
        });
    }
}
