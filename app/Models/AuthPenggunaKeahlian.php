<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class AuthPenggunaKeahlian extends Pivot
{
    protected $table = 'auth_pengguna_keahlian';
    protected $primaryKey = null;
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'id_pengguna',
        'id_keahlian',
    ];

    public function pengguna(): BelongsTo
    {
        return $this->belongsTo(AuthUser::class, 'id_pengguna', 'id_pengguna');
    }

    public function keahlian(): BelongsTo
    {
        return $this->belongsTo(AuthKeahlianMaster::class, 'id_keahlian', 'id_keahlian');
    }
}
