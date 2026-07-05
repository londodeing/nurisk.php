<?php

namespace App\Http\Controllers\Operasi;

use App\Http\Controllers\Controller;
use App\Models\AuthUser;
use App\Models\OperasiInsiden;
use App\Models\OperasiPenugasan;
use App\Models\OperasiPenugasanHistory;
use App\Services\Operasi\PenugasanService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PenugasanController extends Controller
{
    public function __construct(
        private PenugasanService $penugasanService
    ) {}

    public function index(OperasiInsiden $insiden)
    {
        $this->authorize('viewAny', [OperasiPenugasan::class, $insiden]);

        $penugasans = OperasiPenugasan::with(['pengguna.profil', 'pemberiTugas.profil'])
            ->where('id_insiden', $insiden->id_insiden)
            ->orderBy('dibuat_pada', 'desc')
            ->paginate(15);

        return view('operasi.penugasan.index', compact('insiden', 'penugasans'));
    }

    public function create(OperasiInsiden $insiden)
    {
        $this->authorize('create', [OperasiPenugasan::class, $insiden]);

        $authUsers = AuthUser::with('profil')
            ->where('status_akun', 'aktif')
            ->orderBy('id_pengguna')
            ->get();

        return view('operasi.penugasan.create', compact('insiden', 'authUsers'));
    }

    public function store(Request $request, OperasiInsiden $insiden)
    {
        $this->authorize('create', [OperasiPenugasan::class, $insiden]);

        $validated = $request->validate([
            'id_pengguna'    => ['required', 'integer', 'exists:auth_users,id_pengguna'],
            'peran_otoritas' => ['required', 'string', 'in:komandan_insiden,trc,relawan,medis,logistik,operator'],
            'catatan'        => ['nullable', 'string', 'max:500'],
        ]);

        try {
            $penugasan = $this->penugasanService->createPenugasan([
                'id_insiden'     => $insiden->id_insiden,
                'id_pengguna'    => $validated['id_pengguna'],
                'peran_otoritas' => $validated['peran_otoritas'],
                'catatan'        => $validated['catatan'] ?? null,
            ]);

            return redirect()->route('insiden.penugasan.show', [$insiden, $penugasan])
                ->with('success', 'Penugasan berhasil dibuat.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Gagal membuat penugasan: ' . $e->getMessage());
        }
    }

    public function show(OperasiInsiden $insiden, OperasiPenugasan $penugasan)
    {
        $this->authorize('view', $penugasan);

        $penugasan->loadMissing([
            'pengguna.profil',
            'pemberiTugas.profil',
            'klasterOperasi',
            'suratTugas',
        ]);

        $history = OperasiPenugasanHistory::where('id_penugasan', $penugasan->id_penugasan)
            ->orderBy('waktu_perubahan', 'desc')
            ->get();

        return view('operasi.penugasan.show', compact('insiden', 'penugasan', 'history'));
    }

    public function edit(OperasiInsiden $insiden, OperasiPenugasan $penugasan)
    {
        $this->authorize('update', $penugasan);

        if ($penugasan->status_penugasan !== 'draft') {
            return back()->with('error', 'Hanya penugasan draft yang bisa diedit.');
        }

        $authUsers = AuthUser::with('profil')
            ->where('status_akun', 'aktif')
            ->orderBy('id_pengguna')
            ->get();

        return view('operasi.penugasan.edit', compact('insiden', 'penugasan', 'authUsers'));
    }

    public function update(Request $request, OperasiInsiden $insiden, OperasiPenugasan $penugasan)
    {
        $this->authorize('update', $penugasan);

        if ($penugasan->status_penugasan !== 'draft') {
            return back()->with('error', 'Hanya penugasan draft yang bisa diedit.');
        }

        $validated = $request->validate([
            'peran_otoritas' => ['required', 'string', 'in:komandan_insiden,trc,relawan,medis,logistik,operator'],
            'catatan'        => ['nullable', 'string', 'max:500'],
        ]);

        $penugasan->update($validated);

        return redirect()->route('insiden.penugasan.show', [$insiden, $penugasan])
            ->with('success', 'Penugasan berhasil diperbarui.');
    }

    public function updateStatus(Request $request, OperasiInsiden $insiden, OperasiPenugasan $penugasan)
    {
        $this->authorize('update', $penugasan);

        $validated = $request->validate([
            'status'  => ['required', 'string'],
            'catatan' => ['nullable', 'string', 'max:500'],
        ]);

        try {
            $this->penugasanService->updateStatus(
                $penugasan,
                $validated['status'],
                $validated['catatan'] ?? null
            );

            return redirect()->route('insiden.penugasan.show', [$insiden, $penugasan])
                ->with('success', 'Status penugasan berhasil diperbarui.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal mengubah status: ' . $e->getMessage());
        }
    }

    public function destroy(OperasiInsiden $insiden, OperasiPenugasan $penugasan)
    {
        $this->authorize('delete', $penugasan);

        if ($penugasan->status_penugasan !== 'draft') {
            return back()->with('error', 'Hanya penugasan draft yang bisa dihapus.');
        }

        $penugasan->delete();

        return redirect()->route('insiden.penugasan.index', $insiden)
            ->with('success', 'Penugasan berhasil dihapus.');
    }
}
