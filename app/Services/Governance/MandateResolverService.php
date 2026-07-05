<?php

namespace App\Services\Governance;

use App\Models\AuthUser;
use App\Models\OrgMandate;
use App\Models\OrgNodePosition;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * MandateResolverService
 *
 * Core service untuk resolusi User → Mandate → Authority.
 * Menggantikan role-based authorization untuk governance workflow.
 *
 * PRINSIP:
 * - Semua keputusan organisasi berdasarkan Mandate, bukan User
 * - Mandate = SK + Position + Node + Territory
 * - Authority diturunkan melalui chain: Mandate → NodePosition → Function → Authority
 */
class MandateResolverService
{
    /**
     * Cache TTL (5 menit) — mandate jarang berubah dalam satu sesi.
     */
    private const CACHE_TTL = 300;

    /**
     * Resolve semua mandate aktif user saat ini.
     *
     * @param AuthUser $user
     * @return Collection<OrgMandate>
     */
    public function getAllActiveMandates(AuthUser $user): Collection
    {
        $cacheKey = "mandates:user:{$user->id_pengguna}:active";

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($user) {
            return OrgMandate::with([
                    'nodePosition.node.institution',
                    'nodePosition.node.structureLevel',
                    'nodePosition.position',
                    'sk',
                ])
                ->where('user_id', $user->id_pengguna)
                ->where('status', 'active')
                ->where('tanggal_mulai', '<=', today())
                ->where(function ($q) {
                    $q->whereNull('tanggal_berakhir')
                      ->orWhere('tanggal_berakhir', '>=', today());
                })
                ->whereHas('sk', function ($q) {
                    $q->where('status', 'active');
                })
                ->get();
        });
    }

    /**
     * Resolve mandate aktif user pada node tertentu.
     *
     * @param AuthUser $user
     * @param int $nodeId
     * @return OrgMandate|null
     */
    public function resolveActiveMandate(AuthUser $user, int $nodeId): ?OrgMandate
    {
        $mandates = $this->getAllActiveMandates($user);

        // Direct mandate
        $direct = $mandates->first(function (OrgMandate $m) use ($nodeId) {
            return $m->nodePosition && $m->nodePosition->node_id === $nodeId;
        });

        if ($direct) {
            return $direct;
        }

        // Fallback: check delegations (PLT)
        return $this->resolveDelegatedMandate($user, $nodeId);
    }

    /**
     * Get primary mandate (untuk dashboard context).
     * Prioritas: mandate dengan position level tertinggi (terkecil).
     *
     * @param AuthUser $user
     * @return OrgMandate|null
     */
    public function getPrimaryMandate(AuthUser $user): ?OrgMandate
    {
        $mandates = $this->getAllActiveMandates($user);

        if ($mandates->isEmpty()) {
            return null;
        }

        // Sort by position level (ascending = higher authority)
        return $mandates->sortBy(function (OrgMandate $m) {
            return $m->nodePosition?->position?->level ?? PHP_INT_MAX;
        })->first();
    }

    /**
     * Check apakah mandate memiliki authority tertentu.
     *
     * Traversal: Mandate → NodePosition → PositionFunction → GovernanceFunction → FunctionAuthority → Authority
     *
     * @param OrgMandate $mandate
     * @param string $authorityCode
     * @return bool
     */
    public function hasAuthority(OrgMandate $mandate, string $authorityCode): bool
    {
        $cacheKey = "mandate:{$mandate->id}:authority:{$authorityCode}";

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($mandate, $authorityCode) {
            return DB::table('org_node_positions')
                ->join('org_position_functions', 'org_node_positions.id', '=', 'org_position_functions.node_position_id')
                ->join('org_governance_functions', 'org_position_functions.function_id', '=', 'org_governance_functions.id')
                ->join('org_function_authorities', 'org_governance_functions.id', '=', 'org_function_authorities.function_id')
                ->join('org_authorities', 'org_function_authorities.authority_id', '=', 'org_authorities.id')
                ->where('org_node_positions.id', $mandate->node_position_id)
                ->where('org_authorities.code', $authorityCode)
                ->exists();
        });
    }

    /**
     * Check apakah user memiliki authority tertentu pada node manapun.
     *
     * @param AuthUser $user
     * @param string $authorityCode
     * @return bool
     */
    public function userHasAuthority(AuthUser $user, string $authorityCode): bool
    {
        $mandates = $this->getAllActiveMandates($user);

        foreach ($mandates as $mandate) {
            if ($this->hasAuthority($mandate, $authorityCode)) {
                return true;
            }
        }

        // Check delegated mandates
        return $this->userHasDelegatedAuthority($user, $authorityCode);
    }

    /**
     * Check apakah user memiliki authority pada node tertentu.
     *
     * @param AuthUser $user
     * @param string $authorityCode
     * @param int $nodeId
     * @return bool
     */
    public function userHasAuthorityOnNode(AuthUser $user, string $authorityCode, int $nodeId): bool
    {
        $mandate = $this->resolveActiveMandate($user, $nodeId);

        if (!$mandate) {
            return false;
        }

        return $this->hasAuthority($mandate, $authorityCode);
    }

    /**
     * Get semua authority codes yang dimiliki mandate.
     *
     * @param OrgMandate $mandate
     * @return array<string>
     */
    public function getAuthorityCodes(OrgMandate $mandate): array
    {
        $cacheKey = "mandate:{$mandate->id}:authorities";

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($mandate) {
            return DB::table('org_node_positions')
                ->join('org_position_functions', 'org_node_positions.id', '=', 'org_position_functions.node_position_id')
                ->join('org_governance_functions', 'org_position_functions.function_id', '=', 'org_governance_functions.id')
                ->join('org_function_authorities', 'org_governance_functions.id', '=', 'org_function_authorities.function_id')
                ->join('org_authorities', 'org_function_authorities.authority_id', '=', 'org_authorities.id')
                ->where('org_node_positions.id', $mandate->node_position_id)
                ->pluck('org_authorities.code')
                ->unique()
                ->values()
                ->toArray();
        });
    }

    /**
     * Generate immutable legal snapshot dari mandate.
     * Digunakan untuk audit trail dan digital signature.
     *
     * @param OrgMandate $mandate
     * @return array
     */
    public function getLegalSnapshot(OrgMandate $mandate): array
    {
        $mandate->loadMissing([
            'nodePosition.position',
            'nodePosition.node',
            'sk',
            'user.profil',
        ]);

        return [
            'mandate_id' => $mandate->id,
            'user_id' => $mandate->user_id,
            'user_name' => $mandate->user?->profil?->nama_lengkap ?? 'Unknown',
            'position_name' => $mandate->nodePosition?->position?->name ?? 'Unknown',
            'node_name' => $mandate->nodePosition?->node?->name ?? 'Unknown',
            'sk_number' => $mandate->sk?->nomor_sk ?? null,
            'territory_code' => $mandate->nodePosition?->node?->territory_code ?? null,
            'snapshot_at' => now()->toIso8601String(),
        ];
    }

    /**
     * Clear cache untuk user tertentu (dipanggil saat mandate berubah).
     *
     * @param int $userId
     */
    public function clearCache(int $userId): void
    {
        Cache::forget("mandates:user:{$userId}:active");
    }

    /**
     * Clear semua cache mandate (untuk seeder/migration).
     */
    public function clearAllCache(): void
    {
        // Pattern-based flush — implementasi tergantung cache driver
        Cache::flush();
    }

    // ===================================================================
    // PRIVATE
    // ===================================================================

    /**
     * Resolve delegated mandate (PLT) pada node tertentu.
     */
    private function resolveDelegatedMandate(AuthUser $user, int $nodeId): ?OrgMandate
    {
        $delegation = DB::table('org_delegations')
            ->join('org_mandates as m_pengganti', 'org_delegations.mandat_pengganti_id', '=', 'm_pengganti.id')
            ->join('org_mandates as m_asal', 'org_delegations.mandat_asal_id', '=', 'm_asal.id')
            ->join('org_node_positions', 'm_asal.node_position_id', '=', 'org_node_positions.id')
            ->where('m_pengganti.user_id', $user->id_pengguna)
            ->where('org_node_positions.node_id', $nodeId)
            ->where('org_delegations.mulai', '<=', now())
            ->where('org_delegations.selesai', '>=', now())
            ->where('m_asal.status', 'active')
            ->select('m_asal.id')
            ->first();

        if ($delegation) {
            return OrgMandate::with([
                'nodePosition.node',
                'nodePosition.position',
                'sk',
            ])->find($delegation->id);
        }

        return null;
    }

    /**
     * Check delegated authority.
     */
    private function userHasDelegatedAuthority(AuthUser $user, string $authorityCode): bool
    {
        return DB::table('org_delegations')
            ->join('org_mandates as m_pengganti', 'org_delegations.mandat_pengganti_id', '=', 'm_pengganti.id')
            ->join('org_mandates as m_asal', 'org_delegations.mandat_asal_id', '=', 'm_asal.id')
            ->join('org_node_positions', 'm_asal.node_position_id', '=', 'org_node_positions.id')
            ->join('org_position_functions', 'org_node_positions.id', '=', 'org_position_functions.node_position_id')
            ->join('org_governance_functions', 'org_position_functions.function_id', '=', 'org_governance_functions.id')
            ->join('org_function_authorities', 'org_governance_functions.id', '=', 'org_function_authorities.function_id')
            ->join('org_authorities', 'org_function_authorities.authority_id', '=', 'org_authorities.id')
            ->where('m_pengganti.user_id', $user->id_pengguna)
            ->where('org_delegations.mulai', '<=', now())
            ->where('org_delegations.selesai', '>=', now())
            ->where('m_asal.status', 'active')
            ->where('org_authorities.code', $authorityCode)
            ->exists();
    }
}
