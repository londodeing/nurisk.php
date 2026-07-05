<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Media extends Model
{
    use SoftDeletes;

    protected $guarded = ['id'];

    protected $casts = [
        'metadata' => 'array',
        'size_bytes' => 'integer',
        'width' => 'integer',
        'height' => 'integer',
        'version' => 'integer',
        'is_active' => 'boolean',
    ];

    public const MEDIA_TYPE_IMAGE = 'IMAGE';
    public const MEDIA_TYPE_DOCUMENT = 'DOCUMENT';
    public const MEDIA_TYPE_VIDEO = 'VIDEO';
    public const MEDIA_TYPE_AUDIO = 'AUDIO';

    public const ACCESS_PUBLIC = 'PUBLIC';
    public const ACCESS_INTERNAL = 'INTERNAL';
    public const ACCESS_CONFIDENTIAL = 'CONFIDENTIAL';
    public const ACCESS_SECRET = 'SECRET';

    public function entity()
    {
        return $this->morphTo();
    }

    public function uploader()
    {
        return $this->belongsTo(AuthUser::class, 'uploaded_by');
    }

    public function url(): ?string
    {
        if ($this->trashed()) {
            return null;
        }
        return Storage::disk($this->disk)->url($this->path);
    }

    public function exists(): bool
    {
        return !$this->trashed() && Storage::disk($this->disk)->exists($this->path);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByEntity($query, string $entityType, int $entityId)
    {
        return $query->where('entity_type', $entityType)->where('entity_id', $entityId);
    }

    public function scopeByAccess($query, string $level)
    {
        return $query->where('access_level', $level);
    }

    public function scopePublic($query)
    {
        return $query->where('access_level', self::ACCESS_PUBLIC);
    }

    public function scopeMediaType($query, string $type)
    {
        return $query->where('media_type', $type);
    }

    public function isImage(): bool
    {
        return $this->media_type === self::MEDIA_TYPE_IMAGE;
    }

    public function isDocument(): bool
    {
        return $this->media_type === self::MEDIA_TYPE_DOCUMENT;
    }

    public function isVideo(): bool
    {
        return $this->media_type === self::MEDIA_TYPE_VIDEO;
    }

    public function isAudio(): bool
    {
        return $this->media_type === self::MEDIA_TYPE_AUDIO;
    }
}
