<?php

namespace App\Policies;

use App\Models\AuthUser;
use App\Models\OperasiInsiden;
use App\Models\OperasiPleno;
use App\Services\Auth\AuthorizationContextService;
use Illuminate\Auth\Access\HandlesAuthorization;

class PlenoPolicy
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
        if ($this->authCtx()->hasRole('pcnu') && $user->default_scope_id === $insiden->id_pcnu) {
            return true;
        }
        return false;
    }

    public function viewAny(AuthUser $user, OperasiInsiden $insiden): bool
    {
        return $this->bolehAksesInsiden($user, $insiden) || $this->authCtx()->hasRole('trc');
    }

    public function view(AuthUser $user, OperasiPleno $pleno): bool
    {
        if ($this->bolehAksesInsiden($user, $pleno->insiden)) {
            return true;
        }
        
        // Peserta rapat bisa lihat detail pleno
        $isPeserta = $pleno->peserta()->where('id_pengguna', $user->id_pengguna)->exists();
        if ($isPeserta) {
            return true;
        }

        return false;
    }

    public function create(AuthUser $user, OperasiInsiden $insiden): bool
    {
        // Hanya Pimpinan (PWNU/PCNU setempat atau super admin) yang bisa buat pleno
        return $this->bolehAksesInsiden($user, $insiden);
    }

    public function update(AuthUser $user, OperasiPleno $pleno): bool
    {
        // Hanya bisa diubah jika belum final
        if ($pleno->isFinal() || $pleno->status_pleno === 'dibatalkan') {
            return false;
        }

        // Pimpinan rapat atau notulis bisa merubah
        if ($pleno->pimpinan_pleno === $user->id_pengguna || $pleno->notulis_pleno === $user->id_pengguna) {
            return true;
        }
        
        // Admin wilayah juga bisa merubah
        return $this->bolehAksesInsiden($user, $pleno->insiden);
    }

    public function delete(AuthUser $user, OperasiPleno $pleno): bool
    {
        // Hanya bisa dihapus jika masih draft
        if ($pleno->status_pleno !== 'draft') {
            return false;
        }

        return $this->update($user, $pleno);
    }

    public function finalize(AuthUser $user, OperasiPleno $pleno): bool
    {
        // Hanya pimpinan pleno yang sah yang bisa melakukan finalisasi (mengunci pleno)
        return $pleno->pimpinan_pleno === $user->id_pengguna;
    }
}
