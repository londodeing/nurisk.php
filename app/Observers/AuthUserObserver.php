<?php

namespace App\Observers;

use App\Models\AuthUser;
use Illuminate\Support\Facades\Log;

class AuthUserObserver
{
    public function updated(AuthUser $user): void
    {
        if ($user->wasChanged('status_akun') && $user->status_akun !== 'aktif') {
            $count = $user->tokens()->count();
            $user->tokens()->delete();
            Log::info('Tokens revoked due to account status change', [
                'user_id' => $user->id_pengguna,
                'new_status' => $user->status_akun,
                'tokens_revoked' => $count,
            ]);
        }
    }

    public function deleted(AuthUser $user): void
    {
        $count = $user->tokens()->count();
        $user->tokens()->delete();
        Log::info('Tokens revoked due to account deletion', [
            'user_id' => $user->id_pengguna,
            'tokens_revoked' => $count,
        ]);
    }

    public function forceDeleted(AuthUser $user): void
    {
        $count = $user->tokens()->count();
        $user->tokens()->delete();
        Log::info('Tokens revoked due to account force deletion', [
            'user_id' => $user->id_pengguna,
            'tokens_revoked' => $count,
        ]);
    }
}
