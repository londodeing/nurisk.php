<?php

namespace App\Policies;

use App\Models\AuthUser;
use App\Services\Auth\AuthorizationContextService;

class AuthUserPolicy
{
    protected function context(): AuthorizationContextService
    {
        return app(AuthorizationContextService::class);
    }

    public function viewAny(AuthUser $user): bool
    {
        return in_array($this->context()->getRoleName(), ['super_admin', 'pwnu', 'pcnu', 'trc', 'relawan']);
    }

    public function view(AuthUser $user, AuthUser $model): bool
    {
        return $this->viewAny($user);
    }

    public function viewApprovalQueue(AuthUser $user): bool
    {
        return in_array($this->context()->getRoleName(), ['super_admin', 'pwnu', 'pcnu']);
    }

    public function update(AuthUser $user, AuthUser $model): bool
    {
        return in_array($this->context()->getRoleName(), ['super_admin', 'pwnu']);
    }

    public function approve(AuthUser $user, AuthUser $calon): bool
    {
        $ctx = $this->context();
        $role = $ctx->getRoleName();

        if ($role === 'super_admin') {
            return $calon->peran()->where('nama_peran', 'pwnu')->exists();
        }

        if ($role === 'pwnu') {
            return $calon->jabatanPosisi()
                ->whereHas('jabatan', fn($q) => $q->whereIn('slug', ['anggota-trc-pwnu', 'admin-pcnu']))
                ->exists();
        }

        if ($role === 'pcnu') {
            $jabatan = $calon->jabatanPosisi()
                ->whereHas('jabatan', fn($q) => $q->where('slug', 'anggota-trc-pcnu'))
                ->first();

            if (!$jabatan) {
                return false;
            }

            return $jabatan->tipe_lingkup === 'pcnu'
                && (int) $jabatan->id_lingkup === (int) $ctx->getScopeId();
        }

        return false;
    }
}
