<?php

namespace App\Http\Controllers\Api\Governance;

use Illuminate\Http\Request;

trait GovernanceAuthorization
{
    private function authorizeGovernance(Request $request): void
    {
        $user = $request->user();

        if (!$user || !$user->hasRole(['super_admin', 'pwnu'])) {
            abort(403, 'Aksi tidak diizinkan. Hanya super_admin dan pwnu yang dapat mengelola governance.');
        }
    }
}
