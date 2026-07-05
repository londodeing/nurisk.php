<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Services\Auth\AuthorizationContextService;
use Illuminate\Http\RedirectResponse;

class DashboardController extends Controller
{
    public function __construct(
        private AuthorizationContextService $authCtx
    ) {}

    public function __invoke(): RedirectResponse
    {
        $user = \Illuminate\Support\Facades\Auth::user();
        if ($user->status_akun === 'registered' || $user->status_akun === 'pending_verification') {
            return redirect()->route('role-application.create');
        }

        $role = $this->authCtx->getRoleName();

        return match ($role) {
            'super_admin', 'pwnu' => redirect()->route('dashboard.pwnu'),
            'pcnu' => redirect()->route('dashboard.pcnu'),
            'posko_commander', 'posko' => redirect()->route('dashboard.posko'),
            'trc' => redirect()->route('dashboard.trc'),
            'relawan' => redirect()->route('dashboard.relawan'),
            default => redirect()->route('dashboard.relawan'),
        };
    }
}
