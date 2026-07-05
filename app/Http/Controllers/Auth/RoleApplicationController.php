<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\AuthRoleApplication;
use App\Models\AuthRole;
use App\Services\Auth\RoleApplicationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoleApplicationController extends Controller
{
    public function __construct(private RoleApplicationService $applicationService) {}

    public function create()
    {
        $user = Auth::user();

        // Jika sudah aktif, tidak perlu apply role
        if ($user->status_akun !== 'registered' && $user->status_akun !== 'pending_verification') {
            return redirect()->route('dashboard');
        }

        // Cek apakah sudah punya aplikasi yang pending
        $pendingApplication = AuthRoleApplication::where('id_pengguna', $user->id_pengguna)
            ->where('status_aplikasi', 'pending')
            ->first();

        $roles = AuthRole::whereIn('nama_peran', ['trc', 'relawan', 'operator_posko', 'koordinator_klaster'])->get();

        return view('auth.role-application.create', compact('roles', 'pendingApplication', 'user'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'id_peran_diminta' => 'required|exists:auth_roles,id_peran',
            'catatan' => 'nullable|string|max:1000',
        ]);

        $this->applicationService->submitApplication(
            $user,
            $request->id_peran_diminta,
            $request->catatan
        );

        return redirect()->route('role-application.create')->with('success', 'Aplikasi peran berhasil diajukan dan sedang menunggu persetujuan.');
    }
}
