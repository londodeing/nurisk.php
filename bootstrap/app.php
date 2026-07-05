<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: [
            __DIR__.'/../routes/web.php',
            __DIR__.'/../routes/dashboard.php',
        ],
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(prepend: [
            \App\Http\Middleware\SecurityHeadersMiddleware::class,
        ])->web(append: [
            \App\Http\Middleware\RefreshAuthorizationContext::class,
            \App\Http\Middleware\CheckAccountStatus::class,
        ]);

        $middleware->api(prepend: [
            \App\Http\Middleware\CorrelationIdMiddleware::class,
        ])->api(append: [
            \App\Http\Middleware\SentryUserContextMiddleware::class,
        ]);

        $middleware->throttleApi('api');

        $middleware->alias([
            'role' => \App\Http\Middleware\RoleMiddleware::class,
            'scope' => \App\Http\Middleware\ScopeMiddleware::class,
            'scopebasic' => \App\Http\Middleware\ScopeEnclosure::class,
            'correlation' => \App\Http\Middleware\CorrelationIdMiddleware::class,
            'security_headers' => \App\Http\Middleware\SecurityHeadersMiddleware::class,
            'active_approver' => \App\Http\Middleware\EnsureActiveApprover::class,
            'mandate.context' => \App\Http\Middleware\ResolveMandateContext::class,
        ]);

    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (\Illuminate\Validation\ValidationException $e, \Illuminate\Http\Request $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $e->errors(),
                ], 422);
            }
        });

        $exceptions->render(function (\Illuminate\Database\Eloquent\ModelNotFoundException $e, \Illuminate\Http\Request $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Resource tidak ditemukan',
                ], 404);
            }
        });

        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\NotFoundHttpException $e, \Illuminate\Http\Request $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Resource tidak ditemukan',
                ], 404);
            }
        });

        $exceptions->render(function (\Illuminate\Auth\AuthenticationException $e, \Illuminate\Http\Request $request) {
            \Illuminate\Support\Facades\Log::info('Auth exception: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Forbidden',
                ], 401);
            }
        });

        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException $e, \Illuminate\Http\Request $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Forbidden',
                ], 403);
            }
        });

        $exceptions->render(function (\Illuminate\Auth\AccessDeniedException $e, \Illuminate\Http\Request $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Forbidden',
                ], 403);
            }
        });

        $exceptions->render(function (\Illuminate\Http\Exceptions\ThrottleRequestsException $e, \Illuminate\Http\Request $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Terlalu banyak permintaan (Rate Limit)',
                    'meta' => [
                        'retryable' => true,
                        'retry_after' => $e->getHeaders()['Retry-After'] ?? 60
                    ]
                ], 429);
            }
        });

        $exceptions->render(function (\Illuminate\Database\QueryException $e, \Illuminate\Http\Request $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                $isDeadlock = str_contains($e->getMessage(), 'Deadlock found');
                $isRetryable = $isDeadlock;

                return response()->json([
                    'success' => false,
                    'message' => config('app.debug') ? $e->getMessage() : 'Database Error',
                    'meta' => [
                        'retryable' => $isRetryable,
                        'retry_after' => $isRetryable ? 5 : null
                    ]
                ], 500);
            }
        });
    })->create();
