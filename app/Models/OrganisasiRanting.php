<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class OrganisasiRanting extends Model
{
    use HasFactory;

    protected $table = 'organisasi_ranting';
    protected $primaryKey = 'id_ranting';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'id_mwc',
        'nama_ranting',
        'id_unit',
    ];

    /**
     * Relasi ke model OrganisasiMwc
     */
    public function mwc(): BelongsTo
    {
        return $this->belongsTo(OrganisasiMwc::class, 'id_mwc', 'id_mwc');
    }

    /**
     * Relasi ke model OrganisasiUnit
     */
    public function unit(): BelongsTo
    {
        return $this->belongsTo(OrganisasiUnit::class, 'id_unit', 'id_unit');
    }
}
