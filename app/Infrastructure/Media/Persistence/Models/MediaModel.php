<?php

declare(strict_types=1);

namespace App\Infrastructure\Media\Persistence\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Eloquent model for the media table.
 *
 * Pure data layer — zero domain logic.
 * Mapped to/from Domain aggregate via MediaMapper.
 */
final class MediaModel extends Model
{
    use SoftDeletes;

    protected $table = 'media';

    protected $guarded = ['id'];

    protected static function boot()
    {
        parent::boot();

        static::deleted(function (self $model) {
            if ($model->isForceDeleting()) {
                return;
            }
            \App\Jobs\Media\CleanSoftDeletedConversionsJob::dispatch($model->id);
        });
    }

    protected $casts = [
        'metadata' => 'array',
        'hash_sha256' => 'string',
        'size_bytes' => 'integer',
        'width' => 'integer',
        'height' => 'integer',
        'version' => 'integer',
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Associated media conversions.
     *
     * @return HasMany<MediaConversionModel>
     */
    public function conversions(): HasMany
    {
        return $this->hasMany(MediaConversionModel::class, 'media_id');
    }
}
