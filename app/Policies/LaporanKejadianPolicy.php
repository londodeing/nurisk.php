<?php

namespace App\Policies;

use App\Models\AuthUser;
use App\Models\LaporanKejadian;
use App\Services\Auth\AuthorizationContextService;

class LaporanKejadianPolicy
{
    public function __construct(
        protected AuthorizationContextService $authContext
    ) {}

    public function viewAny(AuthUser $user): bool
    {
        if ($user->hasRole('relawan')) {
            return false;
        }
        return true;
    }

    public function view(AuthUser $user, LaporanKejadian $laporan): bool
    {
        if ($user->hasRole('relawan')) {
            return false;
        }
        if ($this->authContext->hasAnyRole(['super_admin', 'pwnu'])) {
            return true;
        }
        if ($this->authContext->hasRole('pcnu')) {
            return $this->authContext->canAccessInsiden($laporan->id_pcnu ?? 0);
        }
        return $laporan->id_pengguna === $user->id_pengguna;
    }

    public function create(AuthUser $user): bool
    {
        return true;
    }

    public function validasi(AuthUser $user, LaporanKejadian $laporan): bool
    {
        return $this->authContext->hasAnyRole(['super_admin', 'pwnu', 'pcnu'])
            && $this->authContext->canAccessInsiden($laporan->id_pcnu ?? 0);
    }

    public function eskalasi(AuthUser $user, LaporanKejadian $laporan): bool
    {
        return $this->validasi($user, $laporan);
    }
}
