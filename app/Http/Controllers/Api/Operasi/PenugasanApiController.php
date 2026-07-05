<?php

namespace App\Http\Controllers\Api\Operasi;

use App\Http\Controllers\Controller;
use App\Http\Requests\Operasi\StorePenugasanRequest;
use App\Http\Requests\Operasi\UpdatePenugasanStatusRequest;
use App\Http\Resources\Operasi\PenugasanResource;
use App\Models\OperasiInsiden;
use App\Models\OperasiPenugasan;
use App\Models\OperasiPenugasanHistory;
use App\Services\Operasi\PenugasanService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class PenugasanApiController extends Controller
{
    private PenugasanService $service;

    public function __construct(PenugasanService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request): JsonResponse
    {
        $request->validate(['uuid_insiden' => 'required|exists:operasi_insiden,uuid_insiden']);
        $insiden = OperasiInsiden::where('uuid_insiden', $request->query('uuid_insiden'))->firstOrFail();

        $this->authorize('viewAny', [OperasiPenugasan::class, $insiden]);

        $query = OperasiPenugasan::with(['pengguna', 'klasterOperasi'])
            ->where('id_insiden', $insiden->id_insiden);

        // Incremental sync
        if ($request->has('updated_since')) {
            $query->where('diperbarui_pada', '>', $request->query('updated_since'));
        }

        // Filtering standard
        $filterable = ['status_penugasan', 'peran_otoritas', 'id_pengguna', 'id_klaster_operasi', 'ditugaskan_oleh'];
        foreach ($filterable as $field) {
            if ($request->has($field)) {
                $query->where($field, $request->query($field));
            }
        }

        if ($request->has('status')) {
            $query->where('status_penugasan', $request->query('status'));
        }

        // Sorting standard
        $sortBy = $request->query('sort_by', 'waktu_mulai');
        $sortOrder = $request->query('sort_order', 'desc');
        $allowedSortColumns = ['waktu_mulai', 'waktu_selesai', 'dibuat_pada', 'diperbarui_pada', 'status_penugasan', 'peran_otoritas'];
        
        if (!in_array($sortBy, $allowedSortColumns)) {
            $sortBy = 'waktu_mulai';
        }
        if (!in_array(strtolower($sortOrder), ['asc', 'desc'])) {
            $sortOrder = 'desc';
        }

        $penugasan = $query->orderBy($sortBy, $sortOrder)->paginate(15);

        return $this->apiPaginatedResponse($penugasan, PenugasanResource::class);
    }

    public function store(StorePenugasanRequest $request): JsonResponse
    {
        $data = $request->validated();
        $insiden = OperasiInsiden::where('uuid_insiden', $data['uuid_insiden'])->firstOrFail();
        $data['id_insiden'] = $insiden->id_insiden;

        $this->authorize('create', [OperasiPenugasan::class, $insiden]);

        $penugasan = $this->service->createPenugasan($data);
        $penugasan->load(['pengguna', 'klasterOperasi']);

        return $this->apiResponse(new PenugasanResource($penugasan), 'Penugasan berhasil dibuat', 201);
    }

    public function show(string $uuid): JsonResponse
    {
        $penugasan = OperasiPenugasan::with(['pengguna', 'klasterOperasi'])
            ->where('uuid_penugasan', $uuid)
            ->firstOrFail();

        $this->authorize('viewAny', [OperasiPenugasan::class, $penugasan->insiden]);

        return $this->apiResponse(new PenugasanResource($penugasan));
    }

    public function updateStatus(UpdatePenugasanStatusRequest $request, string $uuid): JsonResponse
    {
        $penugasan = OperasiPenugasan::where('uuid_penugasan', $uuid)->firstOrFail();
        $this->authorize('update', $penugasan);

        $data = $request->validated();
        $penugasan = $this->service->updateStatus($penugasan, $data['status_penugasan'], $data['catatan'] ?? null);

        return $this->apiResponse(new PenugasanResource($penugasan), 'Status penugasan berhasil diperbarui');
    }

    public function destroy(string $uuid): JsonResponse
    {
        $penugasan = OperasiPenugasan::where('uuid_penugasan', $uuid)->firstOrFail();
        $this->authorize('delete', $penugasan);

        if ($penugasan->status_penugasan !== 'draft') {
            return response()->json([
                'success' => false,
                'message' => 'Hanya penugasan draft yang bisa dihapus secara permanen. Gunakan update status menjadi dibatalkan untuk penugasan aktif.'
            ], 400);
        }

        $penugasan->delete();

        return $this->apiResponse(null, 'Penugasan berhasil dihapus');
    }

    public function history(string $uuid): JsonResponse
    {
        $penugasan = OperasiPenugasan::where('uuid_penugasan', $uuid)->firstOrFail();
        $this->authorize('view', $penugasan);
        $items = OperasiPenugasanHistory::where('id_penugasan', $penugasan->id_penugasan)
            ->orderBy('waktu_perubahan', 'desc')
            ->get();
        return response()->json(['data' => $items]);
    }

    public function bulk(Request $request): JsonResponse
    {
        $request->validate([
            'items' => 'required|array',
            'items.*.uuid_insiden' => 'required|exists:operasi_insiden,uuid_insiden',
            'items.*.id_pengguna' => 'required|exists:auth_users,id_pengguna',
            'items.*.peran_otoritas' => 'required|string',
            'items.*.catatan' => 'nullable|string',
        ]);

        $items = $request->input('items');
        $successes = [];
        $failures = [];

        foreach ($items as $index => $itemData) {
            try {
                DB::beginTransaction();

                // Duplicate Protection
                $insidenDb = OperasiInsiden::where('uuid_insiden', $itemData['uuid_insiden'])->firstOrFail();
                $itemData['id_insiden'] = $insidenDb->id_insiden;
                $exists = OperasiPenugasan::where('id_insiden', $itemData['id_insiden'])
                    ->where('id_pengguna', $itemData['id_pengguna'])
                    ->where('status_penugasan', 'aktif')
                    ->exists();

                if ($exists) {
                    throw new \Exception("Duplicate: Pengguna sudah memiliki penugasan aktif di insiden ini.");
                }

                $itemData['ditugaskan_oleh'] = Auth::id() ?: 1;
                $penugasan = $this->service->createPenugasan($itemData);

                DB::commit();

                $successes[] = [
                    'index' => $index,
                    'uuid' => $penugasan->uuid_penugasan,
                    'message' => 'Penugasan berhasil dibuat',
                ];
            } catch (\Exception $e) {
                DB::rollBack();
                $failures[] = [
                    'index' => $index,
                    'error' => $e->getMessage(),
                ];
            }
        }

        return response()->json([
            'success' => count($failures) === 0,
            'message' => 'Proses bulk selesai',
            'data' => [
                'processed' => count($items),
                'success_count' => count($successes),
                'failed_count' => count($failures),
                'successes' => $successes,
                'failures' => $failures,
            ]
        ], count($failures) > 0 ? 207 : 200);
    }
}
