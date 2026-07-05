<?php

namespace App\Services\Auth;

use App\Models\AuthUser;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthenticationService
{
    /**
     * Memproses verifikasi login pengguna menggunakan nomor handphone dan kata sandi.
     *
     * @param array $credentials
     * @return bool
     * @throws ValidationException
     */
    public function login(array $credentials): bool
    {
        $noHp = $credentials['no_hp'] ?? '';
        $password = $credentials['kata_sandi'] ?? '';

        // 1. Cari pengguna berdasarkan nomor handphone
        $user = AuthUser::where('no_hp', $noHp)->first();

        \Illuminate\Support\Facades\Log::info("DEBUG LOGIN", [
            'no_hp_input' => $noHp,
            'password_input' => $password,
            'user_found' => $user ? true : false,
            'db_connection' => \Illuminate\Support\Facades\DB::connection()->getName(),
            'db_database' => \Illuminate\Support\Facades\DB::connection()->getDatabaseName(),
        ]);

        if (!$user) {
            throw ValidationException::withMessages([
                'no_hp' => __('Nomor handphone atau kata sandi yang Anda masukkan salah.'),
            ]);
        }

        // 2. Verifikasi kata sandi
        if (!Hash::check($password, $user->kata_sandi)) {
            throw ValidationException::withMessages([
                'no_hp' => __('Nomor handphone atau kata sandi yang Anda masukkan salah.'),
            ]);
        }

        // 3. Verifikasi status akun (hanya status 'aktif' yang diizinkan masuk)
        if (!$user->isAktif()) {
            throw ValidationException::withMessages([
                'no_hp' => __('Akun Anda belum aktif atau sedang dinonaktifkan/ditangguhkan.'),
            ]);
        }

        // 4. Lakukan login ke dalam guard web Laravel
        Auth::login($user);

        // 5. Update timestamp terakhir_masuk
        $user->update([
            'terakhir_masuk' => now(),
        ]);

        return true;
    }

    /**
     * Memproses log keluar dari sistem.
     *
     * @return void
     */
    public function logout(): void
    {
        Auth::logout();
    }
}
