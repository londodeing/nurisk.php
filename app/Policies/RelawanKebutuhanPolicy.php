<?php

namespace App\Policies;

use App\Models\AuthUser;
use App\Models\RelawanKebutuhan;
use App\Services\Auth\AuthorizationContextService;

class RelawanKebutuhanPolicy
{
    protected AuthorizationContextService $authContext;

    public function __construct(AuthorizationContextService $authContext)
    {
        $this->authContext = $authContext;
    }

    public function viewAny(AuthUser $user): bool
    {
        return $this->authContext->hasAnyRole(['super_admin', 'pwnu', 'pcnu', 'mwc', 'relawan']);
    }

    public function view(AuthUser $user, RelawanKebutuhan $kebutuhan): bool
    {
        return $this->viewAny($user);
    }

    public function create(AuthUser $user): bool
    {
        return $this->authContext->hasAnyRole(['super_admin', 'pwnu', 'pcnu']);
    }

    public function update(AuthUser $user, RelawanKebutuhan $kebutuhan): bool
    {
        if ($this->authContext->hasAnyRole(['super_admin', 'pwnu'])) {
            return true;
        }

        if ($this->authContext->hasRole('pcnu')) {
            $kebutuhan->loadMissing('posaju.insiden');
            if ($kebutuhan->posaju && $kebutuhan->posaju->insiden && $kebutuhan->posaju->insiden->id_pcnu == $this->authContext->getScopeId()) {
                return true;
            }
        }

        return false;
    }

    public function delete(AuthUser $user, RelawanKebutuhan $kebutuhan): bool
    {
        return $this->update($user, $kebutuhan);
    }
}
