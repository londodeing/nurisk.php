<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Hash;

class AuthPenggunaPin extends Model
{
    protected $table = 'auth_pengguna_pins';
    protected $primaryKey = 'id_pengguna';
    public $incrementing = false;
    protected $keyType = 'int';
    public $timestamps = false;

    const CREATED_AT = 'dibuat_pada';
    const UPDATED_AT = 'diperbarui_pada';

    protected $fillable = [
        'id_pengguna',
        'pin_hash',
    ];

    protected $hidden = [
        'pin_hash',
    ];

    public function pengguna(): BelongsTo
    {
        return $this->belongsTo(AuthUser::class, 'id_pengguna', 'id_pengguna');
    }

    public static function setPin(int $idPengguna, string $pin): static
    {
        return static::updateOrCreate(
            ['id_pengguna' => $idPengguna],
            ['pin_hash' => Hash::make($pin)]
        );
    }

    public function verify(string $pin): bool
    {
        return Hash::check($pin, $this->pin_hash);
    }
}
