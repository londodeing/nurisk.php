<?php

namespace App\Policies;

use App\Models\AuthUser;
use App\Models\OperasiInsiden;
use App\Models\OperasiKlaster;
use App\Models\OperasiPenugasan;
use Illuminate\Auth\Access\HandlesAuthorization;
use App\Services\Auth\AuthorizationContextService;

class KlasterPolicy
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

    public function viewAny(AuthUser $user, ?OperasiInsiden $insiden = null): bool
    {
        if (!$insiden) {
            $insidenId = request()->input('id_insiden') ?? request()->route('insiden');
            if ($insidenId) {
                $insiden = OperasiInsiden::find($insidenId);
            }
        }
        if (!$insiden) {
            return $this->authCtx()->hasAnyRole(['super_admin', 'pwnu', 'pcnu']);
        }

        if ($this->authCtx()->hasRole('relawan')) {
            $isAssigned = OperasiPenugasan::where('id_insiden', $insiden->id_insiden)
                ->where('id_pengguna', $user->id_pengguna)
                ->whereIn('status_penugasan', ['aktif', 'selesai'])
                ->exists();
            if ($isAssigned) return true;
        }

        return $this->bolehAksesInsiden($user, $insiden);
    }

    public function view(AuthUser $user, OperasiKlaster $klaster): bool
    {
        $insiden = $klaster->insiden;
        if (!$insiden) return false;
        return $this->bolehAksesInsiden($user, $insiden);
    }

    public function create(AuthUser $user, ?OperasiInsiden $insiden = null): bool
    {
        if (!$insiden) {
            $insidenId = request()->input('id_insiden') ?? request()->route('insiden');
            if ($insidenId) {
                $insiden = OperasiInsiden::find($insidenId);
            }
        }
        if (!$insiden) {
            return false;
        }

        if ($insiden->isTerkunci()) return false;
        
        return $this->bolehAksesInsiden($user, $insiden);
    }

    public function update(AuthUser $user, OperasiKlaster $klaster): bool
    {
        $insiden = $klaster->insiden;
        if (!$insiden || $insiden->isTerkunci()) return false;

        return $this->bolehAksesInsiden($user, $insiden);
    }

    public function updateProgress(AuthUser $user, OperasiKlaster $klaster): bool
    {
        $insiden = $klaster->insiden;
        if (!$insiden || $insiden->isTerkunci()) return false;

        return $this->bolehAksesInsiden($user, $insiden);
    }

    public function complete(AuthUser $user, OperasiKlaster $klaster): bool
    {
        $insiden = $klaster->insiden;
        if (!$insiden || $insiden->isTerkunci()) return false;
        if (!$this->bolehAksesInsiden($user, $insiden)) return false;

        if ($klaster->status_klaster !== 'aktif') {
            return false;
        }

        // Tidak boleh ada tugas aktif
        $hasActiveTasks = $klaster->tugas()->whereIn('status_tugas', ['rencana', 'berjalan', 'tertunda'])->exists();
        if ($hasActiveTasks) {
            return false;
        }

        return true;
    }

    public function delete(AuthUser $user, OperasiKlaster $klaster): bool
    {
        $insiden = $klaster->insiden;
        if (!$insiden || $insiden->isTerkunci()) return false;
        
        return $this->bolehAksesInsiden($user, $insiden);
    }
}
