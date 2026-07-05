<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureActiveApprover
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            abort(401, 'Unauthorized');
        }

        // Block users with inactive statuses
        $blockedStatuses = ['suspend', 'nonaktif', 'archived'];

        if (in_array(strtolower($user->status_akun), $blockedStatuses)) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Akun anda tidak aktif atau disuspend. Anda tidak diizinkan melakukan persetujuan.'
                ], 403);
            }
            abort(403, 'Akun anda tidak aktif atau disuspend. Anda tidak diizinkan melakukan persetujuan.');
        }

        return $next($request);
    }
}
