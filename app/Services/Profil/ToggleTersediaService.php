<?php

namespace App\Services\Profil;

use App\Models\AuthUser;
use Illuminate\Support\Facades\Log;

class ToggleTersediaService
{
    public function toggle(AuthUser $user): bool
    {
        $oldStatus = $user->is_tersedia;
        $newStatus = !$oldStatus;

        $user->update(['is_tersedia' => $newStatus]);

        Log::info('[ToggleTersedia] User #{user} changed is_tersedia: {old} → {new}', [
            'user' => $user->id_pengguna,
            'old' => $oldStatus ? '1' : '0',
            'new' => $newStatus ? '1' : '0',
        ]);

        return $newStatus;
    }
}
