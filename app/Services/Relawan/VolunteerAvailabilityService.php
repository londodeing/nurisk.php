<?php

namespace App\Services\Relawan;

use App\Models\AuthUser;
use Illuminate\Support\Facades\DB;

class VolunteerAvailabilityService
{
    public function updateStatus(AuthUser $user, string $status): void
    {
        $validStatuses = [
            AuthUser::READINESS_NOT_READY,
            AuthUser::READINESS_LIMITED_READY,
            AuthUser::READINESS_READY,
            AuthUser::READINESS_DEPLOYED,
            AuthUser::READINESS_ON_MISSION,
            AuthUser::READINESS_RESTING
        ];
        
        if (!in_array(strtolower($status), $validStatuses)) {
            throw new \InvalidArgumentException("Status ketersediaan tidak valid: $status");
        }

        $user->update(['status_ketersediaan' => strtolower($status)]);
    }

    public function calculateReadinessScore(AuthUser $user): int
    {
        $score = 0;

        // Profile lengkap = 20
        $profil = $user->profil;
        if ($profil && $profil->nama_lengkap && $profil->nik && $user->no_hp) {
            $score += 20;
        }

        // Alamat lengkap = 20
        if ($profil && $profil->id_desa_domisili) {
            $score += 20;
        }

        // Keahlian = 20
        if ($user->keahlian()->count() > 0) {
            $score += 20;
        }

        // Sertifikasi = 20
        if (method_exists($user, 'sertifikasi') && $user->sertifikasi()->count() > 0) {
            $score += 20;
        }

        // Availability = 20
        if (strtolower($user->status_ketersediaan) === AuthUser::READINESS_READY) {
            $score += 20;
        }

        return $score;
    }

    public function getAvailableVolunteers(array $filters = [])
    {
        $query = AuthUser::where('status_ketersediaan', AuthUser::READINESS_READY)
            ->where('status_akun', AuthUser::STATUS_ACTIVE)
            ->where('is_tersedia', true)
            ->with(['profil', 'keahlian', 'peran']);

        if (!empty($filters['wilayah'])) {
            $query->whereHas('profil', function ($q) use ($filters) {
                $q->where('id_desa_domisili', 'like', $filters['wilayah'] . '%');
            });
        }

        if (!empty($filters['kompetensi'])) {
            $query->whereHas('keahlian', function ($q) use ($filters) {
                $q->whereIn('auth_keahlian_master.id_keahlian', (array)$filters['kompetensi']);
            });
        }

        if (!empty($filters['sertifikasi'])) {
            if (method_exists(AuthUser::class, 'sertifikasi')) {
                $query->whereHas('sertifikasi', function ($q) use ($filters) {
                    $q->whereIn('master_sertifikasi.id_sertifikasi', (array)$filters['sertifikasi']);
                });
            }
        }

        return $query->get()->filter(function ($user) {
            // Strict filtering for TRC: Must have both Keahlian and Sertifikasi to be in the pool
            if ($user->hasRole('trc')) {
                $hasKeahlian = $user->keahlian()->count() > 0;
                $hasSertifikasi = method_exists($user, 'sertifikasi') && $user->sertifikasi()->count() > 0;
                
                if (!$hasKeahlian || !$hasSertifikasi) {
                    return false;
                }
            }

            return $this->calculateReadinessScore($user) >= 80;
        });
    }
}
