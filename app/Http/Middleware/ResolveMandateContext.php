<?php

namespace App\Http\Middleware;

use App\Services\Governance\MandateResolverService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * ResolveMandateContext Middleware
 *
 * Resolve authenticated user ke mandate aktifnya.
 * Hasil: request()->get('_mandate') tersedia di seluruh controller/policy.
 *
 * PENGGUNAAN DI ROUTES:
 *   Route::middleware('mandate.context')->group(...)
 *
 * AKSES DI CONTROLLER:
 *   $mandate = $request->get('_mandate');
 *   // atau
 *   $mandate = request()->get('_mandate');
 */
class ResolveMandateContext
{
    public function __construct(
        private MandateResolverService $resolver
    ) {}

    public function handle(Request $request, Closure $next, ?string $requiredAuthority = null): Response
    {
        $user = $request->user();

        if (!$user) {
            return $next($request);
        }

        // Resolve primary mandate
        $mandate = $this->resolver->getPrimaryMandate($user);

        // Jika route memerlukan node-specific mandate, cek dari parameter
        if ($mandate && $request->route('node_id')) {
            $nodeMandate = $this->resolver->resolveActiveMandate($user, (int) $request->route('node_id'));
            if ($nodeMandate) {
                $mandate = $nodeMandate;
            }
        }

        // Merge ke request agar tersedia di mana saja
        $request->merge([
            '_mandate' => $mandate,
            '_all_mandates' => $mandate ? $this->resolver->getAllActiveMandates($user) : collect(),
        ]);

        // Jika middleware menerima parameter authority, cek langsung
        if ($requiredAuthority && $mandate) {
            if (!$this->resolver->hasAuthority($mandate, $requiredAuthority)) {
                abort(403, "Anda tidak memiliki wewenang [{$requiredAuthority}] pada posisi saat ini.");
            }
        }

        return $next($request);
    }
}
