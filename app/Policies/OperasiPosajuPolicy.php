<?php

namespace App\Policies;

use App\Models\AuthUser;
use App\Models\OperasiPosaju;
use App\Services\Auth\AuthorizationContextService;

class OperasiPosajuPolicy
{
    protected AuthorizationContextService $authContext;

    public function __construct(AuthorizationContextService $authContext)
    {
        $this->authContext = $authContext;
    }



    public function viewAny(AuthUser $user): bool
    {
        return $this->authContext->hasAnyRole(['super_admin', 'pwnu', 'pcnu']);
    }

    public function view(AuthUser $user, OperasiPosaju $posaju): bool
    {
        return $this->authContext->canManageInsiden($user, $posaju->insiden);
    }

    public function create(AuthUser $user): bool
    {
        return $this->authContext->hasAnyRole(['super_admin', 'pwnu', 'pcnu']);
    }

    public function update(AuthUser $user, OperasiPosaju $posaju): bool
    {
        return $this->authContext->canManageInsiden($user, $posaju->insiden);
    }

    public function activate(AuthUser $user, OperasiPosaju $posaju): bool
    {
        return $this->update($user, $posaju) && $posaju->status_alur === 'direncanakan';
    }

    public function extend(AuthUser $user, OperasiPosaju $posaju): bool
    {
        return $this->update($user, $posaju) && $posaju->status_alur === 'aktif';
    }

    public function close(AuthUser $user, OperasiPosaju $posaju): bool
    {
        return $this->update($user, $posaju) && in_array($posaju->status_alur, ['aktif', 'diperpanjang']);
    }
}
