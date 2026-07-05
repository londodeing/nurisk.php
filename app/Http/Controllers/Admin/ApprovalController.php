<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuthUser;
use App\Services\Auth\ApprovalService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ApprovalController extends Controller
{
    public function __construct(private ApprovalService $approvalService) {}

    public function index(Request $request): View
    {
        $this->authorize('viewApprovalQueue', AuthUser::class);

        $menunggu = $this->approvalService->daftarMenungguApproval($request->user());

        return view('admin.approval.index', compact('menunggu'));
    }

    public function setujui(Request $request, AuthUser $calon): RedirectResponse
    {
        $this->authorize('approve', $calon);

        $this->approvalService->setujui($calon, $request->user());

        return back()->with('success', "Akun {$calon->profil?->nama_lengkap} berhasil disetujui. User dapat login sekarang.");
    }

    public function tolak(Request $request, AuthUser $calon): RedirectResponse
    {
        $this->authorize('approve', $calon);

        $request->validate([
            'alasan' => ['required', 'string', 'min:10', 'max:500'],
        ]);

        $this->approvalService->tolak($calon, $request->user(), $request->alasan);

        return back()->with('success', "Pendaftaran {$calon->profil?->nama_lengkap} ditolak.");
    }
}
