<?php

namespace App\Http\Controllers\Operasi;

use App\Http\Controllers\Controller;
use App\Models\OperasiInsiden;
use App\Models\OperasiPosaju;
use App\Models\OperasiPosajuKomandan;
use App\Services\Operasi\PosajuJurnalService;
use Illuminate\Http\Request;

class PosajuKomandanController extends Controller
{
    public function __construct(
        private PosajuJurnalService $jurnal
    ) {}

    public function store(Request $request, OperasiInsiden $insiden, OperasiPosaju $posaju)
    {
        $this->authorize('tambahKomandan', $posaju);

        $validated = $request->validate([
            'id_pengguna'       => ['required', 'integer', 'exists:auth_users,id_pengguna'],
            'id_pleno_keputusan' => ['required', 'integer', 'exists:operasi_pleno_keputusan,id_keputusan'],
        ]);

        // Akhiri tugas komandan aktif sebelumnya
        OperasiPosajuKomandan::where('id_posaju', $posaju->id_posaju)
            ->whereNull('waktu_selesai_tugas')
            ->update(['waktu_selesai_tugas' => now()]);

        $komandan = OperasiPosajuKomandan::create([
            'id_posaju'          => $posaju->id_posaju,
            'id_pengguna'        => $validated['id_pengguna'],
            'id_pleno_keputusan' => $validated['id_pleno_keputusan'],
            'waktu_mulai_tugas'  => now(),
        ]);

        $posaju->update(['pj_posaju' => $validated['id_pengguna']]);
        $this->jurnal->catat('komandan_ditunjuk', $posaju);

        return redirect()->route('insiden.posaju.show', [$insiden, $posaju])
            ->with('success', 'Komandan Pos Aju berhasil ditunjuk.');
    }

    public function destroy(OperasiInsiden $insiden, OperasiPosaju $posaju, OperasiPosajuKomandan $komandan)
    {
        $this->authorize('tambahKomandan', $posaju);

        if ($komandan->id_posaju !== $posaju->id_posaju) {
            return back()->with('error', 'Data komandan tidak valid.');
        }

        $komandan->update(['waktu_selesai_tugas' => now()]);
        $this->jurnal->catat('komandan_berakhir', $posaju);

        return redirect()->route('insiden.posaju.show', [$insiden, $posaju])
            ->with('success', 'Tugas komandan diakhiri.');
    }
}
