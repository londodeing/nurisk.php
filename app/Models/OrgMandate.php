<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
class OrgMandate extends Model
{
    protected $table = 'org_mandates';
        protected $primaryKey = 'id';
protected $fillable = ['sk_id', 'user_id', 'node_position_id', 'tanggal_mulai', 'tanggal_berakhir', 'status'];
    protected $casts = [
        'tanggal_mulai' => 'date',
        'tanggal_berakhir' => 'date',
    ];
    public function sk() { return $this->belongsTo(OrgSk::class, 'sk_id'); }
    public function user() { return $this->belongsTo(AuthUser::class, 'user_id', 'id_pengguna'); }
    public function nodePosition() { return $this->belongsTo(OrgNodePosition::class, 'node_position_id'); }
}
