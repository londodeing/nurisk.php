<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    private const ROLE_HIERARCHY = [
        'super_admin' => 1,
        'pwnu'        => 2,
        'pcnu'        => 3,
    ];

    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = null;

        if ($request->is('api/*')) {
            if (Auth::guard('sanctum')->check()) {
                $user = Auth::guard('sanctum')->user();
            } else {
                $user = $request->user('sanctum');
            }
        }

        if (!$user && Auth::guard('web')->check()) {
            $user = Auth::guard('web')->user();
        }

        if (!$user) {
            $user = $request->user();
        }

        if (!$user) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
            }
            return redirect()->route('login');
        }

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
            if ($userRole === $role) {
                $allowed = true;
                break;
            }
            if (isset(self::ROLE_HIERARCHY[$role])) {
                $requiredLevel = self::ROLE_HIERARCHY[$role];
                if ($userLevel <= $requiredLevel) {
                    $allowed = true;
                    break;
                }
            }
        }

        if (!$allowed) {
            abort(403, 'Aksi tidak diizinkan. Peran Anda tidak memiliki hak akses.');
        }

        return $next($request);
    }
}
