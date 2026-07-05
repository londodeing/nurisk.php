<?php

namespace App\Policies;

use App\Models\AuthUser;
use App\Models\OrgAsset;
use App\Services\Auth\AuthorizationContextService;

class OrgAssetPolicy
{
    public function __construct(
        protected AuthorizationContextService $authContext
    ) {}

    public function viewAny(AuthUser $user): bool
    {
        return in_array($this->authContext->getRoleName(), ['super_admin', 'pwnu', 'pcnu', 'relawan']);
    }

    public function view(AuthUser $user, OrgAsset $aset): bool
    {
        if ($this->authContext->isSuperAdmin()) {
            return true;
        }
        $aset->loadMissing('node.unit');
        $idPcnu = $aset?->node?->unit?->id_lingkup;
        return $idPcnu && $this->authContext->canAccessInsiden($idPcnu);
    }

    public function create(AuthUser $user): bool
    {
        return $this->authContext->hasAnyRole(['super_admin', 'pwnu', 'pcnu']);
    }

    public function updateStatus(AuthUser $user, OrgAsset $aset): bool
    {
        return $this->view($user, $aset);
    }

    public function update(AuthUser $user, OrgAsset $aset): bool
    {
        return $this->view($user, $aset);
    }

    public function delete(AuthUser $user, OrgAsset $aset): bool
    {
        return $this->authContext->isSuperAdmin();
    }
}
