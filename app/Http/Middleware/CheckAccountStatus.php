<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckAccountStatus
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        \Illuminate\Support\Facades\Log::info('CheckAccountStatus middleware executed');
        if (Auth::check()) {
            // Kueri segar untuk memverifikasi status akun terbaru di database
            $user = \App\Models\AuthUser::query()
                ->select(['id_pengguna', 'status_akun'])
                ->find(Auth::id());

            if (!$user || !$user->isAktif()) {
                // Logout paksa, bersihkan session dan redirect ke login
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('login')->withErrors([
                    'no_hp' => __('Akun Anda telah dinonaktifkan atau ditangguhkan.'),
                ]);
            }
        }

        return $next($request);
    }
}
