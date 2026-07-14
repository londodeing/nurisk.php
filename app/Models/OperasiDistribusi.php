<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class OperasiDistribusi extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'operasi_distribusi';
    protected $primaryKey = 'id_distribusi';

    const CREATED_AT = 'dibuat_pada';
    const UPDATED_AT = 'diperbarui_pada';
    const DELETED_AT = 'dihapus_pada';

    protected $fillable = [
        'uuid_distribusi',
        'id_posaju',
        'id_klaster_operasi',
        'id_penugasan',
        'id_barang_katalog',
        'nama_barang',
        'jumlah',
        'satuan',
        'lokasi_tujuan',
        'penerima',
        'waktu_distribusi',
        'status_distribusi',
        'dibuat_oleh',
    ];

    protected $casts = [
        'waktu_distribusi' => 'datetime',
        'jumlah' => 'decimal:2',
    ];

    public function posaju(): BelongsTo
    {
        return $this->belongsTo(OperasiPosaju::class, 'id_posaju', 'id_posaju');
    }

    public function klasterOperasi(): BelongsTo
    {
        return $this->belongsTo(OperasiKlaster::class, 'id_klaster_operasi', 'id_klaster_operasi');
    }

    public function penugasan(): BelongsTo
    {
        return $this->belongsTo(OperasiPenugasan::class, 'id_penugasan', 'id_penugasan');
    }

    public function barangKatalog(): BelongsTo
    {
        return $this->belongsTo(LogistikBarangKatalog::class, 'id_barang_katalog', 'id_katalog');
    }

    public function pembuat(): BelongsTo
    {
        return $this->belongsTo(AuthUser::class, 'dibuat_oleh', 'id_pengguna');
    }

    public function feedback(): HasOne
    {
        return $this->hasOne(OperasiFeedbackDistribusi::class, 'id_distribusi', 'id_distribusi');
    }

    public function scopeAktif($query)
    {
        return $query->whereIn('status_distribusi', ['direncanakan', 'didistribusikan']);
    }

    public function isFinal(): bool
    {
        return $this->status_distribusi === 'direview';
    }

    public function labelStatus(): string
    {
        return match ($this->status_distribusi) {
            'direncanakan'      => 'Direncanakan',
            'didistribusikan'   => 'Didistribusikan',
            'diterima'          => 'Diterima',
            'direview'          => 'Direview',
            default             => ucfirst(str_replace('_', ' ', $this->status_distribusi)),
        };
    }
}