<?php

namespace App\Policies;

use App\Models\AuthUser;
use App\Services\Auth\AuthorizationContextService;

class DashboardPolicy
{
    private function ctx(): AuthorizationContextService
    {
        return app(AuthorizationContextService::class);
    }

    public function viewCommandCenter(AuthUser $user): bool
    {
        if ($this->ctx()->hasAnyRole(['super_admin', 'pwnu', 'pcnu'])) {
            return true;
        }

        // Relawan dengan penugasan aktif di insiden manapun
        if ($this->ctx()->hasRole('relawan')) {
            return $user->penugasanAktif()->exists();
        }

        return false;
    }
}
