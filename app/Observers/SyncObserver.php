<?php

namespace App\Observers;

use App\Models\OperasiInsiden;
use App\Models\SyncCursor;
use App\Models\SyncTombstone;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class SyncObserver
{
    private static array $insidenCache = [];

    protected function getUuid(Model $model): string
    {
        if (isset($model->uuid_assessment)) return $model->uuid_assessment;
        if (isset($model->uuid_sitrep)) return $model->uuid_sitrep;
        if (isset($model->uuid_klaster_operasi)) return $model->uuid_klaster_operasi;
        if (isset($model->uuid_penugasan)) return $model->uuid_penugasan;
        if (isset($model->uuid_mobilisasi)) return $model->uuid_mobilisasi;
        return '';
    }

    protected function getEntityType(Model $model): string
    {
        return match (get_class($model)) {
            \App\Models\AssessmentUtama::class => 'assessment',
            \App\Models\OperasiSitrep::class => 'sitrep',
            \App\Models\OperasiKlaster::class => 'klaster',
            \App\Models\OperasiPenugasan::class => 'penugasan',
            \App\Models\OperasiMobilisasi::class => 'mobilisasi',
            default => strtolower(class_basename($model)),
        };
    }

    protected function getPcnuId(Model $model): ?int
    {
        if (isset($model->id_insiden)) {
            $insidenId = $model->id_insiden;
            if (!isset(self::$insidenCache[$insidenId])) {
                self::$insidenCache[$insidenId] = OperasiInsiden::find($insidenId);
            }
            return self::$insidenCache[$insidenId]?->id_pcnu;
        }
        if (isset($model->id_pcnu)) {
            return $model->id_pcnu;
        }
        return null;
    }

    protected function logCursor(Model $model, string $action): int
    {
        $uuid = $this->getUuid($model);
        if (empty($uuid)) {
            return 0;
        }

        $pcnuId = $this->getPcnuId($model);

        $log = SyncCursor::create([
            'entity_type' => $this->getEntityType($model),
            'uuid_entity' => $uuid,
            'cursor_value' => 0,
            'action' => $action,
            'id_pcnu' => $pcnuId,
            'scope_type' => $pcnuId ? 'pcnu' : null,
            'scope_id' => $pcnuId,
        ]);

        $log->cursor_value = $log->id_cursor;
        $log->save();

        return $log->cursor_value;
    }

    public function created(Model $model): void
    {
        if (!config('features.sync_engine_enabled')) return;
        $this->logCursor($model, 'create');
    }

    public function updated(Model $model): void
    {
        if (!config('features.sync_engine_enabled')) return;
        // Don't log if it's a soft-delete (this is handled in deleting/deleted)
        if ($model->isDirty('dihapus_pada') && $model->dihapus_pada !== null) {
            return;
        }
        $this->logCursor($model, 'update');
    }

    public function restored(Model $model): void
    {
        if (!config('features.sync_engine_enabled')) return;
        $this->logCursor($model, 'update');

        $uuid = $this->getUuid($model);
        if (!empty($uuid)) {
            SyncTombstone::where('entity_type', $this->getEntityType($model))
                         ->where('uuid_entity', $uuid)
                         ->delete();
        }
    }

    public function deleted(Model $model): void
    {
        if (!config('features.sync_engine_enabled')) return;
        $this->processDeletion($model);
    }

    public function forceDeleted(Model $model): void
    {
        if (!config('features.sync_engine_enabled')) return;
        $this->processDeletion($model);
    }

    protected function processDeletion(Model $model): void
    {
        $uuid = $this->getUuid($model);
        if (empty($uuid)) {
            return;
        }

        $cursorValue = $this->logCursor($model, 'delete');
        $pcnuId = $this->getPcnuId($model);

        SyncTombstone::updateOrCreate(
            [
                'entity_type' => $this->getEntityType($model),
                'uuid_entity' => $uuid,
            ],
            [
                'deleted_at' => now(),
                'deleted_by' => $model->deleted_by ?? Auth::id(),
                'alasan_hapus' => $model->alasan_hapus ?? 'deleted via api/system',
                'cursor_value' => $cursorValue,
                'id_pcnu' => $pcnuId,
                'scope_type' => $pcnuId ? 'pcnu' : null,
                'scope_id' => $pcnuId,
            ]
        );
    }
}
