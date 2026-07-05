<?php

namespace App\Policies;

use App\Models\AuthUser;
use App\Models\OperasiInsiden;
use App\Models\OperasiSitrep;
use App\Models\OperasiPenugasan;
use Illuminate\Auth\Access\HandlesAuthorization;
use App\Services\Auth\AuthorizationContextService;

class SitrepPolicy
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

        // Lapis 4: Operational Assignment (Hanya komandan_insiden yang boleh create sitrep dari relawan)
        if ($this->authCtx()->hasRole('relawan')) {
            return OperasiPenugasan::where('id_insiden', $insiden->id_insiden)
                ->where('id_pengguna', $user->id_pengguna)
                ->whereIn('peran_otoritas', ['komandan_insiden'])
                ->whereNull('waktu_selesai')
                ->exists();
        }

        return false;
    }

    public function viewAny(AuthUser $user, OperasiInsiden $insiden): bool
    {
        // Untuk melihat sitrep, relawan biasa (TRC) boleh melihat asal ditugaskan
        if ($this->authCtx()->hasRole('relawan')) {
            $isAssigned = OperasiPenugasan::where('id_insiden', $insiden->id_insiden)
                ->where('id_pengguna', $user->id_pengguna)
                ->whereNull('waktu_selesai')
                ->exists();
            if ($isAssigned) return true;
        }

        return $this->bolehAksesInsiden($user, $insiden);
    }

    public function create(AuthUser $user, OperasiInsiden $insiden): bool
    {
        // Status insiden harus valid
        if (!in_array($insiden->status_insiden, ['terverifikasi', 'respon', 'pemulihan'])) {
            return false;
        }

        if ($insiden->isTerkunci()) {
            return false;
        }

        return $this->bolehAksesInsiden($user, $insiden);
    }

    public function view(AuthUser $user, OperasiSitrep $sitrep): bool
    {
        $insiden = $sitrep->insiden;
        if (!$insiden) return false;

        if ($this->authCtx()->hasRole('relawan')) {
            $isAssigned = OperasiPenugasan::where('id_insiden', $insiden->id_insiden)
                ->where('id_pengguna', $user->id_pengguna)
                ->whereNull('waktu_selesai')
                ->exists();
            if ($isAssigned) return true;
        }

        return $this->bolehAksesInsiden($user, $insiden);
    }

    public function update(AuthUser $user, OperasiSitrep $sitrep): bool
    {
        $insiden = $sitrep->insiden;
        if (!$insiden || $insiden->isTerkunci()) return false;

        if (!in_array($insiden->status_insiden, ['terverifikasi', 'respon', 'pemulihan'])) {
            return false;
        }

        if ($this->authCtx()->hasRole('relawan')) {
            $isKomandan = OperasiPenugasan::where('id_insiden', $insiden->id_insiden)
                ->where('id_pengguna', $user->id_pengguna)
                ->whereIn('peran_otoritas', ['komandan_insiden'])
                ->whereNull('waktu_selesai')
                ->exists();
            if ($isKomandan) return true;
        }

        return $this->bolehAksesInsiden($user, $insiden);
    }

    public function delete(AuthUser $user, OperasiSitrep $sitrep): bool
    {
        return $this->update($user, $sitrep);
    }
}
