<?php

namespace App\Policies;

use App\Models\AuthUser;
use App\Models\RelawanPenugasan;
use App\Services\Auth\AuthorizationContextService;

class RelawanPenugasanPolicy
{
    protected AuthorizationContextService $authContext;

    public function __construct(AuthorizationContextService $authContext)
    {
        $this->authContext = $authContext;
    }

    public function completePenugasan(AuthUser $user, RelawanPenugasan $penugasan): bool
    {
        if ($this->authContext->hasAnyRole(['super_admin', 'pwnu'])) {
            return true;
        }

        if ($this->authContext->hasRole('pcnu')) {
            $pendaftaran = $penugasan->pendaftaran;
            $kebutuhan = $pendaftaran?->kebutuhan;
            if ($kebutuhan && $kebutuhan->insiden && $kebutuhan->insiden->id_pcnu == $this->authContext->getScopeId()) {
                return true;
            }
        }

        return false;
    }
}
