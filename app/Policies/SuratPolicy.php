<?php

namespace App\Policies;

use App\Models\AuthUser;
use App\Models\DokumenSuratParaf;
use App\Models\DokumenSuratUtama;
use App\Services\Auth\AuthorizationContextService;
use Illuminate\Auth\Access\HandlesAuthorization;

class SuratPolicy
{
    use HandlesAuthorization;

    private function authCtx(): AuthorizationContextService
    {
        return app(AuthorizationContextService::class);
    }

    public function viewAny(AuthUser $user): bool
    {
        return $this->authCtx()->hasAnyRole(['super_admin', 'pwnu', 'pcnu']);
    }

    public function view(AuthUser $user, DokumenSuratUtama $surat): bool
    {
        if ($this->authCtx()->hasAnyRole(['super_admin', 'pwnu'])) {
            return true;
        }
        if ($this->authCtx()->hasRole('pcnu')) {
            return $surat->insiden && $this->authCtx()->getScopeId() === $surat->insiden->id_pcnu;
        }
        return false;
    }

    public function create(AuthUser $user): bool
    {
        return $this->authCtx()->hasAnyRole(['super_admin', 'pwnu', 'pcnu']);
    }

    public function update(AuthUser $user, DokumenSuratUtama $surat): bool
    {
        if (!$surat->isDraft()) {
            return false;
        }
        return $this->authCtx()->hasAnyRole(['super_admin', 'pwnu', 'pcnu']);
    }

    public function paraf(AuthUser $user, DokumenSuratParaf $parafRecord): bool
    {
        if ($parafRecord->id_pengguna !== $user->id_pengguna) {
            return false;
        }
        if ($parafRecord->status_paraf !== 'menunggu') {
            return false;
        }
        return true;
    }

    public function finalisasi(AuthUser $user, DokumenSuratUtama $surat): bool
    {
        if ($surat->status_surat !== 'siap_tanda_tangan') {
            return false;
        }
        if (!$this->authCtx()->hasAnyRole(['super_admin', 'pwnu'])) {
            return false;
        }
        if ($surat->paraf()->where('status_paraf', '!=', 'disetujui')->exists()) {
            return false;
        }
        return true;
    }

    public function delete(AuthUser $user, DokumenSuratUtama $surat): bool
    {
        if (!$surat->isDraft()) {
            return false;
        }
        return $this->authCtx()->isSuperAdmin();
    }
}
