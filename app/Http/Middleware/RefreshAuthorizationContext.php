<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RefreshAuthorizationContext
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            // Kueri segar dari database untuk menghindari stale memory pada request terautentikasi
            $user = \App\Models\AuthUser::query()
                ->select(['id_pengguna', 'id_peran', 'default_scope_type', 'default_scope_id'])
                ->find(Auth::id());

            if ($user) {
                session([
                    'id_peran' => $user->id_peran,
                    'default_scope_type' => $user->default_scope_type,
                    'default_scope_id' => $user->default_scope_id,
                ]);
            }
        }

        return $next($request);
    }
}
