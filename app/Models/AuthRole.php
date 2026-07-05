<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AuthRole extends Model
{
    use HasFactory;

    protected $table = 'auth_roles';
    protected $primaryKey = 'id_peran';
    public $timestamps = false;

    protected $fillable = [
        'nama_peran',
        'deskripsi',
        'level_otoritas',
    ];

    /**
     * Relasi ke model AuthUser
     */
    public function pengguna(): HasMany
    {
        return $this->hasMany(AuthUser::class, 'id_peran', 'id_peran');
    }
}
