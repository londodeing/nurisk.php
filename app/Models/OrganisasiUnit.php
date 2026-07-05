<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class OrganisasiUnit extends Model
{
    use HasFactory;

    protected $table = 'organisasi_unit';
    protected $primaryKey = 'id_unit';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'parent_id',
        'nama_unit',
        'tipe_unit',
        'id_wilayah',
    ];

    /**
     * Relasi ke parent unit (self reference)
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id', 'id_unit');
    }

    /**
     * Relasi ke children unit (self reference)
     */
    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id', 'id_unit');
    }

    /**
     * Relasi HasOne ke OrganisasiPcnu
     */
    public function pcnu(): HasOne
    {
        return $this->hasOne(OrganisasiPcnu::class, 'id_unit', 'id_unit');
    }

    /**
     * Relasi HasOne ke OrganisasiMwc
     */
    public function mwc(): HasOne
    {
        return $this->hasOne(OrganisasiMwc::class, 'id_unit', 'id_unit');
    }

    /**
     * Relasi HasOne ke OrganisasiRanting
     */
    public function ranting(): HasOne
    {
        return $this->hasOne(OrganisasiRanting::class, 'id_unit', 'id_unit');
    }
}
