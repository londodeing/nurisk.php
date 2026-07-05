<?php

namespace App\Policies\Governance;

use App\Models\AuthUser;
use App\Models\OrgMandate;
use App\Services\Governance\MandateResolverService;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * GovernanceBasePolicy
 *
 * Base class untuk semua governance workflow policies.
 * Menggunakan Mandate-Based Access Control, BUKAN role-based.
 *
 * PRINSIP:
 * - Semua authorization berdasarkan Mandate + Authority
 * - Territory isolation otomatis
 * - Delegasi (PLT) otomatis dihandle oleh MandateResolverService
 *
 * CONTOH PENGGUNAAN DI CHILD POLICY:
 *   return $this->mandateHasAuthority($user, 'create_meeting');
 */
abstract class GovernanceBasePolicy
{
    use HandlesAuthorization;

    protected MandateResolverService $mandateResolver;

    public function __construct()
    {
        $this->mandateResolver = app(MandateResolverService::class);
    }

    /**
     * Super admin bypass — sebelum semua check.
     * Laravel memanggil before() secara otomatis.
     */
    public function before(AuthUser $user, string $ability): ?bool
    {
        if ($user->hasRole('super_admin')) {
            return true;
        }

        return null; // jatuh ke method spesifik
    }

    /**
     * Check apakah user memiliki authority berdasarkan mandate aktifnya.
     */
    protected function mandateHasAuthority(AuthUser $user, string $authorityCode): bool
    {
        return $this->mandateResolver->userHasAuthority($user, $authorityCode);
    }

    /**
     * Check apakah user memiliki authority pada node tertentu.
     */
    protected function mandateHasAuthorityOnNode(AuthUser $user, string $authorityCode, int $nodeId): bool
    {
        return $this->mandateResolver->userHasAuthorityOnNode($user, $authorityCode, $nodeId);
    }

    /**
     * Get primary mandate user saat ini.
     */
    protected function getPrimaryMandate(AuthUser $user): ?OrgMandate
    {
        return $this->mandateResolver->getPrimaryMandate($user);
    }

    /**
     * Get mandate dari request context (sudah di-resolve oleh middleware).
     */
    protected function getRequestMandate(): ?OrgMandate
    {
        return request()->get('_mandate');
    }

    /**
     * Check territory isolation — user hanya bisa akses data di territory-nya.
     *
     * @param AuthUser $user
     * @param string $territoryCode Territory code pada resource
     * @return bool
     */
    protected function isInTerritory(AuthUser $user, string $territoryCode): bool
    {
        $mandates = $this->mandateResolver->getAllActiveMandates($user);

        foreach ($mandates as $mandate) {
            $mandateTerritory = $mandate->nodePosition?->node?->territory_code;
            if ($mandateTerritory === null) {
                continue;
            }

            // Exact match atau hierarkis (33 matches 33.20)
            if ($mandateTerritory === $territoryCode || str_starts_with($territoryCode, $mandateTerritory . '.')) {
                return true;
            }
        }

        return false;
    }
}
