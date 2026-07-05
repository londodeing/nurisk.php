<?php

namespace App\Services\Auth;

use App\Models\AuthUser;
use Illuminate\Support\Facades\Auth;

class AuthorizationContextService
{
    private ?AuthUser $cachedUser = null;

    /**
     * Mengambil instance user terautentikasi terbaru dari database (dengan cache level request).
     */
    public function getCurrentUser(): ?AuthUser
    {
        if ($this->cachedUser === null) {
            $user = Auth::user();
            if (!$user) {
                return null;
            }
            if (!$user->relationLoaded('peran')) {
                $user->load('peran');
            }
            $this->cachedUser = $user;
        }

        return $this->cachedUser;
    }

    /**
     * Mendapatkan nama peran (role) user saat ini.
     */
    public function getRoleName(): ?string
    {
        $user = $this->getCurrentUser();
        return $user && $user->peran ? $user->peran->nama_peran : null;
    }

    /**
     * Mendapatkan level otoritas peran (role) user saat ini.
     */
    public function getRoleLevel(): ?int
    {
        $user = $this->getCurrentUser();
        return $user && $user->peran ? $user->peran->level_otoritas : null;
    }

    /**
     * Mendapatkan tipe scope wilayah yurisdiksi user.
     */
    public function getScopeType(): ?string
    {
        $user = $this->getCurrentUser();
        return $user ? $user->default_scope_type : null;
    }

    /**
     * Mendapatkan ID scope wilayah yurisdiksi user.
     */
    public function getScopeId(): ?int
    {
        $user = $this->getCurrentUser();
        return $user ? $user->default_scope_id : null;
    }

    /**
     * Mengecek apakah user saat ini adalah super_admin.
     */
    public function isSuperAdmin(): bool
    {
        return $this->getRoleName() === 'super_admin';
    }

    /**
     * Mengecek kecocokan peran tunggal.
     */
    public function hasRole(string $role): bool
    {
        return $this->getRoleName() === $role;
    }

    /**
     * Mengecek apakah salah satu peran di parameter cocok dengan peran user.
     */
    public function hasAnyRole(array $roles): bool
    {
        return in_array($this->getRoleName(), $roles);
    }

    /**
     * Mengecek kecocokan tipe scope wilayah.
     */
    public function hasScope(string $scope): bool
    {
        return $this->getScopeType() === $scope;
    }

    /**
     * Mengecek apakah user dapat me-manage insiden tertentu (jurisdiction check).
     */
    public function canManageInsiden(\App\Models\AuthUser $user, ?\App\Models\OperasiInsiden $insiden): bool
    {
        if (!$insiden) return false;

        $roleName = $user->relationLoaded('peran') && $user->peran ? $user->peran->nama_peran : (\App\Models\AuthRole::find($user->id_peran)->nama_peran ?? null);

        if ($roleName === 'super_admin') return true;

        if ($roleName === 'pwnu') {
            $pwnuUnitId = $user->default_scope_id;
            $pcnu = \App\Models\OrganisasiPcnu::with('unit')->find($insiden->id_pcnu);
            if ($pcnu && $pcnu->unit) {
                return $pcnu->unit->parent_id == $pwnuUnitId;
            }
            return false;
        }

        if ($roleName === 'pcnu') {
            return $user->default_scope_id == $insiden->id_pcnu;
        }

        return false;
    }

    /**
     * Mendapatkan daftar id_pcnu yang dapat diakses oleh user saat ini.
     * Untuk super_admin: null (semua akses — tidak ada filter).
     * Untuk PWNU: semua PCNU di bawah provinsi user.
     * Untuk PCNU: hanya PCNU user sendiri.
     * Untuk relawan: PCNU dari insiden yang ditugaskan.
     */
    public function getAccessiblePcnuIds(): ?array
    {
        if ($this->isSuperAdmin()) {
            return null; // null = no filter (all access)
        }

        if ($this->hasRole('pwnu')) {
            $pwnuUnitId = $this->getScopeId();
            return \App\Models\OrganisasiPcnu::whereHas('unit', function ($q) use ($pwnuUnitId) {
                $q->where('parent_id', $pwnuUnitId);
            })->pluck('id_pcnu')->toArray();
        }

        if ($this->hasRole('pcnu')) {
            $pcnuId = $this->getScopeId();
            return $pcnuId ? [$pcnuId] : [];
        }

        if ($this->hasRole('relawan')) {
            $userId = $this->getCurrentUser()?->id_pengguna;
            if (!$userId) return [];
            return \App\Models\OperasiPenugasan::where('id_pengguna', $userId)
                ->whereHas('insiden')
                ->with('insiden')
                ->get()
                ->pluck('insiden.id_pcnu')
                ->unique()
                ->values()
                ->toArray();
        }

        return [];
    }

    /**
     * Mengecek apakah user dapat mengakses insiden tertentu.
     */
    public function canAccessInsiden(int $idPcnu): bool
    {
        $accessible = $this->getAccessiblePcnuIds();
        if ($accessible === null) return true; // super_admin
        return in_array($idPcnu, $accessible, true);
    }

    /**
     * Mendapatkan filter scope untuk query sync.
     * Returns array dengan 'scope_type' dan 'scope_id' atau null untuk super_admin.
     */
    public function getSyncScopeFilter(): ?array
    {
        if ($this->isSuperAdmin()) {
            return null;
        }

        $scopeType = $this->getScopeType();
        $scopeId = $this->getScopeId();

        if ($scopeType && $scopeId) {
            return ['scope_type' => $scopeType, 'scope_id' => $scopeId];
        }

        return null;
    }

    /**
     * Mengecek apakah user memiliki jabatan aktif berdasarkan slug dan scope.
     */
    public function hasActiveJabatan(array $slugs, ?string $scopeType = null, ?int $scopeId = null): bool
    {
        $user = $this->getCurrentUser();
        if (!$user || !$user->isAktif()) {
            return false;
        }

        return \App\Models\PenggunaJabatan::where('id_pengguna', $user->id_pengguna)
            ->where('status_aktif', 1)
            ->whereHas('jabatan', function ($q) use ($slugs) {
                $q->whereIn('slug', $slugs);
            })
            ->when($scopeType, fn($q) => $q->where('tipe_lingkup', $scopeType))
            ->when($scopeId, fn($q) => $q->where('id_lingkup', $scopeId))
            ->exists();
    }

    /**
     * Mengosongkan cache memory (untuk kebutuhan testing).
     */
    public function clearCache(): void
    {
        $this->cachedUser = null;
    }
}
