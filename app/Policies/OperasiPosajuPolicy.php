<?php

namespace App\Policies;

use App\Models\AuthUser;
use App\Models\OperasiInsiden;
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

    public function create(AuthUser $user, ?OperasiInsiden $insiden = null): bool
    {
        if (!$this->authContext->hasAnyRole(['super_admin', 'pwnu', 'pcnu'])) {
            return false;
        }

        if ($insiden && $insiden->is_locked) {
            return false;
        }

        return true;
    }

    public function update(AuthUser $user, OperasiPosaju $posaju): bool
    {
        return $this->authContext->canManageInsiden($user, $posaju->insiden);
    }

    public function activate(AuthUser $user, OperasiPosaju $posaju): bool
    {
        return $posaju->status_alur !== 'ditutup'
            && $this->update($user, $posaju)
            && $posaju->status_alur === 'direncanakan'
            && !empty($posaju->id_pleno_keputusan);
    }

    public function extend(AuthUser $user, OperasiPosaju $posaju): bool
    {
        return $posaju->status_alur !== 'ditutup'
            && $this->update($user, $posaju)
            && $posaju->status_alur === 'aktif';
    }

    public function close(AuthUser $user, OperasiPosaju $posaju): bool
    {
        return $posaju->status_alur !== 'ditutup'
            && $this->update($user, $posaju)
            && in_array($posaju->status_alur, ['aktif', 'diperpanjang']);
    }

    public function tambahKomandan(AuthUser $user, OperasiPosaju $posaju): bool
    {
        if ($posaju->status_alur === 'ditutup') {
            return false;
        }

        return $this->authContext->canManageInsiden($user, $posaju->insiden);
    }
}
