<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
class OrganisasiSk extends Model
{
    protected $table = 'organisasi_sk';
        protected $primaryKey = 'id';
protected $fillable = ['nomor_sk', 'tanggal_terbit', 'tanggal_mulai', 'tanggal_berakhir', 'dokumen_file', 'status'];
    protected $casts = [
        'tanggal_terbit' => 'date',
        'tanggal_mulai' => 'date',
        'tanggal_berakhir' => 'date',
    ];
    public function mandats() { return $this->hasMany(OrganisasiMandat::class, 'sk_id'); }
    public function pengurus() { return $this->hasMany(OrganisasiSkPengurus::class, 'sk_id'); }
}
