<?php

namespace App\Http\Controllers\Api\Governance;

use App\Http\Controllers\Controller;
use App\Models\DokumenSuratUtama;
use App\Models\DokumenSuratParaf;
use App\Models\DokumenSuratTembusan;
use App\Services\SuratService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class SuratApiController extends Controller
{
    public function __construct(
        private SuratService $suratService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', DokumenSuratUtama::class);

        $items = DokumenSuratUtama::with(['jenisSurat', 'penandatangan.profil'])
            ->when($request->status_surat, fn($q, $v) => $q->where('status_surat', $v))
            ->when($request->id_insiden, fn($q, $v) => $q->where('id_insiden', $v))
            ->orderBy('dibuat_pada', 'desc')
            ->paginate($request->get('per_page', 15));

        return response()->json([
            'data' => $items->map(fn($s) => [
                'id' => $s->id_surat,
                'nomor' => $s->nomor_surat_resmi,
                'perihal' => $s->perihal,
                'jenis' => $s->jenisSurat?->nama_jenis,
                'status' => $s->status_surat,
                'label_status' => $s->labelStatus(),
                'penandatangan' => $s->penandatangan?->profil?->nama_lengkap,
                'tgl_terbit' => $s->tgl_terbit?->toDateString(),
                'dibuat_pada' => $s->dibuat_pada?->toIso8601String(),
            ]),
            'meta' => ['total' => $items->total()],
        ]);
    }

    public function show(DokumenSuratUtama $surat): JsonResponse
    {
        $this->authorize('view', $surat);

        $surat->load([
            'jenisSurat', 'insiden', 'penandatangan.profil',
            'jabatanTtd', 'paraf.pengguna.profil', 'tembusan',
        ]);

        return response()->json(['data' => $surat]);
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', DokumenSuratUtama::class);

        $validated = $request->validate([
            'id_insiden' => 'required|exists:operasi_insiden,id_insiden',
            'id_jenis_surat' => 'required|exists:master_surat_jenis,id_jenis_surat',
            'perihal' => 'required|string|max:255',
            'id_pengguna_ttd' => 'required|exists:auth_users,id_pengguna',
            'id_jabatan_ttd' => 'required|exists:master_jabatan_penandatangan,id_jabatan',
            'isi_surat_snapshot' => 'nullable|string',
        ]);

        $surat = DokumenSuratUtama::create($validated + ['status_surat' => 'draft']);

        return response()->json(['message' => 'Surat dibuat.', 'data' => ['id' => $surat->id_surat]], 201);
    }

    public function update(Request $request, DokumenSuratUtama $surat): JsonResponse
    {
        $this->authorize('update', $surat);

        $validated = $request->validate([
            'perihal' => 'sometimes|string|max:255',
            'id_pengguna_ttd' => 'sometimes|exists:auth_users,id_pengguna',
            'id_jabatan_ttd' => 'sometimes|exists:master_jabatan_penandatangan,id_jabatan',
            'isi_surat_snapshot' => 'nullable|string',
        ]);

        $surat->update($validated);

        return response()->json(['message' => 'Surat diperbarui.']);
    }

    public function destroy(DokumenSuratUtama $surat): JsonResponse
    {
        $this->authorize('delete', $surat);
        $surat->delete();
        return response()->json(['message' => 'Surat dihapus.']);
    }

    public function ajukanParaf(Request $request, DokumenSuratUtama $surat): JsonResponse
    {
        $this->authorize('update', $surat);

        $validated = $request->validate([
            'paraf' => 'required|array',
            'paraf.*.id_pengguna' => 'required|exists:auth_users,id_pengguna',
            'paraf.*.urutan' => 'required|integer|min:1',
        ]);

        foreach ($validated['paraf'] as $p) {
            $surat->paraf()->create([
                'id_pengguna' => $p['id_pengguna'],
                'urutan' => $p['urutan'],
                'status_paraf' => 'menunggu',
            ]);
        }

        $surat->update(['status_surat' => 'review_paraf']);

        return response()->json(['message' => 'Paraf diajukan.']);
    }

    public function parafAction(Request $request, DokumenSuratParaf $paraf): JsonResponse
    {
        $this->authorize('paraf', $paraf);

        $validated = $request->validate([
            'status_paraf' => 'required|in:disetujui,ditolak',
            'catatan' => 'nullable|string|max:500',
        ]);

        $paraf->update([
            'status_paraf' => $validated['status_paraf'],
            'catatan' => $validated['catatan'] ?? null,
            'waktu_paraf' => now(),
        ]);

        $surat = $paraf->surat;

        if ($validated['status_paraf'] === 'ditolak') {
            $surat->update(['status_surat' => 'ditolak']);
            return response()->json(['message' => 'Paraf ditolak. Surat kembali ke draft.']);
        }

        $next = DokumenSuratParaf::where('id_surat', $paraf->id_surat)
            ->where('status_paraf', 'menunggu')
            ->orderBy('urutan')
            ->first();

        if (!$next) {
            $surat->update(['status_surat' => 'siap_tanda_tangan']);
            return response()->json(['message' => 'Semua paraf selesai. Surat siap ditandatangani.']);
        }

        return response()->json(['message' => 'Paraf disetujui. Menunggu paraf berikutnya.']);
    }

    public function finalisasi(Request $request, DokumenSuratUtama $surat): JsonResponse
    {
        $this->authorize('finalisasi', $surat);

        try {
            $isiSnapshot = $request->input('isi_surat_snapshot');
            $this->suratService->finalisasi($surat, $request->user(), $isiSnapshot);
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json(['message' => 'Surat difinalisasi dan ditandatangani.']);
    }

    public function tembusanIndex(DokumenSuratUtama $surat): JsonResponse
    {
        $this->authorize('view', $surat);
        return response()->json(['data' => $surat->tembusan()->get()]);
    }

    public function tembusanStore(Request $request, DokumenSuratUtama $surat): JsonResponse
    {
        $this->authorize('update', $surat);
        $validated = $request->validate([
            'nama_pihak' => 'required|string|max:255',
        ]);
        $tembusan = $surat->tembusan()->create($validated);
        return response()->json(['message' => 'Tembusan ditambahkan.', 'data' => $tembusan], 201);
    }

    public function tembusanDestroy(DokumenSuratUtama $surat, DokumenSuratTembusan $tembusan): JsonResponse
    {
        $this->authorize('update', $surat);
        if ($tembusan->id_surat !== $surat->id_surat) {
            return response()->json(['message' => 'Not found'], 404);
        }
        $tembusan->delete();
        return response()->json(['message' => 'Tembusan dihapus.']);
    }
}
