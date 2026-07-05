<?php

namespace App\Models;

use InvalidArgumentException;

class WilayahScope
{
    // Konstanta level scope (urutan hierarki NU)
    public const PWNU    = 'pwnu';
    public const PCNU    = 'pcnu';
    public const MWC     = 'mwc';
    public const RANTING = 'ranting';
    public const LEMBAGA = 'lembaga';
    public const BANOM   = 'banom';

    // Semua nilai enum yang valid (persis sesuai SQL, jangan edit)
    public const ALL_TYPES = ['pwnu', 'pcnu', 'mwc', 'ranting', 'lembaga', 'banom'];

    // Hierarki scope yang memiliki representasi model organisasi
    public const HIERARCHY = ['pwnu', 'pcnu', 'mwc', 'ranting'];

    /**
     * Kembalikan class model Eloquent yang bersesuaian dengan scope_type.
     *
     * @throws InvalidArgumentException jika tipe tidak dikenal
     */
    public static function modelClass(string $tipe): string
    {
        return match ($tipe) {
            self::PWNU    => OrganisasiUnit::class,
            self::PCNU    => OrganisasiPcnu::class,
            self::MWC     => OrganisasiMwc::class,
            self::RANTING => OrganisasiRanting::class,
            default       => throw new InvalidArgumentException("Tipe scope tidak dikenal: {$tipe}"),
        };
    }

    /**
     * Kembalikan nama PK dari model yang bersesuaian.
     */
    public static function primaryKey(string $tipe): string
    {
        return match ($tipe) {
            self::PWNU    => 'id_unit',
            self::PCNU    => 'id_pcnu',
            self::MWC     => 'id_mwc',
            self::RANTING => 'id_ranting',
            default       => throw new InvalidArgumentException("Tipe scope tidak dikenal: {$tipe}"),
        };
    }

    /**
     * Kembalikan label display untuk scope_type.
     */
    public static function label(string $tipe): string
    {
        return match ($tipe) {
            self::PWNU    => 'PWNU Jawa Tengah',
            self::PCNU    => 'PCNU (Cabang)',
            self::MWC     => 'MWC (Majelis Wakil Cabang)',
            self::RANTING => 'Ranting',
            self::LEMBAGA => 'Lembaga',
            self::BANOM   => 'Banom',
            default       => ucfirst($tipe),
        };
    }

    /**
     * Cek apakah scope_type valid.
     */
    public static function isValid(string $tipe): bool
    {
        return in_array($tipe, self::ALL_TYPES, true);
    }

    /**
     * Cek apakah scope_type memiliki hierarki (bukan lembaga/banom).
     */
    public static function isHierarchical(string $tipe): bool
    {
        return in_array($tipe, self::HIERARCHY, true);
    }
}
