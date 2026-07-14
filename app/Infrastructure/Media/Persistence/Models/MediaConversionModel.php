<?php

declare(strict_types=1);

namespace App\Infrastructure\Media\Persistence\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Eloquent model for the media_conversions table.
 *
 * Pure data layer — zero domain logic.
 * Mapped to/from domain MediaConversion entity via MediaMapper.
 */
final class MediaConversionModel extends Model
{
    protected $table = 'media_conversions';

    protected $guarded = ['id'];

    protected $casts = [
        'size_bytes' => 'integer',
        'width' => 'integer',
        'height' => 'integer',
        'created_at' => 'datetime',
    ];

    /**
     * The parent media record.
     *
     * @return BelongsTo<MediaModel>
     */
    public function media(): BelongsTo
    {
        return $this->belongsTo(MediaModel::class, 'media_id');
    }
}
