<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SentryUserContextMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (app()->bound('sentry') && $request->user()) {
            $user = $request->user();
            \Sentry\configureScope(function (\Sentry\State\Scope $scope) use ($user): void {
                $scope->setUser([
                    'id' => $user->id_pengguna,
                    'username' => $user->no_hp,
                    'role' => $user->peran?->nama_peran,
                ]);
            });
        }

        return $next($request);
    }
}
