<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JabatanPosisi;
use App\Http\Requests\Admin\StoreJabatanRequest;
use App\Http\Requests\Admin\UpdateJabatanRequest;

class JabatanController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorize('viewAny', JabatanPosisi::class);

        $jabatans = JabatanPosisi::withCount('penggunaJabatan')
            ->orderBy('id_jabatan_posisi')
            ->paginate(15);

        return view('admin.jabatan.index', compact('jabatans'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->authorize('create', JabatanPosisi::class);

        return view('admin.jabatan.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreJabatanRequest $request)
    {
        JabatanPosisi::create($request->validated());

        return redirect()->route('admin.jabatan.index')
            ->with('success', 'Jabatan berhasil ditambahkan.');
    }

    /**
     * Display the specified resource.
     */
    public function show(JabatanPosisi $jabatan)
    {
        $this->authorize('view', $jabatan);

        return redirect()->route('admin.jabatan.index');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(JabatanPosisi $jabatan)
    {
        $this->authorize('update', $jabatan);

        return view('admin.jabatan.edit', compact('jabatan'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateJabatanRequest $request, JabatanPosisi $jabatan)
    {
        $jabatan->update($request->validated());

        return redirect()->route('admin.jabatan.index')
            ->with('success', 'Jabatan berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(JabatanPosisi $jabatan)
    {
        $this->authorize('delete', $jabatan);

        if ($jabatan->penggunaJabatan()->exists()) {
            return back()->with('error', 'Jabatan tidak dapat dihapus karena masih digunakan oleh pengguna.');
        }

        $jabatan->delete();

        return redirect()->route('admin.jabatan.index')
            ->with('success', 'Jabatan berhasil dihapus.');
    }
}
