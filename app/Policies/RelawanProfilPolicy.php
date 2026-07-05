<?php

namespace App\Policies;

use App\Models\AuthUser;
use App\Models\AuthPenggunaProfil;
use App\Services\Auth\AuthorizationContextService;

class RelawanProfilPolicy
{
    protected AuthorizationContextService $authContext;

    public function __construct(AuthorizationContextService $authContext)
    {
        $this->authContext = $authContext;
    }

    public function viewProfil(AuthUser $user, AuthPenggunaProfil $profil): bool
    {
        if ($this->authContext->hasAnyRole(['super_admin', 'pwnu', 'pcnu'])) {
            return true;
        }

        $currentUser = $this->authContext->getCurrentUser();
        return $currentUser && $currentUser->id_pengguna === $profil->id_pengguna;
    }

    public function updateProfil(AuthUser $user, AuthPenggunaProfil $profil): bool
    {
        $currentUser = $this->authContext->getCurrentUser();
        return $currentUser && $currentUser->id_pengguna === $profil->id_pengguna;
    }
}
