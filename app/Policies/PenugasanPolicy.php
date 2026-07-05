<?php

namespace App\Policies;

use App\Models\AuthUser;
use App\Models\OperasiInsiden;
use App\Models\OperasiPenugasan;
use Illuminate\Auth\Access\HandlesAuthorization;
use App\Services\Auth\AuthorizationContextService;

class PenugasanPolicy
{
    use HandlesAuthorization;

    private function authCtx(): AuthorizationContextService
    {
        return app(AuthorizationContextService::class);
    }

    private function bolehAksesInsiden(AuthUser $user, OperasiInsiden $insiden): bool
    {
        if ($this->authCtx()->hasAnyRole(['super_admin', 'pwnu'])) {
            return true;
        }
        if ($this->authCtx()->hasRole('pcnu')) {
            return $user->default_scope_id === $insiden->id_pcnu;
        }

        if ($this->authCtx()->hasRole('relawan')) {
            return OperasiPenugasan::where('id_insiden', $insiden->id_insiden)
                ->where('id_pengguna', $user->id_pengguna)
                ->whereIn('peran_otoritas', ['komandan_insiden'])
                ->where('status_penugasan', 'aktif')
                ->exists();
        }

        return false;
    }

    public function viewAny(AuthUser $user, OperasiInsiden $insiden): bool
    {
        // TRC bisa melihat penugasan lain dalam insiden yang sama
        if ($this->authCtx()->hasRole('relawan')) {
            $isAssigned = OperasiPenugasan::where('id_insiden', $insiden->id_insiden)
                ->where('id_pengguna', $user->id_pengguna)
                ->whereIn('status_penugasan', ['aktif', 'selesai'])
                ->exists();
            if ($isAssigned) return true;
        }

        return $this->bolehAksesInsiden($user, $insiden);
    }

    public function create(AuthUser $user, OperasiInsiden $insiden): bool
    {
        if ($insiden->isTerkunci()) return false;
        
        return $this->bolehAksesInsiden($user, $insiden);
    }

    public function update(AuthUser $user, OperasiPenugasan $penugasan): bool
    {
        $insiden = $penugasan->insiden;
        if (!$insiden || $insiden->isTerkunci()) return false;

        return $this->bolehAksesInsiden($user, $insiden);
    }

    public function delete(AuthUser $user, OperasiPenugasan $penugasan): bool
    {
        $insiden = $penugasan->insiden;
        if (!$insiden || $insiden->isTerkunci()) return false;

        return $this->bolehAksesInsiden($user, $insiden);
    }

    public function view(AuthUser $user, OperasiPenugasan $penugasan): bool
    {
        $insiden = $penugasan->insiden;
        if (!$insiden) return false;

        if ($this->authCtx()->hasRole('relawan')) {
            $isAssigned = OperasiPenugasan::where('id_insiden', $insiden->id_insiden)
                ->where('id_pengguna', $user->id_pengguna)
                ->whereIn('status_penugasan', ['aktif', 'selesai'])
                ->exists();
            if ($isAssigned) return true;
        }

        return $this->bolehAksesInsiden($user, $insiden);
    }
}
