<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
class OrganisasiSkPengurus extends Model
{
    protected $table = 'organisasi_sk_pengurus';
        protected $primaryKey = 'id';
protected $fillable = ['sk_id', 'auth_user_id', 'jabatan_id'];
    public function sk() { return $this->belongsTo(OrganisasiSk::class, 'sk_id'); }
    public function user() { return $this->belongsTo(AuthUser::class, 'auth_user_id', 'id_pengguna'); }
}
