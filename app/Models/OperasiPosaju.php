<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use App\Models\LogistikPermintaan;
use App\Models\OperasiJurnal;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class OperasiPosaju extends Model
{
    use SoftDeletes, HasFactory;

    protected $table = 'operasi_posaju';
    protected $primaryKey = 'id_posaju';
    
    const CREATED_AT = 'dibuat_pada';
    const UPDATED_AT = null;
    const DELETED_AT = 'dihapus_pada';

    // protected $with = ["insiden"]; // removed — eager-load explicitly where needed
    protected $fillable = [
        'id_insiden',
        'id_pleno_pendirian',
        'id_pleno_keputusan',
        'nama_posaju',
        'id_surat_pendirian',
        'alamat_lokasi',
        'latitude',
        'longitude',
        'pj_posaju',
        'status_alur',
        'waktu_diaktifkan',
        'diperpanjang_hingga',
        'waktu_ditutup',
        'alasan_penutupan',
        'alasan_perpanjangan',
    ];

    protected $casts = [
        'latitude' => 'float',
        'longitude' => 'float',
        'waktu_diaktifkan' => 'datetime',
        'diperpanjang_hingga' => 'datetime',
        'waktu_ditutup' => 'datetime',
    ];

    /**
     * Relasi ke Insiden
     */
    public function insiden(): BelongsTo
    {
        return $this->belongsTo(OperasiInsiden::class, 'id_insiden', 'id_insiden');
    }

    /**
     * Relasi ke Penanggung Jawab
     */
    public function pj(): BelongsTo
    {
        return $this->belongsTo(AuthUser::class, 'pj_posaju', 'id_pengguna');
    }

    /**
     * Relasi ke Surat Keluar
     */
    public function suratPendirian(): BelongsTo
    {
        return $this->belongsTo(OperasiSuratKeluar::class, 'id_surat_pendirian', 'id_surat');
    }

    /**
     * Kebutuhan Relawan
     */
    public function kebutuhanRelawan(): HasMany
    {
        return $this->hasMany(RelawanKebutuhan::class, 'id_posaju', 'id_posaju');
    }

    /**
     * Stok logistik di pos aju ini
     */
    public function stok(): HasMany
    {
        return $this->hasMany(LogistikStok::class, 'id_posaju', 'id_posaju');
    }

    /**
     * Personel yang ditugaskan ke pos aju ini
     */
    public function personel(): HasMany
    {
        return $this->hasMany(RelawanPenugasan::class, 'id_posaju', 'id_posaju');
    }

    /**
     * Distribusi bantuan dari pos aju ini
     */
    public function distribusi(): HasMany
    {
        return $this->hasMany(OperasiDistribusi::class, 'id_posaju', 'id_posaju');
    }

    /**
     * Permintaan logistik ke pos aju ini
     */
    public function permintaanLogistik(): HasMany
    {
        return $this->hasMany(LogistikPermintaan::class, 'id_posaju_tujuan', 'id_posaju');
    }

    /**
     * Penugasan personel ke pos aju ini
     */
    public function penugasan(): HasMany
    {
        return $this->hasMany(OperasiPenugasan::class, 'id_posaju', 'id_posaju');
    }

    /**
     * Riwayat komandan pos aju
     */
    public function komandan(): HasMany
    {
        return $this->hasMany(OperasiPosajuKomandan::class, 'id_posaju', 'id_posaju');
    }

    /**
     * Komandan yang sedang aktif (belum selesai tugas)
     */
    public function komandanAktif(): HasOne
    {
        return $this->hasOne(OperasiPosajuKomandan::class, 'id_posaju', 'id_posaju')
            ->whereNull('waktu_selesai_tugas');
    }

    public function keputusanPleno(): BelongsTo
    {
        return $this->belongsTo(OperasiPlenoKeputusan::class, 'id_pleno_keputusan', 'id_keputusan');
    }

    // --- Helpers ---

    public function isAktif(): bool
    {
        return $this->status_alur === 'aktif';
    }

    public function labelStatus(): string
    {
        return match ($this->status_alur) {
            'direncanakan' => 'Direncanakan',
            'aktif'        => 'Aktif',
            'ditutup'      => 'Ditutup',
            default        => ucfirst(str_replace('_', ' ', $this->status_alur ?? '')),
        };
    }

    public function scopeAktif($query)
    {
        return $query->where('status_alur', 'aktif');
    }

    /**
     * Jurnal terkait pos aju ini
     */
    public function jurnal(): HasMany
    {
        return $this->hasMany(OperasiJurnal::class, 'id_referensi', 'id_posaju')
            ->where('tabel_referensi', 'operasi_posaju');
    }
}
