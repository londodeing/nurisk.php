<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NewPasswordController extends Controller
{
    /**
     * Display the password reset view.
     */
    public function create(Request $request): View
    {
        return view('auth.reset-password', ['request' => $request]);
    }

    /**
     * Handle an incoming new password request.
     *
     * Reset password via email tidak didukung di NURISK.
     * Auth menggunakan no_hp sebagai identitas utama, bukan email.
     * Untuk reset kata sandi, hubungi administrator PCNU/PWNU Anda.
     */
    public function store(Request $request): RedirectResponse
    {
        return back()->with('error', 'Fitur lupa password via email belum tersedia. Hubungi administrator PCNU Anda untuk reset kata sandi.');
    }
}
