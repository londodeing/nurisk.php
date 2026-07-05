<?php

namespace App\Policies;

use App\Models\AuthUser;
use App\Services\Auth\AuthorizationContextService;

class OrganisasiPolicy
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
}
