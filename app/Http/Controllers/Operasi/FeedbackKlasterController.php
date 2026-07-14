<?php

namespace App\Http\Controllers\Operasi;

use App\Http\Controllers\Controller;
use App\Models\OperasiFeedbackKlaster;
use App\Models\OperasiGapKebutuhan;
use App\Models\OperasiInsiden;
use App\Models\OperasiKlaster;
use App\Models\OperasiPenugasan;
use App\Models\LogistikBarangKatalog;
use App\Services\Operasi\PosajuJurnalService;
use App\Http\Requests\Operasi\StoreFeedbackKlasterRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FeedbackKlasterController extends Controller
{
    public function __construct(
        private PosajuJurnalService $jurnal
    ) {}

    public function index(Request $request, OperasiInsiden $insiden)
    {
        $this->authorize('viewAny', OperasiFeedbackKlaster::class);

        $feedbacks = OperasiFeedbackKlaster::with(['klasterOperasi.masterKlaster', 'pengguna.profil', 'gapKebutuhan'])
            ->where('id_insiden', $insiden->id_insiden)
            ->when($request->status, fn($q, $v) => $q->where('status_feedback', $v))
            ->when($request->klaster, fn($q, $v) => $q->where('id_klaster_operasi', $v))
            ->orderBy('dibuat_pada', 'desc')
            ->paginate(15);

        $klasterList = OperasiKlaster::with('masterKlaster')
            ->where('id_insiden', $insiden->id_insiden)
            ->orderBy('dibuat_pada', 'desc')
            ->get();

        return view('operasi.feedback-klaster.index', compact('feedbacks', 'insiden', 'klasterList'));
    }

    public function create(OperasiInsiden $insiden)
    {
        $this->authorize('create', [OperasiFeedbackKlaster::class, $insiden]);

        $klasterList = OperasiKlaster::with('masterKlaster')
            ->where('id_insiden', $insiden->id_insiden)
            ->where('status_klaster', 'aktif')
            ->orderBy('dibuat_pada', 'desc')
            ->get();

        return view('operasi.feedback-klaster.create', compact('insiden', 'klasterList'));
    }

    public function store(StoreFeedbackKlasterRequest $request, OperasiInsiden $insiden)
    {
        $this->authorize('create', [OperasiFeedbackKlaster::class, $insiden]);

        $validated = $request->validated();

        $feedback = DB::transaction(function () use ($validated, $insiden) {
            $feedback = OperasiFeedbackKlaster::create([
                'uuid_feedback_klaster' => \Illuminate\Support\Str::uuid(),
                'id_insiden' => $insiden->id_insiden,
                'id_klaster_operasi' => $validated['id_klaster_operasi'],
                'id_pengguna' => auth()->id(),
                'kecukupan_sumberdaya' => $validated['kecukupan_sumberdaya'],
                'kualitas_layanan' => $validated['kualitas_layanan'],
                'tepat_waktu' => $validated['tepat_waktu'],
                'tepat_sasaran' => $validated['tepat_sasaran'],
                'kendala' => $validated['kendala'] ?? null,
                'rekomendasi' => $validated['rekomendasi'] ?? null,
                'gap_terdeteksi' => $validated['gap_terdeteksi'] ?? null,
                'status_feedback' => 'final',
                'dikunci_pada' => now(),
            ]);

            // Create gap records from detected gaps
            if (!empty($validated['gap_terdeteksi'])) {
                foreach ($validated['gap_terdeteksi'] as $gapData) {
                    OperasiGapKebutuhan::create([
                        'uuid_gap_kebutuhan' => \Illuminate\Support\Str::uuid(),
                        'id_insiden' => $insiden->id_insiden,
                        'id_klaster_operasi' => $validated['id_klaster_operasi'],
                        'id_feedback_klaster' => $feedback->id_feedback_klaster,
                        'jenis_gap' => $gapData['jenis_gap'],
                        'deskripsi_gap' => $gapData['deskripsi'] ?? '',
                        'selisih_jumlah' => $gapData['selisih_jumlah'] ?? null,
                        'satuan' => $gapData['satuan'] ?? null,
                        'prioritas' => $gapData['prioritas'] ?? 'sedang',
                        'status_gap' => 'dibuka',
                    ]);
                }
            }

            return $feedback;
        });

        $this->jurnal->catat('feedback_klaster_dikunci', $insiden, "Feedback klaster dikunci oleh " . ($feedback->pengguna?->profil?->nama_lengkap ?? 'sistem'));

        return redirect()
            ->route('insiden.feedback-klaster.index', $insiden)
            ->with('success', 'Feedback klaster berhasil dikunci dan disimpan.');
    }

    public function show(OperasiInsiden $insiden, OperasiFeedbackKlaster $feedback)
    {
        $this->authorize('view', $feedback);

        $feedback->load(['klasterOperasi.masterKlaster', 'pengguna.profil', 'gapKebutuhan']);

        return view('operasi.feedback-klaster.show', compact('insiden', 'feedback'));
    }
}