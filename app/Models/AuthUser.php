<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Laravel\Sanctum\HasApiTokens;
use App\Traits\Scopes\ScopedAuthUser;

class AuthUser extends Authenticatable
{
    use HasApiTokens, Notifiable, HasFactory, ScopedAuthUser;

    protected $table = 'auth_users';
    protected $primaryKey = 'id_pengguna';

    const CREATED_AT = 'dibuat_pada';
    const UPDATED_AT = 'diperbarui_pada';

    // Human Status Machine — SESUAI SQL v37 (enum Indonesia)
    const STATUS_MENUNGGU  = 'menunggu';
    const STATUS_AKTIF     = 'aktif';
    const STATUS_NONAKTIF  = 'nonaktif';
    const STATUS_SUSPEND   = 'suspend';

    // Legacy constants (deprecated) — jangan gunakan, hanya kompatibilitas
    const STATUS_REGISTERED = 'menunggu';
    const STATUS_ACTIVE = 'aktif';
    const STATUS_INACTIVE = 'nonaktif';
    const STATUS_SUSPENDED = 'suspend';
    const STATUS_DECEASED = 'nonaktif';
    const STATUS_ARCHIVED = 'nonaktif';

    // Volunteer Readiness Machine
    const READINESS_NOT_READY = 'not_ready';
    const READINESS_LIMITED_READY = 'limited_ready';
    const READINESS_READY = 'ready';
    const READINESS_DEPLOYED = 'deployed';
    const READINESS_ON_MISSION = 'on_mission';
    const READINESS_RESTING = 'resting';

    protected $fillable = [
        'id_unit',
        'id_peran',
        'no_hp',
        'is_tersedia',
        'terakhir_masuk',
        'status_akun',
        'status_ketersediaan',
        'default_scope_type',
        'default_scope_id',
        'kata_sandi',
    ];

    protected $casts = [
        'terakhir_masuk' => 'datetime',
        'dibuat_pada' => 'datetime',
        'diperbarui_pada' => 'datetime',
        'is_tersedia' => 'boolean',
    ];

    protected $hidden = [
        'kata_sandi',
    ];

    /**
     * Override method Laravel untuk mengambil password authentikasi.
     */
    public function getAuthPassword()
    {
        return $this->kata_sandi;
    }

    /**
     * Helper status — selalu gunakan method ini, JANGAN hardcode string.
     */
    public function isAktif(): bool
    {
        return $this->status_akun === self::STATUS_AKTIF;
    }

    public function isMenunggu(): bool
    {
        return $this->status_akun === self::STATUS_MENUNGGU;
    }

    public function isSuspended(): bool
    {
        return $this->status_akun === self::STATUS_SUSPEND;
    }

    /**
     * Cek apakah user memiliki role tertentu (string atau array)
     */
    public function hasRole($roles): bool
    {
        $userRole = $this->peran ? $this->peran->nama_peran : null;
        if (!$userRole) {
            return false;
        }

        if (is_array($roles)) {
            return in_array($userRole, $roles);
        }

        return $userRole === $roles;
    }

    /**
     * Relasi BelongsTo ke AuthRole
     */
    public function peran(): BelongsTo
    {
        return $this->belongsTo(AuthRole::class, 'id_peran', 'id_peran');
    }

    /**
     * Semua jabatan yang pernah dipegang user (termasuk yang sudah berakhir)
     */
    public function jabatanPosisi(): HasMany
    {
        return $this->hasMany(PenggunaJabatan::class, 'id_pengguna', 'id_pengguna');
    }

    /**
     * Jabatan yang sedang aktif saat ini
     */
    public function jabatanAktif(): HasMany
    {
        return $this->hasMany(PenggunaJabatan::class, 'id_pengguna', 'id_pengguna')
            ->where('status_aktif', 1)
            ->where(function ($q) {
                $q->whereNull('berakhir_pada')
                  ->orWhereDate('berakhir_pada', '>=', now());
            });
    }

    /**
     * Profil identitas pengguna (1:1).
     * FK: auth_pengguna_profil.id_pengguna → auth_users.id_pengguna (CASCADE)
     */
    public function profil(): HasOne
    {
        return $this->hasOne(AuthPenggunaProfil::class, 'id_pengguna', 'id_pengguna');
    }

    /**
     * Daftar keahlian yang dimiliki pengguna (M:N via auth_pengguna_keahlian).
     * FK: auth_pengguna_keahlian.id_pengguna (CASCADE)
     */
    public function keahlian(): BelongsToMany
    {
        return $this->belongsToMany(
            AuthKeahlianMaster::class,
            'auth_pengguna_keahlian',
            'id_pengguna',
            'id_keahlian'
        )->using(AuthPenggunaKeahlian::class);
    }

    /**
     * Seluruh pendaftaran relawan yang dilakukan pengguna ini.
     * FK: relawan_pendaftaran.id_pengguna → auth_users.id_pengguna
     */
    public function pendaftaranRelawan(): HasMany
    {
        return $this->hasMany(RelawanPendaftaran::class, 'id_pengguna', 'id_pengguna');
    }

    /**
     * Penugasan aktif di insiden.
     */
    public function penugasanAktif(): HasMany
    {
        return $this->hasMany(OperasiPenugasan::class, 'id_pengguna', 'id_pengguna')
            ->where('status_penugasan', 'aktif');
    }

    public function mobileDevices(): HasMany
    {
        return $this->hasMany(MobileDevice::class, 'id_pengguna', 'id_pengguna');
    }
}
