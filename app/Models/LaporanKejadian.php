<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;
use App\Traits\Scopes\ScopedByPcnu;

class LaporanKejadian extends Model
{
    use HasFactory, ScopedByPcnu;

    protected $table = 'laporan_kejadian';
    protected $primaryKey = 'id_laporan_kejadian';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = true;
    const CREATED_AT = 'dibuat_pada';
    const UPDATED_AT = 'diperbarui_pada';

    protected $fillable = [
        'kode_kejadian',
        'id_pengguna',
        'id_jenis_bencana',
        'nama_pelapor',
        'hp_pelapor',
        'keterangan_situasi',
        'titik_kenal',
        'waktu_kejadian',
        'latitude',
        'longitude',
        'alamat_lengkap',
        'photo_path',
        'is_valid',
        'alasan_tolak',
        'catatan_validasi',
        'id_pcnu',
        'id_petugas_trc',
        'id_kab',
        'id_kec',
        'id_desa',
    ];

    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'waktu_kejadian' => 'datetime',
        'dibuat_pada' => 'datetime',
        'diperbarui_pada' => 'datetime',
    ];

    public function jenisBencana(): BelongsTo
    {
        return $this->belongsTo(BencanaMasterJenis::class, 'id_jenis_bencana', 'id_jenis');
    }

    public function pengguna(): BelongsTo
    {
        return $this->belongsTo(AuthUser::class, 'id_pengguna', 'id_pengguna');
    }

    public function insiden(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(OperasiInsiden::class, 'id_laporan_asal', 'id_laporan_kejadian');
    }

    public function pcnu(): BelongsTo
    {
        return $this->belongsTo(OrganisasiPcnu::class, 'id_pcnu', 'id_pcnu');
    }

    public function petugasTrc(): BelongsTo
    {
        return $this->belongsTo(AuthUser::class, 'id_petugas_trc', 'id_pengguna');
    }

    public function kabupaten(): BelongsTo
    {
        return $this->belongsTo(WilayahKabupaten::class, 'id_kab', 'id_kab');
    }

    public function kecamatan(): BelongsTo
    {
        return $this->belongsTo(WilayahKecamatan::class, 'id_kec', 'id_kec');
    }

    public function desa(): BelongsTo
    {
        return $this->belongsTo(WilayahDesa::class, 'id_desa', 'id_desa');
    }

    public function scopeValid($query)
    {
        return $query->where('is_valid', 'ya');
    }

    public function scopeMenunggu($query)
    {
        return $query->where('is_valid', 'menunggu');
    }

    public static function generateKodeKejadian($idPcnu = null): string
    {
        $prefixStr = 'LAP';

        if ($idPcnu) {
            $pcnu = \App\Models\OrganisasiPcnu::find($idPcnu);
            if ($pcnu && $pcnu->kode_sni) {
                $prefixStr = $pcnu->kode_sni;
            }
        }

        $prefix = $prefixStr . '-' . now()->format('ymd');

        return DB::transaction(function () use ($prefix) {
            $last = self::where('kode_kejadian', 'like', $prefix . '-%')
                ->lockForUpdate()
                ->orderBy('kode_kejadian', 'desc')
                ->value('kode_kejadian');

            $next = 1;
            if ($last) {
                $parts = explode('-', $last);
                $next = (int) end($parts) + 1;
            }

            return $prefix . '-' . str_pad($next, 3, '0', STR_PAD_LEFT);
        });
    }
}
