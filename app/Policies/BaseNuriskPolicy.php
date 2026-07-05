<?php

namespace App\Policies;

use App\Models\AuthUser;
use App\Services\AssignmentContextService;

abstract class BaseNuriskPolicy
{
    /**
     * @param AuthUser $user
     * @param int $idInsiden
     * @param string|null $peran
     * @return bool
     */
    protected function hasAssignment(AuthUser $user, int $idInsiden, ?string $peran = null): bool
    {
        $service = app(AssignmentContextService::class);

        if ($peran) {
            return $service->hasCommandAuthority($user, $idInsiden, $peran);
        }

        return $service->hasAnyAssignment($user, $idInsiden);
    }
}
