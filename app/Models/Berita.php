<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Berita extends Model
{
    protected $table = 'berita';
    protected $primaryKey = 'id_berita';
    const CREATED_AT = 'dibuat_pada';
    const UPDATED_AT = 'diubah_pada';

    protected $fillable = [
        'judul',
        'slug',
        'ringkasan',
        'konten',
        'gambar',
        'sumber',
        'unggulan',
        'published_at',
        'dihapus_pada',
    ];

    protected $casts = [
        'unggulan' => 'boolean',
        'published_at' => 'datetime',
        'dibuat_pada' => 'datetime',
        'diubah_pada' => 'datetime',
        'dihapus_pada' => 'datetime',
    ];
    
    public function scopePublished($query)
    {
        return $query->whereNotNull('published_at')
                     ->where('published_at', '<=', now())
                     ->whereNull('dihapus_pada');
    }
}
