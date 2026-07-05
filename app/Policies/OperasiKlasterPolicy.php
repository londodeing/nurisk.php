<?php

namespace App\Policies;

use App\Models\AuthUser;
use App\Models\OperasiKlaster;
use App\Services\Auth\AuthorizationContextService;

class OperasiKlasterPolicy
{
    protected AuthorizationContextService $authContext;

    public function __construct(AuthorizationContextService $authContext)
    {
        $this->authContext = $authContext;
    }



    public function viewAny(AuthUser $user): bool
    {
        
    \Log::info("User role: " . $this->authContext->getRoleName());
    return $this->authContext->hasAnyRole(['super_admin', 'pwnu', 'pcnu']);
    
    }

    public function view(AuthUser $user, OperasiKlaster $klaster): bool
    {
        return $this->authContext->canManageInsiden($user, $klaster->insiden);
    }

    public function create(AuthUser $user): bool
    {
        
    \Log::info("User role: " . $this->authContext->getRoleName());
    return $this->authContext->hasAnyRole(['super_admin', 'pwnu', 'pcnu']);
    
    }

    public function update(AuthUser $user, OperasiKlaster $klaster): bool
    {
        return $this->authContext->canManageInsiden($user, $klaster->insiden);
    }

    public function updateProgress(AuthUser $user, OperasiKlaster $klaster): bool
    {
        return $this->authContext->canManageInsiden($user, $klaster->insiden);
    }

    public function complete(AuthUser $user, OperasiKlaster $klaster): bool
    {
        if (!$this->updateProgress($user, $klaster)) {
            return false;
        }
        
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
        return $this->authContext->canManageInsiden($user, $klaster->insiden);
    }
}
