<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    private const ROLE_HIERARCHY = [
        'super_admin' => 100,
        'pwnu'        => 80,
        'pcnu'        => 60,
        'relawan'     => 40,
    ];

    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (!Auth::check()) {
            \Illuminate\Support\Facades\Log::info('RoleMiddleware triggered 401: Auth::check() is false');
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
            }
            return redirect()->route('login');
        }

        $user = Auth::user();
        if (!$user->relationLoaded('peran')) {
            $user->load('peran');
        }
        $userRole = $user->peran ? $user->peran->nama_peran : null;
        $userLevel = $user->peran ? $user->peran->level_otoritas : 0;

        if (!$userRole) {
            abort(403, 'Aksi tidak diizinkan. Peran Anda tidak memiliki hak akses.');
        }

        $allowed = false;
        foreach ($roles as $role) {
            if (isset(self::ROLE_HIERARCHY[$role])) {
                $requiredLevel = self::ROLE_HIERARCHY[$role];
                if ($userLevel >= $requiredLevel) {
                    $allowed = true;
                    break;
                }
            }
            if ($userRole === $role) {
                $allowed = true;
                break;
            }
        }

        if (!$allowed) {
            abort(403, 'Aksi tidak diizinkan. Peran Anda tidak memiliki hak akses.');
        }

        return $next($request);
    }
}
