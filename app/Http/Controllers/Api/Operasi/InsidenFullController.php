<?php

namespace App\Http\Controllers\Api\Operasi;

use App\Http\Controllers\Controller;
use App\Models\OperasiInsiden;
use App\Models\OperasiEskalasi;
use App\Models\OperasiAktivasi;
use App\Models\AssessmentUtama;
use App\Models\OperasiSitrep;
use App\Models\OperasiPleno;
use App\Models\OperasiPlenoKeputusan;
use App\Models\OperasiPlenoPeserta;
use App\Models\DokumenSuratUtama;
use App\Models\DokumenSuratParaf;
use App\Models\DokumenSuratTembusan;
use App\Models\LogistikGudang;
use App\Models\LogistikPermintaan;
use App\Models\RelawanKebutuhan;
use App\Models\RelawanSertifikasi;
use App\Models\RelawanShift;
use App\Models\OrganisasiSk;
use App\Models\OrganisasiMandat;
use App\Models\OrganisasiDelegasi;
use App\Models\OrganisasiJabatan;
use App\Models\OrgStructureLevel;
use App\Models\OrgInstitution;
use App\Models\OrgNode;
use App\Models\OrgPosition;
use App\Models\OrgSk;
use App\Models\OrgMandate;
use App\Models\OrgDelegation;
use App\Models\OrgAsset;
use App\Models\MasterJabatanPenandatangan;
use App\Models\MasterSuratTemplate;
use App\Models\MasterSuratJenis;
use App\Models\MasterKlaster;
use App\Models\BencanaMasterJenis;
use App\Models\MasterSertifikasi;
use App\Models\AuthUser;
use App\Models\AuthPenggunaProfil;
use App\Models\RiwayatStatusInsiden;
use App\Services\Auth\AuthorizationContextService;
use App\Services\InsidenService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

// ===================================================================
// OPERASI INSIDEN — Full CRUD + lifecycle
// ===================================================================

