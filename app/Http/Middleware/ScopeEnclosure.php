<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class ScopeEnclosure
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$allowedScopes
     */
    public function handle(Request $request, Closure $next, string ...$allowedScopes): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        // Ambil data scope dinamis langsung dari database untuk menghindari stale context
        $user = \App\Models\AuthUser::query()
            ->select(['id_pengguna', 'default_scope_type', 'default_scope_id'])
            ->find(Auth::id());

        $userScopeType = $user ? $user->default_scope_type : null;

        // Jika scope user tidak didefinisikan atau tidak terdaftar dalam daftar parameter allowedScopes
        if (!$userScopeType || !in_array($userScopeType, $allowedScopes)) {
            abort(403, 'Aksi ditolak. Wilayah kepengurusan (scope) Anda tidak memiliki wewenang untuk area ini.');
        }

        return $next($request);
    }
}
