<?php

namespace App\Policies;

use App\Models\AuthUser;
use App\Models\JabatanPosisi;
use App\Services\Auth\AuthorizationContextService;

class JabatanPolicy
{
    /**
     * Resolve AuthorizationContextService instance.
     */
    protected function context(): AuthorizationContextService
    {
        return app(AuthorizationContextService::class);
    }

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(AuthUser $user): bool
    {
        $role = $this->context()->getRoleName();
        return in_array($role, ['super_admin', 'pwnu', 'pcnu']);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(AuthUser $user, JabatanPosisi $jabatan): bool
    {
        $role = $this->context()->getRoleName();
        return in_array($role, ['super_admin', 'pwnu', 'pcnu']);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(AuthUser $user): bool
    {
        return $this->context()->isSuperAdmin();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(AuthUser $user, JabatanPosisi $jabatan): bool
    {
        return $this->context()->isSuperAdmin();
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(AuthUser $user, JabatanPosisi $jabatan): bool
    {
        return $this->context()->isSuperAdmin();
    }
}
