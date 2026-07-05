<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\AuthKeahlianMaster;
use App\Models\AuthPenggunaProfil;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        $user = $request->user();
        $user->load('profil', 'keahlian');
        
        $keahlianMaster = AuthKeahlianMaster::orderBy('nama_keahlian')->get();

        return view('profile.edit', [
            'user' => $user,
            'keahlianMaster' => $keahlianMaster,
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
        $validated = $request->validated();
        
        // Update or Create Profil
        $profil = $user->profil ?: new AuthPenggunaProfil(['id_pengguna' => $user->id_pengguna]);
        $profil->fill([
            'nik' => $validated['nik'],
            'nama_lengkap' => $validated['nama_lengkap'],
            'email' => $validated['email'],
            'alamat' => $validated['alamat'] ?? null,
            'tanggal_lahir' => $validated['tanggal_lahir'] ?? null,
            'jenis_kelamin' => $validated['jenis_kelamin'] ?? null,
            'tempat_lahir' => $validated['tempat_lahir'] ?? null,
            'profesi' => $validated['profesi'] ?? null,
            'pengalaman_kebencanaan' => $validated['pengalaman_kebencanaan'] ?? null,
        ]);
        
        $profil->save();
        
        // Sync Keahlian
        if (isset($validated['keahlian']) && is_array($validated['keahlian'])) {
            $user->keahlian()->sync($validated['keahlian']);
        } else {
            $user->keahlian()->detach();
        }

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
