<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class MobileDevice extends Model
{
    use HasFactory;

    protected $table = 'mobile_devices';
    protected $primaryKey = 'id_device';

    const CREATED_AT = 'dibuat_pada';
    const UPDATED_AT = 'diperbarui_pada';

    protected $fillable = [
        'uuid_device',
        'id_pengguna',
        'platform',
        'app_version',
        'last_sync_at',
        'push_token',
        'status',
        'trust_score',
        'device_token',
        'token_expires_at',
    ];

    protected $casts = [
        'last_sync_at' => 'datetime',
        'dibuat_pada' => 'datetime',
        'diperbarui_pada' => 'datetime',
        'token_expires_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->uuid_device)) {
                $model->uuid_device = (string) Str::uuid();
            }
        });
    }

    public function pengguna(): BelongsTo
    {
        return $this->belongsTo(AuthUser::class, 'id_pengguna', 'id_pengguna');
    }
}
