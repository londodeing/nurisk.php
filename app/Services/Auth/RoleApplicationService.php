<?php

namespace App\Services\Auth;

use App\Models\AuthRoleApplication;
use App\Models\AuthUser;
use Exception;
use Illuminate\Support\Facades\DB;

class RoleApplicationService
{
    public function applyForRole(AuthUser $user, int $roleId): AuthRoleApplication
    {
        // Prevent duplicate pending applications
        $existing = AuthRoleApplication::where('id_pengguna', $user->id_pengguna)
            ->where('id_peran_diminta', $roleId)
            ->where('status_aplikasi', 'pending')
            ->first();

        if ($existing) {
            throw new Exception("Anda sudah memiliki pengajuan untuk role ini yang sedang menunggu persetujuan.");
        }

        // Change user status to pending_verification if currently registered
        if ($user->status_akun === 'registered') {
            $user->update(['status_akun' => 'pending_verification']);
        }

        return AuthRoleApplication::create([
            'id_pengguna' => $user->id_pengguna,
            'id_peran_diminta' => $roleId,
            'status_aplikasi' => 'pending',
            'waktu_pengajuan' => now(),
        ]);
    }

    public function approveApplication(AuthRoleApplication $application, AuthUser $approver): AuthRoleApplication
    {
        if ($application->status_aplikasi !== 'pending') {
            throw new Exception("Aplikasi ini sudah diproses.");
        }

        $applicant = $application->pemohon;

        // Wilayah-based Role Validation
        $this->validateApproverScope($approver, $applicant);

        DB::transaction(function () use ($application, $approver, $applicant) {
            $application->update([
                'status_aplikasi' => 'approved',
                'waktu_diproses' => now(),
                'id_approver' => $approver->id_pengguna,
            ]);

            // Update user role and status
            $applicant->update([
                'id_peran' => $application->id_peran_diminta,
                'status_akun' => AuthUser::STATUS_ACTIVE,
            ]);
            
            // Sync with Spatie Permissions if necessary
            $roleName = \Illuminate\Support\Facades\DB::table('auth_roles')
                ->where('id_peran', $application->id_peran_diminta)
                ->value('nama_peran');
                
            if ($roleName) {
                // Remove all current roles and assign the new one via Spatie
                // Note: Spatie works with Laravel's User model, but let's assume it maps correctly
                $spatieRole = \Spatie\Permission\Models\Role::where('name', $roleName)->first();
                if ($spatieRole) {
                    \Illuminate\Support\Facades\DB::table('model_has_roles')
                        ->where('model_id', $applicant->id_pengguna)
                        ->where('model_type', AuthUser::class)
                        ->delete();
                        
                    \Illuminate\Support\Facades\DB::table('model_has_roles')->insert([
                        'role_id' => $spatieRole->id,
                        'model_type' => AuthUser::class,
                        'model_id' => $applicant->id_pengguna,
                    ]);
                }
            }
        });

        return $application;
    }

    public function rejectApplication(AuthRoleApplication $application, AuthUser $approver, string $catatan): AuthRoleApplication
    {
        if ($application->status_aplikasi !== 'pending') {
            throw new Exception("Aplikasi ini sudah diproses.");
        }

        $applicant = $application->pemohon;

        $this->validateApproverScope($approver, $applicant);

        $application->update([
            'status_aplikasi' => 'rejected',
            'waktu_diproses' => now(),
            'id_approver' => $approver->id_pengguna,
            'catatan' => $catatan,
        ]);

        return $application;
    }

    private function validateApproverScope(AuthUser $approver, AuthUser $applicant): void
    {
        $applicantRole = $applicant->peran->nama_peran ?? '';

        // Super Admin can approve anyone
        if ($approver->hasRole('super_admin')) {
            return;
        }

        // PWNU can only approve Kandidat Admin PCNU
        if ($approver->hasRole('pwnu')) {
            if ($applicantRole === 'kandidat_admin_pcnu') {
                return;
            }
            throw new Exception("PWNU hanya dapat menyetujui Kandidat Admin PCNU.");
        }

        // PCNU has no approval authority in V2 (Relawan/TRC is auto-active)
        if ($approver->hasRole('pcnu')) {
            throw new Exception("PCNU tidak memiliki wewenang approval pada alur pendaftaran baru.");
        }

        throw new Exception("Anda tidak memiliki wewenang sebagai approver.");
    }
}
