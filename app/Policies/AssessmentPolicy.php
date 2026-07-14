<?php

namespace App\Policies;

use App\Models\AuthUser;
use App\Models\AssessmentUtama;
use App\Models\OperasiInsiden;
use App\Models\OperasiPenugasan;
use App\Services\Auth\AuthorizationContextService;
use App\Services\Operasi\AssessmentDeletionGuard;
use Illuminate\Auth\Access\HandlesAuthorization;

class AssessmentPolicy
{
    use HandlesAuthorization;

    private function authCtx(): AuthorizationContextService
    {
        return app(AuthorizationContextService::class);
    }

    private function bolehAksesInsiden(AuthUser $user, OperasiInsiden $insiden): bool
    {
        if ($this->authCtx()->hasAnyRole(['super_admin', 'pwnu'])) {
            return true;
        }
        if ($this->authCtx()->hasRole('pcnu')) {
            return $this->authCtx()->getScopeId() === $insiden->id_pcnu;
        }

        // Lapis 3: TRC — access incidents within their PCNU scope
        if ($this->authCtx()->hasRole('trc')) {
            return $this->authCtx()->getScopeId() === $insiden->id_pcnu;
        }

        // Lapis 4: Operational Assignment
        if ($this->authCtx()->hasRole('relawan')) {
            return OperasiPenugasan::where('id_insiden', $insiden->id_insiden)
                ->where('id_pengguna', $user->id_pengguna)
                ->whereIn('peran_otoritas', ['trc', 'komandan_insiden'])
                ->whereNull('waktu_selesai')
                ->exists();
        }

        return false;
    }

    public function viewAny(AuthUser $user, OperasiInsiden $insiden): bool
    {
        return $this->bolehAksesInsiden($user, $insiden);
    }

    public function view(AuthUser $user, AssessmentUtama $assessmentUtama): bool
    {
        return $this->bolehAksesInsiden($user, $assessmentUtama->insiden);
    }

    public function create(AuthUser $user, OperasiInsiden $insiden): bool
    {
        // BR-ASSESSMENT-001: Prasyarat Status Insiden
        if (!in_array($insiden->status_insiden, ['terverifikasi', 'respon'])) {
            return false;
        }

        // Assessment tidak bisa dibuat sebelum terdapat surat tugas (SPK)
        if (empty($insiden->no_spk_assesment)) {
            return false;
        }

        if ($insiden->isTerkunci()) {
            return false;
        }
        return $this->bolehAksesInsiden($user, $insiden);
    }

    public function update(AuthUser $user, AssessmentUtama $assessmentUtama): bool
    {
        if ($assessmentUtama->insiden->isTerkunci()) {
            return false;
        }
        return $this->bolehAksesInsiden($user, $assessmentUtama->insiden);
    }

    public function delete(AuthUser $user, AssessmentUtama $assessmentUtama): bool
    {
        // BR-ASSESSMENT-008: Larangan Hapus Assessment Basis Sitrep
        $guard = app(AssessmentDeletionGuard::class);
        if (!$guard->canDelete($assessmentUtama)) {
            return false;
        }

        if ($assessmentUtama->insiden->isTerkunci()) {
            return false;
        }

        return $this->authCtx()->isSuperAdmin() || $this->authCtx()->hasRole('pwnu');
    }

    public function submit(AuthUser $user, AssessmentUtama $assessment): bool
    {
        if ($assessment->insiden->isTerkunci()) {
            return false;
        }

        // Creator can always submit their own draft assessment
        if ($assessment->id_petugas_assessment === $user->id_pengguna) {
            return true;
        }

        // Super admin, PWNU, and scope-matching PCNU/TRC can also submit
        return $this->bolehAksesInsiden($user, $assessment->insiden);
    }

    public function review(AuthUser $user, AssessmentUtama $assessment): bool
    {
        if ($this->authCtx()->hasAnyRole(['super_admin', 'pwnu'])) {
            return true;
        }
        if ($this->authCtx()->hasRole('pcnu')) {
            $insiden = $assessment->insiden;
            return $insiden && $this->authCtx()->getScopeId() === $insiden->id_pcnu;
        }
        return false;
    }

    public function approve(AuthUser $user, AssessmentUtama $assessment): bool
    {
        return $this->review($user, $assessment);
    }

    public function reject(AuthUser $user, AssessmentUtama $assessment): bool
    {
        return $this->review($user, $assessment);
    }
}
