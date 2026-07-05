<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class OrganisasiMwc extends Model
{
    use HasFactory;

    protected $table = 'organisasi_mwc';
    protected $primaryKey = 'id_mwc';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'id_pcnu',
        'nama_mwc',
        'id_unit',
    ];

    /**
     * Relasi ke model OrganisasiPcnu
     */
    public function pcnu(): BelongsTo
    {
        return $this->belongsTo(OrganisasiPcnu::class, 'id_pcnu', 'id_pcnu');
    }

    /**
     * Relasi ke model OrganisasiUnit
     */
    public function unit(): BelongsTo
    {
        return $this->belongsTo(OrganisasiUnit::class, 'id_unit', 'id_unit');
    }

    /**
     * Relasi ke model OrganisasiRanting
     */
    public function ranting(): HasMany
    {
        return $this->hasMany(OrganisasiRanting::class, 'id_mwc', 'id_mwc');
    }
}
