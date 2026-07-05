<?php

namespace App\Http\Controllers;

use App\Models\OrgAsset;
use App\Services\Media\MediaUploadService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class OrgAssetController extends Controller
{
    public function __construct(
        private MediaUploadService $mediaUploadService,
    ) {}

    public function index()
    {
        $assets = OrgAsset::with(['ownerNode', 'custodianNode'])->get();
        return view('assets.index', compact('assets'));
    }

    public function create()
    {
        $nodes = class_exists(\App\Models\OrgNode::class) ? \App\Models\OrgNode::all() : collect([]);
        return view('assets.create', compact('nodes'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|string|in:FACILITY,FLEET,EQUIPMENT,INVENTORY,DISASTER_SPECIFIC',
            'sub_category' => 'nullable|string|max:100',
            'owner_node_id' => 'required|exists:org_nodes,id',
            'custodian_node_id' => 'nullable|exists:org_nodes,id',
            'home_territory_code' => 'required|string|max:20',
            'metadata' => 'nullable|array',
            'foto' => ['nullable', ...$this->mediaUploadService->toValidationRules('aset')],
        ]);

        $validated['asset_code'] = 'AST-' . strtoupper(Str::random(8));
        $validated['status'] = 'DRAFT';
        $validated['readiness'] = 'UNAVAILABLE';
        $validated['current_territory_code'] = $validated['home_territory_code'];

        if ($request->hasFile('foto')) {
            $result = $this->mediaUploadService->upload($request->file('foto'), 'aset');
            $validated['foto_utama_path'] = $result->path;
        }

        $asset = OrgAsset::create($validated);

        return redirect()->route('assets.create')->with('success', 'Aset ' . $asset->name . ' berhasil didaftarkan!');
    }

    public function show($id)
    {
        $asset = OrgAsset::with(['ownerNode', 'custodianNode'])->findOrFail($id);
        return response()->json($asset);
    }

    public function import(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt',
            'owner_node_id' => 'required|exists:org_nodes,id',
            'home_territory_code' => 'required|string',
        ]);

        $file = $request->file('csv_file');
        $result = $this->mediaUploadService->upload($file, 'aset');
        $handle = fopen($file->getRealPath(), 'r');

        $header = true;
        $count = 0;

        while (($row = fgetcsv($handle, 1000, ',')) !== false) {
            if ($header) {
                $header = false;
                continue;
            }

            if (isset($row[0]) && isset($row[1])) {
                $categoryMap = [
                    'FASILITAS' => 'FACILITY',
                    'ARMADA' => 'FLEET',
                    'PERLENGKAPAN' => 'EQUIPMENT',
                ];

                $catInput = strtoupper(trim($row[1]));
                $category = $categoryMap[$catInput] ?? 'EQUIPMENT';

                OrgAsset::create([
                    'asset_code' => 'AST-' . strtoupper(Str::random(8)),
                    'name' => trim($row[0]),
                    'category' => $category,
                    'legal_owner_name' => isset($row[2]) && trim($row[2]) !== '' ? trim($row[2]) : null,
                    'owner_node_id' => $request->owner_node_id,
                    'home_territory_code' => $request->home_territory_code,
                    'current_territory_code' => $request->home_territory_code,
                    'status' => 'AKTIF',
                    'readiness' => 'UNAVAILABLE',
                    'verification_status' => 'UNVERIFIED',
                ]);
                $count++;
            }
        }
        fclose($handle);

        return redirect()->route('assets.create')->with('success', "$count Aset berhasil diimpor dari CSV!");
    }
}
