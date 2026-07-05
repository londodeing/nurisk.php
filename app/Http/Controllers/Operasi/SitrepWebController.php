<?php

namespace App\Http\Controllers\Operasi;

use App\Http\Controllers\Controller;
use App\Models\OperasiSitrep;
use App\Models\OperasiInsiden;
use App\Services\Operasi\SitrepService;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Http\Request;

class SitrepWebController extends Controller
{
    public function __construct(
        private SitrepService $sitrepService
    ) {}

    public function index(Request $request)
    {
        $sitreps = OperasiSitrep::with(['insiden', 'pembuat.profil'])
            ->orderBy('dibuat_pada', 'desc')
            ->paginate(15);

        return view('operasi.sitrep.index', compact('sitreps'));
    }

    public function create()
    {
        $insidenList = OperasiInsiden::whereIn('status_insiden', ['terverifikasi', 'respon', 'pemulihan'])
            ->orderBy('dibuat_pada', 'desc')
            ->get(['id_insiden', 'kode_kejadian']);

        return view('operasi.sitrep.create', compact('insidenList'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_insiden'     => ['required', 'integer', 'exists:operasi_insiden,id_insiden'],
            'periode_sitrep' => ['nullable', 'string', 'max:255'],
            'catatan'        => ['nullable', 'string'],
        ]);

        try {
            $sitrep = $this->sitrepService->generateSitrep($validated);
            return redirect()->route('sitrep.show', $sitrep)
                ->with('success', 'Sitrep berhasil diterbitkan.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal membuat Sitrep: ' . $e->getMessage());
        }
    }

    public function show(OperasiSitrep $sitrep)
    {
        $sitrep->load(['insiden', 'pembuat.profil', 'dampak', 'kebutuhan']);
        return view('operasi.sitrep.show', compact('sitrep'));
    }

    public function pdf(OperasiSitrep $sitrep)
    {
        $sitrep->load(['insiden.pcnu', 'pembuat.profil', 'dampak', 'kebutuhan']);

        $html = view('pdf.sitrep', compact('sitrep'))->render();

        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', false);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $filename = 'sitrep-' . ($sitrep->nomor_sitrep ?? $sitrep->id_sitrep) . '.pdf';

        return response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $filename . '"',
        ]);
    }
}
