<?php

namespace App\Policies;

use App\Models\AuthUser;
use App\Models\OperasiInsiden;
use App\Services\Auth\AuthorizationContextService;
use Illuminate\Auth\Access\HandlesAuthorization;

class EskalasiPolicy
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

    public function view(AuthUser $user): bool
    {
        return $this->authCtx()->hasAnyRole(['super_admin', 'pwnu', 'pcnu']);
    }

    public function create(AuthUser $user, OperasiInsiden $insiden): bool
    {
        if ($insiden->isTerkunci()) {
            return false;
        }
        return $this->authCtx()->hasAnyRole(['super_admin', 'pwnu']);
    }

    public function delete(AuthUser $user): bool
    {
        return $this->authCtx()->isSuperAdmin();
    }
}
