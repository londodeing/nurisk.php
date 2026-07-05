<?php

namespace App\Http\Controllers\Api\Relawan;

use App\Http\Controllers\Controller;
use App\Http\Resources\Relawan\RelawanProfilResource;
use App\Models\AuthPenggunaProfil;
use App\Services\Relawan\RelawanService;
use App\Services\Auth\AuthorizationContextService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class RelawanProfilController extends Controller
{
    protected RelawanService $service;

    public function __construct(RelawanService $service)
    {
        $this->service = $service;
    }

    public function show(AuthPenggunaProfil $profil)
    {
        Gate::authorize('viewProfil', $profil);
        $profil->load(['pengguna.keahlian']);
        return new RelawanProfilResource($profil);
    }

    public function update(Request $request, AuthPenggunaProfil $profil)
    {
        Gate::authorize('updateProfil', $profil);

        $data = $request->validate([
            'nama_lengkap' => ['required', 'string', 'max:255'],
            'nik' => ['required', 'string', 'max:20'],
            'email' => ['required', 'email', 'max:255'],
            'id_desa_domisili' => ['nullable', 'string', 'max:10'],
        ]);

        $profil->update($data);
        $profil->load(['pengguna.keahlian']);

        return new RelawanProfilResource($profil);
    }

    public function syncSkills(Request $request, AuthPenggunaProfil $profil)
    {
        Gate::authorize('updateProfil', $profil);

        $request->validate([
            'keahlian' => ['required', 'array'],
            'keahlian.*' => ['integer', 'exists:auth_keahlian_master,id_keahlian'],
        ]);

        $this->service->syncVolunteerSkills($profil->id_pengguna, $request->keahlian);

        return new RelawanProfilResource($profil->fresh(['pengguna.keahlian']));
    }

    public function available(AuthorizationContextService $authContext)
    {
        if (!$authContext->hasAnyRole(['super_admin', 'pwnu', 'pcnu'])) {
            abort(403);
        }

        $volunteers = $this->service->getAvailableVolunteers();
        $profiles = $volunteers->map(function ($user) {
            return $user->profil ?? $this->service->getVolunteerProfile($user->id_pengguna);
        });

        return RelawanProfilResource::collection($profiles);
    }
}
