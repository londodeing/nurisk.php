<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class OrganisasiPcnu extends Model
{
    use HasFactory;

    protected $table = 'organisasi_pcnu';
    protected $primaryKey = 'id_pcnu';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'id_unit',
        'nama_pcnu',
    ];

    /**
     * Relasi ke model OrganisasiUnit
     */
    public function unit(): BelongsTo
    {
        return $this->belongsTo(OrganisasiUnit::class, 'id_unit', 'id_unit');
    }

    /**
     * Relasi ke model OrganisasiMwc
     */
    public function mwc(): HasMany
    {
        return $this->hasMany(OrganisasiMwc::class, 'id_pcnu', 'id_pcnu');
    }

    /**
     * Insiden yang tercatat di PCNU ini
     */
    public function insiden(): HasMany
    {
        return $this->hasMany(OperasiInsiden::class, 'id_pcnu', 'id_pcnu');
    }

    /**
     * Pengguna dengan scope default PCNU ini
     */
    public function pengguna(): HasMany
    {
        return $this->hasMany(AuthUser::class, 'default_scope_id', 'id_pcnu')
            ->where('default_scope_type', 'pcnu');
    }

    /**
     * Semua ranting di bawah PCNU (via MWC)
     */
    public function ranting(): HasManyThrough
    {
        return $this->hasManyThrough(
            OrganisasiRanting::class,
            OrganisasiMwc::class,
            'id_pcnu',
            'id_mwc',
            'id_pcnu',
            'id_mwc'
        );
    }
}
