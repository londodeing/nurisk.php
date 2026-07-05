<?php

namespace App\Http\Controllers\Api\Operasi;

use App\Http\Controllers\Controller;
use App\Http\Requests\Operasi\StoreMobilisasiRequest;
use App\Http\Requests\Operasi\UpdateMobilisasiRequest;
use App\Http\Resources\Operasi\MobilisasiResource;
use App\Models\OperasiMobilisasi;
use App\Models\OperasiInsiden;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MobilisasiApiController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', OperasiMobilisasi::class);

        $query = OperasiMobilisasi::with(['insiden', 'pengguna']);

        if ($request->has('uuid_insiden')) {
            $insiden = OperasiInsiden::where('uuid_insiden', $request->uuid_insiden)->firstOrFail();
            $query->where('id_insiden', $insiden->id_insiden);
        }

        $mobilisasi = $query->paginate($request->get('per_page', 15));

        return MobilisasiResource::collection($mobilisasi)->additional([
            'success' => true,
        ]);
    }

    public function show($uuid)
    {
        $mobilisasi = OperasiMobilisasi::where('uuid_mobilisasi', $uuid)->firstOrFail();
        
        // Authorization via Policy
        $this->authorize('view', $mobilisasi);

        return (new MobilisasiResource($mobilisasi))->additional([
            'success' => true,
        ]);
    }

    public function store(StoreMobilisasiRequest $request)
    {
        $validated = $request->validated();
        
        $insiden = OperasiInsiden::where('uuid_insiden', $validated['uuid_insiden'])->firstOrFail();

        // Authorization via Policy
        $this->authorize('create', [OperasiMobilisasi::class, $insiden]);

        $mobilisasi = DB::transaction(function () use ($validated, $insiden) {
            $data = $validated;
            $data['id_insiden'] = $insiden->id_insiden;
            unset($data['uuid_insiden']);

            // IDOR Protection: Validate that id_pengguna is within scope if not SuperAdmin/PWNU
            $user = auth()->user();
            if ($user && in_array($user->id_peran, [3, 4])) { // PCNU or Relawan
                $targetUser = \App\Models\AuthUser::find($data['id_pengguna']);
                if (!$targetUser || ($targetUser->default_scope_type === 'pcnu' && $targetUser->default_scope_id !== $user->default_scope_id)) {
                    abort(403, 'Anda tidak berhak memobilisasi pengguna di luar lingkup wilayah Anda.');
                }
            }

            // Auth user as created_by
            $data['created_by'] = auth()->id() ?? 1;
            $data['status_mobilisasi'] = 'draft';

            return OperasiMobilisasi::create($data);
        });

        return (new MobilisasiResource($mobilisasi))->additional([
            'success' => true,
            'message' => 'Mobilisasi berhasil dibuat'
        ]);
    }

    public function update(UpdateMobilisasiRequest $request, $uuid)
    {
        $mobilisasi = OperasiMobilisasi::where('uuid_mobilisasi', $uuid)->firstOrFail();
        
        $this->authorize('update', $mobilisasi);

        $validated = $request->validated();

        DB::transaction(function () use ($mobilisasi, $validated) {
            $mobilisasi->updated_by = auth()->id() ?? 1;
            $mobilisasi->update($validated);
        });

        return (new MobilisasiResource($mobilisasi->refresh()))->additional([
            'success' => true,
            'message' => 'Mobilisasi berhasil diperbarui'
        ]);
    }

    public function destroy($uuid)
    {
        $mobilisasi = OperasiMobilisasi::where('uuid_mobilisasi', $uuid)->firstOrFail();
        
        $this->authorize('delete', $mobilisasi);

        DB::transaction(function () use ($mobilisasi) {
            $mobilisasi->deleted_by = auth()->id() ?? 1;
            $mobilisasi->alasan_hapus = request('alasan_hapus');
            $mobilisasi->save();
            $mobilisasi->delete();
        });

        return response()->json([
            'success' => true,
            'message' => 'Mobilisasi berhasil dihapus'
        ]);
    }

    public function approve($uuid)
    {
        $mobilisasi = OperasiMobilisasi::where('uuid_mobilisasi', $uuid)->firstOrFail();
        $this->authorize('approve', $mobilisasi);

        if ($mobilisasi->status_mobilisasi !== 'draft') {
            return response()->json(['message' => 'Invalid state transition'], 422);
        }

        $mobilisasi->update(['status_mobilisasi' => 'disetujui', 'updated_by' => auth()->id()]);

        return new MobilisasiResource($mobilisasi);
    }

    public function depart($uuid)
    {
        $mobilisasi = OperasiMobilisasi::where('uuid_mobilisasi', $uuid)->firstOrFail();
        $this->authorize('depart', $mobilisasi);

        if ($mobilisasi->status_mobilisasi !== 'disetujui') {
            return response()->json(['message' => 'Invalid state transition'], 422);
        }

        $mobilisasi->update(['status_mobilisasi' => 'berangkat', 'waktu_berangkat' => now(), 'updated_by' => auth()->id()]);

        return new MobilisasiResource($mobilisasi);
    }

    public function arrive($uuid)
    {
        $mobilisasi = OperasiMobilisasi::where('uuid_mobilisasi', $uuid)->firstOrFail();
        $this->authorize('arrive', $mobilisasi);

        if ($mobilisasi->status_mobilisasi !== 'berangkat') {
            return response()->json(['message' => 'Invalid state transition'], 422);
        }

        $mobilisasi->update(['status_mobilisasi' => 'tiba', 'waktu_tiba' => now(), 'updated_by' => auth()->id()]);

        return new MobilisasiResource($mobilisasi);
    }

    public function finish($uuid)
    {
        $mobilisasi = OperasiMobilisasi::where('uuid_mobilisasi', $uuid)->firstOrFail();
        $this->authorize('finish', $mobilisasi);

        if ($mobilisasi->status_mobilisasi !== 'tiba') {
            return response()->json(['message' => 'Invalid state transition'], 422);
        }

        $mobilisasi->update(['status_mobilisasi' => 'selesai', 'updated_by' => auth()->id()]);

        return new MobilisasiResource($mobilisasi);
    }

    public function cancel($uuid)
    {
        $mobilisasi = OperasiMobilisasi::where('uuid_mobilisasi', $uuid)->firstOrFail();
        $this->authorize('cancel', $mobilisasi);

        if (!in_array($mobilisasi->status_mobilisasi, ['draft', 'disetujui'])) {
            return response()->json(['message' => 'Invalid state transition'], 422);
        }

        $mobilisasi->update(['status_mobilisasi' => 'dibatalkan', 'updated_by' => auth()->id()]);

        return new MobilisasiResource($mobilisasi);
    }
}
