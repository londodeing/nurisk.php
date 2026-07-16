<?php

namespace App\Http\Controllers\Api\Operasi;

use App\Http\Controllers\Controller;
use App\Models\AssessmentUtama;
use App\Models\MobileDevice;
use App\Models\MobileSyncQueue;
use App\Models\OperasiInsiden;
use App\Models\OperasiKlaster;
use App\Models\OperasiMobilisasi;
use App\Models\OperasiPenugasan;
use App\Models\OperasiSitrep;
use App\Models\SyncAuditLog;
use App\Models\SyncConflict;
use App\Models\SyncCursor;
use App\Models\SyncTombstone;
use App\Services\Auth\AuthorizationContextService;
use App\Http\Resources\Operasi\AssessmentResource;
use App\Http\Resources\Operasi\KlasterResource;
use App\Http\Resources\Operasi\MobilisasiResource;
use App\Http\Resources\Operasi\PenugasanResource;
use App\Http\Resources\Operasi\SitrepResource;
use App\Services\Sync\SnapshotStorageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SyncApiController extends Controller
{
    public function __construct(
        private readonly AuthorizationContextService $authCtx
    ) {}

    public function sync(Request $request): JsonResponse
    {
        if (!config('features.sync_engine_enabled')) {
            return response()->json([
                'error'   => 'SYNC_DISABLED',
                'message' => 'Fitur sinkronisasi offline belum aktif di versi ini. Tersedia di v2.0.',
            ], 503);
        }
        $request->validate([
            'request_id' => 'required|uuid',
            'device_uuid' => 'required|string',
            'cursors' => 'present|array',
            'changes' => 'present|array',
        ]);

        $requestId = $request->input('request_id');
        $deviceUuid = $request->input('device_uuid');
        $clientCursors = $request->input('cursors');
        $clientChanges = $request->input('changes');

        // 1. Resolve Device
        $device = MobileDevice::firstOrCreate(
            ['uuid_device' => $deviceUuid],
            [
                'id_pengguna' => Auth::id() ?? 1,
                'platform' => $request->header('User-Agent', 'unknown'),
                'app_version' => '1.0.0',
                'status' => 'active',
                'trust_score' => 100,
            ]
        );

        if ($device->status !== 'active') {
            return response()->json([
                'success' => false,
                'message' => 'Forbidden: Device has been ' . $device->status,
            ], 403);
        }

        // 2. Idempotency Check
        $queue = MobileSyncQueue::where('request_id', $requestId)->first();
        if ($queue) {
            if ($queue->status === 'processed') {
                return response()->json($queue->response, 200);
            }
            if ($queue->status === 'pending') {
                return response()->json(['success' => false, 'message' => 'Sync request is already being processed.'], 409);
            }
        }

        $queue = MobileSyncQueue::create([
            'request_id' => $requestId,
            'device_uuid' => $deviceUuid,
            'scope_type' => $this->authCtx->getScopeType(),
            'scope_id' => $this->authCtx->getScopeId(),
            'status' => 'pending',
        ]);

        $startTime = microtime(true);
        $conflictsResponse = [];
        $entitiesSynced = 0;

        // 3. Process Changes Atomically
        try {
            DB::beginTransaction();

            foreach ($clientChanges as $change) {
                $table = $change['table'];
                $action = $change['action'];
                $data = $change['data'];

                [$modelClass, $uuidColumn] = match ($table) {
                    'assessment_utama', 'assessment' => [AssessmentUtama::class, 'uuid_assessment'],
                    'operasi_sitrep', 'sitrep' => [OperasiSitrep::class, 'uuid_sitrep'],
                    'operasi_klaster', 'klaster' => [OperasiKlaster::class, 'uuid_klaster_operasi'],
                    'operasi_penugasan', 'penugasan' => [OperasiPenugasan::class, 'uuid_penugasan'],
                    'operasi_mobilisasi', 'mobilisasi' => [OperasiMobilisasi::class, 'uuid_mobilisasi'],
                    default => [null, null],
                };

                if (!$modelClass) {
                    continue;
                }

                $uuidVal = $data[$uuidColumn] ?? null;
                if (!$uuidVal) {
                    continue;
                }

                // --- COMPREHENSIVE SCOPE VALIDATION (Task 13.2) ---
                $insiden = null;
                
                // Validate id_insiden if provided
                if (isset($data['id_insiden'])) {
                    $insiden = OperasiInsiden::find($data['id_insiden']);
                    if (!$insiden || !$this->authCtx->canAccessInsiden($insiden->id_pcnu)) {
                        DB::rollBack();
                        return response()->json([
                            'success' => false,
                            'message' => 'Forbidden: you do not have access to this incident',
                        ], 403);
                    }
                }

                // Validate uuid_insiden if provided
                if (isset($data['uuid_insiden'])) {
                    $insiden = OperasiInsiden::where('uuid_insiden', $data['uuid_insiden'])->first();
                    if (!$insiden || !$this->authCtx->canAccessInsiden($insiden->id_pcnu)) {
                        DB::rollBack();
                        return response()->json([
                            'success' => false,
                            'message' => 'Forbidden: you do not have access to this incident',
                        ], 403);
                    }
                    $data['id_insiden'] = $insiden->id_insiden;
                    unset($data['uuid_insiden']);
                }

                // Validate id_pcnu if provided (for entities that don't have insiden)
                if (isset($data['id_pcnu']) && !$insiden) {
                    if (!$this->authCtx->canAccessInsiden($data['id_pcnu'])) {
                        DB::rollBack();
                        return response()->json([
                            'success' => false,
                            'message' => 'Forbidden: you do not have access to this PCNU',
                        ], 403);
                    }
                }

                // For delete action, also verify the existing record is in scope
                if ($action === 'delete') {
                    $record = $modelClass::where($uuidColumn, $uuidVal)->first();
                    if ($record) {
                        $recordScopeValid = $this->validateRecordScope($record, $table, $insiden);
                        if (!$recordScopeValid) {
                            DB::rollBack();
                            return response()->json([
                                'success' => false,
                                'message' => 'Forbidden: you do not have access to this record',
                            ], 403);
                        }
                    }
                }

                if ($action === 'upsert') {
                    $record = $modelClass::where($uuidColumn, $uuidVal)->first();

                    if ($record) {
                        // Validate existing record's scope (prevents cross-tenant overwrite)
                        $recordScopeValid = $this->validateRecordScope($record, $table, $insiden);
                        if (!$recordScopeValid) {
                            DB::rollBack();
                            return response()->json([
                                'success' => false,
                                'message' => 'Forbidden: you do not have access to this record',
                            ], 403);
                        }

                        // --- LAST-WRITE-WINS (Task 13.7) ---
                        $clientVersion = (int) ($data['sync_version'] ?? 1);
                        $serverVersion = (int) ($record->sync_version ?? 1);

                        if ($clientVersion < $serverVersion) {
                            // Log conflict for audit, but APPLY the update (last-write-wins)
                            $conflict = SyncConflict::create([
                                'device_uuid' => $deviceUuid,
                                'entity_type' => $table,
                                'uuid_entity' => $uuidVal,
                                'id_pcnu' => $insiden->id_pcnu ?? null,
                                'scope_type' => $this->authCtx->getScopeType(),
                                'scope_id' => $this->authCtx->getScopeId(),
                                'client_version' => $clientVersion,
                                'server_version' => $serverVersion,
                                'client_data' => $data,
                                'server_data' => $record->toArray(),
                            ]);

                            $conflictsResponse[] = [
                                'entity_type' => $table,
                                'uuid_entity' => $uuidVal,
                                'server_version' => $serverVersion,
                                'client_version' => $clientVersion,
                                'message' => 'Conflict recorded; server accepted client change (last-write-wins)',
                                'conflict_id' => $conflict->id,
                            ];
                        }

                        // Always apply — last-write-wins
                        $data['sync_version'] = max($serverVersion, $clientVersion) + 1;
                        $record->update($data);
                        $entitiesSynced++;
                    } else {
                        $data['sync_version'] = 1;
                        $data['scope_type'] = $this->authCtx->getScopeType();
                        $data['scope_id'] = $this->authCtx->getScopeId();
                        $modelClass::create($data);
                        $entitiesSynced++;
                    }
                } elseif ($action === 'delete') {
                    $record = $modelClass::where($uuidColumn, $uuidVal)->first();
                    if ($record) {
                        $record->alasan_hapus = $change['alasan_hapus'] ?? 'deleted via sync api';
                        $record->deleted_by = Auth::id() ?? 1;
                        $record->save();
                        $record->delete();
                        $entitiesSynced++;
                    }
                }
            }

            $device->last_sync_at = now();
            $device->save();

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            $queue->update(['status' => 'failed', 'processed_at' => now()]);
            return response()->json([
                'success' => false,
                'message' => 'Sync failed: ' . $e->getMessage(),
            ], 500);
        }

        // 4. Fetch Updates & Tombstones — SCOPE FILTERED
        $supportedEntities = ['assessment', 'sitrep', 'klaster', 'penugasan', 'mobilisasi'];
        $scopeFilter = $this->authCtx->getSyncScopeFilter();
        $entityModelMap = [
            'assessment' => [AssessmentUtama::class, 'uuid_assessment', AssessmentResource::class],
            'sitrep' => [OperasiSitrep::class, 'uuid_sitrep', SitrepResource::class],
            'klaster' => [OperasiKlaster::class, 'uuid_klaster_operasi', KlasterResource::class],
            'penugasan' => [OperasiPenugasan::class, 'uuid_penugasan', PenugasanResource::class],
            'mobilisasi' => [OperasiMobilisasi::class, 'uuid_mobilisasi', MobilisasiResource::class],
        ];
        $serverChanges = [];
        $tombstonesResponse = [];
        $serverCursors = [];

        $applyScope = function ($q) use ($scopeFilter) {
            if ($scopeFilter !== null) {
                $q->where('scope_type', $scopeFilter['scope_type'])
                    ->where('scope_id', $scopeFilter['scope_id']);
            }
        };

        // Batch fetch all cursors grouped by entity type
        $allCursorsRaw = SyncCursor::whereIn('entity_type', $supportedEntities)
            ->orderBy('cursor_value', 'asc');
        $applyScope($allCursorsRaw);
        $allCursors = $allCursorsRaw->get()->groupBy('entity_type');

        foreach ($supportedEntities as $entityType) {
            $clientCursor = $clientCursors[$entityType] ?? 0;
            $cursorsNewer = collect($allCursors[$entityType] ?? [])
                ->where('cursor_value', '>', $clientCursor);

            // Collect UUIDs to batch-fetch
            $uuidToCursor = [];
            foreach ($cursorsNewer as $cur) {
                if ($cur->action !== 'delete') {
                    $uuidToCursor[$cur->uuid_entity] = $cur->cursor_value;
                }
            }

            if ($uuidToCursor !== []) {
                [$modelClass, $uuidColumn, $resourceClass] = $entityModelMap[$entityType];
                
                $eagerLoads = ['insiden'];
                if ($entityType === 'assessment') {
                    $eagerLoads = array_merge($eagerLoads, [
                        'dampakManusia', 'kebutuhanMendesak',
                        'dampakManusiaV2', 'kebutuhanNumerik', 'lokasiDetail', 'narasiDetail',
                        'dampakInfrastruktur', 'dampakRumah', 'dampakFasum', 'dampakVital',
                        'dampakLingkungan', 'dampakEkonomi', 'biodataKejadian', 'narasiKejadian'
                    ]);
                }

                $entities = $modelClass::with($eagerLoads)
                    ->whereIn($uuidColumn, array_keys($uuidToCursor))
                    ->get()
                    ->keyBy($uuidColumn);

                foreach ($uuidToCursor as $uuid => $cursor) {
                    $entity = $entities->get($uuid);
                    if ($entity) {
                        $serverChanges[] = [
                            'table' => $entityType,
                            'cursor' => $cursor,
                            'data' => (new $resourceClass($entity))->resolve($request),
                        ];
                    }
                }
            }
        }

        // Batch fetch all tombstones
        $allTombstonesRaw = SyncTombstone::whereIn('entity_type', $supportedEntities)
            ->orderBy('cursor_value', 'asc');
        $applyScope($allTombstonesRaw);
        $allTombstones = $allTombstonesRaw->get()->groupBy('entity_type');

        foreach ($supportedEntities as $entityType) {
            $clientCursor = $clientCursors[$entityType] ?? 0;
            $tombstonesNewer = collect($allTombstones[$entityType] ?? [])
                ->where('cursor_value', '>', $clientCursor);

            foreach ($tombstonesNewer as $tomb) {
                $tombstonesResponse[] = [
                    'entity_type' => $tomb->entity_type,
                    'uuid_entity' => $tomb->uuid_entity,
                    'deleted_at' => $tomb->deleted_at,
                    'cursor' => $tomb->cursor_value,
                ];
            }
        }

        // Compute max cursor values from already-fetched $allCursors
        foreach ($supportedEntities as $entityType) {
            $cursors = $allCursors->get($entityType, collect());
            $serverCursors[$entityType] = (int) ($cursors->max('cursor_value') ?? 0);
        }

        $durationMs = (int) ((microtime(true) - $startTime) * 1000);

        SyncAuditLog::create([
            'device_uuid' => $deviceUuid,
            'request_id' => $requestId,
            'entities_synced' => $entitiesSynced,
            'duration_ms' => $durationMs,
            'status' => 'success',
            'scope_type' => $this->authCtx->getScopeType(),
            'scope_id' => $this->authCtx->getScopeId(),
        ]);

        $responseData = [
            'success' => true,
            'message' => 'Sync completed',
            'data' => [
                'server_cursors' => $serverCursors,
                'changes' => $serverChanges,
                'tombstones' => $tombstonesResponse,
                'conflicts' => $conflictsResponse,
            ],
        ];

        $queue->update([
            'response' => $responseData,
            'status' => 'processed',
            'processed_at' => now(),
        ]);

        return response()->json($responseData, 200);
    }

    private function validateRecordScope($record, string $entityType, ?OperasiInsiden $cachedInsiden = null): bool
    {
        if ($this->authCtx->isSuperAdmin()) {
            return true;
        }

        $scopeFilter = $this->authCtx->getSyncScopeFilter();
        if ($scopeFilter === null) {
            return false;
        }

        // Check if record has id_pcnu (for insiden-related entities)
        if (isset($record->id_insiden)) {
            $insiden = $cachedInsiden ?? OperasiInsiden::find($record->id_insiden);
            if ($insiden) {
                return $this->authCtx->canAccessInsiden($insiden->id_pcnu);
            }
        }

        // Check if record has id_pcnu directly
        if (isset($record->id_pcnu)) {
            return $this->authCtx->canAccessInsiden($record->id_pcnu);
        }

        return false;
    }

    public function status(): JsonResponse
    {
        if (!config('features.sync_engine_enabled')) {
            return response()->json([
                'error'   => 'SYNC_DISABLED',
                'message' => 'Fitur sinkronisasi offline belum aktif di versi ini. Tersedia di v2.0.',
            ], 503);
        }
        $scopeFilter = $this->authCtx->getSyncScopeFilter();
        $supportedEntities = ['assessment', 'sitrep', 'klaster', 'penugasan', 'mobilisasi'];
        $serverCursors = [];

        foreach ($supportedEntities as $entityType) {
            $query = SyncCursor::where('entity_type', $entityType);
            if ($scopeFilter !== null) {
                $query->where('scope_type', $scopeFilter['scope_type'])
                    ->where('scope_id', $scopeFilter['scope_id']);
            }
            $serverCursors[$entityType] = (int) ($query->max('cursor_value') ?? 0);
        }

        $tombstonesQuery = SyncTombstone::query();
        if ($scopeFilter !== null) {
            $tombstonesQuery->where('scope_type', $scopeFilter['scope_type'])
                ->where('scope_id', $scopeFilter['scope_id']);
        }
        $pendingTombstones = $tombstonesQuery->count();

        $cursorsQuery = SyncCursor::where('action', '!=', 'delete');
        if ($scopeFilter !== null) {
            $cursorsQuery->where('scope_type', $scopeFilter['scope_type'])
                ->where('scope_id', $scopeFilter['scope_id']);
        }
        $pendingChanges = $cursorsQuery->count();

        return response()->json([
            'success' => true,
            'data' => [
                'server_time' => now()->toIso8601String(),
                'latest_cursors' => $serverCursors,
                'pending_tombstones' => $pendingTombstones,
                'pending_changes' => $pendingChanges,
            ],
        ]);
    }

    public function state(): JsonResponse
    {
        if (!config('features.sync_engine_enabled')) {
            return response()->json([
                'error'   => 'SYNC_DISABLED',
                'message' => 'Fitur sinkronisasi offline belum aktif di versi ini. Tersedia di v2.0.',
            ], 503);
        }
        $scopeFilter = $this->authCtx->getSyncScopeFilter();

        $cursorQuery = SyncCursor::query();
        $tombstoneQuery = SyncTombstone::query();

        if ($scopeFilter !== null) {
            $cursorQuery->where('scope_type', $scopeFilter['scope_type'])
                ->where('scope_id', $scopeFilter['scope_id']);
            $tombstoneQuery->where('scope_type', $scopeFilter['scope_type'])
                ->where('scope_id', $scopeFilter['scope_id']);
        }

        $lastCursor = $cursorQuery->max('cursor_value') ?? 0;
        $lastTombstone = $tombstoneQuery->max('cursor_value') ?? 0;

        $globalMax = max($lastCursor, $lastTombstone);

        return response()->json([
            'success' => true,
            'data' => [
                'server_time' => now()->toIso8601String(),
                'global_version' => $globalMax,
            ],
        ]);
    }

    public function bootstrap(Request $request): JsonResponse
    {
        if (!config('features.sync_engine_enabled')) {
            return response()->json([
                'error'   => 'SYNC_DISABLED',
                'message' => 'Fitur sinkronisasi offline belum aktif di versi ini. Tersedia di v2.0.',
            ], 503);
        }
        $startTime = microtime(true);
        $scopeFilter = $this->authCtx->getSyncScopeFilter();
        $supportedEntities = ['assessment', 'sitrep', 'klaster', 'penugasan', 'mobilisasi'];
        $entityModelMap = [
            'assessment' => [AssessmentUtama::class, 'uuid_assessment'],
            'sitrep' => [OperasiSitrep::class, 'uuid_sitrep'],
            'klaster' => [OperasiKlaster::class, 'uuid_klaster_operasi'],
            'penugasan' => [OperasiPenugasan::class, 'uuid_penugasan'],
            'mobilisasi' => [OperasiMobilisasi::class, 'uuid_mobilisasi'],
        ];
        $serverCursors = [];

        // Build cursors — scope filtered
        foreach ($supportedEntities as $entityType) {
            $query = SyncCursor::where('entity_type', $entityType);
            if ($scopeFilter !== null) {
                $query->where('scope_type', $scopeFilter['scope_type'])
                    ->where('scope_id', $scopeFilter['scope_id']);
            }
            $serverCursors[$entityType] = (int) ($query->max('cursor_value') ?? 0);
        }

        // Generate snapshot as static file
        $snapshotKey = 'snapshots/' . ($scopeFilter ? $scopeFilter['scope_type'] . '_' . $scopeFilter['scope_id'] : 'global') . '_' . time() . '.json';
        $storage = app(SnapshotStorageService::class);
        $snapshotData = [];

        foreach ($supportedEntities as $entityType) {
            [$modelClass, $uuidColumn] = $entityModelMap[$entityType];

            $eagerLoads = ['insiden'];
            if ($entityType === 'assessment') {
                $eagerLoads = array_merge($eagerLoads, [
                    'dampakManusia', 'kebutuhanMendesak',
                    'dampakManusiaV2', 'kebutuhanNumerik', 'lokasiDetail', 'narasiDetail',
                    'dampakInfrastruktur', 'dampakRumah', 'dampakFasum', 'dampakVital',
                    'dampakLingkungan', 'dampakEkonomi', 'biodataKejadian', 'narasiKejadian'
                ]);
            }

            $query = $modelClass::with($eagerLoads);
            if ($scopeFilter !== null && $entityType !== 'sitrep') {
                $query->whereHas('insiden', function ($q) use ($scopeFilter) {
                    if (isset($scopeFilter['scope_id'])) {
                        $q->where('id_pcnu', $scopeFilter['scope_id']);
                    }
                });
            }
            if ($scopeFilter !== null && $entityType === 'sitrep') {
                $query->whereHas('insiden', function ($q) use ($scopeFilter) {
                    if (isset($scopeFilter['scope_id'])) {
                        $q->where('id_pcnu', $scopeFilter['scope_id']);
                    }
                });
            }

            $snapshotData[$entityType] = $query->get()->toArray();
        }

        $storage->store($snapshotKey, json_encode($snapshotData));

        $durationMs = (int) ((microtime(true) - $startTime) * 1000);
        SyncAuditLog::create([
            'device_uuid' => $request->header('X-Device-UUID', 'bootstrap'),
            'request_id' => (string) \Illuminate\Support\Str::uuid(),
            'entities_synced' => array_sum(array_map('count', $snapshotData)),
            'duration_ms' => $durationMs,
            'status' => 'bootstrap',
            'scope_type' => $this->authCtx->getScopeType(),
            'scope_id' => $this->authCtx->getScopeId(),
        ]);

        return response()->json([
            'success' => true,
            'data' => [
                'server_time' => now()->toIso8601String(),
                'cursors' => $serverCursors,
                'snapshot_url' => $storage->temporaryUrl($snapshotKey),
            ],
        ]);
    }

    public function downloadSnapshot(Request $request): JsonResponse|\Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        if (!config('features.sync_engine_enabled')) {
            return response()->json([
                'error'   => 'SYNC_DISABLED',
                'message' => 'Fitur sinkronisasi offline belum aktif di versi ini. Tersedia di v2.0.',
            ], 503);
        }
        $path = $request->query('path');
        $expires = (int) $request->query('expires');
        $signature = $request->query('signature');

        $storage = app(SnapshotStorageService::class);

        if (!$storage->verify($path, $expires, $signature)) {
            return response()->json(['success' => false, 'message' => 'Invalid or expired link'], 403);
        }

        $content = $storage->get($path);
        if ($content === null) {
            return response()->json(['success' => false, 'message' => 'Snapshot not found'], 404);
        }

        return response()->json(json_decode($content));
    }

    public function metrics(): JsonResponse
    {
        if (!config('features.sync_engine_enabled')) {
            return response()->json([
                'error'   => 'SYNC_DISABLED',
                'message' => 'Fitur sinkronisasi offline belum aktif di versi ini. Tersedia di v2.0.',
            ], 503);
        }
        $scopeFilter = $this->authCtx->getSyncScopeFilter();

        $auditQuery = SyncAuditLog::query();
        $conflictQuery = SyncConflict::query();
        $deviceQuery = MobileDevice::query();

        if ($scopeFilter !== null) {
            $auditQuery->where('scope_type', $scopeFilter['scope_type'])
                ->where('scope_id', $scopeFilter['scope_id']);
            $conflictQuery->where('scope_type', $scopeFilter['scope_type'])
                ->where('scope_id', $scopeFilter['scope_id']);
        }

        $totalSync = $auditQuery->count();
        $successSync = (clone $auditQuery)->where('status', 'success')->count();
        $successRate = $totalSync > 0 ? round(($successSync / $totalSync) * 100, 2) . '%' : '0%';
        $averageDuration = (clone $auditQuery)->avg('duration_ms') ?? 0;
        $totalConflict = $conflictQuery->count();
        $totalDevice = $deviceQuery->count();

        $lastSync = (clone $auditQuery)->orderBy('dibuat_pada', 'desc')->first();

        return response()->json([
            'success' => true,
            'data' => [
                'total_sync' => $totalSync,
                'success_rate' => $successRate,
                'average_duration_ms' => round($averageDuration, 2),
                'total_conflict' => $totalConflict,
                'total_device' => $totalDevice,
                'last_sync_at' => $lastSync?->dibuat_pada?->toIso8601String(),
            ],
        ]);
    }
}
