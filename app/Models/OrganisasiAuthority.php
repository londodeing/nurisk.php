<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class OrganisasiAuthority extends Model
{
    protected $table = 'organisasi_authority';
        protected $primaryKey = 'id';
protected $fillable = ['domain', 'code', 'description', 'can_delegate', 'need_dual_approval'];
    protected $casts = [
        'can_delegate' => 'boolean',
        'need_dual_approval' => 'boolean',
    ];
}
