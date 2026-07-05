<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
class OrganisasiAuditLog extends Model
{
    protected $table = 'organisasi_audit_log';
        protected $primaryKey = 'id';
protected $fillable = ['actor_id', 'jabatan_snapshot', 'sk_snapshot', 'mandat_id', 'delegasi_id', 'aksi', 'target_table', 'target_id', 'timestamp'];
    protected $casts = [
        'timestamp' => 'datetime',
    ];
    public function actor() { return $this->belongsTo(AuthUser::class, 'actor_id', 'id_pengguna'); }
}
