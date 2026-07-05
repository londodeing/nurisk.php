<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Services\Auth\AuthenticationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    protected AuthenticationService $authService;

    /**
     * Injeksi dependensi AuthenticationService.
     */
    public function __construct(AuthenticationService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * Menampilkan halaman login.
     */
    public function showLoginForm(): View
    {
        return view('auth.login');
    }

    /**
     * Memproses request masuk dan melakukan autentikasi via Service Layer.
     */
    public function login(LoginRequest $request): RedirectResponse
    {
        // DEBUG
        if ($request->input('no_hp') === '08111111111') {
            \Illuminate\Support\Facades\Log::info("LOGIN ATTEMPT", $request->all());
        }
        
        try {
            $this->authService->login($request->validated());

            // Proteksi regenerasi session bawaan Laravel untuk session-fixation attack
            $request->session()->regenerate();

            return redirect()->intended('/dashboard');
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput($request->only('no_hp'));
        }
    }

    /**
     * Memproses logout pengguna, membatalkan session, dan me-regenerate token.
     */
    public function logout(\Illuminate\Http\Request $request): RedirectResponse
    {
        $this->authService->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}
