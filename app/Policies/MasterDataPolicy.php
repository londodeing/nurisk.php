<?php

namespace App\Policies;

use App\Models\AuthUser;
use App\Services\Auth\AuthorizationContextService;

class MasterDataPolicy
{
    public function __construct(
        protected AuthorizationContextService $authContext
    ) {}

    public function viewAny(AuthUser $user): bool
    {
        return $this->authContext->hasAnyRole(['super_admin', 'pwnu', 'pcnu']);
    }

    public function view(AuthUser $user, $model = null): bool
    {
        return $this->authContext->hasAnyRole(['super_admin', 'pwnu', 'pcnu']);
    }

    public function create(AuthUser $user): bool
    {
        return $this->authContext->hasAnyRole(['super_admin', 'pwnu']);
    }

    public function update(AuthUser $user, $model = null): bool
    {
        return $this->authContext->hasAnyRole(['super_admin', 'pwnu']);
    }

    public function delete(AuthUser $user, $model = null): bool
    {
        return $this->authContext->isSuperAdmin();
    }

    public function approve(AuthUser $user, $model = null): bool
    {
        return $this->authContext->hasAnyRole(['super_admin', 'pwnu']);
    }
}
