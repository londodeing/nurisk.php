<?php

namespace App\Traits;

use App\Models\OrgMandate;
use App\Models\OrgNode;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

/**
 * Trait HasGovernanceFields
 *
 * Enforces Enterprise Governance schema convention:
 * - UUID primary key
 * - created_by_mandate_id / updated_by_mandate_id
 * - node_id / territory_id
 * - soft deletes
 *
 * Semua model Governance Workflow WAJIB menggunakan trait ini.
 */
trait HasGovernanceFields
{
    use HasUuids, SoftDeletes;

    /**
     * Boot trait: auto-fill governance fields saat creating/updating.
     */
    public static function bootHasGovernanceFields(): void
    {
        static::creating(function ($model) {
            // Auto-generate UUID jika belum di-set
            if (empty($model->{$model->getKeyName()})) {
                $model->{$model->getKeyName()} = (string) Str::uuid();
            }

            // Auto-fill mandate context dari request jika tersedia
            $mandate = request()?->get('_mandate');
            if ($mandate instanceof OrgMandate) {
                if (empty($model->created_by_mandate_id)) {
                    $model->created_by_mandate_id = $mandate->id;
                }
                if (empty($model->node_id) && $mandate->nodePosition?->node_id) {
                    $model->node_id = $mandate->nodePosition->node_id;
                }
                if (empty($model->territory_id) && $mandate->nodePosition?->node?->territory_code) {
                    $model->territory_id = $mandate->nodePosition->node->territory_code;
                }
            }
        });

        static::updating(function ($model) {
            $mandate = request()?->get('_mandate');
            if ($mandate instanceof OrgMandate) {
                $model->updated_by_mandate_id = $mandate->id;
            }
        });
    }

    /**
     * UUID sebagai primary key.
     */
    public function getKeyType(): string
    {
        return 'string';
    }

    public function getIncrementing(): bool
    {
        return false;
    }

    // ===================================================================
    // RELASI GOVERNANCE
    // ===================================================================

    public function createdByMandate(): BelongsTo
    {
        return $this->belongsTo(OrgMandate::class, 'created_by_mandate_id');
    }

    public function updatedByMandate(): BelongsTo
    {
        return $this->belongsTo(OrgMandate::class, 'updated_by_mandate_id');
    }

    public function node(): BelongsTo
    {
        return $this->belongsTo(OrgNode::class, 'node_id');
    }

    // ===================================================================
    // SCOPES
    // ===================================================================

    /**
     * Filter berdasarkan territory.
     */
    public function scopeForTerritory($query, string $territoryCode)
    {
        return $query->where('territory_id', $territoryCode);
    }

    /**
     * Filter berdasarkan node.
     */
    public function scopeForNode($query, int $nodeId)
    {
        return $query->where('node_id', $nodeId);
    }

    /**
     * Filter berdasarkan mandate yang membuat.
     */
    public function scopeCreatedByMandate($query, int $mandateId)
    {
        return $query->where('created_by_mandate_id', $mandateId);
    }
}
