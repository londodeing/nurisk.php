<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
class OrganisasiMandat extends Model
{
    protected $table = 'organisasi_mandat';
        protected $primaryKey = 'id';
protected $fillable = ['sk_id', 'user_id', 'jabatan_id', 'organisasi_id', 'tanggal_mulai', 'tanggal_berakhir', 'status'];
    protected $casts = [
        'tanggal_mulai' => 'date',
        'tanggal_berakhir' => 'date',
    ];
    public function sk() { return $this->belongsTo(OrganisasiSk::class, 'sk_id'); }
    public function user() { return $this->belongsTo(AuthUser::class, 'user_id', 'id_pengguna'); }
}
