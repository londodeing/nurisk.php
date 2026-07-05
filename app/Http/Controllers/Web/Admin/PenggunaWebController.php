<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuthUser;
use Illuminate\Http\Request;

class PenggunaWebController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', AuthUser::class);

        $query = AuthUser::with(['profil', 'peran', 'jabatanPosisi.jabatan'])
            ->latest('dibuat_pada');

        if ($request->filled('search')) {
            $query->whereHas('profil', function ($q) use ($request) {
                $q->where('nama_lengkap', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('status_akun')) {
            $query->where('status_akun', $request->status_akun);
        } else {
            $query->where('status_akun', 'menunggu');
        }

        $penggunas = $query->paginate(15);

        return view('admin.pengguna.index', compact('penggunas'));
    }

    public function setujui(AuthUser $pengguna)
    {
        $this->authorize('approve', $pengguna);

        if ($pengguna->status_akun !== 'menunggu') {
            return back()->with('error', 'Pengguna ini sudah diproses sebelumnya.');
        }

        $pengguna->update([
            'status_akun' => 'aktif',
            'is_tersedia' => 1
        ]);
        
        // Update jabatan_posisi status as well
        foreach($pengguna->jabatanPosisi as $jabatan) {
            $jabatan->update(['status_aktif' => 1]);
        }

        return back()->with('success', 'Akun ' . ($pengguna->profil?->nama_lengkap ?? 'pengguna') . ' berhasil disetujui.');
    }

    public function tolak(AuthUser $pengguna)
    {
        $this->authorize('approve', $pengguna);

        if ($pengguna->status_akun !== 'menunggu') {
            return back()->with('error', 'Pengguna ini sudah diproses sebelumnya.');
        }

        $pengguna->update([
            'status_akun' => 'nonaktif',
            'is_tersedia' => 0
        ]);

        return back()->with('success', 'Pendaftaran akun ditolak.');
    }

    public function show(AuthUser $pengguna)
    {
        $this->authorize('view', $pengguna);
        $pengguna->load(['profil', 'peran', 'keahlian', 'jabatanPosisi.jabatan']);
        return view('admin.pengguna.show', compact('pengguna'));
    }

    public function edit(AuthUser $pengguna)
    {
        $this->authorize('update', $pengguna);
        $roles = \App\Models\AuthRole::orderBy('level_otoritas')->get();
        return view('admin.pengguna.edit', compact('pengguna', 'roles'));
    }

    public function update(Request $request, AuthUser $pengguna)
    {
        $this->authorize('update', $pengguna);
        
        $rules = [
            'status_akun' => 'required|in:aktif,nonaktif,suspend',
            'id_peran' => 'required|exists:auth_roles,id_peran',
            'is_tersedia' => 'nullable|boolean',
            'kata_sandi' => 'nullable|string|min:6',
        ];
        
        $validated = $request->validate($rules);
        if (empty($validated['kata_sandi'])) {
            unset($validated['kata_sandi']);
        } else {
            $validated['kata_sandi'] = \Illuminate\Support\Facades\Hash::make($validated['kata_sandi']);
        }
        
        $validated['is_tersedia'] = $request->has('is_tersedia');

        $pengguna->update($validated);

        return redirect()->route('admin.pengguna.index')
            ->with('success', 'Pengguna berhasil diperbarui.');
    }

    public function toggleJabatan(\App\Models\PenggunaJabatan $pengguna_jabatan)
    {
        $this->authorize('update', $pengguna_jabatan->pengguna);

        $pengguna_jabatan->update([
            'status_aktif' => !$pengguna_jabatan->status_aktif
        ]);

        $statusLabel = $pengguna_jabatan->status_aktif ? 'diaktifkan' : 'dinonaktifkan';

        return back()->with('success', 'Jabatan ' . ($pengguna_jabatan->jabatan?->nama_jabatan ?? 'pengguna') . ' berhasil ' . $statusLabel . '.');
    }
}
