<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\AuthKeahlianMaster;
use App\Models\OrganisasiPcnu;
use App\Services\Auth\RegistrationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class RegistrationController extends Controller
{
    public function __construct(private RegistrationService $registrationService) {}

    public function pilihJenis(): View
    {
        return view('auth.register-pilih-jenis');
    }

    public function form(string $jenis): View|RedirectResponse
    {
        if (!in_array($jenis, RegistrationService::SEMUA_JENIS)) {
            return redirect()->route('register')->with('error', 'Jenis pendaftaran tidak valid.');
        }

        $keahlianList = AuthKeahlianMaster::orderBy('nama_keahlian')->get();
        $pcnuList     = in_array($jenis, [
            RegistrationService::JENIS_TRC_PCNU,
            RegistrationService::JENIS_ADMIN_PCNU,
        ]) ? OrganisasiPcnu::orderBy('nama_pcnu')->get() : collect();

        return view('auth.register', compact('jenis', 'keahlianList', 'pcnuList'));
    }

    public function proses(RegisterRequest $request, string $jenis): RedirectResponse
    {
        if (!in_array($jenis, RegistrationService::SEMUA_JENIS)) {
            return redirect()->route('register')->with('error', 'Jenis pendaftaran tidak valid.');
        }

        try {
            $user = $this->registrationService->daftar($request->validated(), $jenis);
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Pendaftaran gagal: ' . $e->getMessage());
        }

        if ($user->isAktif()) {
            Auth::login($user);
            $request->session()->regenerate();
            return redirect()->route('dashboard')->with('success', 'Selamat datang! Akun Anda langsung aktif.');
        }

        return redirect()->route('register.menunggu')->with([
            'info'  => 'Pendaftaran berhasil dikirim.',
            'no_hp' => $user->no_hp,
        ]);
    }

    public function menunggu(): View
    {
        return view('auth.register-menunggu');
    }
}
