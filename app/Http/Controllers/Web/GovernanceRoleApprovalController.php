<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\AuthRoleApplication;
use App\Services\Auth\RoleApplicationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GovernanceRoleApprovalController extends Controller
{
    public function __construct(private RoleApplicationService $applicationService) {}

    public function index()
    {
        $user = Auth::user();

        // Ambil semua aplikasi pending
        $applicationsQuery = AuthRoleApplication::with(['pemohon.profil', 'peranDiminta'])
            ->where('status_aplikasi', 'pending');

        if ($user->hasRole('pwnu')) {
            // PWNU hanya bisa melihat kandidat PCNU
            $applicationsQuery->whereHas('peranDiminta', function ($q) {
                $q->where('nama_peran', 'pcnu');
            });
        } elseif ($user->hasRole('super_admin')) {
            // Super Admin melihat kandidat PWNU (dan mungkin PCNU juga, tapi utamanya PWNU)
            // Biarkan melihat semua
        } else {
            // Role lain tidak boleh melihat aplikasi
            $applicationsQuery->whereRaw('1 = 0');
        }

        $applications = $applicationsQuery->latest('waktu_pengajuan')->get();

        return view('governance.role-approval.index', compact('applications'));
    }

    public function approve(Request $request, $id)
    {
        $application = AuthRoleApplication::findOrFail($id);
        $approver = Auth::user();

        $this->applicationService->approveApplication(
            $application,
            $approver,
            $request->input('catatan', 'Disetujui dari dashboard admin.')
        );

        return redirect()->back()->with('success', 'Aplikasi peran berhasil disetujui.');
    }

    public function reject(Request $request, $id)
    {
        $application = AuthRoleApplication::findOrFail($id);
        $approver = Auth::user();

        $this->applicationService->rejectApplication(
            $application,
            $approver,
            $request->input('catatan', 'Ditolak dari dashboard admin.')
        );

        return redirect()->back()->with('success', 'Aplikasi peran berhasil ditolak.');
    }
}
