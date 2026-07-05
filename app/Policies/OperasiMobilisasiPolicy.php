<?php

namespace App\Policies;

use App\Models\AuthUser;
use App\Models\OperasiMobilisasi;
use App\Services\Auth\AuthorizationContextService;

class OperasiMobilisasiPolicy
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

    protected function canManageOrOwn(AuthUser $user, OperasiMobilisasi $mobilisasi): bool
    {
        if ($this->authContext->canManageInsiden($user, $mobilisasi->insiden)) {
            return true;
        }

        if ($this->authContext->hasRole('relawan') || $this->authContext->hasRole('mwc') || $this->authContext->hasRole('ranting')) {
            return $mobilisasi->id_pengguna === $user->id_pengguna;
        }

        return false;
    }

    public function view(AuthUser $user, OperasiMobilisasi $mobilisasi): bool
    {
        return $this->canManageOrOwn($user, $mobilisasi);
    }

    public function create(AuthUser $user, \App\Models\OperasiInsiden $insiden): bool
    {
        return $this->authContext->canManageInsiden($user, $insiden);
    }

    public function update(AuthUser $user, OperasiMobilisasi $mobilisasi): bool
    {
        return $this->authContext->canManageInsiden($user, $mobilisasi->insiden);
    }

    public function delete(AuthUser $user, OperasiMobilisasi $mobilisasi): bool
    {
        return $this->authContext->canManageInsiden($user, $mobilisasi->insiden);
    }

    public function approve(AuthUser $user, OperasiMobilisasi $mobilisasi): bool
    {
        return $this->canManageOrOwn($user, $mobilisasi);
    }

    public function depart(AuthUser $user, OperasiMobilisasi $mobilisasi): bool
    {
        return $this->canManageOrOwn($user, $mobilisasi);
    }

    public function arrive(AuthUser $user, OperasiMobilisasi $mobilisasi): bool
    {
        return $this->canManageOrOwn($user, $mobilisasi);
    }

    public function finish(AuthUser $user, OperasiMobilisasi $mobilisasi): bool
    {
        return $this->canManageOrOwn($user, $mobilisasi);
    }

    public function cancel(AuthUser $user, OperasiMobilisasi $mobilisasi): bool
    {
        return $this->canManageOrOwn($user, $mobilisasi);
    }
}
