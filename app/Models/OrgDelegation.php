<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
class OrgDelegation extends Model
{
    protected $table = 'org_delegations';
        protected $primaryKey = 'id';
protected $fillable = ['mandat_asal_id', 'mandat_pengganti_id', 'mulai', 'selesai', 'jenis'];
    protected $casts = [
        'mulai' => 'datetime',
        'selesai' => 'datetime',
    ];
    public function mandatAsal() { return $this->belongsTo(OrgMandate::class, 'mandat_asal_id'); }
    public function mandatPengganti() { return $this->belongsTo(OrgMandate::class, 'mandat_pengganti_id'); }
}
