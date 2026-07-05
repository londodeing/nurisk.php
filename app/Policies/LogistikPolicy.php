<?php

namespace App\Policies;

use App\Models\AuthUser;
use App\Models\LogistikStok;
use App\Models\LogistikMutasi;
use App\Models\LogistikPermintaan;
use App\Services\Auth\AuthorizationContextService;
use App\Models\OperasiPenugasan;

class LogistikPolicy
{
    protected AuthorizationContextService $authContext;

    public function __construct(AuthorizationContextService $authContext)
    {
        $this->authContext = $authContext;
    }

    /**
     * Otorisasi untuk master data (Katalog & Kategori)
     */
    public function manageMasterData(AuthUser $user): bool
    {
        return $this->authContext->hasAnyRole(['super_admin', 'pwnu']);
    }

    /**
     * Otorisasi untuk mutasi keluar
     */
    public function mutasiKeluar(AuthUser $user, LogistikStok $stok): bool
    {
        if ($this->authContext->isSuperAdmin()) {
            return true;
        }

        // Jika stok ada di Gudang Master
        if ($stok->id_gudang) {
            $stok->loadMissing('gudang');
            if ($stok->gudang) {
                // Penanggung jawab gudang berhak
                if ($stok->gudang->pj_gudang === $user->id_pengguna) {
                    return true;
                }

                // Pengurus wilayah (PWNU/PCNU) sesuai yurisdiksi
                if ($stok->gudang->id_pcnu) {
                    if ($this->authContext->hasAnyRole(['pwnu', 'pcnu']) && $this->authContext->canAccessInsiden($stok->gudang->id_pcnu)) {
                        return true;
                    }
                } else {
                    // Gudang PWNU (id_pcnu = null), hanya PWNU yang bisa
                    if ($this->authContext->hasRole('pwnu')) {
                        return true;
                    }
                }
            }
        }

        // Jika stok ada di Pos Aju
        if ($stok->id_posaju) {
            $stok->loadMissing('posaju.insiden');
            if ($stok->posaju && $stok->posaju->insiden) {
                $idPcnu = $stok->posaju->insiden->id_pcnu;

                // Pengurus wilayah (PWNU/PCNU) sesuai yurisdiksi
                if ($this->authContext->hasAnyRole(['pwnu', 'pcnu']) && $this->authContext->canAccessInsiden($idPcnu)) {
                    return true;
                }

                // Relawan yang ditugaskan sebagai logistik atau komandan pada insiden ini
                $isDitugaskan = OperasiPenugasan::where('id_pengguna', $user->id_pengguna)
                    ->where('id_insiden', $stok->posaju->id_insiden)
                    ->whereIn('peran_otoritas', ['logistik', 'komandan_insiden'])
                    ->where('status_penugasan', 'aktif')
                    ->exists();

                if ($isDitugaskan) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Otorisasi untuk mengajukan permintaan logistik
     */
    public function create(AuthUser $user): bool
    {
        return $this->authContext->hasAnyRole(['super_admin', 'pwnu', 'pcnu']);
    }

    public function ajukanPermintaan(AuthUser $user, LogistikPermintaan $permintaan): bool
    {
        if ($this->authContext->isSuperAdmin()) {
            return true;
        }

        $permintaan->loadMissing('posaju.insiden');
        
        if ($permintaan->posaju && $permintaan->posaju->insiden) {
            $idPcnu = $permintaan->posaju->insiden->id_pcnu;
            if ($this->authContext->hasAnyRole(['pwnu', 'pcnu']) && $this->authContext->canAccessInsiden($idPcnu)) {
                return true;
            }

            // Relawan yang ditugaskan sebagai logistik atau komandan
            $isDitugaskan = OperasiPenugasan::where('id_pengguna', $user->id_pengguna)
                ->where('id_insiden', $permintaan->posaju->id_insiden)
                ->whereIn('peran_otoritas', ['logistik', 'komandan_insiden'])
                ->where('status_penugasan', 'aktif')
                ->exists();

            if ($isDitugaskan) {
                return true;
            }
        }

        return false;
    }
}
