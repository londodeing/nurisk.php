<?php

namespace App\Policies;

use App\Models\AuthUser;
use App\Models\OperasiTugas;
use App\Services\Auth\AuthorizationContextService;

class OperasiTugasPolicy
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

    public function view(AuthUser $user, OperasiTugas $tugas): bool
    {
        return $this->authContext->canManageInsiden($user, $tugas->klaster->insiden ?? null);
    }

    public function create(AuthUser $user): bool
    {
        return $this->authContext->hasAnyRole(['super_admin', 'pwnu', 'pcnu']);
    }

    public function update(AuthUser $user, OperasiTugas $tugas): bool
    {
        return $this->authContext->canManageInsiden($user, $tugas->klaster->insiden ?? null);
    }

    protected function hasAccess(OperasiTugas $tugas): bool
    {
        return $this->authContext->canManageInsiden($this->authContext->getCurrentUser(), $tugas->klaster->insiden ?? null);
    }

    public function start(AuthUser $user, OperasiTugas $tugas): bool
    {
        return $this->hasAccess($tugas) && in_array($tugas->status_tugas, ['rencana', 'tertunda']);
    }

    public function pause(AuthUser $user, OperasiTugas $tugas): bool
    {
        return $this->hasAccess($tugas) && $tugas->status_tugas === 'berjalan';
    }

    public function complete(AuthUser $user, OperasiTugas $tugas): bool
    {
        return $this->hasAccess($tugas) && $tugas->status_tugas === 'berjalan';
    }
}
