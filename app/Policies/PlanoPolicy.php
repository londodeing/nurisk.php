<?php

namespace App\Policies;

use App\Models\AssessmentUtama;
use App\Models\AuthUser;
use App\Models\OperasiInsiden;
use App\Models\OperasiPleno;
use App\Services\Auth\AuthorizationContextService;
use Illuminate\Auth\Access\HandlesAuthorization;

class PlanoPolicy
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

    public function view(AuthUser $user, OperasiPleno $pleno): bool
    {
        if ($this->authCtx()->hasAnyRole(['super_admin', 'pwnu'])) {
            return true;
        }
        if ($this->authCtx()->hasRole('pcnu')) {
            return $this->authCtx()->getScopeId() === $pleno->insiden->id_pcnu;
        }
        return false;
    }

    public function create(AuthUser $user, OperasiInsiden $insiden): bool
    {
        if ($insiden->isClosed()) {
            return false;
        }

        if (!$this->authCtx()->hasAnyRole(['super_admin', 'pwnu', 'pcnu'])) {
            return false;
        }

        $hasApprovedAssessment = AssessmentUtama::where('id_insiden', $insiden->id_insiden)
            ->where('is_latest', true)
            ->where('status_review', 'approved')
            ->exists();

        if (!$hasApprovedAssessment) {
            return false;
        }

        return true;
    }

    private function insidenTidakKunci(OperasiPleno $pleno): bool
    {
        return !$pleno->insiden->isClosed();
    }

    public function tambahKeputusan(AuthUser $user, OperasiPleno $pleno): bool
    {
        if (!$this->insidenTidakKunci($pleno)) {
            return false;
        }
        if ($pleno->isFinal()) {
            return false;
        }
        return $this->authCtx()->hasAnyRole(['super_admin', 'pwnu']);
    }

    public function tambahPeserta(AuthUser $user, OperasiPleno $pleno): bool
    {
        if (!$this->insidenTidakKunci($pleno)) {
            return false;
        }
        if (!$pleno->isDraft()) {
            return false;
        }
        if ($this->authCtx()->hasAnyRole(['super_admin', 'pwnu'])) {
            return true;
        }
        if ($this->authCtx()->hasRole('pcnu')) {
            return $this->authCtx()->getScopeId() === $pleno->insiden->id_pcnu;
        }
        return false;
    }

    public function update(AuthUser $user, OperasiPleno $pleno): bool
    {
        if (!$this->insidenTidakKunci($pleno)) {
            return false;
        }
        if ($pleno->isFinal()) {
            return false;
        }
        return $this->authCtx()->hasAnyRole(['super_admin', 'pwnu', 'pcnu']);
    }

    public function finalisasi(AuthUser $user, OperasiPleno $pleno): bool
    {
        if (!$this->insidenTidakKunci($pleno)) {
            return false;
        }
        if ($pleno->isFinal()) {
            return false;
        }
        return $this->authCtx()->hasAnyRole(['super_admin', 'pwnu']);
    }

    public function delete(AuthUser $user, OperasiPleno $pleno): bool
    {
        if (!$this->insidenTidakKunci($pleno)) {
            return false;
        }
        if (!$pleno->isDraft()) {
            return false;
        }
        return $this->authCtx()->isSuperAdmin();
    }
}
