<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class ScopeMiddleware
{
    public function handle(Request $request, Closure $next, string ...$allowedScopes): Response
    {
        if (!Auth::check()) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
            }
            return redirect()->route('login');
        }

        $user = \App\Models\AuthUser::query()
            ->select(['id_pengguna', 'default_scope_type', 'default_scope_id', 'id_peran'])
            ->with('peran:id_peran,nama_peran,level_otoritas')
            ->find(Auth::id());

        if (!$user) {
            abort(403, 'Aksi ditolak. Pengguna tidak ditemukan.');
        }

        // Super admin bypasses scope
        if ($user->peran && $user->peran->nama_peran === 'super_admin') {
            return $next($request);
        }

        $userScopeType = $user->default_scope_type;
        $userScopeId = $user->default_scope_id;

        if (!$userScopeType) {
            Log::warning('Scope access denied: no scope defined', [
                'user_id' => $user->id_pengguna,
                'path' => $request->path(),
                'method' => $request->method(),
            ]);
            abort(403, 'Aksi ditolak. Wilayah kepengurusan (scope) Anda tidak memiliki wewenang untuk area ini.');
        }

        if (!in_array($userScopeType, $allowedScopes)) {
            Log::warning('Scope access denied: wrong scope type', [
                'user_id' => $user->id_pengguna,
                'user_scope' => $userScopeType,
                'required_scopes' => $allowedScopes,
                'path' => $request->path(),
            ]);
            abort(403, 'Aksi ditolak. Wilayah kepengurusan (scope) Anda tidak memiliki wewenang untuk area ini.');
        }

        $resourceScopeType = $request->route('scope_type') ?? $request->input('scope_type');
        $resourceScopeId = $request->route('scope_id') ?? $request->input('scope_id');

        if ($resourceScopeType && $resourceScopeId) {
            if ($resourceScopeType !== $userScopeType || (int) $resourceScopeId !== (int) $userScopeId) {
                Log::warning('Scope access denied: cross-wilayah access attempt', [
                    'user_id' => $user->id_pengguna,
                    'user_scope' => $userScopeType . ':' . $userScopeId,
                    'resource_scope' => $resourceScopeType . ':' . $resourceScopeId,
                    'path' => $request->path(),
                ]);
                abort(403, 'Aksi ditolak. Anda tidak memiliki akses ke wilayah ini.');
            }
        }

        return $next($request);
    }
}
