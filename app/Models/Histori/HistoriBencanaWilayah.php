<?php

namespace App\Models\Histori;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HistoriBencanaWilayah extends Model
{
    use HasFactory;

    protected $table = 'histori_bencana_wilayah';
    protected $primaryKey = 'id_histori';
    const CREATED_AT = 'dibuat_pada';
    const UPDATED_AT = 'diperbarui_pada';

    protected $guarded = ['id_histori'];

    protected $casts = [
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
        'is_terverifikasi' => 'boolean',
        'kerugian_estimasi_juta' => 'decimal:2',
    ];

    public function kabupaten()
    {
        return $this->belongsTo(\App\Models\WilayahKabupaten::class, 'id_kab', 'id_kab');
    }

    public function jenisBencana()
    {
        return $this->belongsTo(\App\Models\BencanaMasterJenis::class, 'id_jenis_bencana', 'id_jenis');
    }

    public function insidenNurisk()
    {
        return $this->belongsTo(\App\Models\Operasi\OperasiInsiden::class, 'id_insiden_nurisk', 'id_insiden');
    }

    public function scopeTerverifikasi($query)
    {
        return $query->where('is_terverifikasi', 1);
    }

    public function scopeByKabupaten($query, $id)
    {
        return $query->where('id_kab', $id);
    }

    public function scopeByJenis($query, $id)
    {
        return $query->where('id_jenis_bencana', $id);
    }

    public function scopeByTahun($query, $tahun)
    {
        return $query->where('tahun', $tahun);
    }
}