class InsidenFullController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', OperasiInsiden::class);

        $items = OperasiInsiden::with(['jenisBencana:id_jenis,nama_bencana', 'pcnu:id_pcnu,nama_pcnu'])
            ->when($request->status, fn($q, $v) => $q->where('status_insiden', $v))
            ->when($request->id_pcnu, fn($q, $v) => $q->where('id_pcnu', $v))
            ->when($request->id_jenis_bencana, fn($q, $v) => $q->where('id_jenis_bencana', $v))
            ->when($request->search, fn($q, $v) => $q->where('kode_kejadian', 'like', "%{$v}%"))
            ->when($request->aktif, fn($q) => $q->aktif())
            ->orderBy($request->sort_by ?? 'dibuat_pada', $request->sort_order ?? 'desc')
            ->paginate($request->get('per_page', 15));

        return response()->json([
            'data' => $items->map(fn($i) => [
                'id' => $i->id_insiden,
                'uuid' => $i->uuid_insiden,
                'kode' => $i->kode_kejadian,
                'status' => $i->status_insiden,
                'label_status' => $i->labelStatus(),
                'prioritas' => $i->prioritas,
                'jenis_bencana' => $i->jenisBencana?->nama_bencana,
                'pcnu' => $i->pcnu?->nama_pcnu,
                'is_locked' => $i->is_locked,
                'waktu_mulai' => $i->waktu_mulai?->toIso8601String(),
                'dibuat_pada' => $i->dibuat_pada?->toIso8601String(),
            ]),
            'meta' => ['total' => $items->total(), 'current_page' => $items->currentPage()],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', OperasiInsiden::class);

        $validated = $request->validate([
            'id_laporan_asal' => 'nullable|exists:laporan_kejadian,id_laporan_kejadian',
            'id_jenis_bencana' => 'required|exists:bencana_master_jenis,id_jenis',
            'id_pcnu' => 'required|exists:organisasi_pcnu,id_pcnu',
            'id_mwc' => 'nullable|exists:organisasi_mwc,id_mwc',
            'prioritas' => 'required|in:rendah,sedang,tinggi',
            'waktu_mulai' => 'required|date',
            'kode_kejadian' => 'nullable|string|max:50|unique:operasi_insiden',
        ]);

        if (empty($validated['kode_kejadian'])) {
            $validated['kode_kejadian'] = 'INS-' . strtoupper(\Illuminate\Support\Str::random(8));
        }

        $insiden = OperasiInsiden::create($validated + ['status_insiden' => 'draft']);

        return response()->json(['message' => 'Insiden berhasil dibuat.', 'data' => [
            'id' => $insiden->id_insiden,
            'kode' => $insiden->kode_kejadian,
        ]], 201);
    }

    public function show(OperasiInsiden $insiden): JsonResponse
    {
        $this->authorize('view', $insiden);

        $insiden->load([
            'jenisBencana', 'pcnu', 'laporanAsal',
            'posaju', 'riwayatStatus.pengubah.profil',
            'penugasan.pengguna.profil',
            'assessments.petugas.profil',
        ]);

        // Jurnal Operasi
        $jurnal = \App\Models\OperasiJurnal::where('id_insiden', $insiden->id_insiden)
            ->orderBy('dibuat_pada', 'desc')
            ->get();

        return response()->json(['data' => [
            'id'           => $insiden->id_insiden,
            'uuid'         => $insiden->uuid_insiden,
            'kode'         => $insiden->kode_kejadian,
            'status'       => $insiden->status_insiden,
            'label_status' => $insiden->labelStatus(),
            'prioritas'    => $insiden->prioritas,
            'is_locked'    => $insiden->is_locked,
            'no_spk_assesment' => $insiden->no_spk_assesment,
            'tgl_spk_assesment' => $insiden->tgl_spk_assesment?->toDateString(),
            'jenis_bencana' => $insiden->jenisBencana?->nama_bencana,
            'pcnu'          => $insiden->pcnu ? ['id' => $insiden->pcnu->id_pcnu, 'nama' => $insiden->pcnu->nama_pcnu] : null,
            'waktu_mulai'   => $insiden->waktu_mulai?->toIso8601String(),
            'waktu_selesai' => $insiden->waktu_selesai?->toIso8601String(),
            'dibuat_pada'   => $insiden->dibuat_pada?->toIso8601String(),

            // Laporan Asal
            'laporan_asal' => $insiden->laporanAsal ? [
                'id'                   => $insiden->laporanAsal->id_laporan_kejadian,
                'kode_kejadian'        => $insiden->laporanAsal->kode_kejadian,
                'nama_pelapor'         => $insiden->laporanAsal->nama_pelapor,
                'hp_pelapor'           => $insiden->laporanAsal->hp_pelapor,
                'keterangan_situasi'   => $insiden->laporanAsal->keterangan_situasi,
                'titik_kenal'          => $insiden->laporanAsal->titik_kenal,
                'alamat_lengkap'       => $insiden->laporanAsal->alamat_lengkap,
                'latitude'             => $insiden->laporanAsal->latitude,
                'longitude'            => $insiden->laporanAsal->longitude,
                'waktu_kejadian'       => $insiden->laporanAsal->waktu_kejadian?->toIso8601String(),
                'photo_path'           => $insiden->laporanAsal->photo_path,
                'media_url'            => $insiden->laporanAsal->photo_path ? media_url($insiden->laporanAsal->photo_path) : null,
            ] : null,

            // Riwayat Status
            'riwayat_status' => $insiden->riwayatStatus->map(fn($r) => [
                'status'   => $r->status_baru,
                'pengubah' => $r->pengubah?->profil?->nama_lengkap ?? $r->pengubah?->no_hp,
                'waktu'    => $r->dibuat_pada?->toIso8601String(),
                'alasan'   => $r->alasan,
            ]),

            // Penugasan Aktif
            'penugasan' => $insiden->penugasan->map(fn($p) => [
                'id'              => $p->id_penugasan,
                'nama_personel'   => $p->pengguna?->profil?->nama_lengkap ?? $p->pengguna?->no_hp ?? '-',
                'peran_otoritas'  => $p->peran_otoritas,
                'status_penugasan' => $p->status_penugasan,
                'waktu_mulai'     => $p->waktu_mulai?->toIso8601String(),
            ]),

            // Assessments Summary
            'assessments' => $insiden->assessments->map(fn($a) => [
                'id'              => $a->id_assessment,
                'uuid'            => $a->uuid_assessment ?? '',
                'is_latest'       => (bool) $a->is_latest,
                'waktu_assesment' => $a->waktu_assesment?->toIso8601String(),
                'nama_petugas'    => $a->petugas?->profil?->nama_lengkap ?? $a->petugas?->no_hp,
            ]),

            // Jurnal Operasi
            'jurnal' => $jurnal->map(fn($j) => [
                'judul_event'    => $j->judul_event,
                'deskripsi_event' => $j->deskripsi_event,
                'kategori_event'  => $j->kategori_event,
                'dibuat_pada'     => $j->dibuat_pada?->toIso8601String(),
            ]),
        ]]);
    }

    public function update(Request $request, OperasiInsiden $insiden): JsonResponse
    {
        $this->authorize('update', $insiden);

        $validated = $request->validate([
            'id_jenis_bencana' => 'sometimes|exists:bencana_master_jenis,id_jenis',
            'id_pcnu' => 'sometimes|exists:organisasi_pcnu,id_pcnu',
            'id_mwc' => 'nullable|exists:organisasi_mwc,id_mwc',
            'prioritas' => 'sometimes|in:rendah,sedang,tinggi',
            'waktu_mulai' => 'sometimes|date',
            'waktu_selesai' => 'nullable|date',
            'no_spk_assesment' => 'nullable|string|max:100',
            'tgl_spk_assesment' => 'nullable|date',
            'id_pemberi_spk' => 'nullable|exists:auth_users,id_pengguna',
            'id_penerima_spk' => 'nullable|exists:auth_users,id_pengguna',
        ]);

        $insiden->update($validated);

        return response()->json(['message' => 'Insiden diperbarui.', 'data' => ['id' => $insiden->id_insiden]]);
    }

    public function destroy(OperasiInsiden $insiden): JsonResponse
    {
        $this->authorize('delete', $insiden);
        $insiden->delete();
        return response()->json(['message' => 'Insiden dihapus.']);
    }

    public function ubahStatus(Request $request, OperasiInsiden $insiden): JsonResponse
    {
        $this->authorize('ubahStatus', $insiden);

        $validated = $request->validate([
            'status' => 'required|in:draft,terverifikasi,respon,pemulihan,selesai,dibatalkan',
            'alasan' => 'nullable|string|max:500',
        ]);

        try {
            $insiden = app(InsidenService::class)->ubahStatus(
                insiden: $insiden,
                statusBaru: $validated['status'],
                pengguna: $request->user(),
                alasan: $validated['alasan'],
            );
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json([
            'message' => "Status berubah menjadi {$validated['status']}.",
            'data' => [
                'id' => $insiden->id_insiden,
                'status' => $insiden->status_insiden,
                'is_locked' => $insiden->is_locked,
            ],
        ]);
    }

    public function lock(OperasiInsiden $insiden): JsonResponse
    {
        $this->authorize('update', $insiden);
        $insiden->update(['is_locked' => true]);
        return response()->json(['message' => 'Insiden dikunci.']);
    }

    public function unlock(OperasiInsiden $insiden): JsonResponse
    {
        $this->authorize('update', $insiden);
        $insiden->update(['is_locked' => false]);
        return response()->json(['message' => 'Insiden dibuka.']);
    }
}
