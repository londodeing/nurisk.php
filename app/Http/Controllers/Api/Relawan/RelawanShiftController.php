<?php

namespace App\Http\Controllers\Api\Relawan;

use App\Http\Controllers\Controller;
use App\Models\RelawanShift;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RelawanShiftController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', RelawanShift::class);
        $items = RelawanShift::with(['penugasanRelawan.pendaftaran.pengguna.profil'])
            ->when($request->id_penugasan_relawan, fn($q, $v) => $q->where('id_penugasan_relawan', $v))
            ->orderBy('waktu_mulai', 'desc')
            ->paginate($request->get('per_page', 15));
        return response()->json(['data' => $items, 'meta' => ['total' => $items->total()]]);
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', RelawanShift::class);
        $validated = $request->validate([
            'id_penugasan_relawan' => 'required|exists:relawan_penugasan,id_penugasan_relawan',
            'waktu_mulai' => 'required|date',
            'waktu_selesai' => 'required|date|after:waktu_mulai',
        ]);
        $shift = RelawanShift::create($validated);
        return response()->json(['message' => 'Shift tercatat.', 'data' => ['id' => $shift->id_relawan_shift]], 201);
    }

    public function show(RelawanShift $shift): JsonResponse
    {
        $this->authorize('view', $shift);
        $shift->load(['penugasanRelawan.pendaftaran.pengguna.profil']);
        return response()->json(['data' => $shift]);
    }

    public function update(Request $request, RelawanShift $shift): JsonResponse
    {
        $this->authorize('update', $shift);
        $validated = $request->validate([
            'waktu_mulai' => 'sometimes|date',
            'waktu_selesai' => 'sometimes|date|after:waktu_mulai',
        ]);
        $shift->update($validated);
        return response()->json(['message' => 'Shift diperbarui.']);
    }

    public function destroy(RelawanShift $shift): JsonResponse
    {
        $this->authorize('delete', $shift);
        $shift->delete();
        return response()->json(['message' => 'Shift dihapus.']);
    }
}
