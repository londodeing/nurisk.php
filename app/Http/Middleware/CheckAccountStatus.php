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
        
        // Handle API/JSON requests
        if ($request->expectsJson() || $request->is('api/*')) {
            $user = $request->user();
            if ($user) {
                // 1. Validasi status akun (Source of Truth)
                $dbUser = \App\Models\AuthUser::query()
                    ->select(['id_pengguna', 'status_akun', 'id_peran'])
                    ->find($user->id_pengguna);

                if (!$dbUser || !$dbUser->isAktif()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Akun Anda telah dinonaktifkan atau ditangguhkan.'
                    ], 401);
                }

                // 2. Validasi silang Mandat Aktif (Role & Scope)
                $clientRole = $request->header('X-Role');
                $clientScopeId = $request->header('X-Scope-Id');
                
                $exemptedRoles = ['public', 'publik', 'relawan', 'trc', 'pwnu', 'pcnu', 'super_admin'];
                if ($clientRole && !in_array(strtolower($clientRole), $exemptedRoles)) {
                    $hasValidMandate = \Illuminate\Support\Facades\DB::table('pengguna_jabatan')
                        ->where('id_pengguna', $user->id_pengguna)
                        ->where('status_aktif', true)
                        ->exists();

                    if (!$hasValidMandate) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Mandat Anda telah kedaluwarsa atau dicabut.'
                        ], 403);
                    }
                }
            }
            return $next($request);
        }

        // Handle Web requests
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

