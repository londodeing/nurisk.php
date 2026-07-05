<?php

namespace App\Policies;

use App\Models\AuthUser;
use App\Models\OperasiPenugasan;

class AssignmentPolicy
{
    /**
     * Determine whether the user can create models.
     */
    public function create(AuthUser $user): bool
    {
        return $user->hasRole(['super_admin', 'commander', 'pcnu', 'pwnu']);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(AuthUser $user, OperasiPenugasan $penugasan): bool
    {
        return $user->hasRole(['super_admin', 'commander', 'pcnu', 'pwnu']) || $user->id_pengguna === $penugasan->id_pengguna;
    }
}
