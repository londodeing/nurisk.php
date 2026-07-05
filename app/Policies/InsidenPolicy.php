<?php

namespace App\Policies;

use App\Models\AuthUser;
use App\Models\OperasiInsiden;
use App\Services\Auth\AuthorizationContextService;
use Illuminate\Auth\Access\HandlesAuthorization;

class InsidenPolicy
{
    use HandlesAuthorization;

    /**
     * Helper to retrieve AuthorizationContextService.
     */
    private function authCtx(): AuthorizationContextService
    {
        return app(AuthorizationContextService::class);
    }

    /**
     * Internal check if user can access a specific incident.
     */
    private function bolehAksesInsiden(AuthUser $user, OperasiInsiden $insiden): bool
    {
        // super_admin dan pwnu bisa akses semua insiden
        if ($this->authCtx()->hasAnyRole(['super_admin', 'pwnu'])) {
            return true;
        }
        // pcnu hanya bisa akses insiden di scope PCNU-nya sendiri
        if ($this->authCtx()->hasRole('pcnu')) {
            return $this->authCtx()->getScopeId() === $insiden->id_pcnu;
        }
        return false;
    }

    /**
     * Determine whether the user can view any incidents.
     */
    public function viewAny(AuthUser $user): bool
    {
        $roleName = $this->authCtx()->getRoleName();
        \Log::info('POLICY viewAny Check', [
            'user_id' => $user->id_pengguna,
            'auth_id' => auth()->id(),
            'role_name' => $roleName,
        ]);
        return in_array($roleName, ['super_admin', 'pwnu', 'pcnu']);
    }

    /**
     * Determine whether the user can view the incident.
     */
    public function view(AuthUser $user, OperasiInsiden $insiden): bool
    {
        return $this->bolehAksesInsiden($user, $insiden);
    }

    /**
     * Determine whether the user can create incidents.
     */
    public function create(AuthUser $user): bool
    {
        return $this->authCtx()->hasAnyRole(['super_admin', 'pwnu', 'pcnu']);
    }

    /**
     * Determine whether the user can update the incident.
     */
    public function update(AuthUser $user, OperasiInsiden $insiden): bool
    {
        // Insiden terkunci TIDAK BOLEH diubah oleh siapapun
        if ($insiden->isTerkunci()) {
            return false;
        }
        return $this->bolehAksesInsiden($user, $insiden);
    }

    /**
     * Determine whether the user can delete the incident.
     */
    public function delete(AuthUser $user, OperasiInsiden $insiden): bool
    {
        // Hanya super_admin yang bisa soft-delete insiden
        return $this->authCtx()->isSuperAdmin();
    }

    /**
     * Determine whether the user can transition/change the status of the incident.
     */
    public function ubahStatus(AuthUser $user, OperasiInsiden $insiden): bool
    {
        if ($insiden->isTerkunci()) {
            return false;
        }
        return $this->bolehAksesInsiden($user, $insiden);
    }

    /**
     * Determine whether the user can issue SPK/Surat Tugas for the incident.
     */
    public function issueSpk(AuthUser $user, OperasiInsiden $insiden): bool
    {
        if ($insiden->isTerkunci()) {
            return false;
        }

        if ($this->authCtx()->isSuperAdmin()) {
            return true;
        }

        // Pimpinan (Ketua PCNU / Ketua PWNU) must hold the active position matching the scope
        return $this->authCtx()->hasActiveJabatan(['ketua-pcnu', 'ketua-pwnu'], 'pcnu', $insiden->id_pcnu);
    }
}
