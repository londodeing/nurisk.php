<?php

namespace App\Policies;

use App\Models\AuthUser;
use App\Models\RelawanPendaftaran;
use App\Services\Auth\AuthorizationContextService;

class RelawanPendaftaranPolicy
{
    protected AuthorizationContextService $authContext;

    public function __construct(AuthorizationContextService $authContext)
    {
        $this->authContext = $authContext;
    }

    public function approveRelawan(AuthUser $user, RelawanPendaftaran $pendaftaran): bool
    {
        if ($this->authContext->hasAnyRole(['super_admin', 'pwnu'])) {
            return true;
        }

        if ($this->authContext->hasRole('pcnu')) {
            $kebutuhan = $pendaftaran->kebutuhan;
            if ($kebutuhan && $kebutuhan->insiden && $kebutuhan->insiden->id_pcnu == $this->authContext->getScopeId()) {
                return true;
            }
        }

        return false;
    }

    public function rejectRelawan(AuthUser $user, RelawanPendaftaran $pendaftaran): bool
    {
        return $this->approveRelawan($user, $pendaftaran);
    }

    public function assignRelawan(AuthUser $user, RelawanPendaftaran $pendaftaran): bool
    {
        return $this->approveRelawan($user, $pendaftaran);
    }
}
