<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
class OrgSk extends Model
{
    protected $table = 'org_sks';
        protected $primaryKey = 'id';
protected $fillable = ['nomor_sk', 'tanggal_mulai', 'tanggal_berakhir', 'status'];
    protected $casts = [
        'tanggal_mulai' => 'date',
        'tanggal_berakhir' => 'date',
    ];
    public function mandates() { return $this->hasMany(OrgMandate::class, 'sk_id'); }
}
